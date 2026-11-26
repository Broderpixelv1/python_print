<?php
/**
 * Python Print — Plantilla general de correos.
 */

defined('ABSPATH') || exit;

$titulo = $args['titulo'] ?? '';
$mensaje = $args['mensaje'] ?? '';
$boton_texto = $args['boton_texto'] ?? '';
$boton_url = $args['boton_url'] ?? '';
$detalles = $args['detalles'] ?? array();
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

    <title><?php echo esc_html($titulo); ?></title>

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
    style="background-color:#f0f3fa;"
>

<tr>

<td align="center" style="padding:30px 12px;">

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
            border-radius:16px;
        "
    >

        <!-- ENCABEZADO -->

        <tr>

            <td align="center" style="
                background-color:#182235;
                padding:20px 24px;
                border-radius:16px 16px 0 0;
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
                           padding:10px 16px;
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

        <!-- CONTENIDO -->

        <tr>

            <td style="padding:36px 28px;">

                <h1 style="
                    font-size:26px;
                    line-height:1.3;
                    color:#182235;
                    margin:0 0 24px;
                ">

                    <?php echo esc_html($titulo); ?>

                </h1>

                <div style="
                    font-size:15px;
                    line-height:1.8;
                    color:#667085;
                ">

                    <?php echo wp_kses_post($mensaje); ?>

                </div>

                <!-- DETALLES -->

                <?php if (!empty($detalles)) : ?>

                    <table
                        role="presentation"
                        width="100%"
                        cellspacing="0"
                        cellpadding="0"
                        border="0"
                        style="
                            background-color:#f0f3fa;
                            border-radius:12px;
                            margin:24px 0;
                        "
                    >

                        <?php foreach ($detalles as $etiqueta => $valor) : ?>

                            <tr>

                                <td style="
                                    padding:10px 16px;
                                    line-height:1.4;
                                    font-size:14px;
                                    color:#667085;
                                ">

                                    <?php echo esc_html($etiqueta); ?>

                                </td>

                                <td align="right" style="
                                    padding:10px 16px;
                                    line-height:1.4;
                                    font-size:14px;
                                    font-weight:bold;
                                    color:#182235;
                                ">

                                    <?php echo esc_html($valor); ?>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    </table>

                <?php endif; ?>

                <!-- BOTÓN -->

                <?php if ($boton_texto && $boton_url) : ?>

                    <table
                        role="presentation"
                        width="100%"
                        cellspacing="0"
                        cellpadding="0"
                        border="0"
                    >

                        <tr>

                            <td align="center" style="padding:24px 0;">

                                <table
                                    role="presentation"
                                    align="center"
                                    cellspacing="0"
                                    cellpadding="0"
                                    border="0"
                                >

                                    <tr>

                                        <td
                                            align="center"
                                            bgcolor="#f25462"
                                            style="
                                                border-radius:8px;
                                                padding:16px 32px;
                                            "
                                        >

                                            <a
                                                href="<?php echo esc_url($boton_url); ?>"
                                                style="
                                                    display:block;
                                                    color:#ffffff;
                                                    font-size:15px;
                                                    line-height:1.4;
                                                    font-weight:bold;
                                                    text-decoration:none;
                                                "
                                            >

                                                <?php echo esc_html($boton_texto); ?>

                                            </a>

                                        </td>

                                    </tr>

                                </table>

                            </td>

                        </tr>

                    </table>

                <?php endif; ?>

                <p style="
                    margin:30px 0 0;
                    font-size:14px;
                    line-height:1.7;
                    color:#667085;
                ">

                    Gracias por confiar en nosotros.<br>

                    <strong style="color:#182235;">
                        Equipo Python Print
                    </strong>

                </p>

            </td>

        </tr>

        <!-- PIE DE CORREO -->

        <tr>

            <td align="center" style="
                background-color:#182235;
                padding:26px 20px;
                border-radius:0 0 16px 16px;
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