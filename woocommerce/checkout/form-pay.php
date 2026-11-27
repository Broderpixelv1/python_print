
<?php
/**
 * Python Print - Página de pago de pedidos.
 *
 * @package WooCommerce\Templates
 * @version 10.9.0
 */

defined('ABSPATH') || exit;

$totals = $order->get_order_item_totals();
$cotizacion_id = absint(
    $order->get_meta('_python_cotizacion_id')
);

$url_cotizacion = '';

if ($cotizacion_id > 0) {

    $url_cotizacion = add_query_arg(
        'cotizacion',
        $cotizacion_id,
        home_url('/mis-cotizaciones/')
    );

}

?>

<main class="python-checkout">

    <!-- PROGRESO DE COMPRA -->
    <nav class="python-checkout__steps" aria-label="Proceso de compra">

        <span class="is-completed">
            <span class="dashicons dashicons-yes-alt"></span>
            Cotización
        </span>

        <span class="python-checkout__separator">›</span>

        <span class="is-completed">
            <span class="dashicons dashicons-yes-alt"></span>
            Aprobación
        </span>

        <span class="python-checkout__separator">›</span>

        <span class="is-active" aria-current="step">
            Pago
        </span>

        <span class="python-checkout__separator">›</span>

        <span>Confirmación</span>

    </nav>

    <!-- ENCABEZADO -->
    <header class="python-checkout__header">

        <h1>Finaliza tu pedido</h1>

        <p>
            Revisa los detalles y selecciona tu método de pago.
        </p>

        <span class="python-checkout__order-number">
            Pedido #<?php echo esc_html($order->get_order_number()); ?>
        </span>

    </header>

    <form id="order_review" method="post">

        <div class="python-checkout__grid">

            <!-- RESUMEN DEL PEDIDO -->
            <section class="python-checkout__summary">

                <h2>Resumen del pedido</h2>

                <table class="shop_table python-checkout__table">

                    <thead>
                        <tr>
                            <th class="product-name">Producto</th>
                            <th class="product-quantity">Cantidad</th>
                            <th class="product-total">Importe</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php foreach ($order->get_items() as $item_id => $item) : ?>

                            <?php
                            if (!apply_filters(
                                'woocommerce_order_item_visible',
                                true,
                                $item
                            )) {
                                continue;
                            }
                            ?>

                            <tr class="<?php echo esc_attr(
                                apply_filters(
                                    'woocommerce_order_item_class',
                                    'order_item',
                                    $item,
                                    $order
                                )
                            ); ?>">

                                <td class="product-name">

                                    <div class="python-checkout__product">

                                        <?php
                                        $product = $item->get_product();

                                        if ($product) :
                                        ?>

                                            <div class="python-checkout__product-image">
                                                <?php echo wp_kses_post(
                                                    $product->get_image('woocommerce_thumbnail')
                                                ); ?>
                                            </div>

                                        <?php endif; ?>

                                        <div class="python-checkout__product-info">

                                            <strong class="python-checkout__product-name">

                                                <?php echo wp_kses_post(
                                                    apply_filters(
                                                        'woocommerce_order_item_name',
                                                        $item->get_name(),
                                                        $item,
                                                        false
                                                    )
                                                ); ?>

                                            </strong>

                                            <?php
                                            do_action(
                                                'woocommerce_order_item_meta_start',
                                                $item_id,
                                                $item,
                                                $order,
                                                false
                                            );

                                            wc_display_item_meta($item);

                                            do_action(
                                                'woocommerce_order_item_meta_end',
                                                $item_id,
                                                $item,
                                                $order,
                                                false
                                            );
                                            ?>

                                        </div>

                                    </div>

                                </td>

                                <td class="product-quantity">

                                    <?php echo apply_filters(
                                        'woocommerce_order_item_quantity_html',
                                        '<strong class="product-quantity">' .
                                        sprintf(
                                            '&times;&nbsp;%s',
                                            esc_html($item->get_quantity())
                                        ) .
                                        '</strong>',
                                        $item
                                    ); ?>

                                </td>

                                <td class="product-subtotal">

                                    <?php echo wp_kses_post(
                                        $order->get_formatted_line_subtotal($item)
                                    ); ?>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    </tbody>

                    <tfoot>

                        <?php if ($totals) : ?>

                            <?php foreach ($totals as $key => $total) : ?>

                                <tr class="python-checkout__total--<?php echo esc_attr($key); ?>">

                                    <th scope="row" colspan="2">
                                        <?php echo wp_kses_post($total['label']); ?>
                                    </th>

                                    <td class="product-total">
                                        <?php echo wp_kses_post($total['value']); ?>
                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        <?php endif; ?>

                    </tfoot>

                </table>

            </section>

            <!-- MÉTODOS DE PAGO -->
            <section class="python-checkout__payment">

                <h2>Método de pago</h2>

                <?php do_action('woocommerce_pay_order_before_payment'); ?>

                <div id="payment">

                    <?php if ($order->needs_payment()) : ?>

                        <ul
                            class="wc_payment_methods payment_methods methods"
                            aria-label="<?php esc_attr_e('Payment methods', 'woocommerce'); ?>"
                        >

                            <?php
                            if (!empty($available_gateways)) {

                                foreach ($available_gateways as $gateway) {

                                    wc_get_template(
                                        'checkout/payment-method.php',
                                        array('gateway' => $gateway)
                                    );

                                }

                            } else {

                                echo '<li>';

                                wc_print_notice(
                                    apply_filters(
                                        'woocommerce_no_available_payment_methods_message',
                                        esc_html__(
                                            'Sorry, it seems that there are no available payment methods for your location. Please contact us if you require assistance or wish to make alternate arrangements.',
                                            'woocommerce'
                                        )
                                    ),
                                    'notice'
                                );

                                echo '</li>';

                            }
                            ?>

                        </ul>

                    <?php endif; ?>

                    <div class="python-checkout__security">

                        <span class="dashicons dashicons-shield-alt"></span>

                        <p>
                            Tu pedido será procesado una vez confirmado
                            el pago requerido.
                        </p>

                    </div>

                    <div class="form-row">

                        <input
                            type="hidden"
                            name="woocommerce_pay"
                            value="1"
                        />

                        <?php wc_get_template('checkout/terms.php'); ?>

                        <?php do_action('woocommerce_pay_order_before_submit'); ?>

                        <?php echo apply_filters(
                            'woocommerce_pay_order_button_html',
                            '<button type="submit" class="button alt' .
                            esc_attr(
                                wc_wp_theme_get_element_class_name('button')
                                ? ' ' . wc_wp_theme_get_element_class_name('button')
                                : ''
                            ) .
                            '" id="place_order" value="' .
                            esc_attr($order_button_text) .
                            '" data-value="' .
                            esc_attr($order_button_text) .
                            '">' .
                            esc_html($order_button_text) .
                            '</button>'
                        ); ?>

                        <?php do_action('woocommerce_pay_order_after_submit'); ?>

                        <?php wp_nonce_field(
                            'woocommerce-pay',
                            'woocommerce-pay-nonce'
                        ); ?>

                    </div>

                </div>
                
                <?php if (!empty($url_cotizacion)) : ?>

                    <a
                        class="python-checkout__back"
                        href="<?php echo esc_url($url_cotizacion); ?>"
                    >

                        <span class="dashicons dashicons-arrow-left-alt2"></span>

                        Volver a mi cotización

                    </a>

                <?php endif; ?>
                
                <!-- SOPORTE -->
                <div class="python-checkout__support">

                    <span class="dashicons dashicons-email-alt"></span>

                    <div>

                        <strong>¿Necesitas ayuda?</strong>

                        <a href="mailto:info@python.com.ec">
                            info@python.com.ec
                        </a>

                    </div>

                </div>

            </section>

        </div>

    </form>

    <footer class="python-checkout__footer">

        <span class="dashicons dashicons-lock"></span>

        Python Print · Compra segura

    </footer>

</main>
