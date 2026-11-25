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

    // Recuperar el estado comercial efectivo.
    $estado = python_obtener_estado_comercial_proforma(
        $cotizacion_id
    );

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

        python_redirigir_aviso_proforma(
            $cotizacion_id,
            'actualizada'
        );

    }

    // Comprobar que la proforma no haya vencido.
    if (!python_proforma_esta_vigente($detalle['proforma'])) {

        wp_die(
            'Esta proforma ha vencido. Comunícate con Python Print para solicitar una actualización de tu cotización.',
            'Proforma vencida',
            array(
                'response' => 409,
                'back_link' => true,
            )
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

/**
 * Procesar los enlaces comerciales enviados por correo.
 */
function python_procesar_enlace_proforma() {

    // Ejecutar únicamente en Mis cotizaciones.
    if (!is_page('mis-cotizaciones')) {
        return;
    }

    // Comprobar si existe una acción comercial.
    if (
        !isset($_GET['accion']) ||
        !is_string($_GET['accion'])
    ) {
        return;
    }

    $accion = sanitize_key(
        wp_unslash($_GET['accion'])
    );

    // Permitir únicamente acciones autorizadas.
    if (!in_array(
        $accion,
        array('aprobar', 'descargar'),
        true
    )) {
        return;
    }

    nocache_headers();

    // Si no existe sesión, mostrar el acceso habitual.
    if (!is_user_logged_in()) {
        return;
    }

    // Recuperar el identificador de la cotización.
    $cotizacion_id = isset($_GET['cotizacion'])
        && is_string($_GET['cotizacion'])
        ? absint($_GET['cotizacion'])
        : 0;

    // Recuperar la versión solicitada.
    $version = isset($_GET['version'])
        && is_string($_GET['version'])
        ? sanitize_text_field(
            wp_unslash($_GET['version'])
        )
        : '';

    // Verificar la propiedad de la cotización.
    $detalle = python_obtener_detalle_cotizacion(
        $cotizacion_id
    );

    if (!$detalle || !$detalle['tiene_proforma']) {

        wp_die(
            'No tienes autorización para acceder a esta proforma.',
            'Acceso no autorizado',
            array('response' => 403)
        );

    }

    // Comprobar la versión comercial.
    $version_actual = $detalle['proforma']['version'] ?? '';

    if (
        empty($version_actual) ||
        $version !== $version_actual
    ) {

        python_redirigir_aviso_proforma(
            $cotizacion_id,
            'actualizada'
        );

    }
    // Procesar la descarga del PDF.
    if ($accion === 'descargar') {

        $url_pdf = add_query_arg(
            array(
                'action' => 'python_descargar_pdf_proforma',
                'cotizacion_id' => $cotizacion_id,
                'version' => $version_actual,
            ),
            admin_url('admin-post.php')
        );

        $url_pdf = add_query_arg(
            'python_pdf_nonce',
            wp_create_nonce(
                'python_descargar_pdf_' .
                $cotizacion_id . '_' .
                $version_actual
            ),
            $url_pdf
        );

        wp_safe_redirect($url_pdf);
        exit;

    }

    // La aprobación se confirma desde el detalle.
    // Nunca aprobar automáticamente mediante GET.

}

add_action(
    'template_redirect',
    'python_procesar_enlace_proforma',
    10
);

/**
 * Redirigir al detalle con un aviso comercial.
 */
function python_redirigir_aviso_proforma(
    $cotizacion_id,
    $aviso
) {

    $avisos_permitidos = array(
        'actualizada',
        'vencida',
        'no_disponible',
    );

    if (!in_array(
        $aviso,
        $avisos_permitidos,
        true
    )) {
        $aviso = 'no_disponible';
    }

    $url = add_query_arg(
        array(
            'cotizacion' => absint($cotizacion_id),
            'aviso' => $aviso,
        ),
        home_url('/mis-cotizaciones/')
    );

    nocache_headers();

    wp_safe_redirect($url, 303);
    exit;

}