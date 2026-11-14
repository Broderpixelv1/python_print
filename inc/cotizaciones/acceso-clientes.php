
<?php
/**
 * Python Print — Acceso seguro por correo electrónico.
 * Enlaces de un solo uso con vigencia de 15 minutos.
 */

defined('ABSPATH') || exit;

/**
 * Solicita un enlace de acceso.
 */
function python_solicitar_acceso_cliente() {

    if (!check_ajax_referer('python_acceso_cliente', 'nonce', false)) {
        wp_send_json_error(array(
            'mensaje' => 'Actualiza la página e inténtalo nuevamente.'
        ), 403);
    }

    $email = isset($_POST['email'])
        ? sanitize_email(wp_unslash($_POST['email']))
        : '';

    if (!is_email($email)) {
        wp_send_json_error(array(
            'mensaje' => 'Ingresa un correo electrónico válido.'
        ), 422);
    }

    // Limitar solicitudes por correo y por dirección IP.
    $ip = isset($_SERVER['REMOTE_ADDR'])
        ? (string) $_SERVER['REMOTE_ADDR']
        : '';

    $email_key = 'python_mail_limit_' . hash('sha256', strtolower($email));
    $ip_key = 'python_ip_limit_' . hash('sha256', $ip);

    if (get_transient($email_key) || get_transient($ip_key)) {
        wp_send_json_success(array(
            'mensaje' => 'Si es posible procesar la solicitud, recibirás un enlace en tu correo. Revisa también spam.'
        ));
    }

    set_transient($email_key, 1, 60);
    set_transient($ip_key, 1, 30);

    try {
        $token = bin2hex(random_bytes(32));
    } catch (Exception $e) {
        wp_send_json_error(array(
            'mensaje' => 'No fue posible preparar el acceso. Inténtalo más tarde.'
        ), 500);
    }

    $token_hash = hash('sha256', $token);
    $token_key = 'python_acceso_' . $token_hash;

    set_transient($token_key, array(
        'email' => strtolower($email),
        'created' => time()
    ), 15 * MINUTE_IN_SECONDS);

    $url = add_query_arg(
        array('python_acceso' => $token),
        home_url('/')
    );

    $asunto = 'Tu acceso seguro a Python Print';

    $mensaje = "Hola,\n\n";
    $mensaje .= "Recibimos una solicitud para acceder a Python Print.\n\n";
    $mensaje .= "Utiliza este enlace para ingresar:\n\n";
    $mensaje .= $url . "\n\n";
    $mensaje .= "El enlace vence en 15 minutos y solo puede utilizarse una vez.\n\n";
    $mensaje .= "Si no solicitaste este acceso, puedes ignorar este mensaje.\n\n";
    $mensaje .= "Python Print";

    $enviado = wp_mail($email, $asunto, $mensaje);

    if (!$enviado) {
        delete_transient($token_key);

        // No revelamos si existe una cuenta con este correo.
        wp_send_json_success(array(
            'mensaje' => 'Si es posible procesar la solicitud, recibirás un enlace en tu correo. Revisa también spam.'
        ));
    }

    wp_send_json_success(array(
        'mensaje' => 'Si es posible procesar la solicitud, recibirás un enlace en tu correo. Revisa también spam.'
    ));
}

add_action(
    'wp_ajax_python_solicitar_acceso',
    'python_solicitar_acceso_cliente'
);

add_action(
    'wp_ajax_nopriv_python_solicitar_acceso',
    'python_solicitar_acceso_cliente'
);


/**
 * Comprueba el enlace y autentica al cliente.
 */
function python_verificar_acceso_cliente() {

    if (!isset($_GET['python_acceso'])) {
        return;
    }

    nocache_headers();

    $token = sanitize_text_field(
        wp_unslash($_GET['python_acceso'])
    );

    if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
        wp_die('El enlace de acceso no es válido.');
    }

    $token_key = 'python_acceso_' . hash('sha256', $token);

    $datos = get_transient($token_key);

    if (
        !is_array($datos) ||
        empty($datos['email']) ||
        !is_email($datos['email'])
    ) {
        wp_die(
            'El enlace ha caducado o ya fue utilizado. Solicita uno nuevo.'
        );
    }

    // Invalidar antes de autenticar.
    delete_transient($token_key);

    $email = $datos['email'];
    $usuario = get_user_by('email', $email);

    if (!$usuario) {

        $username = 'cliente_' . strtolower(
            wp_generate_password(12, false, false)
        );

        while (username_exists($username)) {
            $username = 'cliente_' . strtolower(
                wp_generate_password(12, false, false)
            );
        }

        $user_id = wp_insert_user(array(
            'user_login' => $username,
            'user_email' => $email,
            'user_pass'  => wp_generate_password(32, true, true),
            'role'       => 'customer'
        ));

        if (is_wp_error($user_id)) {
            wp_die(
                'No se pudo crear la cuenta. Contacta con Python Print.'
            );
        }

        $usuario = get_user_by('id', $user_id);
    }

    if (!$usuario || !$usuario->exists()) {
        wp_die('No fue posible acceder a la cuenta.');
    }

    // Respetar las cuentas deshabilitadas o bloqueadas
    // si posteriormente se implementan restricciones.
    if (!in_array('customer', (array) $usuario->roles, true)) {
        wp_die(
            'Esta cuenta requiere un método de acceso diferente.'
        );
    }

    wp_clear_auth_cookie();

    wp_set_current_user($usuario->ID);

    wp_set_auth_cookie(
        $usuario->ID,
        false,
        is_ssl()
    );

    do_action('wp_login', $usuario->user_login, $usuario);

    /**
     * Después de verificar el correo,
     * regresar al cliente a Mi pedido.
     */
    $pagina_pedido = get_page_by_path('mi-pedido');

    $destino = $pagina_pedido
        ? get_permalink($pagina_pedido->ID)
        : home_url('/');

    wp_safe_redirect($destino);
    exit;
}

add_action(
    'template_redirect',
    'python_verificar_acceso_cliente',
    1
);
