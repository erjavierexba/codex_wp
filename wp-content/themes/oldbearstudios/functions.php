<?php
if (!defined('ABSPATH')) { exit; }

function oldbear_setup() {
    load_theme_textdomain('oldbearstudios', get_template_directory() . '/languages');
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('custom-logo', ['height'=>80,'width'=>280,'flex-height'=>true,'flex-width'=>true]);
    add_theme_support('html5', ['search-form','comment-form','comment-list','gallery','caption','style','script']);
    add_theme_support('responsive-embeds');
    add_theme_support('align-wide');
    add_theme_support('editor-styles');
    add_editor_style('assets/editor.css');
    register_nav_menus(['primary'=>__('Navegación principal', 'oldbearstudios')]);
}
add_action('after_setup_theme', 'oldbear_setup');

function oldbear_assets() {
    $version = wp_get_theme()->get('Version');
    wp_enqueue_style('oldbear', get_theme_file_uri('assets/site.css'), [], $version);
    wp_enqueue_script('oldbear', get_theme_file_uri('assets/site.js'), [], $version, ['strategy'=>'defer','in_footer'=>true]);
}
add_action('wp_enqueue_scripts', 'oldbear_assets');

function oldbear_defaults() {
    return [
        'eyebrow'=>'ESTUDIO INDEPENDIENTE DE VIDEOJUEGOS',
        'hero_title'=>'Pequeño estudio. Grandes aventuras.',
        'hero_text'=>'Creamos mundos a los que apetece volver. Juegos con carácter, hechos con curiosidad, mimo y un punto de instinto.',
        'studio_title'=>'El instinto de crear. La paciencia de un oso.',
        'studio_text'=>'OldBearStudios nace de una idea sencilla: hacer los juegos que nos gustaría descubrir. Explorar una mecánica hasta encontrar la chispa, dar personalidad a cada mundo y cuidar esos pequeños detalles que se quedan contigo.',
        'studio_note'=>'Nos mueve lo que ocurre al otro lado de la pantalla: una decisión difícil, un descubrimiento inesperado y las ganas de jugar una vez más.',
        'contact_text'=>'¿Tienes una idea, quieres colaborar o simplemente saludar? Aquí empieza la conversación.',
        'public_email'=>'','steam_url'=>'','itch_url'=>'','discord_url'=>'','trailer_url'=>''
    ];
}
function oldbear_text($key) {
    $defaults = oldbear_defaults();
    return (string) get_theme_mod('oldbear_' . $key, $defaults[$key] ?? '');
}
function oldbear_customize($customizer) {
    $customizer->add_section('oldbear', ['title'=>__('OldBearStudios', 'oldbearstudios'),'priority'=>30]);
    $labels = [
        'eyebrow'=>'Etiqueta del estudio','hero_title'=>'Titular principal','hero_text'=>'Introducción',
        'studio_title'=>'Titular del estudio','studio_text'=>'Sobre el estudio','studio_note'=>'Nuestra visión',
        'contact_text'=>'Introducción de contacto','public_email'=>'Correo público (opcional)',
        'steam_url'=>'URL de Steam (opcional)','itch_url'=>'URL de itch.io (opcional)',
        'discord_url'=>'URL de Discord (opcional)','trailer_url'=>'URL del tráiler (opcional)'
    ];
    foreach (oldbear_defaults() as $key=>$default) {
        $is_url = str_ends_with($key, '_url');
        $is_email = $key === 'public_email';
        $sanitize = $is_url ? 'esc_url_raw' : ($is_email ? 'sanitize_email' : 'sanitize_textarea_field');
        $id = 'oldbear_' . $key;
        $customizer->add_setting($id, ['default'=>$default,'sanitize_callback'=>$sanitize,'transport'=>'refresh']);
        $customizer->add_control($id, ['section'=>'oldbear','label'=>$labels[$key], 'type'=>$is_url ? 'url' : ($is_email ? 'email' : 'textarea')]);
    }
}
add_action('customize_register', 'oldbear_customize');

function oldbear_brand() {
    if (has_custom_logo()) { the_custom_logo(); return; }
    ?>
    <a class="brand" href="<?php echo esc_url(home_url('/')); ?>" rel="home" aria-label="<?php echo esc_attr(get_bloginfo('name')); ?>, inicio">
        <img src="<?php echo esc_url(get_theme_file_uri('assets/bear-mark.svg')); ?>" width="44" height="44" alt="" />
        <span><?php echo esc_html(get_bloginfo('name')); ?><small>INDEPENDENT GAME STUDIO</small></span>
    </a>
    <?php
}
function oldbear_link($section) { return home_url('/#' . $section); }
function oldbear_fallback_menu() {
    echo '<ul class="menu">';
    foreach (['juegos'=>'Juegos','estudio'=>'El estudio','diario'=>'Diario','contacto'=>'Hablemos'] as $id=>$label) {
        echo '<li><a href="' . esc_url(oldbear_link($id)) . '">' . esc_html($label) . ($id === 'contacto' ? ' ' . oldbear_arrow_icon() : '') . '</a></li>';
    }
    echo '</ul>';
}
function oldbear_meta() {
    if (defined('WPSEO_VERSION') || defined('RANK_MATH_VERSION') || defined('AIOSEO_VERSION')) { return; }
    $description = is_singular() && has_excerpt() ? wp_strip_all_tags(get_the_excerpt()) : oldbear_text('hero_text');
    $title = wp_get_document_title();
    $url = is_singular() ? get_permalink() : (is_front_page() ? home_url('/') : '');
    echo '<meta name="description" content="' . esc_attr($description) . '">' . "\n";
    echo '<meta property="og:title" content="' . esc_attr($title) . '">' . "\n";
    echo '<meta property="og:description" content="' . esc_attr($description) . '">' . "\n";
    echo '<meta property="og:type" content="' . (is_singular('post') ? 'article' : 'website') . '">' . "\n";
    if ($url) { echo '<meta property="og:url" content="' . esc_url($url) . '">' . "\n"; }
    if (is_singular() && has_post_thumbnail()) { echo '<meta property="og:image" content="' . esc_url(get_the_post_thumbnail_url(null, 'large')) . '">' . "\n"; }
}
add_action('wp_head', 'oldbear_meta', 1);


/** Vector icon avoids missing arrow glyphs on Linux system fonts. */
function oldbear_arrow_icon() {
    return '<svg class="arrow-icon" viewBox="0 0 24 24" width="18" height="18" fill="none" aria-hidden="true" focusable="false"><path d="M5 19 19 5M5 5h14v14" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>';
}
