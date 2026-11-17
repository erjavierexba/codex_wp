<?php
namespace OldBearStudio;
if (!defined('ABSPATH')) { exit; }

final class Catalog {
    public static function boot(): void {
        add_action('rest_api_init', [self::class, 'routes']);
        add_action('save_post_oldbear_game', [self::class,'invalidate']);
        add_action('before_delete_post', static function($id) { if (get_post_type($id) === 'oldbear_game') { self::invalidate(); } });
        foreach (['added_post_meta','updated_post_meta','deleted_post_meta'] as $hook) {
            add_action($hook, static function($meta_id, $id, $key) {
                if (get_post_type($id) === 'oldbear_game' && (str_starts_with($key, 'oldbear_') || str_starts_with($key, '_ob_') || $key === '_thumbnail_id')) { self::invalidate(); }
            }, 10, 3);
        }
        add_action('set_object_terms', static function($id, $terms, $tt_ids, $taxonomy) {
            if (in_array($taxonomy, ['ob_genre','ob_platform'], true)) { self::invalidate(); }
        }, 10, 4);
        foreach (['edited_term','delete_term'] as $hook) {
            add_action($hook, static function($id, $tt_id, $taxonomy) {
                if (in_array($taxonomy, ['ob_genre','ob_platform'], true)) { self::invalidate(); }
            }, 10, 3);
        }
    }
    public static function invalidate(...$unused): void {
        update_option('oldbear_catalog_version', wp_generate_uuid4(), false);
    }
    public static function routes(): void {
        $text = ['type'=>'string','default'=>'','sanitize_callback'=>'sanitize_text_field','validate_callback'=>static function($v) { return is_string($v) && strlen($v) <= 80; }];
        register_rest_route('oldbear/v1', '/games', [
            'methods'=>'GET','permission_callback'=>'__return_true','callback'=>[self::class,'endpoint'],
            'args'=>[
                'search'=>$text,'genre'=>$text,'platform'=>$text,
                'page'=>['type'=>'integer','default'=>1,'minimum'=>1,'maximum'=>1000],
                'per_page'=>['type'=>'integer','default'=>6,'minimum'=>1,'maximum'=>12]
            ]
        ]);
    }
    public static function endpoint(\WP_REST_Request $request): \WP_REST_Response {
        $data = self::query($request->get_params());
        $data['html'] = self::cards($data);
        $response = new \WP_REST_Response($data);
        $response->header('X-WP-Total', $data['total']);
        $response->header('X-WP-TotalPages', $data['pages']);
        return $response;
    }
    public static function query(array $raw = []): array {
        $args = [
            'search'=>sanitize_text_field(is_string($raw['search'] ?? null) ? substr($raw['search'], 0, 80) : ''),
            'genre'=>sanitize_title(is_string($raw['genre'] ?? null) ? $raw['genre'] : ''),
            'platform'=>sanitize_title(is_string($raw['platform'] ?? null) ? $raw['platform'] : ''),
            'page'=>max(1, min(1000, absint($raw['page'] ?? 1))),
            'per_page'=>max(1, min(12, absint($raw['per_page'] ?? 6)))
        ];
        $key = 'ob_catalog_' . md5(wp_json_encode([$args, get_locale(), get_option('oldbear_catalog_version', '1')]));
        $cached = get_transient($key);
        if (is_array($cached)) { return $cached; }
        $query = ['post_type'=>'oldbear_game','post_status'=>'publish','has_password'=>false,'posts_per_page'=>$args['per_page'],'paged'=>$args['page'],'s'=>$args['search'],'orderby'=>['menu_order'=>'ASC','date'=>'DESC']];
        foreach (['genre'=>'ob_genre','platform'=>'ob_platform'] as $name=>$taxonomy) {
            if ($args[$name]) { $query['tax_query'][] = ['taxonomy'=>$taxonomy,'field'=>'slug','terms'=>$args[$name]]; }
        }
        $posts = new \WP_Query($query);
        $items = [];
        foreach ($posts->posts as $post) {
            $terms = static function($taxonomy) use ($post) {
                $value = get_the_terms($post->ID, $taxonomy);
                return is_array($value) ? array_map(static fn($term)=>$term->name, $value) : [];
            };
            $items[] = [
                'id'=>$post->ID,'title'=>get_the_title($post),'excerpt'=>wp_strip_all_tags($post->post_excerpt ?: wp_trim_words($post->post_content, 28)),
                'url'=>get_permalink($post),'cover'=>get_the_post_thumbnail_url($post,'large') ?: Kit::art((string) get_post_meta($post->ID, '_ob_art', true)),
                'status'=>get_post_meta($post->ID, 'oldbear_status', true) ?: 'En desarrollo',
                'genres'=>$terms('ob_genre'),'platforms'=>$terms('ob_platform'),
                'demo'=>(bool) get_post_meta($post->ID, '_ob_demo_key', true)
            ];
        }
        $data = ['items'=>$items,'total'=>(int) $posts->found_posts,'pages'=>(int) $posts->max_num_pages,'page'=>$args['page'],'filters'=>$args];
        set_transient($key, $data, 5 * MINUTE_IN_SECONDS);
        return $data;
    }
    public static function cards(array $data): string {
        ob_start();
        echo '<div class="ob-cards">';
        foreach ($data['items'] as $item) {
            echo '<article class="ob-game"><a class="ob-cover" href="' . esc_url($item['url']) . '"><img src="' . esc_url($item['cover']) . '" alt="" loading="lazy" width="960" height="640"><span class="screen-reader-text">' . esc_html($item['title']) . '</span>';
            if ($item['demo']) { echo '<span class="ob-demo-label">CONCEPTO VISUAL · MUESTRA</span>'; }
            echo '</a><p class="ob-game-tags"><span class="ob-badge">' . esc_html($item['status']) . '</span> ' . esc_html(implode(' · ', $item['genres'])) . '</p><h3><a href="' . esc_url($item['url']) . '">' . esc_html($item['title']) . '</a></h3><p>' . esc_html($item['excerpt']) . '</p><small>' . esc_html(implode(' / ', $item['platforms'])) . '</small></article>';
        }
        if (!$data['items']) { echo '<p class="ob-empty">No hay juegos que coincidan. Prueba otros filtros.</p>'; }
        echo '</div>';
        if ($data['pages'] > 1) {
            echo '<nav class="ob-pagination" aria-label="Páginas del catálogo">';
            $start = max(1, $data['page'] - 2);
            $end = min($data['pages'], $data['page'] + 2);
            for ($page=$start; $page<=$end; $page++) {
                $url = add_query_arg(['ob_page'=>$page,'ob_search'=>$data['filters']['search'],'ob_genre'=>$data['filters']['genre'],'ob_platform'=>$data['filters']['platform']], get_permalink() ?: home_url('/'));
                echo '<a data-ob-page="' . absint($page) . '" href="' . esc_url($url . '#juegos') . '"' . ($page === $data['page'] ? ' aria-current="page"' : '') . '>' . absint($page) . '</a>';
            }
            echo '</nav>';
        }
        return ob_get_clean();
    }
    public static function shortcode($attributes = []): string {
        $attributes = shortcode_atts(['per_page'=>6,'filters'=>'yes'], $attributes, 'oldbear_games');
        $args = ['per_page'=>absint($attributes['per_page'])];
        foreach (['search','genre','platform','page'] as $key) {
            $value = $_GET['ob_' . $key] ?? '';
            if (is_string($value) && $value !== '') { $args[$key] = wp_unslash($value); }
        }
        $data = self::query($args);
        $id = wp_unique_id('ob-catalog-');
        ob_start();
        echo '<div class="ob-kit ob-catalog" data-ob-catalog data-endpoint="' . esc_url(rest_url('oldbear/v1/games')) . '" data-per-page="' . max(1, min(12, absint($attributes['per_page']))) . '">';
        if ($attributes['filters'] !== 'no') {
            echo '<form class="ob-filters" method="get"><label for="' . esc_attr($id) . '-search">Buscar juego<input id="' . esc_attr($id) . '-search" name="ob_search" type="search" maxlength="80" value="' . esc_attr($args['search'] ?? '') . '" placeholder="Un mundo por descubrir…"></label>';
            foreach (['genre'=>['ob_genre','Género'],'platform'=>['ob_platform','Plataforma']] as $key=>$settings) {
                echo '<label for="' . esc_attr($id . '-' . $key) . '">' . esc_html($settings[1]) . '<select id="' . esc_attr($id . '-' . $key) . '" name="ob_' . esc_attr($key) . '"><option value="">Todos</option>';
                $terms = get_terms(['taxonomy'=>$settings[0],'hide_empty'=>true]);
                if (is_array($terms)) {
                    foreach ($terms as $term) { echo '<option value="' . esc_attr($term->slug) . '"' . selected($args[$key] ?? '', $term->slug, false) . '>' . esc_html($term->name) . '</option>'; }
                }
                echo '</select></label>';
            }
            echo '<button type="submit" class="ob-button">Explorar</button><button type="reset" class="ob-reset">Limpiar</button></form>';
        }
        echo '<p class="ob-result-count" role="status" aria-live="polite">' . absint($data['total']) . ' juegos encontrados</p><div class="ob-results" aria-busy="false">' . self::cards($data) . '</div></div>';
        return ob_get_clean();
    }
}
