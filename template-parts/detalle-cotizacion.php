<?php
/**
 * Python Print — Detalle de cotización del cliente.
 */

defined('ABSPATH') || exit;

$detalle = $args['detalle'] ?? false;

if (!$detalle) {
    return;
}

$proforma = $detalle['proforma'];
$tiene_proforma = $detalle['tiene_proforma'];

$productos = $tiene_proforma
    ? ($proforma['productos'] ?? array())
    : $detalle['productos'];

?>

<section class="python-quote-detail">

    <a
        class="python-quote-detail__back"
        href="<?php echo esc_url(get_permalink()); ?>"
    >
        &larr; Volver a mis cotizaciones
    </a>

    <header class="python-quote-detail__header">

        <h2>
            <?php echo esc_html($detalle['numero']); ?>
        </h2>

        <p>
            Fecha de solicitud:
            <?php
            echo esc_html(
                get_the_date(
                    'd/m/Y',
                    $detalle['id']
                )
            );
            ?>
        </p>

    </header>

    <?php if (!$tiene_proforma) : ?>

        <div class="python-quote-detail__notice">

            <p>
                Tu solicitud todavía no dispone
                de una proforma publicada.
            </p>

        </div>

    <?php endif; ?>

    <div class="python-quote-detail__products">

        <h3>Productos y combinaciones</h3>

        <?php foreach ($productos as $indice => $producto) : ?>

            <?php

            $producto_id = absint(
                $producto['producto_id'] ?? 0
            );

            $config = $producto['configuracion'] ?? array();

            $config = is_array($config)
                ? $config
                : array();

            $cantidad = (int) (
                $config['cantidad']
                ?? $producto['cantidad']
                ?? 0
            );

            $precio = $proforma['precios'][$indice]
                ?? null;

            $imagen = get_the_post_thumbnail_url(
                $producto_id,
                'medium'
            );

            if (!$imagen) {
                $imagen = wc_placeholder_img_src('medium');
            }

            $campos = array(
                'tipo'     => 'Tipo',
                'material' => 'Material',
                'ancho'    => 'Ancho',
                'longitud' => 'Largo',
            );

            ?>

            <article class="python-quote-product">

                <div class="python-quote-product__image">

                    <img
                        src="<?php echo esc_url($imagen); ?>"
                        alt="<?php echo esc_attr(
                            $producto['nombre'] ?? 'Producto'
                        ); ?>"
                        loading="lazy"
                    >

                </div>

                <div class="python-quote-product__info">

                    <h4>
                        <?php echo esc_html(
                            $producto['nombre'] ?? 'Producto'
                        ); ?>
                    </h4>

                    <div class="python-quote-product__specs">

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

                            <p>
                                <strong>
                                    <?php echo esc_html($etiqueta); ?>:
                                </strong>

                                <?php echo esc_html($valor); ?>
                            </p>

                        <?php endforeach; ?>

                    </div>

                    <?php if (
                        !empty($config['tallas'])
                        && is_array($config['tallas'])
                    ) : ?>

                        <div class="python-quote-product__sizes">

                            <?php foreach ($config['tallas'] as $fila) : ?>

                                <?php if (!is_array($fila)) continue; ?>

                                <span>
                                    <?php echo esc_html(
                                        $fila['talla'] ?? 'Sin talla'
                                    ); ?>:

                                    <?php echo esc_html(
                                        (int) ($fila['cantidad'] ?? 0)
                                    ); ?>
                                </span>

                            <?php endforeach; ?>

                        </div>

                    <?php endif; ?>

                    <p class="python-quote-product__quantity">

                        <strong>
                            <?php echo esc_html($cantidad); ?>
                            unidades
                        </strong>

                    </p>

                </div>

                <div class="python-quote-product__price">

                    <span>Precio total</span>

                    <strong>

                        <?php if ($tiene_proforma && $precio !== null) : ?>

                            <?php echo esc_html(
                                '$' . number_format(
                                    (float) $precio,
                                    2,
                                    '.',
                                    ','
                                )
                            ); ?>

                        <?php else : ?>

                            Por cotizar

                        <?php endif; ?>

                    </strong>

                </div>

            </article>

        <?php endforeach; ?>

    </div>
                
    <?php if ($tiene_proforma) : ?>

        <?php

        $totales = $proforma['totales'] ?? array();

        $moneda = static function ($valor) {
            return '$' . number_format(
                (float) $valor,
                2,
                '.',
                ','
            );
        };

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

        <?php if (isset($totales['total'])) : ?>

            <aside class="python-quote-summary">

                <h3>Resumen de la proforma</h3>

                <div class="python-quote-summary__rows">

                    <div class="python-quote-summary__row">

                        <span>Subtotal</span>

                        <strong>
                            <?php echo esc_html(
                                $moneda($totales['subtotal'] ?? 0)
                            ); ?>
                        </strong>

                    </div>

                    <div class="python-quote-summary__row">

                        <span>
                            <?php echo esc_html($etiqueta_descuento); ?>
                        </span>

                        <strong>
                            <?php echo esc_html(
                                '-' . $moneda($totales['descuento'] ?? 0)
                            ); ?>
                        </strong>

                    </div>

                    <div class="python-quote-summary__row">

                        <span>Base imponible</span>

                        <strong>
                            <?php echo esc_html(
                                $moneda($totales['base_imponible'] ?? 0)
                            ); ?>
                        </strong>

                    </div>

                    <div class="python-quote-summary__row">

                        <span>
                            <?php echo esc_html($etiqueta_iva); ?>
                        </span>

                        <strong>
                            <?php echo esc_html(
                                $moneda($totales['iva'] ?? 0)
                            ); ?>
                        </strong>

                    </div>

                    <div class="python-quote-summary__row">

                        <span>Envío</span>

                        <strong>
                            <?php echo esc_html(
                                $moneda($totales['envio'] ?? 0)
                            ); ?>
                        </strong>

                    </div>

                </div>

                <div class="python-quote-summary__total">

                    <span>Total de la proforma</span>

                    <strong>
                        <?php echo esc_html(
                            $moneda($totales['total'])
                        ); ?>
                    </strong>

                </div>

                <p class="python-quote-summary__currency">
                    Valores expresados en dólares estadounidenses.
                </p>
                <div class="python-quote-summary__actions">

                    <?php if (
                        $detalle['estado'] === 'enviada' &&
                        !empty($proforma['version'])
                    ) : ?>

                        <form
                            method="post"
                            action="<?php echo esc_url(
                                admin_url('admin-post.php')
                            ); ?>"
                        >

                            <input
                                type="hidden"
                                name="action"
                                value="python_responder_proforma"
                            >

                            <input
                                type="hidden"
                                name="cotizacion_id"
                                value="<?php echo esc_attr($detalle['id']); ?>"
                            >

                            <input
                                type="hidden"
                                name="version"
                                value="<?php echo esc_attr($proforma['version']); ?>"
                            >

                            <?php wp_nonce_field(
                                'python_responder_proforma_' . $detalle['id'],
                                'python_proforma_nonce'
                            ); ?>

                            <button
                                type="submit"
                                name="respuesta"
                                value="aprobar"
                                class="python-quote-approve"
                                onclick="return confirm('¿Confirmas que deseas aprobar esta proforma?');"
                            >
                                Aprobar proforma
                            </button>

                            <button
                                type="submit"
                                name="respuesta"
                                value="rechazar"
                                class="python-quote-reject"
                                onclick="return confirm('¿Confirmas que deseas rechazar esta proforma?');"
                            >
                                Rechazar proforma
                            </button>

                        </form>

                    <?php elseif ($detalle['estado'] === 'aprobada') : ?>

                        <div class="python-quote-response python-quote-response--approved">

                            <strong>Proforma aprobada</strong>

                            <p>
                                Has aceptado las condiciones comerciales
                                de esta proforma.
                            </p>

                        </div>

                        <form
                            method="post"
                            action="<?php echo esc_url(
                                admin_url('admin-post.php')
                            ); ?>"
                            class="python-quote-payment-form"
                        >

                            <input
                                type="hidden"
                                name="action"
                                value="python_proceder_pago_proforma"
                            >

                            <input
                                type="hidden"
                                name="cotizacion_id"
                                value="<?php echo esc_attr(
                                    $detalle['id']
                                ); ?>"
                            >

                            <?php wp_nonce_field(
                                'python_pagar_proforma_' . $detalle['id'],
                                'python_pago_nonce'
                            ); ?>

                            <button
                                type="submit"
                                class="python-quote-pay"
                            >
                                Proceder al pago
                            </button>

                            <p class="python-quote-payment-info">
                                Selecciona tu método de pago para
                                completar el pedido.
                            </p>

                        </form>

                    <?php elseif ($detalle['estado'] === 'rechazada') : ?>

                        <div class="python-quote-response python-quote-response--rejected">

                            <strong>Proforma rechazada</strong>

                            <p>
                                Has rechazado esta propuesta comercial.
                            </p>

                        </div>

                    <?php endif; ?>

                </div>
            </aside>

        <?php else : ?>

            <p class="python-quote-detail__notice">
                El resumen financiero todavía no está disponible.
            </p>

        <?php endif; ?>

    <?php endif; ?>
</section>