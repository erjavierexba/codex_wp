<?php
namespace OldBearStudio;
if (!defined('ABSPATH')) { exit; }

final class Admin {
    public static function boot(): void {
        add_action('admin_menu', [self::class,'menu']);
        add_action('admin_post_ob_studio_action', [self::class,'handle']);
    }
    public static function menu(): void {
        add_menu_page('OldBear Studio', 'OldBear Studio', 'edit_posts', 'oldbear-studio', [self::class,'page'], 'dashicons-games', 25);
        add_submenu_page('oldbear-studio', 'Géneros', 'Géneros', 'manage_categories', 'edit-tags.php?taxonomy=ob_genre&post_type=oldbear_game');
        add_submenu_page('oldbear-studio', 'Plataformas', 'Plataformas', 'manage_categories', 'edit-tags.php?taxonomy=ob_platform&post_type=oldbear_game');
    }
    public static function authorize(string $nonce): bool {
        return current_user_can('manage_options') && (bool) wp_verify_nonce($nonce, 'ob_studio_action');
    }
    public static function handle(): void {
        if (!self::authorize(oldbear_post_value('_wpnonce'))) { wp_die('No tienes permiso para realizar esta acción.', '', ['response'=>403]); }
        $action = sanitize_key(oldbear_post_value('ob_task'));
        if ($action === 'landing') {
            $result = Starter::install(false);
            if (is_wp_error($result)) { wp_die(esc_html($result->get_error_message())); }
        } elseif ($action === 'demo') {
            Starter::seed();
        } elseif ($action === 'remove-demo') {
            $posts = get_posts(['post_type'=>['oldbear_game','ob_milestone'],'post_status'=>['publish','draft','pending','private'],'posts_per_page'=>-1,'meta_key'=>'_ob_demo_key','fields'=>'ids']);
            foreach ($posts as $id) { wp_trash_post($id); }
            Catalog::invalidate();
        } else { wp_die('Acción desconocida.', '', ['response'=>400]); }
        wp_safe_redirect(admin_url('admin.php?page=oldbear-studio&updated=1'));
        exit;
    }
    private static function action(string $task, string $label): void {
        echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '" style="display:inline-block;margin:0 12px 12px 0"><input type="hidden" name="action" value="ob_studio_action"><input type="hidden" name="ob_task" value="' . esc_attr($task) . '">';
        wp_nonce_field('ob_studio_action');
        echo '<button class="button button-secondary" type="submit">' . esc_html($label) . '</button></form>';
    }
    public static function page(): void {
        if (!current_user_can('edit_posts')) { return; }
        $games=wp_count_posts('oldbear_game'); $milestones=wp_count_posts('ob_milestone');
        echo '<div class="wrap"><h1>OldBear Studio — Control room</h1><p>De una idea a un mundo jugable. Gestiona el catálogo y el desarrollo desde un solo sitio.</p>';
        echo '<div style="background:#101412;color:#f2eadc;padding:28px;border-radius:10px;margin:24px 0;max-width:1000px"><h2 style="color:#efaa59;margin-top:0">Studio Kit 2.0</h2><p>' . absint($games->publish) . ' juegos publicados · ' . absint($milestones->publish) . ' hitos publicados</p><p>Catálogo con filtros · API REST · Widgets Elementor · Roadmap · Contacto</p></div>';
        $home=absint(get_option('oldbear_elementor_home'));
        if ($home && current_user_can('edit_post', $home)) {
            echo '<p><a class="button button-primary" href="' . esc_url(admin_url('post.php?post=' . $home . '&action=elementor')) . '">Editar landing con Elementor</a> <a class="button" href="' . esc_url(get_permalink($home)) . '">Ver landing</a></p>';
        }
        echo '<h2>Conexiones y estado</h2><ul><li>Elementor: ' . (defined('ELEMENTOR_VERSION') ? esc_html(ELEMENTOR_VERSION) : 'no está activo; catálogo y roadmap funcionan mediante shortcodes') . '</li><li>Contacto: ' . (oldbear_contact_ready() ? 'activado' : 'pendiente de destinatario, política y activación en Ajustes → Generales') . '</li></ul>';
        echo '<h2>Cómo mostrar el plugin</h2><ol><li>Filtra el catálogo por género y plataforma, busca un título y prueba una búsqueda sin resultados.</li><li>Edita un juego, sus taxonomías o un hito: el catálogo invalida su caché y el roadmap refleja el progreso real.</li><li>En Elementor busca la categoría OldBear Studio y arrastra sus cuatro widgets a cualquier página.</li></ol>';
        if (current_user_can('manage_options')) {
            echo '<h2>Preparación y contenido de demostración</h2><p>La demo está marcada como muestra. Retirarla mueve únicamente los registros identificados como demo a la papelera. La creación de la landing es idempotente y no sobrescribe diseños existentes.</p>';
            self::action('landing', 'Preparar landing Elementor');
            self::action('demo', 'Cargar demo identificada');
            self::action('remove-demo', 'Retirar demo a la papelera');
        }
        echo '<h2>Shortcodes</h2><p><code>[oldbear_games per_page="6"]</code> · <code>[oldbear_roadmap]</code> · <code>[oldbear_contact]</code></p><p>El diseño de la landing se edita en Elementor. La identidad y navegación del tema se editan desde Apariencia.</p></div>';
    }
}
