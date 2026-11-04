
<?php
/**
 * Template Name: Mi pedido Python Print
 */

defined('ABSPATH') || exit;

get_header();

$cart = function_exists('WC') ? WC()->cart : null;
?>

<main id="primary" class="python-order-page">

    <div class="python-order-container">

        <header class="python-order-header">
            <h1>Mi pedido</h1>
            <p>
                Revisa tus productos y combinaciones
                antes de solicitar una cotización.
            </p>
        </header>

        <?php if ($cart && !$cart->is_empty()) : ?>

            <div class="python-order-layout">

                <section class="python-order-products">

                    <?php foreach ($cart->get_cart() as $cart_item_key => $cart_item) :

                        $product = $cart_item['data'];

                        if (!$product || !$product->exists()) {
                            continue;
                        }

                        $config = $cart_item['python_configuracion'] ?? null;
                    ?>

                        <article class="python-order-item">

                            <div class="python-order-item__image">
                                <?php echo $product->get_image('woocommerce_thumbnail'); ?>
                            </div>

                            <div class="python-order-item__details">

                                <h2>
                                    <?php echo esc_html($product->get_name()); ?>
                                </h2>

                                <?php if ($config) : ?>

                                    <p>
                                        <?php echo esc_html($config['material']); ?>
                                        ·
                                        <?php echo esc_html($config['ancho']); ?> ×
                                        <?php echo esc_html($config['longitud']); ?> mm
                                    </p>

                                    <?php if (!empty($config['tallas'])) : ?>

                                        <?php foreach ($config['tallas'] as $fila) : ?>

                                            <p>
                                                Talla <?php echo esc_html($fila['talla']); ?>:
                                                <?php echo esc_html($fila['cantidad']); ?> unidades
                                            </p>

                                        <?php endforeach; ?>

                                    <?php endif; ?>

                                    <strong>
                                        Total:
                                        <?php echo esc_html($config['cantidad']); ?>
                                        etiquetas
                                    </strong>

                                <?php else : ?>

                                    <p>
                                        Cantidad:
                                        <?php echo esc_html($cart_item['quantity']); ?>
                                    </p>

                                <?php endif; ?>
                                 
                                
                                <div class="python-order-item__actions">

                                    <a
                                        href="<?php echo esc_url(
                                            wp_nonce_url(
                                                add_query_arg(
                                                    array(
                                                        'python_remove_item' => $cart_item_key,
                                                    ),
                                                    get_permalink()
                                                ),
                                                'python_remove_' . $cart_item_key
                                            )
                                        ); ?>"
                                        class="python-order-item__remove"
                                        onclick="return confirm('¿Eliminar esta combinación de tu pedido?');"
                                    >
                                        Eliminar combinación
                                    </a>

                                </div>

                                
                            </div>

                        </article>

                    <?php endforeach; ?>

                </section>

                <aside class="python-order-summary">

                    <h2>Resumen del pedido</h2>

                    <p>
                        <?php echo esc_html($cart->get_cart_contents_count()); ?>
                        artículos en tu pedido
                    </p>

                    <p>
                        Los productos personalizados
                        se cotizarán según sus características.
                    </p>

                </aside>

            </div>

        <?php else : ?>

            <div class="python-order-empty">
                <h2>Tu pedido está vacío</h2>
                <p>Explora nuestro catálogo y agrega tus productos favoritos.</p>
            </div>

        <?php endif; ?>

    </div>

</main>

<?php get_footer(); ?>
