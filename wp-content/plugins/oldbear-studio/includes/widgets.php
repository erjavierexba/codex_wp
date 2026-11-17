<?php
namespace OldBearStudio;
use Elementor\Controls_Manager as Controls;
if (!defined('ABSPATH') || !class_exists('Elementor\\Widget_Base')) { exit; }

abstract class Studio_Widget extends \Elementor\Widget_Base {
    public function get_categories(): array { return ['oldbear-studio']; }
    public function get_icon(): string { return 'eicon-gamepad'; }
    public function get_style_depends(): array { return ['oldbear-studio-kit']; }
    public function get_script_depends(): array { return ['oldbear-studio-kit']; }
    protected function is_dynamic_content(): bool { return true; }
    protected function register_controls(): void {
        $this->start_controls_section('content', ['label'=>'Contenido']);
        $this->fields();
        $this->end_controls_section();
        $this->start_controls_section('appearance', ['label'=>'Diseño','tab'=>Controls::TAB_STYLE]);
        $this->add_control('accent', ['label'=>'Color de acento','type'=>Controls::COLOR,'selectors'=>['{{WRAPPER}} .ob-kit'=>'--ob-accent: {{VALUE}};']]);
        $this->add_responsive_control('text_size', ['label'=>'Tamaño del titular','type'=>Controls::SLIDER,'size_units'=>['px','rem'],'range'=>['px'=>['min'=>20,'max'=>110],'rem'=>['min'=>1,'max'=>7]],'selectors'=>['{{WRAPPER}} .ob-widget-title, {{WRAPPER}} .ob-game h3, {{WRAPPER}} .ob-lane h3'=>'font-size: {{SIZE}}{{UNIT}};']]);
        $this->end_controls_section();
    }
    abstract protected function fields(): void;
    protected function text(string $name, string $label, string $default = '', bool $area = false): void {
        $this->add_control($name, ['label'=>$label,'type'=>$area ? Controls::TEXTAREA : Controls::TEXT,'default'=>$default,'label_block'=>true]);
    }
}
final class Hero_Widget extends Studio_Widget {
    public function get_name(): string { return 'oldbear-hero'; }
    public function get_title(): string { return 'OldBear · Portada'; }
    protected function fields(): void {
        $this->text('eyebrow','Etiqueta','ESTUDIO INDEPENDIENTE DE VIDEOJUEGOS');
        $this->text('title','Titular','Pequeño estudio. Grandes aventuras.');
        $this->text('copy','Descripción','Creamos mundos a los que apetece volver. Juegos con carácter, hechos con curiosidad, mimo y un punto de instinto.',true);
        $this->add_control('image', ['label'=>'Ilustración','type'=>Controls::MEDIA,'default'=>['url'=>Kit::art('hero')]]);
        $this->text('image_alt','Texto alternativo','Un oso contempla un valle de montañas bajo un gran sol ámbar.');
        $this->text('button','Texto del botón','Explora nuestros mundos');
        $this->add_control('link', ['label'=>'Enlace del botón','type'=>Controls::URL,'default'=>['url'=>'#juegos']]);
        $this->text('footnote','Nota breve','Ideas propias. Pasión por jugar.');
    }
    protected function render(): void {
        $s = $this->get_settings_for_display();
        echo '<section class="ob-kit ob-hero"><div><p class="ob-eyebrow">' . esc_html($s['eyebrow']) . '</p><h1 class="ob-widget-title">' . esc_html($s['title']) . '</h1><p class="ob-lead">' . esc_html($s['copy']) . '</p>';
        if (!empty($s['link']['url'])) {
            echo '<a class="ob-button" href="' . esc_url($s['link']['url']) . '"' . (!empty($s['link']['is_external']) ? ' target="_blank" rel="noopener noreferrer"' : '') . '>' . esc_html($s['button']) . '<span aria-hidden="true">→</span></a>';
        }
        echo '<p class="ob-footnote">' . esc_html($s['footnote']) . '</p></div>';
        if (!empty($s['image']['url'])) { echo '<figure><img src="' . esc_url($s['image']['url']) . '" alt="' . esc_attr($s['image_alt']) . '" width="900" height="1000" fetchpriority="high"><figcaption>THE NEXT ADVENTURE STARTS HERE</figcaption></figure>'; }
        echo '</section>';
    }
}
final class Catalog_Widget extends Studio_Widget {
    public function get_name(): string { return 'oldbear-catalog'; }
    public function get_title(): string { return 'OldBear · Catálogo de juegos'; }
    protected function fields(): void {
        $this->add_control('per_page', ['label'=>'Juegos por página','type'=>Controls::NUMBER,'min'=>1,'max'=>12,'default'=>6]);
        $this->add_control('filters', ['label'=>'Mostrar filtros','type'=>Controls::SWITCHER,'return_value'=>'yes','default'=>'yes']);
    }
    protected function render(): void {
        $s=$this->get_settings_for_display();
        echo Catalog::shortcode(['per_page'=>$s['per_page'],'filters'=>$s['filters'] === 'yes' ? 'yes' : 'no']);
    }
}
final class Roadmap_Widget extends Studio_Widget {
    public function get_name(): string { return 'oldbear-roadmap'; }
    public function get_title(): string { return 'OldBear · Roadmap'; }
    protected function fields(): void {
        $options=[0=>'Todo el estudio'];
        foreach (get_posts(['post_type'=>'oldbear_game','posts_per_page'=>100,'post_status'=>'publish']) as $game) { $options[$game->ID]=$game->post_title; }
        $this->add_control('game', ['label'=>'Juego','type'=>Controls::SELECT,'options'=>$options,'default'=>0]);
        $this->add_control('limit', ['label'=>'Máximo de hitos','type'=>Controls::NUMBER,'min'=>1,'max'=>60,'default'=>30]);
    }
    protected function render(): void {
        $s=$this->get_settings_for_display();
        echo Content::roadmap(['game'=>$s['game'],'limit'=>$s['limit']]);
    }
}
final class Contact_Widget extends Studio_Widget {
    public function get_name(): string { return 'oldbear-contact'; }
    public function get_title(): string { return 'OldBear · Contacto'; }
    protected function fields(): void {
        $this->text('title','Titular','Toda gran aventura empieza con un hola.');
        $this->text('copy','Descripción','¿Tienes una idea, quieres colaborar o simplemente saludar? Aquí empieza la conversación.',true);
    }
    protected function render(): void {
        $s=$this->get_settings_for_display();
        echo '<div class="ob-kit ob-contact"><div><p class="ob-eyebrow">HABLEMOS</p><h2 class="ob-widget-title">' . esc_html($s['title']) . '</h2><p class="ob-lead">' . esc_html($s['copy']) . '</p></div><div class="ob-contact-form">' . oldbear_render_contact() . '</div></div>';
    }
}
final class Brand_Widget extends Studio_Widget {
    public function get_name(): string { return 'oldbear-brand'; }
    public function get_title(): string { return 'OldBear · Marca'; }
    public function get_icon(): string { return 'eicon-site-logo'; }
    protected function fields(): void {
        $this->add_control('image', ['label'=>'Emblema','type'=>Controls::MEDIA,'default'=>['url'=>Kit::art('logo')],'description'=>'Si hay un logotipo en Apariencia → Personalizar, se usa ese.']);
        $this->text('tagline','Lema bajo el nombre','INDEPENDENT GAME STUDIO');
    }
    protected function render(): void {
        $s = $this->get_settings_for_display();
        if (has_custom_logo()) { echo '<div class="ob-brand">' . get_custom_logo() . '</div>'; return; }
        $name = get_bloginfo('name');
        echo '<a class="brand ob-brand" href="' . esc_url(home_url('/')) . '" rel="home" aria-label="' . esc_attr($name . ', inicio') . '">';
        if (!empty($s['image']['url'])) { echo '<img src="' . esc_url($s['image']['url']) . '" width="44" height="44" alt="">'; }
        echo '<span>' . esc_html($name) . ($s['tagline'] !== '' ? '<small>' . esc_html($s['tagline']) . '</small>' : '') . '</span></a>';
    }
}
final class Nav_Widget extends Studio_Widget {
    public function get_name(): string { return 'oldbear-nav'; }
    public function get_title(): string { return 'OldBear · Menú'; }
    public function get_icon(): string { return 'eicon-nav-menu'; }
    protected function fields(): void {
        $options = [''=>'Menú asignado a «Navegación principal»'];
        foreach (wp_get_nav_menus() as $menu) { $options[(string) $menu->term_id] = $menu->name; }
        $this->add_control('menu', ['label'=>'Menú','type'=>Controls::SELECT,'options'=>$options,'default'=>'','description'=>'Los enlaces se gestionan en Apariencia → Menús.']);
        $this->text('toggle','Texto del botón móvil','Menú');
    }
    public static function fallback(): void {
        $journal = Layout::journal_url();
        $links = ['juegos'=>['Juegos', home_url('/#juegos')],'estudio'=>['El estudio', home_url('/#estudio')],'diario'=>['Diario', $journal ?: home_url('/#diario')],'contacto'=>['Hablemos', home_url('/#contacto')]];
        echo '<ul class="menu">';
        foreach ($links as $key=>$link) {
            echo '<li><a href="' . esc_url($link[1]) . '">' . esc_html($link[0]) . ($key === 'contacto' ? ' ' . Kit::arrow() : '') . '</a></li>';
        }
        echo '</ul>';
    }
    protected function render(): void {
        $s = $this->get_settings_for_display();
        $args = ['container'=>false,'fallback_cb'=>false,'depth'=>1,'echo'=>false];
        if (!empty($s['menu'])) { $args['menu'] = absint($s['menu']); } else { $args['theme_location'] = 'primary'; }
        $menu = (!empty($s['menu']) || has_nav_menu('primary')) ? wp_nav_menu($args) : '';
        if (!$menu) { ob_start(); self::fallback(); $menu = ob_get_clean(); }
        echo '<div class="ob-nav"><button class="menu-toggle" aria-controls="site-nav" aria-expanded="false" hidden>' . esc_html($s['toggle']) . ' <span aria-hidden="true">☰</span></button><nav id="site-nav" aria-label="Navegación principal">' . $menu . '</nav></div>';
    }
}
final class Social_Widget extends Studio_Widget {
    public function get_name(): string { return 'oldbear-social'; }
    public function get_title(): string { return 'OldBear · Redes'; }
    public function get_icon(): string { return 'eicon-social-icons'; }
    public const NETWORKS = ['steam'=>'Steam','itch'=>'itch.io','discord'=>'Discord'];
    protected function fields(): void {
        foreach (self::NETWORKS as $key=>$label) {
            $this->add_control($key, ['label'=>'URL de ' . $label,'type'=>Controls::URL,'default'=>['url'=>''],'description'=>'Vacío: se usa la de Apariencia → Personalizar, si existe.']);
        }
    }
    protected function render(): void {
        $s = $this->get_settings_for_display();
        $links = '';
        foreach (self::NETWORKS as $key=>$label) {
            $url = $s[$key]['url'] ?? '';
            if ($url === '') { $url = (string) get_theme_mod('oldbear_' . $key . '_url', ''); }
            if ($url !== '') { $links .= '<a href="' . esc_url($url) . '">' . esc_html($label) . ' ' . Kit::arrow() . '</a>'; }
        }
        if ($links !== '') { echo '<div class="social-links ob-social">' . $links . '</div>'; }
        elseif ($this->is_editor()) { echo '<p class="ob-editor-hint">Añade aquí las URL de tus redes. Sin enlaces, el bloque no se muestra.</p>'; }
    }
    private function is_editor(): bool { return \Elementor\Plugin::$instance->editor->is_edit_mode() || \Elementor\Plugin::$instance->preview->is_preview_mode(); }
}
final class Credits_Widget extends Studio_Widget {
    public function get_name(): string { return 'oldbear-credits'; }
    public function get_title(): string { return 'OldBear · Créditos del pie'; }
    public function get_icon(): string { return 'eicon-footer'; }
    protected function fields(): void {
        $this->text('copyright','Texto legal','© {year} {site}.');
        $this->add_control('copyright_help', ['type'=>Controls::RAW_HTML,'raw'=>'<small>{year} = año actual · {site} = nombre del sitio</small>']);
        $this->text('privacy','Enlace a privacidad','Privacidad');
        $this->text('top','Volver arriba','Volver arriba ↑');
    }
    protected function render(): void {
        $s = $this->get_settings_for_display();
        $text = strtr((string) $s['copyright'], ['{year}'=>wp_date('Y'),'{site}'=>get_bloginfo('name')]);
        echo '<div class="ob-credits"><small>' . esc_html($text) . '</small><div>';
        if ($s['privacy'] !== '' && get_privacy_policy_url()) { echo '<a href="' . esc_url(get_privacy_policy_url()) . '">' . esc_html($s['privacy']) . '</a>'; }
        if ($s['top'] !== '') { echo '<a href="#contenido">' . esc_html($s['top']) . '</a>'; }
        echo '</div></div>';
    }
}
final class Journal_Widget extends Studio_Widget {
    public function get_name(): string { return 'oldbear-journal'; }
    public function get_title(): string { return 'OldBear · Diario'; }
    public function get_icon(): string { return 'eicon-posts-grid'; }
    protected function fields(): void {
        $this->add_control('per_page', ['label'=>'Entradas por página','type'=>Controls::NUMBER,'min'=>1,'max'=>24,'default'=>9]);
        $this->add_control('pagination', ['label'=>'Paginación','type'=>Controls::SWITCHER,'return_value'=>'yes','default'=>'yes']);
        $this->add_control('images', ['label'=>'Mostrar imagen destacada','type'=>Controls::SWITCHER,'return_value'=>'yes','default'=>'yes']);
        $this->text('empty_title','Titular sin entradas','Las buenas historias también se cuentan por el camino.');
        $this->text('empty_text','Texto sin entradas','Próximamente: procesos creativos, avances y pequeñas historias del desarrollo.', true);
    }
    protected function render(): void {
        $s = $this->get_settings_for_display();
        $paged = $s['pagination'] === 'yes' ? max(1, absint($_GET['ob_diario'] ?? 1)) : 1;
        $query = new \WP_Query(['post_type'=>'post','post_status'=>'publish','posts_per_page'=>max(1, min(24, absint($s['per_page']))),'paged'=>$paged,'ignore_sticky_posts'=>true,'no_found_rows'=>$s['pagination'] !== 'yes']);
        echo '<div class="ob-kit ob-journal">';
        if (!$query->have_posts()) {
            echo '<div class="journal-empty"><span aria-hidden="true">✳</span><div><h3>' . esc_html($s['empty_title']) . '</h3><p>' . esc_html($s['empty_text']) . '</p></div></div></div>';
            return;
        }
        echo '<div class="journal-grid">';
        foreach ($query->posts as $post) {
            $url = get_permalink($post);
            echo '<article class="journal-card">';
            if ($s['images'] === 'yes' && has_post_thumbnail($post)) { echo '<a href="' . esc_url($url) . '" tabindex="-1" aria-hidden="true">' . get_the_post_thumbnail($post, 'medium_large', ['loading'=>'lazy','alt'=>'']) . '</a>'; }
            echo '<p class="eyebrow"><time datetime="' . esc_attr(get_the_date('c', $post)) . '">' . esc_html(get_the_date('', $post)) . '</time></p><h3><a href="' . esc_url($url) . '">' . esc_html(get_the_title($post)) . ' ' . Kit::arrow() . '</a></h3><p>' . esc_html(wp_strip_all_tags(get_the_excerpt($post))) . '</p></article>';
        }
        echo '</div>';
        if ($s['pagination'] === 'yes' && $query->max_num_pages > 1) {
            echo '<nav class="ob-pagination" aria-label="Páginas del diario">';
            for ($page = 1; $page <= $query->max_num_pages; $page++) {
                echo '<a href="' . esc_url(add_query_arg('ob_diario', $page, get_permalink() ?: home_url('/'))) . '"' . ($page === $paged ? ' aria-current="page"' : '') . '>' . absint($page) . '</a>';
            }
            echo '</nav>';
        }
        echo '</div>';
    }
}
