<?php
namespace OldBearStudio;
if (!defined('ABSPATH')) { exit; }

/**
 * Elementor Free has no Theme Builder: header, footer and 404 are Elementor templates
 * (Plantillas → Plantillas guardadas) that the theme prints through the `oldbear_theme_part` filter.
 */
final class Layout {
    public const OPTION = 'oldbear_elementor_parts';
    public const PARTS = ['header'=>'Cabecera','footer'=>'Pie de página','404'=>'Página 404'];
    private static array $rendered = [];

    public static function boot(): void {
        add_filter('oldbear_theme_part', [self::class, 'part'], 10, 2);
        add_action('wp_enqueue_scripts', [self::class, 'prepare'], 30);
        add_filter('option_elementor_cpt_support', [self::class, 'cpt_support']);
        add_filter('default_option_elementor_cpt_support', [self::class, 'cpt_support']);
    }
    public static function cpt_support($types): array {
        $types = is_array($types) ? $types : ['page','post'];
        return array_values(array_unique(array_merge($types, ['page','post','oldbear_game'])));
    }
    public static function ids(): array {
        $ids = get_option(self::OPTION, []);
        return is_array($ids) ? array_map('absint', $ids) : [];
    }
    /** Published Elementor document for a part or page key, or 0. */
    public static function id(string $key): int {
        $id = self::ids()[$key] ?? 0;
        return $id && get_post_status($id) === 'publish' && get_post_meta($id, '_elementor_edit_mode', true) === 'builder' ? $id : 0;
    }
    public static function remember(string $key, int $id): void {
        $ids = self::ids();
        $ids[$key] = $id;
        update_option(self::OPTION, $ids, true);
    }
    public static function journal_url(): string {
        $id = self::ids()['journal'] ?? 0;
        return $id && get_post_status($id) === 'publish' ? (string) get_permalink($id) : '';
    }
    private static function active(): bool {
        return did_action('elementor/loaded') && class_exists('Elementor\\Plugin');
    }
    /** Editing a part in Elementor must not wrap it in itself. */
    private static function editing(int $id): bool {
        $elementor = \Elementor\Plugin::$instance;
        return ($elementor->preview->is_preview_mode() || $elementor->editor->is_edit_mode()) && get_queried_object_id() === $id;
    }
    /** Render early so Elementor enqueues widget and document CSS in <head>. */
    public static function prepare(): void {
        if (!self::active()) { return; }
        $frontend = \Elementor\Plugin::$instance->frontend;
        $found = false;
        foreach (array_keys(self::PARTS) as $key) {
            if ($key === '404' && !is_404()) { continue; }
            $id = self::id($key);
            if (!$id || self::editing($id) || is_singular('elementor_library')) { continue; }
            \Elementor\Core\Files\CSS\Post::create($id)->enqueue();
            self::$rendered[$key] = (string) $frontend->get_builder_content_for_display($id, false);
            $found = $found || self::$rendered[$key] !== '';
        }
        if ($found) { $frontend->enqueue_styles(); $frontend->enqueue_scripts(); }
    }
    public static function part($html, $key) {
        if ($html !== '' || !is_string($key)) { return $html; }
        return self::$rendered[$key] ?? $html;
    }
}
