
<?php
/**
 * Python Print — Correo comercial de proformas.
 */

defined('ABSPATH') || exit;

// Recuperar los datos comerciales.
$numero = $args['numero'] ?? '';
$cliente = $args['cliente'] ?? array();
$proforma = $args['proforma'] ?? array();
$enlaces = $args['enlaces'] ?? array();

$productos = $proforma['productos'] ?? array();
$totales = $proforma['totales'] ?? array();

$nombre = $cliente['nombres'] ?? 'Cliente';

// Formato monetario.
$moneda = static function ($valor) {

    return '$' . number_format(
        (float) $valor,
        2,
        '.',
        ','
    );

};

// Fechas comerciales.
$fecha_emision = !empty($proforma['fecha_emision'])
    ? mysql2date('d/m/Y', $proforma['fecha_emision'])
    : '';

$vencimiento = python_obtener_vencimiento_proforma(
    $proforma
);

$fecha_vencimiento = $vencimiento
    ? $vencimiento->format('d/m/Y')
    : '';

// Etiquetas comerciales.
$etiqueta_descuento = 'Descuento';

if (
    ($proforma['descuento_tipo'] ?? '') === 'porcentaje'
    && !empty($proforma['descuento'])
) {

    $etiqueta_descuento .= ' (' .
        $proforma['descuento'] . '%)';

}

$etiqueta_iva = ($proforma['iva'] ?? 'sin') === 'con'
    ? 'IVA (' . (($totales['tasa_iva'] ?? 0) * 100) . '%)'
    : 'IVA (no aplicado)';

// Resumen económico.
$resumen = array(
    'Subtotal' => $moneda($totales['subtotal'] ?? 0),

    $etiqueta_descuento =>
        '-' . $moneda($totales['descuento'] ?? 0),

    'Base imponible' =>
        $moneda($totales['base_imponible'] ?? 0),

    $etiqueta_iva =>
        $moneda($totales['iva'] ?? 0),

    'Envío' =>
        $moneda($totales['envio'] ?? 0),
);

// Logotipo corporativo.
$logo_url = get_template_directory_uri()
    . '/assets/images/brand/logo-python-print.png';

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Tu proforma está lista</title>

</head>

<body style="
    margin:0;
    padding:0;
    background-color:#f0f3fa;
    font-family:Arial,Helvetica,sans-serif;
    color:#182235;
">

<table
    role="presentation"
    width="100%"
    cellspacing="0"
    cellpadding="0"
    border="0"
    bgcolor="#f0f3fa"
>

<tr>

<td align="center" style="padding:24px 10px;">

    <table
        role="presentation"
        width="600"
        cellspacing="0"
        cellpadding="0"
        border="0"
        style="
            width:100%;
            max-width:600px;
            background-color:#ffffff;
        "
    >

        <!-- ENCABEZADO -->

        <tr>

            <td align="center" style="
                background-color:#182235;
                padding:28px 20px;
            ">

                <table
                    role="presentation"
                    cellspacing="0"
                    cellpadding="0"
                    border="0"
                >

                    <tr>

                        <td style="
                            background-color:#ffffff;
                            padding:14px 20px;
                            border-radius:8px;
                        ">

                            <img
                                src="<?php echo esc_url($logo_url); ?>"
                                alt="Python Print"
                                width="180"
                                style="
                                    display:block;
                                    width:180px;
                                    max-width:100%;
                                    height:auto;
                                "
                            >

                        </td>

                    </tr>

                </table>

                <p style="
                    color:#ffffff;
                    font-size:11px;
                    letter-spacing:1px;
                    margin:16px 0 0;
                ">

                    SOLUCIONES DE IDENTIDAD TEXTIL

                </p>

            </td>

        </tr>

        <!-- PRESENTACIÓN -->

        <tr>

            <td style="padding:28px 24px;">

                <p style="
                    color:#667085;
                    font-size:11px;
                    letter-spacing:1px;
                    margin:0 0 12px;
                ">

                    COTIZACIONES · PYTHON PRINT

                </p>

                <h1 style="
                    color:#182235;
                    font-size:28px;
                    line-height:1.3;
                    margin:0 0 20px;
                ">

                    ¡Tu proforma está lista!

                </h1>

                <p style="
                    color:#667085;
                    font-size:15px;
                    line-height:1.7;
                ">

                    Hola, <?php echo esc_html($nombre); ?>.

                </p>

                <p style="
                    color:#667085;
                    font-size:15px;
                    line-height:1.7;
                ">

                    Hemos preparado una propuesta comercial
                    para tus productos personalizados.
                    Revisa los detalles y aprueba tu proforma
                    para continuar con tu pedido.

                </p>

                <!-- IDENTIFICACIÓN Y VIGENCIA -->

                <table
                    role="presentation"
                    width="100%"
                    cellspacing="0"
                    cellpadding="0"
                    border="0"
                    bgcolor="#f5f6f9"
                    style="margin:24px 0;"
                >

                    <tr>

                        <td colspan="2" style="
                            padding:18px;
                            font-size:20px;
                            font-weight:bold;
                            color:#182235;
                        ">

                            <?php echo esc_html($numero); ?>

                        </td>

                    </tr>

                    <tr>

                        <td width="50%" style="
                            padding:0 18px 18px;
                            font-size:13px;
                            color:#667085;
                        ">

                            Fecha de emisión

                            <br>

                            <strong style="color:#182235;">

                                <?php echo esc_html(
                                    $fecha_emision
                                ); ?>

                            </strong>

                        </td>

                        <td width="50%" style="
                            padding:0 18px 18px;
                            font-size:13px;
                            color:#667085;
                        ">

                            Válida hasta

                            <br>

                            <strong style="color:#182235;">

                                <?php echo esc_html(
                                    $fecha_vencimiento
                                ); ?>

                            </strong>

                        </td>

                    </tr>

                </table>

                <!-- PRODUCTOS -->

                <h2 style="
                    font-size:18px;
                    margin:28px 0 16px;
                    color:#182235;
                ">

                    Detalle de tu cotización

                </h2>

                <table
                    width="100%"
                    cellspacing="0"
                    cellpadding="0"
                    border="0"
                    style="border-collapse:collapse;"
                >

                    <?php foreach ($productos as $indice => $producto) : ?>

                        <?php

                        $config = $producto['configuracion']
                            ?? array();

                        $cantidad = (int) (
                            $config['cantidad']
                            ?? $producto['cantidad']
                            ?? 0
                        );

                        $precio = (float) (
                            $proforma['precios'][$indice] ?? 0
                        );

                        $precio_unitario = $cantidad > 0
                            ? $precio / $cantidad
                            : 0;

                        $campos = array(
                            'tipo' => 'Tipo',
                            'material' => 'Material',
                            'ancho' => 'Ancho',
                            'longitud' => 'Largo',
                        );

                        ?>

                        <tr>

                            <td style="
                                padding:16px 0;
                                border-bottom:1px solid #e4e7ec;
                                vertical-align:top;
                            ">

                                <strong style="
                                    font-size:15px;
                                    color:#182235;
                                ">

                                    <?php echo esc_html(
                                        $producto['nombre']
                                        ?? 'Producto'
                                    ); ?>

                                </strong>

                                <?php foreach ($campos as $clave => $etiqueta) : ?>

                                    <?php

                                    $valor = $config[$clave] ?? '';

                                    if (
                                        !is_scalar($valor)
                                        || $valor === ''
                                    ) {
                                        continue;
                                    }

                                    if (
                                        in_array(
                                            $clave,
                                            array('ancho', 'longitud'),
                                            true
                                        )
                                        && is_numeric($valor)
                                    ) {

                                        $valor = number_format_i18n(
                                            (float) $valor / 10,
                                            1
                                        ) . ' cm';

                                    }

                                    ?>

                                    <div style="
                                        font-size:12px;
                                        line-height:1.7;
                                        color:#667085;
                                    ">

                                        <?php echo esc_html(
                                            $etiqueta . ': ' . $valor
                                        ); ?>

                                    </div>

                                <?php endforeach; ?>

                                <div style="
                                    font-size:12px;
                                    line-height:1.7;
                                    color:#667085;
                                ">

                                    Cantidad:

                                    <?php echo esc_html($cantidad); ?>

                                    unidades

                                </div>

                                <?php if ($cantidad > 0) : ?>

                                    <div style="
                                        font-size:12px;
                                        line-height:1.7;
                                        color:#667085;
                                    ">

                                        Precio unitario:

                                        <?php echo esc_html(
                                            '$' . number_format(
                                                $precio_unitario,
                                                6,
                                                '.',
                                                ','
                                            )
                                        ); ?>

                                    </div>

                                <?php endif; ?>

                                <?php foreach (
                                    (array) ($config['tallas'] ?? array())
                                    as $fila
                                ) : ?>

                                    <?php if (!is_array($fila)) continue; ?>

                                    <div style="
                                        font-size:12px;
                                        line-height:1.7;
                                        color:#667085;
                                    ">

                                        Talla

                                        <?php echo esc_html(
                                            $fila['talla'] ?? ''
                                        ); ?>:

                                        <?php echo esc_html(
                                            (int) ($fila['cantidad'] ?? 0)
                                        ); ?>

                                        unidades

                                    </div>

                                <?php endforeach; ?>

                            </td>

                            <td align="right" style="
                                padding:16px 0 16px 12px;
                                border-bottom:1px solid #e4e7ec;
                                vertical-align:top;
                                font-size:15px;
                                font-weight:bold;
                                white-space:nowrap;
                            ">

                                <?php echo esc_html(
                                    $moneda($precio)
                                ); ?>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                </table>

                <!-- RESUMEN ECONÓMICO -->

                <table
                    width="100%"
                    cellspacing="0"
                    cellpadding="0"
                    border="0"
                    style="margin:24px 0 16px;"
                >

                    <?php foreach ($resumen as $etiqueta => $valor) : ?>

                        <tr>

                            <td style="
                                padding:7px 0;
                                color:#667085;
                                font-size:14px;
                            ">

                                <?php echo esc_html($etiqueta); ?>

                            </td>

                            <td align="right" style="
                                padding:7px 0;
                                color:#182235;
                                font-size:14px;
                            ">

                                <?php echo esc_html($valor); ?>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                </table>

                <!-- TOTAL -->

                <table
                    width="100%"
                    cellspacing="0"
                    cellpadding="0"
                    border="0"
                    bgcolor="#182235"
                >

                    <tr>

                        <td style="
                            padding:20px 16px;
                            color:#ffffff;
                            font-size:15px;
                            font-weight:bold;
                        ">

                            Total de tu proforma

                        </td>

                        <td align="right" style="
                            padding:20px 16px;
                            color:#ffffff;
                            font-size:26px;
                            font-weight:bold;
                        ">

                            <?php echo esc_html(
                                $moneda($totales['total'] ?? 0)
                            ); ?>

                        </td>

                    </tr>

                    <tr>

                        <td colspan="2" style="
                            padding:0 16px 16px;
                            color:#d0d5dd;
                            font-size:11px;
                        ">

                            Valores expresados en dólares estadounidenses.

                        </td>

                    </tr>

                </table>

                <!-- OBSERVACIONES -->

                <?php if (!empty($proforma['observaciones'])) : ?>

                    <div style="
                        background-color:#f5f6f9;
                        padding:16px;
                        margin:20px 0;
                        font-size:13px;
                        line-height:1.7;
                    ">

                        <strong>Observaciones</strong>

                        <br>

                        <?php echo nl2br(
                            esc_html($proforma['observaciones'])
                        ); ?>

                    </div>

                <?php endif; ?>

                <!-- BOTONES COMERCIALES -->

                <table
                    role="presentation"
                    width="100%"
                    cellspacing="0"
                    cellpadding="0"
                    border="0"
                    style="margin:28px 0 12px;"
                >

                    <?php if (!empty($enlaces['aprobar'])) : ?>

                        <tr>

                            <td align="center" bgcolor="#f25462" style="
                                border-radius:8px;
                            ">

                                <a
                                    href="<?php echo esc_url(
                                        $enlaces['aprobar']
                                    ); ?>"
                                    style="
                                        display:block;
                                        padding:18px 12px;
                                        color:#ffffff;
                                        font-size:15px;
                                        font-weight:bold;
                                        text-decoration:none;
                                    "
                                >

                                    Revisar y aprobar proforma

                                </a>

                            </td>

                        </tr>

                        <tr>
                            <td height="12"></td>
                        </tr>

                    <?php endif; ?>

                    <?php if (!empty($enlaces['descargar'])) : ?>

                        <tr>

                            <td align="center" style="
                                border:1px solid #182235;
                                border-radius:8px;
                            ">

                                <a
                                    href="<?php echo esc_url(
                                        $enlaces['descargar']
                                    ); ?>"
                                    style="
                                        display:block;
                                        padding:16px 12px;
                                        color:#182235;
                                        font-size:15px;
                                        font-weight:bold;
                                        text-decoration:none;
                                    "
                                >

                                    Descargar proforma PDF

                                </a>

                            </td>

                        </tr>

                    <?php endif; ?>

                </table>

                <!-- ARCHIVO ADJUNTO -->

                <?php if (!empty($args['adjuntos'])) : ?>

                    <table
                        role="presentation"
                        width="100%"
                        cellspacing="0"
                        cellpadding="0"
                        border="0"
                        bgcolor="#f5f6f9"
                        style="margin:24px 0;"
                    >

                        <tr>

                            <td style="padding:18px;">

                                <strong style="font-size:13px;">

                                    Proforma-<?php echo esc_html(
                                        $numero
                                    ); ?>.pdf

                                </strong>

                                <p style="
                                    font-size:12px;
                                    color:#667085;
                                    margin:6px 0 0;
                                ">

                                    Documento incluido como archivo adjunto.

                                </p>

                            </td>

                        </tr>

                    </table>

                <?php endif; ?>

                <!-- DESPEDIDA -->

                <p style="
                    color:#667085;
                    font-size:14px;
                    line-height:1.7;
                    margin:28px 0 20px;
                ">

                    ¿Tienes alguna consulta sobre tu cotización?
                    Responde directamente a este correo y nuestro
                    equipo comercial te ayudará.

                </p>

                <p style="
                    font-size:14px;
                    line-height:1.7;
                    color:#182235;
                ">

                    Gracias por confiar en nosotros.

                    <br>

                    <strong>Equipo Python Print</strong>

                </p>

            </td>

        </tr>

        <!-- PIE DE CORREO -->

        <tr>

            <td align="center" bgcolor="#182235" style="
                padding:26px 20px;
                color:#ffffff;
                font-size:12px;
                line-height:1.8;
            ">

                <strong>Python Print</strong>

                <br>

                Etiquetas textiles y productos personalizados

                <br>

                Quito, Ecuador

                <br>

                <a
                    href="mailto:info@python.com.ec"
                    style="color:#ffffff;"
                >

                    info@python.com.ec

                </a>

                <br><br>

                &copy; <?php echo esc_html(wp_date('Y')); ?>

                Python Print.

            </td>

        </tr>

    </table>

</td>

</tr>

</table>

</body>

</html>
