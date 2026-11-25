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
                padding:32px 24px;
                border-radius:16px 16px 0 0;
            ">

                <span style="
                    font-size:30px;
                    font-weight:bold;
                    color:#ffffff;
                ">python<span style="color:#f25462;">print</span></span>

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
                                    padding:14px 18px;
                                    font-size:14px;
                                    color:#667085;
                                ">

                                    <?php echo esc_html($etiqueta); ?>

                                </td>

                                <td align="right" style="
                                    padding:14px 18px;
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
                        cellspacing="0"
                        cellpadding="0"
                        border="0"
                        style="margin:28px auto;"
                    >

                        <tr>

                            <td
                                align="center"
                                bgcolor="#f25462"
                                style="border-radius:10px;"
                            >

                                <a
                                    href="<?php echo esc_url($boton_url); ?>"
                                    style="
                                        display:inline-block;
                                        padding:16px 28px;
                                        color:#ffffff;
                                        font-size:15px;
                                        font-weight:bold;
                                        text-decoration:none;
                                    "
                                >

                                    <?php echo esc_html($boton_texto); ?>

                                </a>

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