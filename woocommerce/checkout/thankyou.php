
<?php
/**
 * Python Print - Confirmación del pedido.
 *
 * @package WooCommerce\Templates
 * @version 11.2.0
 */

defined('ABSPATH') || exit;
?>

<div class="woocommerce-order python-checkout python-confirmation">

    <?php if ($order) : ?>

        <?php
        do_action('woocommerce_before_thankyou', $order->get_id());

        $numero = $order->get_order_number();

        $es_transferencia = $order->get_payment_method() === 'bacs';

        $pendiente_pago = $order->has_status(
            array('pending', 'on-hold')
        );

        $mostrar_transferencia = $es_transferencia && $pendiente_pago;

        $es_propietario = is_user_logged_in()
            && (int) $order->get_user_id() === get_current_user_id();

        $estado = wc_get_order_status_name($order->get_status());

        $url_comprobante = 'mailto:info@python.com.ec?subject='
            . rawurlencode('Comprobante de pago - Pedido #' . $numero);
        ?>

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

            <span class="<?php echo $order->is_paid() ? 'is-completed' : ''; ?>">
                Pago
            </span>

            <span class="python-checkout__separator">›</span>

            <span class="is-active" aria-current="step">
                Confirmación
            </span>

        </nav>

        <?php if ($order->has_status('failed')) : ?>

            <!-- PEDIDO FALLIDO -->

            <section class="python-confirmation__hero is-error">

                <span class="python-confirmation__icon dashicons dashicons-warning"></span>

                <h1>No se pudo procesar tu pedido</h1>

                <?php
                $default_order_failed_text = esc_html__(
                    'Unfortunately your order cannot be processed as the originating bank/merchant has declined your transaction. Please attempt your purchase again.',
                    'woocommerce'
                );

                $order_failed_text = apply_filters(
                    'woocommerce_thankyou_order_failed_text',
                    $default_order_failed_text,
                    $order
                );

                $order_failed_text = is_string($order_failed_text)
                    ? $order_failed_text
                    : $default_order_failed_text;
                ?>

                <p>
                    <?php echo wp_kses_post($order_failed_text); ?>
                </p>

                <div class="python-confirmation__actions">

                    <?php if ($order->needs_payment()) : ?>

                        <a class="button python-confirmation__primary"
                           href="<?php echo esc_url($order->get_checkout_payment_url()); ?>">
                            Intentar nuevamente
                        </a>

                    <?php endif; ?>

                    <?php if (is_user_logged_in()) : ?>

                        <a class="button python-confirmation__secondary"
                           href="<?php echo esc_url(wc_get_page_permalink('myaccount')); ?>">
                            Mi cuenta
                        </a>

                    <?php endif; ?>

                </div>

            </section>

        <?php else : ?>

            <!-- CONFIRMACIÓN DEL PEDIDO -->

            <header class="python-confirmation__hero">

                <?php if ($order->has_status(array('cancelled', 'refunded'))) : ?>

                    <span class="python-confirmation__icon dashicons dashicons-info"></span>

                    <h1>Estado de tu pedido</h1>

                <?php else : ?>

                    <span class="python-confirmation__icon dashicons dashicons-yes-alt"></span>

                    <h1>¡Tu pedido ha sido registrado!</h1>

                <?php endif; ?>

                <?php
                wc_get_template(
                    'checkout/order-received.php',
                    array('order' => $order)
                );
                ?>

                <?php if ($mostrar_transferencia) : ?>

                    <p>
                        Completa tu transferencia para que podamos
                        iniciar la producción de tus productos.
                    </p>

                <?php endif; ?>

                <span class="python-confirmation__status python-confirmation__status--<?php echo esc_attr($order->get_status()); ?>">

                    <?php echo esc_html(
                        $mostrar_transferencia ? 'Pendiente de pago' : $estado
                    ); ?>

                </span>

            </header>

            <!-- INFORMACIÓN GENERAL -->

            <section class="python-confirmation__overview">

                <div class="python-confirmation__field">

                    <span>Número de pedido</span>

                    <strong>
                        #<?php echo esc_html($numero); ?>
                    </strong>

                </div>

                <div class="python-confirmation__field">

                    <span>Fecha del pedido</span>

                    <strong>
                        <?php echo esc_html(
                            wc_format_datetime($order->get_date_created())
                        ); ?>
                    </strong>

                </div>

                <?php if ($order->get_payment_method_title()) : ?>

                    <div class="python-confirmation__field">

                        <span>Método de pago</span>

                        <strong>
                            <?php echo esc_html(
                                $order->get_payment_method_title()
                            ); ?>
                        </strong>

                    </div>

                <?php endif; ?>

                <div class="python-confirmation__field python-confirmation__amount">

                    <span>Total del pedido</span>

                    <strong>
                        <?php echo wp_kses_post(
                            $order->get_formatted_order_total()
                        ); ?>
                    </strong>

                </div>

                <?php if ($es_propietario && $order->get_billing_email()) : ?>

                    <div class="python-confirmation__field">

                        <span>Correo electrónico</span>

                        <strong>
                            <?php echo esc_html(
                                $order->get_billing_email()
                            ); ?>
                        </strong>

                    </div>

                <?php endif; ?>

            </section>

        <?php endif; ?>

        <!-- INFORMACIÓN DEL MÉTODO DE PAGO -->

        <div class="python-confirmation__payment-grid<?php echo $mostrar_transferencia ? ' has-instructions' : ''; ?>">

            <section class="python-confirmation__payment">

                <?php
                do_action(
                    'woocommerce_thankyou_' . $order->get_payment_method(),
                    $order->get_id()
                );
                ?>

            </section>

            <?php if ($mostrar_transferencia) : ?>

                <!-- INSTRUCCIONES PARA EL CLIENTE -->

                <section class="python-confirmation__instructions">

                    <h2>¿Qué debes hacer ahora?</h2>

                    <ol class="python-confirmation__instructions-list">

                        <li>

                            <strong>Realiza la transferencia</strong>

                            <p>
                                Utiliza los datos bancarios indicados
                                y el importe total de tu pedido.
                            </p>

                        </li>

                        <li>

                            <strong>Envía tu comprobante</strong>

                            <p>
                                Envíalo a info@python.com.ec indicando
                                el número de pedido
                                #<?php echo esc_html($numero); ?>.
                            </p>

                        </li>

                        <li>

                            <strong>Confirmaremos tu pago</strong>

                            <p>
                                Una vez verificado, te notificaremos
                                el inicio de la producción.
                            </p>

                        </li>

                    </ol>

                    <a class="button python-confirmation__primary"
                       href="<?php echo esc_url($url_comprobante); ?>">

                        <span class="dashicons dashicons-email-alt"></span>

                        Enviar comprobante

                    </a>

                </section>

            <?php endif; ?>

        </div>

        <!-- RESUMEN Y DETALLES NATIVOS DEL PEDIDO -->

        <div class="python-confirmation__details">

            <?php
            do_action('woocommerce_thankyou', $order->get_id());
            ?>

        </div>

        <!-- NAVEGACIÓN FINAL -->

        <div class="python-confirmation__actions">

            <?php if ($es_propietario) : ?>

                <a class="button python-confirmation__primary"
                   href="<?php echo esc_url($order->get_view_order_url()); ?>">

                    Ver mi pedido

                </a>

            <?php endif; ?>

            <a class="button python-confirmation__secondary"
               href="<?php echo esc_url(home_url('/')); ?>">

                Seguir explorando

            </a>

        </div>

        <!-- SOPORTE -->

        <footer class="python-confirmation__support">

            ¿Necesitas ayuda con tu pedido?

            <a href="mailto:info@python.com.ec">
                Contáctanos
            </a>

        </footer>

    <?php else : ?>

        <section class="python-confirmation__hero">

            <?php
            wc_get_template(
                'checkout/order-received.php',
                array('order' => false)
            );
            ?>

            <a class="button python-confirmation__secondary"
               href="<?php echo esc_url(home_url('/')); ?>">

                Volver al inicio

            </a>

        </section>

    <?php endif; ?>

</div>
