<?php
/**
 * Python Print — Mis pedidos.
 */

defined('ABSPATH') || exit;

do_action('woocommerce_before_account_orders', $has_orders);
?>

<div class="python-account-orders">

    <header class="python-account-orders__header">
        <h1>Mis pedidos</h1>
        <p>Consulta el estado y los detalles de tus compras.</p>
    </header>

    <?php if ($has_orders) : ?>

        <div class="python-account-orders__list">

            <?php foreach ($customer_orders->orders as $customer_order) :

                $order = wc_get_order($customer_order);

                if (!$order) {
                    continue;
                }

                $acciones = wc_get_account_orders_actions($order);

            ?>

                <article class="python-order-card">

                    <header class="python-order-card__header">

                        <div>
                            <h3>
                                Pedido #<?php echo esc_html(
                                    $order->get_order_number()
                                ); ?>
                            </h3>

                            <p>
                                <?php echo esc_html(
                                    wc_format_datetime(
                                        $order->get_date_created()
                                    )
                                ); ?>
                            </p>
                        </div>

                        <span class="python-order-status python-order-status--<?php echo esc_attr($order->get_status()); ?>">

                            <?php echo esc_html(
                                wc_get_order_status_name(
                                    $order->get_status()
                                )
                            ); ?>

                        </span>

                    </header>

                    <div class="python-order-card__products">

                        <?php foreach ($order->get_items() as $item) :

                            $producto = $item->get_product();

                        ?>

                            <div class="python-order-product">

                                <div class="python-order-product__image">

                                    <?php
                                    echo $producto
                                        ? wp_kses_post(
                                            $producto->get_image('thumbnail')
                                        )
                                        : wp_kses_post(
                                            wc_placeholder_img('thumbnail')
                                        );
                                    ?>

                                </div>

                                <div class="python-order-product__info">

                                    <strong>
                                        <?php echo esc_html(
                                            $item->get_name()
                                        ); ?>
                                    </strong>

                                    <p>
                                        <?php echo esc_html(
                                            $item->get_quantity()
                                        ); ?> unidades
                                    </p>

                                </div>

                            </div>

                        <?php endforeach; ?>

                    </div>

                    <footer class="python-order-card__footer">

                        <div class="python-order-card__total">

                            <span>Total del pedido</span>

                            <strong>
                                <?php echo wp_kses_post(
                                    $order->get_formatted_order_total()
                                ); ?>
                            </strong>

                        </div>

                        <div class="python-order-card__actions">

                            <?php foreach ($acciones as $key => $accion) : ?>

                                <a
                                    href="<?php echo esc_url($accion['url']); ?>"
                                    class="python-order-action python-order-action--<?php echo esc_attr($key); ?>"
                                >
                                    <?php echo esc_html(
                                        $key === 'view'
                                            ? 'Ver detalles'
                                            : $accion['name']
                                    ); ?>
                                </a>

                            <?php endforeach; ?>

                        </div>

                    </footer>

                </article>

            <?php endforeach; ?>

        </div>

        <?php do_action('woocommerce_before_account_orders_pagination'); ?>

        <?php if (1 < $customer_orders->max_num_pages) : ?>

            <div class="python-orders-pagination">

                <?php if (1 !== (int) $current_page) : ?>

                    <a href="<?php echo esc_url(
                        wc_get_endpoint_url('orders', $current_page - 1)
                    ); ?>">
                        Anterior
                    </a>

                <?php endif; ?>

                <?php if ($current_page < $customer_orders->max_num_pages) : ?>

                    <a href="<?php echo esc_url(
                        wc_get_endpoint_url('orders', $current_page + 1)
                    ); ?>">
                        Siguiente
                    </a>

                <?php endif; ?>

            </div>

        <?php endif; ?>

    <?php else : ?>

        <div class="python-account-orders__empty">

            <h3>Todavía no tienes pedidos</h3>

            <p>
                Cuando realices una compra,
                podrás consultar su estado aquí.
            </p>

            <a href="<?php echo esc_url(
                home_url('/mis-cotizaciones/')
            ); ?>">
                Ver mis cotizaciones
            </a>

        </div>

    <?php endif; ?>

</div>

<?php do_action('woocommerce_after_account_orders', $has_orders); ?>