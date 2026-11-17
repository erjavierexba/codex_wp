<?php
namespace OldBearStudio;
if (!defined('ABSPATH')) { exit; }

final class Kit {
    public const VERSION = '2.0.0';
    public static function boot(): void {
        require_once __DIR__ . '/class-content.php';
        require_once __DIR__ . '/class-catalog.php';
        require_once __DIR__ . '/class-admin.php';
        require_once __DIR__ . '/class-starter.php';
        require_once __DIR__ . '/class-layout.php';
        add_filter('elementor/frontend/print_google_fonts', '__return_false');
        Content::boot();
        Catalog::boot();
        Admin::boot();
        Layout::boot();
        add_action('init', [self::class, 'assets'], 5);
        add_action('wp_enqueue_scripts', [self::class, 'enqueue']);
        add_action('elementor/elements/categories_registered', static function($manager) {
            $manager->add_category('oldbear-studio', ['title'=>'OldBear Studio','icon'=>'fa fa-gamepad']);
        });
        add_action('elementor/widgets/register', static function($manager) {
            require_once __DIR__ . '/widgets.php';
            foreach ([Hero_Widget::class, Catalog_Widget::class, Roadmap_Widget::class, Contact_Widget::class, Journal_Widget::class, Brand_Widget::class, Nav_Widget::class, Social_Widget::class, Credits_Widget::class] as $class) {
                $manager->register(new $class());
            }
        });
        add_shortcode('oldbear_games', [Catalog::class, 'shortcode']);
        add_shortcode('oldbear_roadmap', [Content::class, 'roadmap']);
        if (defined('WP_CLI') && WP_CLI) {
            \WP_CLI::add_command('oldbear studio-demo', static function() {
                $admin = get_users(['role'=>'administrator','number'=>1]);
                if (!$admin) { \WP_CLI::error('No existe un administrador.'); }
                wp_set_current_user($admin[0]->ID);
                $result = Starter::install(true);
                if (is_wp_error($result)) { \WP_CLI::error($result->get_error_message()); }
                $site = Starter::site();
                if (is_wp_error($site)) { \WP_CLI::error($site->get_error_message()); }
                \WP_CLI::success('Portada Elementor preparada: ' . $result . '. Cabecera, pie, 404, diario y privacidad con Elementor: ' . wp_json_encode($site));
            });
        }
    }
    public static function assets(): void {
        wp_register_style('oldbear-studio-kit', plugins_url('assets/studio.css', dirname(__DIR__) . '/oldbear-studio.php'), [], self::VERSION);
        wp_register_script('oldbear-studio-kit', plugins_url('assets/catalog.js', dirname(__DIR__) . '/oldbear-studio.php'), [], self::VERSION, ['strategy'=>'defer','in_footer'=>true]);
    }
    public static function enqueue(): void {
        if (did_action('elementor/loaded')) { wp_enqueue_style('oldbear-studio-kit'); }
        $post = get_post();
        if (is_front_page() || ($post && (get_post_meta($post->ID, '_elementor_edit_mode', true) || has_shortcode($post->post_content, 'oldbear_games') || has_shortcode($post->post_content, 'oldbear_roadmap') || has_shortcode($post->post_content, 'oldbear_contact')))) {
            wp_enqueue_style('oldbear-studio-kit');
            wp_enqueue_script('oldbear-studio-kit');
        }
    }
    public static function arrow(): string {
        return '<svg class="arrow-icon" viewBox="0 0 24 24" width="18" height="18" fill="none" aria-hidden="true" focusable="false"><path d="M5 19 19 5M5 5h14v14" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>';
    }
    public static function art(string $key = 'forest'): string {
        $files = ['forest'=>'world-forest.svg','dunes'=>'world-dunes.svg','hero'=>'wilderness.svg','logo'=>'bear-mark.svg'];
        return plugins_url('assets/' . ($files[$key] ?? $files['forest']), dirname(__DIR__) . '/oldbear-studio.php');
    }
}
