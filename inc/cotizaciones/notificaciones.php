<?php
/**
 * Python Print — Motor de notificaciones por correo.
 */

defined('ABSPATH') || exit;


/**
 * Enviar un correo electrónico de Python Print.
 *
 * @return bool
 */
function python_enviar_correo($datos = array()) {

    // Configuración predeterminada.
    $datos = wp_parse_args(
        $datos,
        array(
            'destinatario' => '',
            'asunto'       => '',
            'titulo'       => '',
            'mensaje'      => '',
            'boton_texto'  => '',
            'boton_url'    => '',
            'detalles'     => array(),
            'adjuntos'     => array(),
            'plantilla'    => 'base',
        )
    );

    // Validar el destinatario.
    $destinatario = sanitize_email(
        $datos['destinatario']
    );

    if (!is_email($destinatario)) {
        return false;
    }

    // Validar el asunto.
    $asunto = sanitize_text_field(
        $datos['asunto']
    );

    if ($asunto === '') {
        return false;
    }

    // Plantillas autorizadas.
    $plantillas = array(
        'base' => 'plantilla-base.php',
        'proforma' => 'plantilla-proforma.php',
    );

    if (!isset($plantillas[$datos['plantilla']])) {
        return false;
    }

    // Localizar la plantilla.
    $archivo = get_template_directory()
        . '/template-parts/emails/'
        . $plantillas[$datos['plantilla']];

    if (!is_readable($archivo)) {
        return false;
    }

    // Preparar los archivos adjuntos.
    $adjuntos = array();

    foreach ((array) $datos['adjuntos'] as $adjunto) {

        if (!is_string($adjunto)) {
            return false;
        }

        if (!is_file($adjunto) || !is_readable($adjunto)) {
            return false;
        }

        $adjuntos[] = $adjunto;

    }

    // Preparar el contenido HTML.
    $args = $datos;

    ob_start();

    include $archivo;

    $contenido = ob_get_clean();

    if (trim($contenido) === '') {
        return false;
    }

    // Configurar las cabeceras.
    $cabeceras = array(
        'Content-Type: text/html; charset=UTF-8',
        'From: Python Print <info@python.com.ec>',
        'Reply-To: Python Print <info@python.com.ec>',
    );

    // Enviar el correo.
    $enviado = wp_mail(
        $destinatario,
        $asunto,
        $contenido,
        $cabeceras,
        $adjuntos
    );

    // Registrar el resultado sin exponer datos personales.
    if (function_exists('wc_get_logger')) {

        $logger = wc_get_logger();

        $contexto = array(
            'source' => 'python-notificaciones',
        );

        if ($enviado) {

            $logger->info(
                'Correo aceptado por el sistema de envío.',
                $contexto
            );

        } else {

            $logger->error(
                'No fue posible enviar el correo.',
                $contexto
            );

        }

    }

    return $enviado;

}

/**
 * Generar enlaces comerciales de una proforma publicada.
 *
 * @return array|WP_Error
 */
function python_generar_enlaces_proforma($cotizacion_id) {

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
        empty($proforma['version'])
    ) {

        return new WP_Error(
            'proforma_no_disponible',
            'No existe una proforma publicada.'
        );

    }

    // Construir los enlaces comerciales.
    $parametros = array(
        'cotizacion' => $cotizacion_id,
        'version'    => $proforma['version'],
    );

    $url_detalle = add_query_arg(
        $parametros,
        home_url('/mis-cotizaciones/')
    );

    $url_aprobar = add_query_arg(
        'accion',
        'aprobar',
        $url_detalle
    ) . '#python-proforma-acciones';

    $url_descargar = add_query_arg(
        'accion',
        'descargar',
        $url_detalle
    );

    return array(

        'detalle'   => $url_detalle,

        'aprobar'   => $url_aprobar,

        'descargar' => $url_descargar,

    );

}


/**
 * Enviar una proforma comercial al cliente.
 *
 * @return true|WP_Error
 */
function python_enviar_correo_proforma($cotizacion_id) {

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

    // Recuperar la proforma publicada.
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

    // Comprobar el estado comercial.
    $estado = python_obtener_estado_comercial_proforma(
        $cotizacion_id
    );

    if ($estado !== 'enviada') {

        return new WP_Error(
            'estado_no_valido',
            'La proforma no está disponible para envío comercial.'
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

    // Validar el correo del destinatario.
    $email = sanitize_email(
        $cliente['email'] ?? ''
    );

    if (!is_email($email)) {

        return new WP_Error(
            'correo_no_valido',
            'El cliente no tiene un correo electrónico válido.'
        );

    }

    // Generar los enlaces comerciales.
    $enlaces = python_generar_enlaces_proforma(
        $cotizacion_id
    );

    if (is_wp_error($enlaces)) {
        return $enlaces;
    }

    // Generar el documento PDF.
    $pdf = python_generar_pdf_proforma(
        $cotizacion_id
    );

    if (is_wp_error($pdf)) {
        return $pdf;
    }

    // Crear un directorio temporal exclusivo.
    $directorio = trailingslashit(
        sys_get_temp_dir()
    ) . 'python-proforma-' . wp_generate_uuid4();

    if (!mkdir($directorio, 0700)) {

        return new WP_Error(
            'directorio_temporal',
            'No fue posible preparar el archivo adjunto.'
        );

    }

    // Preparar el nombre comercial del PDF.
    $nombre_archivo = sanitize_file_name(
        'Proforma-' . $numero . '.pdf'
    );

    $archivo = trailingslashit($directorio)
        . $nombre_archivo;

    try {

        // Guardar temporalmente el PDF.
        $bytes = file_put_contents(
            $archivo,
            $pdf,
            LOCK_EX
        );

        if (
            $bytes === false ||
            $bytes !== strlen($pdf)
        ) {

            return new WP_Error(
                'archivo_temporal',
                'No fue posible preparar el documento PDF.'
            );

        }

        // Restringir los permisos del archivo.
        chmod($archivo, 0600);

        // Comprobar que la versión comercial no cambió.
        $proforma_actual = get_post_meta(
            $cotizacion_id,
            '_python_proforma_publicada',
            true
        );

        if (
            ($proforma_actual['version'] ?? '') !==
            $proforma['version']
        ) {

            return new WP_Error(
                'version_modificada',
                'La proforma cambió durante la preparación del correo.'
            );

        }

        // Comprobar nuevamente el estado comercial.
        if (
            python_obtener_estado_comercial_proforma(
                $cotizacion_id
            ) !== 'enviada'
        ) {

            return new WP_Error(
                'estado_modificado',
                'El estado comercial de la proforma ha cambiado.'
            );

        }

        // Preparar y enviar el correo.
        $enviado = python_enviar_correo(
            array(

                'destinatario' => $email,

                'asunto' => 'Tu proforma ' .
                    $numero . ' está lista | Python Print',

                'titulo' => '¡Tu proforma está lista!',

                'plantilla' => 'proforma',

                'numero' => $numero,

                'cliente' => $cliente,

                'proforma' => $proforma,

                'enlaces' => $enlaces,

                'adjuntos' => array($archivo),

            )
        );

        if (!$enviado) {

            return new WP_Error(
                'correo_no_enviado',
                'No fue posible enviar el correo de la proforma.'
            );

        }

        return true;

    } catch (\Throwable $error) {

        return new WP_Error(
            'error_correo_proforma',
            'Ocurrió un error al preparar o enviar la proforma.'
        );

    } finally {

        // Eliminar el archivo temporal.
        if (is_file($archivo)) {
            unlink($archivo);
        }

        // Eliminar el directorio temporal.
        if (is_dir($directorio)) {
            rmdir($directorio);
        }

    }

}
