
<?php
/**
 * Python Print — Guardar perfil del cliente.
 */

defined('ABSPATH') || exit;

function python_ajax_guardar_perfil_cliente() {

    if (!is_user_logged_in()) {
        wp_send_json_error(array(
            'mensaje' => 'Debes verificar tu correo antes de guardar tu perfil.'
        ), 401);
    }

    if (!check_ajax_referer(
        'python_perfil_cliente',
        'nonce',
        false
    )) {
        wp_send_json_error(array(
            'mensaje' => 'La sesión ha caducado. Actualiza la página.'
        ), 403);
    }

    $nombres = isset($_POST['nombres'])
        ? sanitize_text_field(wp_unslash($_POST['nombres']))
        : '';

    $apellidos = isset($_POST['apellidos'])
        ? sanitize_text_field(wp_unslash($_POST['apellidos']))
        : '';

    $telefono = isset($_POST['telefono'])
        ? sanitize_text_field(wp_unslash($_POST['telefono']))
        : '';

    if (
        !$nombres ||
        !$apellidos ||
        !$telefono ||
        mb_strlen($nombres) > 100 ||
        mb_strlen($apellidos) > 100 ||
        mb_strlen($telefono) > 25
    ) {
        wp_send_json_error(array(
            'mensaje' => 'Completa correctamente nombres, apellidos y WhatsApp.'
        ), 422);
    }

    $usuario = wp_get_current_user();

    // No permitir que este endpoint modifique cuentas administrativas.
    if (!in_array('customer', (array) $usuario->roles, true)) {
        wp_send_json_error(array(
            'mensaje' => 'Esta cuenta requiere otro método de actualización.'
        ), 403);
    }

    $resultado = wp_update_user(array(
        'ID'           => $usuario->ID,
        'first_name'   => $nombres,
        'last_name'    => $apellidos,
        'display_name' => trim($nombres . ' ' . $apellidos),
    ));

    if (is_wp_error($resultado)) {
        wp_send_json_error(array(
            'mensaje' => 'No fue posible actualizar el perfil.'
        ), 500);
    }

    update_user_meta($usuario->ID, 'billing_first_name', $nombres);
    update_user_meta($usuario->ID, 'billing_last_name', $apellidos);
    update_user_meta($usuario->ID, 'billing_phone', $telefono);

    wp_send_json_success(array(
        'mensaje' => 'Perfil guardado correctamente.',
        'nombre'  => trim($nombres . ' ' . $apellidos),
        'email'   => $usuario->user_email,
    ));
}

add_action(
    'wp_ajax_python_guardar_perfil_cliente',
    'python_ajax_guardar_perfil_cliente'
);
