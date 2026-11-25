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