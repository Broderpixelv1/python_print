
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

/**
 * Python Print — Guardar preferencias y datos de cotización.
 */
function python_ajax_guardar_datos_cotizacion_cliente() {

    if (!is_user_logged_in()) {
        wp_send_json_error(array(
            'mensaje' => 'Debes iniciar sesión para guardar tus datos.'
        ), 401);
    }

    if (!check_ajax_referer('python_perfil_cliente', 'nonce', false)) {
        wp_send_json_error(array(
            'mensaje' => 'La sesión ha caducado. Actualiza la página.'
        ), 403);
    }

    $usuario = wp_get_current_user();

    if (!in_array('customer', (array) $usuario->roles, true)) {
        wp_send_json_error(array(
            'mensaje' => 'Esta cuenta requiere otro método de actualización.'
        ), 403);
    }

    $seccion = isset($_POST['seccion'])
        ? sanitize_key(wp_unslash($_POST['seccion']))
        : '';

    $campo = static function($nombre) {
        return isset($_POST[$nombre]) && is_string($_POST[$nombre])
            ? sanitize_text_field(wp_unslash($_POST[$nombre]))
            : '';
    };

    $datos = array();

    switch ($seccion) {

        case 'pago':

            $metodo = $campo('metodo_pago');

            if (!in_array($metodo, array('tarjeta', 'transferencia'), true)) {
                wp_send_json_error(array(
                    'mensaje' => 'Selecciona un método de pago válido.'
                ), 422);
            }

            $datos['python_metodo_pago'] = $metodo;
            break;

        case 'entrega':

            $metodo = $campo('metodo_entrega');

            if (!in_array($metodo, array('retiro', 'envio'), true)) {
                wp_send_json_error(array(
                    'mensaje' => 'Selecciona un método de entrega válido.'
                ), 422);
            }

            $datos['python_metodo_entrega'] = $metodo;

            if ($metodo === 'envio') {

                $provincia = $campo('provincia');
                $ciudad = $campo('ciudad');
                $direccion = $campo('direccion');
                $referencia = $campo('referencia');

                if (
                    !$provincia || !$ciudad || !$direccion ||
                    mb_strlen($provincia) > 100 ||
                    mb_strlen($ciudad) > 100 ||
                    mb_strlen($direccion) > 250 ||
                    mb_strlen($referencia) > 250
                ) {
                    wp_send_json_error(array(
                        'mensaje' => 'Completa correctamente la dirección de envío.'
                    ), 422);
                }

                $datos['shipping_state'] = $provincia;
                $datos['shipping_city'] = $ciudad;
                $datos['shipping_address_1'] = $direccion;
                $datos['python_referencia_entrega'] = $referencia;
            }

            break;

        case 'facturacion':

            $tipo = $campo('tipo_cliente');

            if (!in_array($tipo, array('natural', 'empresa'), true)) {
                wp_send_json_error(array(
                    'mensaje' => 'Selecciona un tipo de cliente válido.'
                ), 422);
            }

            $datos['python_tipo_cliente'] = $tipo;

            if ($tipo === 'natural') {

                $cedula = $campo('cedula');

                if (!preg_match('/^[0-9]{10}$/', $cedula)) {
                    wp_send_json_error(array(
                        'mensaje' => 'La cédula debe tener 10 dígitos.'
                    ), 422);
                }

                $datos['python_cedula'] = $cedula;

            } else {

                $ruc = $campo('ruc');
                $razon = $campo('razon_social');
                $comercial = $campo('nombre_comercial');

                if (
                    !preg_match('/^[0-9]{13}$/', $ruc) ||
                    !$razon ||
                    mb_strlen($razon) > 150 ||
                    mb_strlen($comercial) > 150
                ) {
                    wp_send_json_error(array(
                        'mensaje' => 'Completa correctamente los datos de la empresa.'
                    ), 422);
                }

                $datos['python_ruc'] = $ruc;
                $datos['billing_company'] = $razon;
                $datos['python_nombre_comercial'] = $comercial;
            }

            break;

        default:

            wp_send_json_error(array(
                'mensaje' => 'Sección no válida.'
            ), 422);
    }

    foreach ($datos as $clave => $valor) {
        update_user_meta($usuario->ID, $clave, $valor);
    }

    wp_send_json_success(array(
        'mensaje' => 'Datos guardados correctamente.',
        'seccion' => $seccion
    ));
}

add_action(
    'wp_ajax_python_guardar_datos_cotizacion_cliente',
    'python_ajax_guardar_datos_cotizacion_cliente'
);