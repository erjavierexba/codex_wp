<?php
/** Idempotent local bootstrap; core installation is performed by setup.sh. */
if (!defined('WP_CLI') || !WP_CLI) { exit; }
if (get_option('oldbear_fresh_install')) {
    update_option('blogdescription', 'Videojuegos con carácter. Historias que dejan huella.');
    update_option('blog_public', 0);
    wp_delete_post(1, true);
    foreach (get_posts(['post_type'=>'page','name'=>'sample-page','post_status'=>'any']) as $sample) { wp_delete_post($sample->ID, true); }
    delete_option('oldbear_fresh_install');
}
if (!function_exists('activate_plugin')) { require_once ABSPATH . 'wp-admin/includes/plugin.php'; }
$result = activate_plugin('oldbear-studio/oldbear-studio.php');
if (is_wp_error($result)) { WP_CLI::error($result->get_error_message()); }
switch_theme('oldbearstudios');
if (!get_option('oldbear_local_bootstrapped')) {
    $home = wp_insert_post(['post_type'=>'page','post_title'=>'Inicio','post_status'=>'publish'], true);
    $blog = wp_insert_post(['post_type'=>'page','post_title'=>'Diario','post_status'=>'publish'], true);
    if (is_wp_error($home) || is_wp_error($blog)) { WP_CLI::error('No se pudieron crear las páginas.'); }
    update_option('show_on_front', 'page');
    update_option('page_on_front', $home);
    update_option('page_for_posts', $blog);
    update_option('oldbear_local_bootstrapped', 1);
}
$url = getenv('WP_URL');
if ($url && !preg_match('~^https?://[a-zA-Z0-9.-]+(?::[0-9]+)?/?$~', $url)) { WP_CLI::error('WP_URL debe ser la raíz de una URL http/https válida.'); }
if ($url) { update_option('home', esc_url_raw($url)); update_option('siteurl', esc_url_raw($url)); }
update_option('permalink_structure', '/%postname%/');
flush_rewrite_rules();
WP_CLI::success('Tema, plugin y páginas preparados. Privacidad y formulario requieren configuración.');
