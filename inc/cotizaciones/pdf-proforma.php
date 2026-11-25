<?php
/**
 * Python Print — Generador de proformas PDF.
 */

defined('ABSPATH') || exit;


/**
 * Cargar la biblioteca Dompdf.
 */
function python_cargar_dompdf() {

    if (class_exists('\Dompdf\Dompdf')) {
        return true;
    }

    $autoload = get_template_directory()
        . '/vendor/autoload.php';

    if (!is_readable($autoload)) {
        return false;
    }

    require_once $autoload;

    return class_exists('\Dompdf\Dompdf');

}


/**
 * Generar un PDF a partir de contenido HTML.
 *
 * @return string|WP_Error
 */
function python_generar_pdf($html) {

    if (!python_cargar_dompdf()) {

        return new WP_Error(
            'dompdf_no_disponible',
            'La biblioteca Dompdf no está disponible.'
        );

    }

    try {

        $opciones = new \Dompdf\Options();

        $opciones->set('defaultFont', 'DejaVu Sans');
        $opciones->set('isRemoteEnabled', false);
        $opciones->set('isPhpEnabled', false);
        $opciones->set('isJavascriptEnabled', false);

        $dompdf = new \Dompdf\Dompdf($opciones);

        $dompdf->loadHtml($html, 'UTF-8');

        $dompdf->setPaper('A4', 'portrait');

        $dompdf->render();

        return $dompdf->output();

    } catch (\Throwable $error) {

        return new WP_Error(
            'error_generar_pdf',
            'No fue posible generar el documento PDF.'
        );

    }

}

/**
 * Generar el PDF de una proforma publicada.
 *
 * Función interna: el acceso debe ser autorizado
 * por el módulo que solicita el documento.
 *
 * @return string|WP_Error
 */
function python_generar_pdf_proforma($cotizacion_id) {

    $cotizacion_id = absint($cotizacion_id);

    $cotizacion = get_post($cotizacion_id);

    // Validar la cotización.
    if (
        !$cotizacion ||
        $cotizacion->post_type !== 'python_cotizacion' ||
        $cotizacion->post_status !== 'private'
    ) {

        return new WP_Error(
            'cotizacion_no_valida',
            'La cotización no existe.'
        );

    }

    // Recuperar la versión comercial publicada.
    $proforma = get_post_meta(
        $cotizacion_id,
        '_python_proforma_publicada',
        true
    );

    if (
        !is_array($proforma) ||
        empty($proforma['version']) ||
        empty($proforma['productos']) ||
        !isset($proforma['totales']['total'])
    ) {

        return new WP_Error(
            'proforma_no_disponible',
            'No existe una proforma publicada.'
        );

    }

    // Recuperar el número de cotización.
    $numero = get_post_meta(
        $cotizacion_id,
        '_python_numero',
        true
    );

    // Recuperar los datos originales del cliente.
    $cliente = get_post_meta(
        $cotizacion_id,
        '_python_datos_cliente',
        true
    );

    if (!is_array($cliente)) {

        return new WP_Error(
            'cliente_no_disponible',
            'No existen datos del cliente.'
        );

    }

    // Localizar la plantilla comercial.
    $plantilla = get_template_directory()
        . '/template-parts/pdf/proforma.php';

    if (!is_readable($plantilla)) {

        return new WP_Error(
            'plantilla_no_disponible',
            'No se encontró la plantilla PDF.'
        );

    }

    // Recuperar el logotipo corporativo.
    $ruta_logo = get_template_directory()
        . '/assets/images/brand/logo-python-print.png';

    $logo = '';

    if (is_readable($ruta_logo)) {

        $contenido_logo = file_get_contents($ruta_logo);

        if ($contenido_logo !== false) {

            $logo = 'data:image/png;base64,' .
                base64_encode($contenido_logo);

        }

    }

    // Preparar los datos de la plantilla.
    $args = array(

        'cotizacion_id' => $cotizacion_id,

        'numero' => $numero,

        'proforma' => $proforma,

        'cliente' => $cliente,

        'logo' => $logo,

    );

    // Generar el contenido HTML.
    ob_start();

    include $plantilla;

    $html = ob_get_clean();

    // Convertir el HTML en PDF.
    return python_generar_pdf($html);

}
/**
 * Descargar el PDF de una proforma.
 */
function python_descargar_pdf_proforma() {

    // Comprobar que el cliente inició sesión.
    if (!is_user_logged_in()) {

        wp_die(
            'Debes iniciar sesión.',
            '',
            array('response' => 403)
        );

    }

    // Recuperar el identificador.
    $cotizacion_id = absint(
        $_GET['cotizacion_id'] ?? 0
    );

    // Recuperar la versión solicitada.
    $version = sanitize_text_field(
        wp_unslash($_GET['version'] ?? '')
    );

    // Verificar el nonce de seguridad.
    check_admin_referer(
        'python_descargar_pdf_' . $cotizacion_id . '_' . $version,
        'python_pdf_nonce'
    );

    // Verificar la propiedad de la cotización.
    $detalle = python_obtener_detalle_cotizacion(
        $cotizacion_id
    );

    if (!$detalle || !$detalle['tiene_proforma']) {

        wp_die(
            'No tienes acceso a esta proforma.',
            '',
            array('response' => 403)
        );

    }

    // Comprobar la versión comercial publicada.
    $version_actual = $detalle['proforma']['version'] ?? '';

    if (
        empty($version_actual) ||
        $version !== $version_actual
    ) {

        wp_die(
            'La proforma ha cambiado. Revisa la versión actual.',
            '',
            array('response' => 409)
        );

    }

    // Generar el documento.
    $pdf = python_generar_pdf_proforma(
        $cotizacion_id
    );

    if (is_wp_error($pdf)) {

        wp_die(
            esc_html($pdf->get_error_message()),
            '',
            array('response' => 500)
        );

    }

    // Comprobar que la versión no cambió durante la generación.
    $proforma_actual = get_post_meta(
        $cotizacion_id,
        '_python_proforma_publicada',
        true
    );

    if (
        ($proforma_actual['version'] ?? '') !== $version
    ) {

        wp_die(
            'La proforma ha cambiado. Intenta nuevamente.',
            '',
            array('response' => 409)
        );

    }

    // Preparar el nombre del archivo.
    $nombre = sanitize_file_name(
        'Proforma-' . $detalle['numero'] . '.pdf'
    );

    // Evitar el almacenamiento en caché.
    nocache_headers();

    header('Cache-Control: private, no-store, max-age=0');

    // Configurar la descarga.
    header('Content-Type: application/pdf');

    header(
        'Content-Disposition: attachment; filename="' .
        $nombre . '"'
    );

    header('X-Content-Type-Options: nosniff');

    echo $pdf;

    exit;

}

add_action(
    'admin_post_python_descargar_pdf_proforma',
    'python_descargar_pdf_proforma'
);