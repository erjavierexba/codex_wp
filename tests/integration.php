<?php
if (!defined('WP_CLI') || !WP_CLI) { exit; }
$checks = 0;
$assert = function($condition, $message) use (&$checks) {
    if (!$condition) { throw new RuntimeException($message); }
    $checks++;
};
$assert(get_stylesheet() === 'oldbearstudios', 'El tema no está activo.');
$assert(function_exists('oldbear_process_contact'), 'El plugin no está activo.');
$assert(post_type_exists('oldbear_game'), 'Falta el tipo Juegos.');
$assert(get_post_type_object('oldbear_game')->show_in_rest, 'Juegos debe admitir Gutenberg.');
$assert(get_post_status((int) get_option('page_on_front')) === 'publish', 'Falta la portada.');
$assert(get_post_status((int) get_option('page_for_posts')) === 'publish', 'Falta el diario.');
$config = json_decode(file_get_contents(get_theme_file_path('theme.json')), true, 512, JSON_THROW_ON_ERROR);
$assert($config['version'] === 3, 'theme.json no es válido.');
$assert(is_wp_error(oldbear_validate_contact(['name'=>[],'email'=>'x','message'=>'test','consent'=>'1'])), 'Debe rechazar arrays.');
$assert(is_wp_error(oldbear_validate_contact(['name'=>'Ana','email'=>'invalid','message'=>'Una consulta con suficiente longitud.','consent'=>'1'])), 'Debe validar el correo.');
$assert(is_wp_error(oldbear_validate_contact(['name'=>'Ana','email'=>'ana@example.org','message'=>'Una consulta con suficiente longitud.','consent'=>'0'])), 'Debe exigir consentimiento.');
$assert(is_wp_error(oldbear_validate_contact(['name'=>'Ana','email'=>'ana@example.org','message'=>'Corto','consent'=>'1'])), 'Debe exigir longitud mínima.');
$sanitized = oldbear_validate_contact(['name'=>'<b>Ana</b>','email'=>'ana@example.org','message'=>'<img src=x onerror=alert(1)>Una consulta con suficiente longitud.','consent'=>'1']);
$assert(!is_wp_error($sanitized) && $sanitized['name'] === 'Ana' && !str_contains($sanitized['message'], '<'), 'Debe eliminar HTML.');

$disabled = function() { return 0; };
add_filter('pre_option_oldbear_contact_enabled', $disabled);
$assert(oldbear_process_contact([], 'integration-disabled') === 'unavailable', 'No debe enviar si está desactivado.');
remove_filter('pre_option_oldbear_contact_enabled', $disabled);

$enabled = function() { return 1; };
$policy = function() { return home_url('/privacy-integration-test/'); };
$recipient = function() { return 'studio@example.org'; };
add_filter('pre_option_oldbear_contact_enabled', $enabled);
add_filter('privacy_policy_url', $policy);
add_filter('pre_option_oldbear_contact_recipient', $recipient);
$prefix = 'integration-' . wp_generate_password(12, false);
$ips = [$prefix . '-success',$prefix . '-fail',$prefix . '-limit'];
$emails = [$prefix . '@example.org', $prefix . '-fail@example.org'];
$mail_calls = 0;
$mail_result = true;
$intercept = function($return, $attributes) use (&$mail_calls, &$mail_result, $assert) {
    $mail_calls++;
    $assert($attributes['to'] === 'studio@example.org', 'Destinatario incorrecto.');
    $assert(str_starts_with($attributes['headers'][0], 'Reply-To: '), 'Falta Reply-To seguro.');
    return $mail_result;
};
add_filter('pre_wp_mail', $intercept, 10, 2);
try {
    $assert(oldbear_process_contact(['nonce'=>'invalid'], $ips[0]) === 'expired', 'Debe rechazar nonce inválido.');
    $data = ['name'=>'Ana','email'=>$emails[0],'message'=>'Quisiera hablar sobre una colaboración.','consent'=>'1','website'=>'','nonce'=>wp_create_nonce('oldbear_contact_send')];
    $bad = $data; $bad['email'] = 'invalid';
    $assert(oldbear_process_contact($bad, $ips[0]) === 'invalid', 'Debe rechazar campos inválidos.');
    $trap = $data; $trap['website'] = 'spam';
    $assert(oldbear_process_contact($trap, $ips[0]) === 'sent' && $mail_calls === 0, 'La trampa no debe enviar correo.');
    $assert(oldbear_process_contact($data, $ips[0]) === 'sent' && $mail_calls === 1, 'Debe aceptar el envío válido.');
    $assert(oldbear_process_contact($data, $ips[0]) === 'limited' && $mail_calls === 1, 'Debe limitar repeticiones por correo.');
    $mail_result = false;
    $data['email'] = $emails[1];
    $assert(oldbear_process_contact($data, $ips[1]) === 'failed', 'Debe comunicar los fallos del correo.');
    $key = 'oldbear_ip_' . hash_hmac('sha256', $ips[2], wp_salt('nonce'));
    set_transient($key, 10, HOUR_IN_SECONDS);
    $assert(oldbear_process_contact($data, $ips[2]) === 'limited', 'Debe limitar intentos por IP.');
} finally {
    remove_filter('pre_wp_mail', $intercept, 10);
    remove_filter('pre_option_oldbear_contact_enabled', $enabled);
    remove_filter('privacy_policy_url', $policy);
    remove_filter('pre_option_oldbear_contact_recipient', $recipient);
    foreach ($ips as $ip) { delete_transient('oldbear_ip_' . hash_hmac('sha256', $ip, wp_salt('nonce'))); }
    foreach ($emails as $email) {
        foreach (['oldbear_email_','oldbear_wait_'] as $prefix_key) { delete_transient($prefix_key . hash_hmac('sha256', strtolower($email), wp_salt('nonce'))); }
    }
}
WP_CLI::success($checks . ' comprobaciones correctas. No se ha enviado correo real.');
