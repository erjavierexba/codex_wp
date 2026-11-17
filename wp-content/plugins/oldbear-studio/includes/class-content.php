<?php
namespace OldBearStudio;
if (!defined('ABSPATH')) { exit; }

final class Content {
    public const STAGES = ['planned'=>'Por explorar','active'=>'En desarrollo','done'=>'Completado'];
    public static function boot(): void {
        add_action('init', [self::class, 'register']);
        add_action('add_meta_boxes', [self::class, 'boxes']);
        add_action('save_post_ob_milestone', [self::class, 'save']);
    }
    public static function register(): void {
        register_taxonomy('ob_genre', 'oldbear_game', [
            'labels'=>['name'=>'Géneros','singular_name'=>'Género'],'hierarchical'=>true,
            'public'=>true,'show_in_rest'=>true,'rewrite'=>['slug'=>'genero']
        ]);
        register_taxonomy('ob_platform', 'oldbear_game', [
            'labels'=>['name'=>'Plataformas','singular_name'=>'Plataforma'],'hierarchical'=>true,
            'public'=>true,'show_in_rest'=>true,'rewrite'=>['slug'=>'plataforma']
        ]);
        register_post_type('ob_milestone', [
            'labels'=>['name'=>'Roadmap','singular_name'=>'Hito','add_new_item'=>'Añadir hito','edit_item'=>'Editar hito'],
            'public'=>true,'publicly_queryable'=>false,'exclude_from_search'=>true,
            'show_ui'=>true,'show_in_menu'=>'oldbear-studio','show_in_rest'=>true,
            'supports'=>['title','editor','excerpt','revisions'],'menu_icon'=>'dashicons-editor-ol'
        ]);
        foreach ([
            'ob_stage'=>['type'=>'string','default'=>'planned','sanitize_callback'=>[self::class,'stage']],
            'ob_game_id'=>['type'=>'integer','default'=>0,'sanitize_callback'=>'absint']
        ] as $key=>$args) {
            register_post_meta('ob_milestone', $key, $args + [
                'single'=>true,'show_in_rest'=>true,
                'auth_callback'=>static function($allowed, $meta_key, $post_id) { return current_user_can('edit_post', $post_id); }
            ]);
        }
    }
    public static function stage($value): string {
        return is_string($value) && isset(self::STAGES[$value]) ? $value : 'planned';
    }
    public static function boxes(): void {
        add_meta_box('ob_milestone_details', 'Progreso del desarrollo', [self::class,'box'], 'ob_milestone');
    }
    public static function box($post): void {
        wp_nonce_field('ob_milestone_save', 'ob_milestone_nonce');
        echo '<p><label for="ob-stage">Estado</label><select class="widefat" id="ob-stage" name="ob_stage">';
        foreach (self::STAGES as $value=>$label) {
            echo '<option value="' . esc_attr($value) . '" ' . selected(get_post_meta($post->ID, 'ob_stage', true) ?: 'planned', $value, false) . '>' . esc_html($label) . '</option>';
        }
        echo '</select></p><p><label for="ob-game">Juego relacionado</label><select class="widefat" id="ob-game" name="ob_game_id"><option value="0">Estudio completo</option>';
        foreach (get_posts(['post_type'=>'oldbear_game','posts_per_page'=>100,'post_status'=>'publish']) as $game) {
            echo '<option value="' . absint($game->ID) . '" ' . selected(get_post_meta($post->ID, 'ob_game_id', true), $game->ID, false) . '>' . esc_html($game->post_title) . '</option>';
        }
        echo '</select></p>';
    }
    public static function save($id): void {
        if ((defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) || wp_is_post_revision($id) || !current_user_can('edit_post', $id) || !wp_verify_nonce(oldbear_post_value('ob_milestone_nonce'), 'ob_milestone_save')) { return; }
        update_post_meta($id, 'ob_stage', self::stage(oldbear_post_value('ob_stage')));
        $game = absint(oldbear_post_value('ob_game_id'));
        update_post_meta($id, 'ob_game_id', get_post_type($game) === 'oldbear_game' ? $game : 0);
    }
    public static function roadmap($attributes = []): string {
        $attributes = shortcode_atts(['game'=>0,'limit'=>30], $attributes, 'oldbear_roadmap');
        $args = ['post_type'=>'ob_milestone','post_status'=>'publish','posts_per_page'=>max(1, min(60, absint($attributes['limit']))),'orderby'=>['menu_order'=>'ASC','date'=>'ASC'],'no_found_rows'=>true];
        if (absint($attributes['game'])) { $args['meta_query'] = [['key'=>'ob_game_id','value'=>absint($attributes['game']),'compare'=>'=','type'=>'NUMERIC']]; }
        $posts = get_posts($args);
        $lanes = array_fill_keys(array_keys(self::STAGES), []);
        foreach ($posts as $post) { $lanes[self::stage(get_post_meta($post->ID, 'ob_stage', true))][] = $post; }
        $done = count($lanes['done']);
        $total = count($posts);
        ob_start();
        echo '<div class="ob-kit ob-roadmap"><p class="ob-roadmap-summary">' . esc_html($total ? sprintf('%d de %d hitos completados', $done, $total) : 'El próximo capítulo todavía se está escribiendo.') . '</p>';
        if ($total) { echo '<progress max="' . absint($total) . '" value="' . absint($done) . '" aria-label="Hitos completados"></progress>'; }
        echo '<div class="ob-roadmap-lanes">';
        foreach (self::STAGES as $key=>$label) {
            echo '<section class="ob-lane ob-lane-' . esc_attr($key) . '"><h3><span class="ob-dot"></span>' . esc_html($label) . '<small>' . count($lanes[$key]) . '</small></h3>';
            foreach ($lanes[$key] as $post) {
                echo '<article class="ob-milestone">';
                if (get_post_meta($post->ID, '_ob_demo_key', true)) { echo '<span class="ob-badge">DEMO</span>'; }
                echo '<h4>' . esc_html($post->post_title) . '</h4><p>' . esc_html(wp_strip_all_tags($post->post_excerpt ?: wp_trim_words($post->post_content, 24))) . '</p></article>';
            }
            if (!$lanes[$key]) { echo '<p class="ob-lane-empty">Sin hitos publicados en esta fase.</p>'; }
            echo '</section>';
        }
        echo '</div></div>';
        return ob_get_clean();
    }
}
