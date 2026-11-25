<?php
/**
 * Python Print — Plantilla comercial PDF.
 */

defined('ABSPATH') || exit;

$numero = $args['numero'] ?? '';
$proforma = $args['proforma'] ?? array();
$logo = $args['logo'] ?? '';


$cliente = $args['cliente'] ?? array();

$es_empresa = (
    ($cliente['tipo_cliente'] ?? '') === 'empresa'
);

$nombre_cliente = trim(
    ($cliente['nombres'] ?? '') . ' ' .
    ($cliente['apellidos'] ?? '')
);

$identificacion = $es_empresa
    ? ($cliente['ruc'] ?? '')
    : ($cliente['cedula'] ?? '');

$metodos_entrega = array(
    'retiro' => 'Retiro en nuestras instalaciones',
    'envio'  => 'Envío a domicilio',
);

$metodo_entrega = $metodos_entrega[
    $cliente['metodo_entrega'] ?? ''
] ?? 'Por confirmar';

$productos = $proforma['productos'] ?? array();
$totales = $proforma['totales'] ?? array();

$moneda = static function ($valor) {
    return '$' . number_format(
        (float) $valor,
        2,
        '.',
        ','
    );
};

$fecha = !empty($proforma['fecha_emision'])
    ? mysql2date('d/m/Y', $proforma['fecha_emision'])
    : '';

$vigencia_dias = (int) (
    $proforma['vigencia_dias'] ?? 8
);

$fecha_vencimiento = '';

if (!empty($proforma['fecha_vencimiento'])) {

    $fecha_vencimiento = mysql2date(
        'd/m/Y',
        $proforma['fecha_vencimiento']
    );

} elseif (!empty($proforma['fecha_emision'])) {

    $fecha_original = new DateTimeImmutable(
        $proforma['fecha_emision'],
        wp_timezone()
    );

    $fecha_vencimiento = $fecha_original
        ->modify('+' . $vigencia_dias . ' days')
        ->format('d/m/Y');

}

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

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <style>

        .logo-corporativo {
            display: block;
            width: 180px;
            height: auto;
            margin-bottom: 8px;
        }

        @page {
            margin: 35px;
        }

        body {
            font-family: "DejaVu Sans", sans-serif;
            font-size: 10px;
            color: #182235;
            line-height: 1.5;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .header td {
            vertical-align: top;
        }

        .logo {
            font-size: 28px;
            font-weight: bold;
        }

        .logo span {
            color: #f25462;
        }

        .documento {
            font-size: 23px;
            font-weight: bold;
        }

        .derecha {
            text-align: right;
        }

        .muted {
            color: #667085;
        }

        .separador {
            border: 0;
            border-top: 3px solid #f25462;
            margin: 20px 0;
        }

        .titulo-seccion {
            font-size: 12px;
            font-weight: bold;
            margin: 20px 0 10px;
        }

        .productos thead {
            display: table-header-group;
        }

        .productos th {
            background: #182235;
            color: #ffffff;
            padding: 10px;
            text-align: left;
        }

        .productos td {
            padding: 12px 10px;
            border-bottom: 1px solid #e4e7ec;
            vertical-align: top;
        }

        .productos tr {
            page-break-inside: avoid;
        }

        .productos .derecha {
            text-align: right;
        }

        .especificacion {
            font-size: 9px;
            color: #667085;
        }

        .resumen {
            margin-top: 25px;
            page-break-inside: avoid;
        }

        .resumen td {
            padding: 6px 10px;
        }

        .total td {
            background: #182235;
            color: #ffffff;
            font-size: 15px;
            font-weight: bold;
            padding: 12px 10px;
        }

        .observaciones {
            margin-top: 25px;
            padding: 12px;
            background: #f0f3fa;
        }

        .pie {
            margin-top: 35px;
            padding-top: 15px;
            border-top: 1px solid #e4e7ec;
            text-align: center;
            color: #667085;
            font-size: 9px;
        }

        .datos-cliente {
            margin-bottom: 25px;
            background: #f0f3fa;
            page-break-inside: avoid;
        }

        .datos-cliente td {
            width: 50%;
            padding: 15px;
            vertical-align: top;
        }

        .datos-cliente h3 {
            font-size: 10px;
            color: #667085;
            margin: 0 0 10px;
            text-transform: uppercase;
        }

        .datos-cliente p {
            margin: 4px 0;
        }
    </style>

</head>

<body>

    <!-- ENCABEZADO -->

    <table class="header">

        <tr>

            <td>

                <?php if (!empty($logo)) : ?>

                    <img
                        class="logo-corporativo"
                        src="<?php echo esc_attr($logo); ?>"
                        alt="Python Print"
                    >

                <?php else : ?>

                    <div class="logo">
                        python<span>print</span>
                    </div>

                <?php endif; ?>

                <div class="muted">
                    Etiquetas textiles y personalizados
                </div>

                Quito, Ecuador<br>

                info@python.com.ec

            </td>

            <td class="derecha">

                <div class="documento">
                    PROFORMA
                </div>

                <strong>
                    <?php echo esc_html($numero); ?>
                </strong>

                <br>

                Fecha de emisión:

                <?php echo esc_html($fecha); ?>

                <br>

                <strong>

                    Válida hasta:

                    <?php echo esc_html($fecha_vencimiento); ?>

                </strong>

                <br>

                <span class="muted">

                    Vigencia:

                    <?php echo esc_html($vigencia_dias); ?>

                    días calendario

                </span>

            </td>

        </tr>

    </table>

    <hr class="separador">

    <!-- DATOS DEL CLIENTE -->

        <table class="datos-cliente">

            <tr>

                <td>

                    <h3>Datos de facturación</h3>

                    <?php if ($es_empresa) : ?>

                        <p>
                            <strong>
                                <?php echo esc_html(
                                    $cliente['razon_social'] ?? ''
                                ); ?>
                            </strong>
                        </p>

                        <?php if (!empty($cliente['nombre_comercial'])) : ?>

                            <p>
                                <?php echo esc_html(
                                    $cliente['nombre_comercial']
                                ); ?>
                            </p>

                        <?php endif; ?>

                    <?php endif; ?>

                    <p>

                        <?php echo esc_html($nombre_cliente); ?>

                    </p>

                    <p>

                        <strong>
                            <?php echo $es_empresa ? 'RUC:' : 'Cédula:'; ?>
                        </strong>

                        <?php echo esc_html($identificacion); ?>

                    </p>

                    <p>

                        <?php echo esc_html(
                            $cliente['email'] ?? ''
                        ); ?>

                    </p>

                    <p>

                        Teléfono:

                        <?php echo esc_html(
                            $cliente['telefono'] ?? ''
                        ); ?>

                    </p>

                </td>

                <td>

                    <h3>Datos de entrega</h3>

                    <p>

                        <strong>Método:</strong>

                        <?php echo esc_html($metodo_entrega); ?>

                    </p>

                    <?php if (
                        ($cliente['metodo_entrega'] ?? '') === 'envio'
                    ) : ?>

                        <p>

                            <?php echo esc_html(
                                $cliente['direccion'] ?? ''
                            ); ?>

                        </p>

                        <p>

                            <?php echo esc_html(
                                $cliente['ciudad'] ?? ''
                            ); ?>

                        </p>

                        <?php if (!empty($cliente['referencia'])) : ?>

                            <p>

                                <strong>Referencia:</strong>

                                <?php echo esc_html(
                                    $cliente['referencia']
                                ); ?>

                            </p>

                        <?php endif; ?>

                    <?php endif; ?>

                    <p>Ecuador</p>

                </td>

            </tr>

        </table>
    <!-- PRODUCTOS -->

    <div class="titulo-seccion">
        Detalles de la cotización
    </div>

    <table class="productos">

        <thead>

            <tr>

                <th width="50%">Descripción</th>

                <th width="12%" class="derecha">
                    Cant.
                </th>

                <th width="18%" class="derecha">
                    P. unit.
                </th>

                <th width="20%" class="derecha">
                    Total
                </th>

            </tr>

        </thead>

        <tbody>

            <?php foreach ($productos as $indice => $producto) : ?>

                <?php

                $config = $producto['configuracion'] ?? array();

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
                    'tipo'     => 'Tipo',
                    'material' => 'Material',
                    'ancho'    => 'Ancho',
                    'longitud' => 'Largo',
                );

                ?>

                <tr>

                    <td>

                        <strong>

                            <?php echo esc_html(
                                $producto['nombre'] ?? 'Producto'
                            ); ?>

                        </strong>

                        <?php foreach ($campos as $clave => $etiqueta) : ?>

                            <?php

                            $valor = $config[$clave] ?? '';

                            if (!is_scalar($valor) || $valor === '') {
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

                            <div class="especificacion">

                                <?php echo esc_html(
                                    $etiqueta . ': ' . $valor
                                ); ?>

                            </div>

                        <?php endforeach; ?>

                        <?php if (!empty($config['tallas'])) : ?>

                            <?php foreach ($config['tallas'] as $fila) : ?>

                                <?php if (!is_array($fila)) continue; ?>

                                <div class="especificacion">

                                    Talla:

                                    <?php echo esc_html(
                                        $fila['talla'] ?? ''
                                    ); ?>

                                    —

                                    <?php echo esc_html(
                                        (int) ($fila['cantidad'] ?? 0)
                                    ); ?>

                                    unidades

                                </div>

                            <?php endforeach; ?>

                        <?php endif; ?>

                    </td>

                    <td class="derecha">

                        <?php echo esc_html($cantidad); ?>

                    </td>

                    <td class="derecha">

                        <?php echo esc_html(
                            '$' . number_format(
                                $precio_unitario,
                                6,
                                '.',
                                ','
                            )
                        ); ?>

                    </td>

                    <td class="derecha">

                        <strong>
                            <?php echo esc_html($moneda($precio)); ?>
                        </strong>

                    </td>

                </tr>

            <?php endforeach; ?>

        </tbody>

    </table>

    <!-- RESUMEN ECONÓMICO -->

    <table class="resumen">

        <tr>

            <td width="50%"></td>

            <td width="30%">Subtotal</td>

            <td width="20%" class="derecha">

                <?php echo esc_html(
                    $moneda($totales['subtotal'] ?? 0)
                ); ?>

            </td>

        </tr>

        <tr>

            <td></td>

            <td>
                <?php echo esc_html($etiqueta_descuento); ?>
            </td>

            <td class="derecha">

                <?php echo esc_html(
                    '-' . $moneda($totales['descuento'] ?? 0)
                ); ?>

            </td>

        </tr>

        <tr>

            <td></td>

            <td>Base imponible</td>

            <td class="derecha">

                <?php echo esc_html(
                    $moneda($totales['base_imponible'] ?? 0)
                ); ?>

            </td>

        </tr>

        <tr>

            <td></td>

            <td>
                <?php echo esc_html($etiqueta_iva); ?>
            </td>

            <td class="derecha">

                <?php echo esc_html(
                    $moneda($totales['iva'] ?? 0)
                ); ?>

            </td>

        </tr>

        <tr>

            <td></td>

            <td>Envío</td>

            <td class="derecha">

                <?php echo esc_html(
                    $moneda($totales['envio'] ?? 0)
                ); ?>

            </td>

        </tr>

        <tr class="total">

            <td></td>

            <td>TOTAL USD</td>

            <td class="derecha">

                <?php echo esc_html(
                    $moneda($totales['total'] ?? 0)
                ); ?>

            </td>

        </tr>

    </table>

    <!-- OBSERVACIONES -->

    <?php if (!empty($proforma['observaciones'])) : ?>

        <div class="observaciones">

            <strong>Observaciones</strong>

            <br>

            <?php echo nl2br(
                esc_html($proforma['observaciones'])
            ); ?>

        </div>

    <?php endif; ?>

    <!-- PIE DEL DOCUMENTO -->

    <div class="pie">

        <strong>Gracias por confiar en Python Print.</strong>

        <br>

        Este documento es una proforma comercial,
        no una factura.

        <br>

        Todos los valores están expresados
        en dólares estadounidenses.

    </div>

</body>

</html>