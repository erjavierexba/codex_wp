<?php
/**
 * Plugin Name: OldBear Studio Kit
 * Description: Catálogo filtrable, roadmap, contacto y widgets de Elementor para estudios de videojuegos.
 * Version: 2.0.0
 * Requires at least: 6.8
 * Requires PHP: 8.2
 * License: GPL-2.0-or-later
 * Text Domain: oldbear-studio
 */
if (!defined('ABSPATH')) { exit; }

function oldbear_register_games() {
    register_post_type('oldbear_game', [
        'labels'=>[
            'name'=>'Juegos','singular_name'=>'Juego','add_new_item'=>'Añadir juego',
            'edit_item'=>'Editar juego','new_item'=>'Nuevo juego','view_item'=>'Ver juego',
            'search_items'=>'Buscar juegos','not_found'=>'Todavía no hay juegos.'
        ],
        'public'=>true,'show_in_rest'=>true,'has_archive'=>true,'rewrite'=>['slug'=>'juegos'],
        'show_in_menu'=>'oldbear-studio','menu_icon'=>'dashicons-games','supports'=>['title','editor','thumbnail','excerpt','revisions'],
    ]);
}
add_action('init', 'oldbear_register_games');
function oldbear_activate() { oldbear_register_games(); flush_rewrite_rules(); }
register_activation_hook(__FILE__, 'oldbear_activate');
function oldbear_deactivate() { unregister_post_type('oldbear_game'); flush_rewrite_rules(); }
register_deactivation_hook(__FILE__, 'oldbear_deactivate');

function oldbear_game_fields() {
    return ['oldbear_status'=>'Estado (por ejemplo: En desarrollo)','oldbear_store_url'=>'URL de la tienda (opcional)'];
}
function oldbear_add_game_meta_box() {
    add_meta_box('oldbear_game_details', 'Ficha del juego', 'oldbear_render_game_meta_box', 'oldbear_game', 'normal');
}
add_action('add_meta_boxes', 'oldbear_add_game_meta_box');
function oldbear_render_game_meta_box($post) {
    wp_nonce_field('oldbear_game_save', 'oldbear_game_nonce');
    foreach (oldbear_game_fields() as $key=>$label) {
        echo '<p><label for="' . esc_attr($key) . '">' . esc_html($label) . '</label>';
        echo '<input class="widefat" type="' . ($key === 'oldbear_store_url' ? 'url' : 'text') . '" id="' . esc_attr($key) . '" name="' . esc_attr($key) . '" value="' . esc_attr(get_post_meta($post->ID, $key, true)) . '"></p>';
    }
}
function oldbear_post_value($key) {
    return isset($_POST[$key]) && is_string($_POST[$key]) ? wp_unslash($_POST[$key]) : '';
}
function oldbear_save_game($post_id) {
    if ((defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) || wp_is_post_revision($post_id) || !current_user_can('edit_post', $post_id)) { return; }
    if (!wp_verify_nonce(oldbear_post_value('oldbear_game_nonce'), 'oldbear_game_save')) { return; }
    foreach (oldbear_game_fields() as $key=>$label) {
        $raw = oldbear_post_value($key);
        $value = $key === 'oldbear_store_url' ? esc_url_raw($raw, ['http','https']) : sanitize_text_field($raw);
        update_post_meta($post_id, $key, $value);
    }
}
add_action('save_post_oldbear_game', 'oldbear_save_game');

function oldbear_recipient() {
    $email = get_option('oldbear_contact_recipient', '');
    return is_email($email) ? $email : get_option('admin_email');
}
function oldbear_contact_ready() {
    return (bool) get_option('oldbear_contact_enabled', false) && (bool) get_privacy_policy_url() && is_email(oldbear_recipient());
}
function oldbear_register_contact_settings() {
    register_setting('general', 'oldbear_contact_enabled', ['type'=>'boolean','default'=>false,'sanitize_callback'=>'rest_sanitize_boolean']);
    register_setting('general', 'oldbear_contact_recipient', ['type'=>'string','default'=>'','sanitize_callback'=>'sanitize_email']);
    add_settings_section('oldbear_contact_settings', 'Contacto OldBearStudios', function() {
        echo '<p>Publica y selecciona tu política en Ajustes → Privacidad, configura el correo y prueba el envío antes de activar el formulario.</p>';
    }, 'general');
    add_settings_field('oldbear_contact_enabled', 'Formulario público', function() {
        echo '<input type="hidden" name="oldbear_contact_enabled" value="0"><label><input type="checkbox" name="oldbear_contact_enabled" value="1" ' . checked(get_option('oldbear_contact_enabled'), true, false) . '> Activar formulario de contacto</label>';
    }, 'general', 'oldbear_contact_settings');
    add_settings_field('oldbear_contact_recipient', 'Correo destinatario', function() {
        echo '<input class="regular-text" type="email" name="oldbear_contact_recipient" value="' . esc_attr(get_option('oldbear_contact_recipient', '')) . '" placeholder="' . esc_attr(get_option('admin_email')) . '"><p class="description">Vacío: correo de administración. Este dato no se muestra al visitante.</p>';
    }, 'general', 'oldbear_contact_settings');
}
add_action('admin_init', 'oldbear_register_contact_settings');
function oldbear_contact_notice() {
    if (current_user_can('manage_options') && !oldbear_contact_ready()) {
        echo '<div class="notice notice-info"><p><strong>OldBearStudios:</strong> el formulario sigue desactivado. Revisa Ajustes → Generales y publica tu política en Ajustes → Privacidad.</p></div>';
    }
}
add_action('admin_notices', 'oldbear_contact_notice');

function oldbear_contact_status($status) {
    $messages = [
        'sent'=>'Gracias. Tu mensaje se ha enviado al sistema de correo del estudio.',
        'invalid'=>'Revisa los campos: nombre, correo válido, mensaje de 20 a 5000 caracteres y aceptación de la política.',
        'expired'=>'El formulario ha caducado. Recarga la página y vuelve a intentarlo.',
        'limited'=>'Has realizado varios intentos. Espera unos minutos antes de volver a enviar.',
        'failed'=>'No se pudo enviar el mensaje. Inténtalo más tarde.',
        'unavailable'=>'El formulario todavía no está disponible.'
    ];
    return $messages[$status] ?? '';
}
function oldbear_render_contact() {
    $status = isset($_GET['contact']) && is_string($_GET['contact']) ? sanitize_key(wp_unslash($_GET['contact'])) : '';
    $notice = oldbear_contact_status($status);
    ob_start();
    if ($notice) {
        echo '<p id="oldbear-contact-status" class="contact-status ' . ($status === 'sent' ? '' : 'error') . '" role="status" tabindex="-1">' . esc_html($notice) . '</p>';
    }
    if (!oldbear_contact_ready()) {
        echo '<h3>La conversación está a punto de empezar.</h3><p>Estamos preparando nuestro canal de contacto. Vuelve pronto.</p>';
        return ob_get_clean();
    }
    $id = wp_unique_id('oldbear-contact-');
    ?>
    <form class="contact-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
        <input type="hidden" name="action" value="oldbear_contact">
        <?php wp_nonce_field('oldbear_contact_send', 'oldbear_contact_nonce', false); ?>
        <div class="form-row">
            <label for="<?php echo esc_attr($id); ?>name">Tu nombre
                <input id="<?php echo esc_attr($id); ?>name" type="text" name="contact_name" autocomplete="name" required minlength="2" maxlength="100">
            </label>
            <label for="<?php echo esc_attr($id); ?>email">Tu correo
                <input id="<?php echo esc_attr($id); ?>email" type="email" name="contact_email" autocomplete="email" required maxlength="254">
            </label>
        </div>
        <label for="<?php echo esc_attr($id); ?>message">Cuéntanos
            <textarea id="<?php echo esc_attr($id); ?>message" name="contact_message" rows="5" required minlength="20" maxlength="5000"></textarea>
        </label>
        <div class="honey" aria-hidden="true"><label for="<?php echo esc_attr($id); ?>website">Deja este campo vacío
            <input id="<?php echo esc_attr($id); ?>website" type="text" name="contact_website" tabindex="-1" autocomplete="off">
        </label></div>
        <label class="consent" for="<?php echo esc_attr($id); ?>consent">
            <input id="<?php echo esc_attr($id); ?>consent" type="checkbox" name="contact_consent" value="1" required>
            <span>He leído la <a href="<?php echo esc_url(get_privacy_policy_url()); ?>">política de privacidad</a> y acepto el tratamiento de mis datos para responder a mi consulta.</span>
        </label>
        <button class="button" type="submit">Enviar mensaje <span aria-hidden="true">↗</span></button>
        <p class="form-caption">Usaremos estos datos para responder a tu consulta. No se añaden a ninguna lista de publicidad.</p>
    </form>
    <?php return ob_get_clean();
}
add_shortcode('oldbear_contact', 'oldbear_render_contact');

function oldbear_validate_contact($data) {
    foreach (['name','email','message','consent'] as $key) {
        if (!isset($data[$key]) || !is_string($data[$key])) { return new WP_Error('invalid'); }
    }
    $name = sanitize_text_field($data['name']);
    $email = sanitize_email($data['email']);
    $message = sanitize_textarea_field($data['message']);
    $length = function($text) { return function_exists('mb_strlen') ? mb_strlen($text) : strlen($text); };
    if ($length($name) < 2 || $length($name) > 100 || strlen($email) > 254 || !is_email($email) || $data['consent'] !== '1' || $length($message) < 20 || $length($message) > 5000) {
        return new WP_Error('invalid');
    }
    return ['name'=>$name,'email'=>$email,'message'=>$message];
}
function oldbear_process_contact($data, $ip) {
    if (!oldbear_contact_ready()) { return 'unavailable'; }
    if (empty($data['nonce']) || !is_string($data['nonce']) || !wp_verify_nonce($data['nonce'], 'oldbear_contact_send')) { return 'expired'; }
    if (!empty($data['website'])) { return 'sent'; }
    $ip_key = 'oldbear_ip_' . hash_hmac('sha256', (string) $ip, wp_salt('nonce'));
    $attempts = (int) get_transient($ip_key);
    if ($attempts >= 10) { return 'limited'; }
    set_transient($ip_key, $attempts + 1, HOUR_IN_SECONDS);
    $fields = oldbear_validate_contact($data);
    if (is_wp_error($fields)) { return 'invalid'; }
    $email_key = 'oldbear_email_' . hash_hmac('sha256', strtolower($fields['email']), wp_salt('nonce'));
    $cooldown_key = 'oldbear_wait_' . hash_hmac('sha256', strtolower($fields['email']), wp_salt('nonce'));
    $email_attempts = (int) get_transient($email_key);
    if ($email_attempts >= 5 || get_transient($cooldown_key)) { return 'limited'; }
    set_transient($email_key, $email_attempts + 1, HOUR_IN_SECONDS);
    set_transient($cooldown_key, 1, MINUTE_IN_SECONDS);
    $body = "Consulta desde OldBearStudios\n\nNombre: " . $fields['name'] . "\nCorreo: " . $fields['email'] . "\n\n" . $fields['message'] . "\n\nLa persona ha aceptado la política de privacidad al enviar.";
    $sent = wp_mail(oldbear_recipient(), '[OldBearStudios] Nueva consulta', $body, ['Reply-To: ' . $fields['email']]);
    return $sent ? 'sent' : 'failed';
}
function oldbear_handle_contact() {
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') { status_header(405); header('Allow: POST'); exit; }
    $data = [
        'nonce'=>oldbear_post_value('oldbear_contact_nonce'),'website'=>oldbear_post_value('contact_website'),
        'name'=>oldbear_post_value('contact_name'),'email'=>oldbear_post_value('contact_email'),
        'message'=>oldbear_post_value('contact_message'),'consent'=>oldbear_post_value('contact_consent')
    ];
    $status = oldbear_process_contact($data, $_SERVER['REMOTE_ADDR'] ?? 'unknown');
    nocache_headers();
    wp_safe_redirect(add_query_arg('contact', $status, home_url('/')) . '#contacto', 303);
    exit;
}
add_action('admin_post_nopriv_oldbear_contact', 'oldbear_handle_contact');
add_action('admin_post_oldbear_contact', 'oldbear_handle_contact');

/** Development only: Compose explicitly supplies this variable. Never enabled by theme activation. */
function oldbear_local_mail($mailer) {
    if (getenv('OLDBEAR_SMTP_HOST') !== 'mailpit') { return; }
    $mailer->isSMTP();
    $mailer->Host = 'mailpit';
    $mailer->Port = 1025;
    $mailer->SMTPAuth = false;
    $mailer->SMTPAutoTLS = false;
    $mailer->SMTPSecure = '';
}
add_action('phpmailer_init', 'oldbear_local_mail');


require_once __DIR__ . '/includes/class-kit.php';
add_action('plugins_loaded', ['OldBearStudio\\Kit', 'boot'], 20);
