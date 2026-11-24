<?php
/**
 * Python Print — Mis cotizaciones.
 *
 * Consulta de cotizaciones y proformas del cliente.
 */

defined('ABSPATH') || exit;


/**
 * Recuperar las cotizaciones del cliente autenticado.
 */
function python_obtener_mis_cotizaciones() {

    if (!is_user_logged_in()) {
        return array();
    }

    $cliente_id = get_current_user_id();

    $cotizaciones = get_posts(array(

        'post_type'      => 'python_cotizacion',
        'post_status'    => 'private',
        'posts_per_page' => -1,
        'orderby'        => 'date',
        'order'          => 'DESC',

        'meta_query' => array(
            array(
                'key'     => '_python_cliente_id',
                'value'   => $cliente_id,
                'compare' => '=',
                'type'    => 'NUMERIC',
            ),
        ),

    ));

    return $cotizaciones;
}

/**
 * Recuperar los detalles de una cotización del cliente.
 */
function python_obtener_detalle_cotizacion($cotizacion_id) {

    if (!is_user_logged_in()) {
        return false;
    }

    $cotizacion_id = absint($cotizacion_id);

    $cotizacion = get_post($cotizacion_id);

    // Comprobar que la cotización existe.
    if (
        !$cotizacion ||
        $cotizacion->post_type !== 'python_cotizacion' ||
        $cotizacion->post_status !== 'private'
    ) {
        return false;
    }

    // Comprobar la propiedad de la cotización.
    $cliente_id = (int) get_post_meta(
        $cotizacion_id,
        '_python_cliente_id',
        true
    );

    if ($cliente_id !== get_current_user_id()) {
        return false;
    }

    // Recuperar el estado comercial.
    $estado = get_post_meta(
        $cotizacion_id,
        '_python_estado_proforma',
        true
    );

    if (empty($estado)) {
        $estado = 'pendiente';
    }

    // Recuperar los productos solicitados.
    $productos = get_post_meta(
        $cotizacion_id,
        '_python_productos',
        true
    );

    // Recuperar únicamente la versión publicada.
    $proforma = get_post_meta(
        $cotizacion_id,
        '_python_proforma_publicada',
        true
    );

    $proforma = is_array($proforma)
        ? $proforma
        : array();

    $productos = is_array($productos)
        ? $productos
        : array();

    return array(
        'id' => $cotizacion_id,

        'numero' => get_post_meta(
            $cotizacion_id,
            '_python_numero',
            true
        ),

        'fecha' => $cotizacion->post_date,

        'estado' => $estado,

        'productos' => $productos,

        'proforma' => $proforma,

        'tiene_proforma' => !empty($proforma),
    );
}

/**
 * Estilos de la página Mis cotizaciones.
 */
add_action('wp_enqueue_scripts', function () {

    if (!is_page('mis-cotizaciones')) {
        return;
    }

    $archivo = get_template_directory()
        . '/assets/css/mis-cotizaciones.css';

    wp_enqueue_style(
        'python-mis-cotizaciones',
        get_template_directory_uri()
            . '/assets/css/mis-cotizaciones.css',
        array(),
        file_exists($archivo)
            ? filemtime($archivo)
            : '1.0.0'
    );

});

/**
 * Procesar la respuesta comercial del cliente.
 */
function python_responder_proforma() {

    if (!is_user_logged_in()) {
        wp_die('Debes iniciar sesión.', '', array(
            'response' => 403,
        ));
    }

    $cotizacion_id = absint(
        $_POST['cotizacion_id'] ?? 0
    );

    check_admin_referer(
        'python_responder_proforma_' . $cotizacion_id,
        'python_proforma_nonce'
    );

    $detalle = python_obtener_detalle_cotizacion(
        $cotizacion_id
    );

    // Verificar la propiedad de la cotización.
    if (!$detalle) {
        wp_die('Acceso no autorizado.', '', array(
            'response' => 403,
        ));
    }

    $respuesta = sanitize_key(
        wp_unslash($_POST['respuesta'] ?? '')
    );

    $respuestas = array(
        'aprobar'  => 'aprobada',
        'rechazar' => 'rechazada',
    );

    if (!isset($respuestas[$respuesta])) {
        wp_die('Respuesta no válida.');
    }

    // Solo responder proformas enviadas.
    if (
        $detalle['estado'] !== 'enviada' ||
        !$detalle['tiene_proforma'] ||
        !isset($detalle['proforma']['totales']['total'])
    ) {
        wp_die('Esta proforma no admite respuestas.');
    }

    // Comprobar la versión comercial vigente.
    $version = sanitize_text_field(
        wp_unslash($_POST['version'] ?? '')
    );

    $version_actual = $detalle['proforma']['version'] ?? '';

    if (
        empty($version_actual) ||
        $version !== $version_actual
    ) {
        wp_die(
            'La proforma ha cambiado. Regresa a Mis cotizaciones y revisa la versión actual.'
        );
    }

    $nuevo_estado = $respuestas[$respuesta];

    // Actualizar únicamente si continúa enviada.
    $actualizado = update_post_meta(
        $cotizacion_id,
        '_python_estado_proforma',
        $nuevo_estado,
        'enviada'
    );

    if (!$actualizado) {
        wp_die('No fue posible actualizar la proforma.');
    }

    // Registrar la respuesta del cliente.
    update_post_meta(
        $cotizacion_id,
        '_python_respuesta_proforma',
        array(
            'estado'     => $nuevo_estado,
            'cliente_id' => get_current_user_id(),
            'fecha'      => current_time('mysql'),
            'version'    => $version_actual,
        )
    );

    // Regresar al detalle de la cotización.
    $url = add_query_arg(
        'cotizacion',
        $cotizacion_id,
        home_url('/mis-cotizaciones/')
    );

    wp_safe_redirect($url);
    exit;
}

add_action(
    'admin_post_python_responder_proforma',
    'python_responder_proforma'
);