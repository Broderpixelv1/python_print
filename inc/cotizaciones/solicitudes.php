<?php
/**
 * Python Print — Solicitudes de cotización.
 *
 * Aquí implementaremos la recepción, validación
 * y almacenamiento seguro de las solicitudes.
 */

defined('ABSPATH') || exit;


/**
 * Valida los datos de una solicitud de cotización.
 *
 * Devuelve true si son válidos o WP_Error si hay errores.
 */
function python_validar_datos_cotizacion($datos) {

    if (!is_array($datos)) {
        return new WP_Error(
            'datos_invalidos',
            'No se recibieron datos válidos.'
        );
    }

    $obligatorios = array(
        'nombres'  => 'nombres',
        'apellidos' => 'apellidos',
        'email'    => 'correo electrónico',
        'telefono' => 'WhatsApp o teléfono',
    );

    foreach ($obligatorios as $campo => $etiqueta) {
        if (
            !isset($datos[$campo]) ||
            !is_scalar($datos[$campo]) ||
            trim((string) $datos[$campo]) === ''
        ) {
            return new WP_Error(
                'campo_obligatorio',
                'Debes completar el campo: ' . $etiqueta
            );
        }
    }

    if (!is_email($datos['email'])) {
        return new WP_Error(
            'email_invalido',
            'Ingresa un correo electrónico válido.'
        );
    }

    $tipo = $datos['tipo_cliente'] ?? '';

    if (!in_array($tipo, array('natural', 'empresa'), true)) {
        return new WP_Error(
            'tipo_cliente_invalido',
            'Selecciona un tipo de cliente válido.'
        );
    }

    if ($tipo === 'natural') {
        $cedula = $datos['cedula'] ?? '';

        if (!is_scalar($cedula) || !preg_match('/^[0-9]{10}$/', (string) $cedula)) {
            return new WP_Error(
                'cedula_invalida',
                'La cédula debe contener 10 dígitos.'
            );
        }
    }

    if ($tipo === 'empresa') {
        $ruc = $datos['ruc'] ?? '';
        $razon_social = $datos['razon_social'] ?? '';

        if (!is_scalar($ruc) || !preg_match('/^[0-9]{13}$/', (string) $ruc)) {
            return new WP_Error(
                'ruc_invalido',
                'El RUC debe contener 13 dígitos.'
            );
        }

        if (!is_scalar($razon_social) || trim((string) $razon_social) === '') {
            return new WP_Error(
                'razon_social_obligatoria',
                'Ingresa la razón social de la empresa.'
            );
        }
    }

    $entrega = $datos['metodo_entrega'] ?? '';

    if (!in_array($entrega, array('retiro', 'envio'), true)) {
        return new WP_Error(
            'entrega_invalida',
            'Selecciona un método de entrega.'
        );
    }

    if ($entrega === 'envio') {
        foreach (array('provincia', 'ciudad', 'direccion') as $campo) {
            if (
                empty($datos[$campo]) ||
                !is_scalar($datos[$campo])
            ) {
                return new WP_Error(
                    'direccion_incompleta',
                    'Completa los datos de envío.'
                );
            }
        }
    }

    $pago = $datos['metodo_pago'] ?? '';

    if (!in_array($pago, array('tarjeta', 'transferencia'), true)) {
        return new WP_Error(
            'pago_invalido',
            'Selecciona un método de pago preferido.'
        );
    }

    if (
        empty($datos['privacidad']) ||
        empty($datos['terminos'])
    ) {
        return new WP_Error(
            'consentimiento_requerido',
            'Debes aceptar la política de privacidad y los términos del servicio.'
        );
    }

    return true;
}


/**
 * Python Print — Comprobar una solicitud de cotización.
 *
 * Etapa de pruebas: no crea usuarios ni cotizaciones.
 */
function python_ajax_validar_cotizacion() {

    if (!check_ajax_referer(
        'python_cotizacion_nonce',
        'nonce',
        false
    )) {
        wp_send_json_error(
            array('mensaje' => 'La sesión de seguridad ha caducado. Actualiza la página.'),
            403
        );
    }

    $datos = array();

    if (isset($_POST['datos']) && is_array($_POST['datos'])) {
        $datos = wp_unslash($_POST['datos']);
    }

    $resultado = python_validar_datos_cotizacion($datos);

    if (is_wp_error($resultado)) {
        wp_send_json_error(
            array('mensaje' => $resultado->get_error_message()),
            422
        );
    }

    wp_send_json_success(
        array(
            'mensaje' => 'Los datos llegaron correctamente a WordPress y pasaron la validación.'
        )
    );
}

add_action(
    'wp_ajax_python_validar_cotizacion',
    'python_ajax_validar_cotizacion'
);

add_action(
    'wp_ajax_nopriv_python_validar_cotizacion',
    'python_ajax_validar_cotizacion'
);
