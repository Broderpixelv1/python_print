
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

                        
                        <?php
                        $materiales = array();
                        $anchos_por_material = array();

                        $terminos = wc_get_product_terms(
                            $product->get_id(),
                            'pa_material',
                            array('fields' => 'all')
                        );

                        foreach ($terminos as $termino) {
                            $materiales[] = $termino->name;

                            $anchos = get_term_meta(
                                $termino->term_id,
                                'python_anchos',
                                true
                            );

                            $anchos_por_material[$termino->name] = array_values(
                                array_filter(
                                    array_map('trim', explode(',', (string) $anchos)),
                                    'strlen'
                                )
                            );
                        }
                        ?>

                        <article
                            class="python-order-item"
                            data-tipo-marca="<?php echo esc_attr(get_post_meta($product->get_id(), '_python_tipo_marca', true)); ?>"
                            data-tipo-marca-talla="<?php echo esc_attr(get_post_meta($product->get_id(), '_python_tipo_marca_talla', true)); ?>"
                            data-tipo-talla="<?php echo esc_attr(get_post_meta($product->get_id(), '_python_tipo_talla', true)); ?>"
                            data-materiales="<?php echo esc_attr(wp_json_encode($materiales)); ?>"
                            data-anchos="<?php echo esc_attr(wp_json_encode($anchos_por_material)); ?>"
                        >


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

                                    
                                    <?php if ($config) : ?>

                                        <button
                                            type="button"
                                            class="python-order-item__edit"
                                            data-cart-key="<?php echo esc_attr($cart_item_key); ?>"
                                            data-product-id="<?php echo esc_attr($product->get_id()); ?>"
                                            data-config="<?php echo esc_attr(wp_json_encode($config)); ?>"

                                        >
                                            Editar combinación
                                        </button>

                                    <?php endif; ?>


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


<?php
// Reutilizar el configurador de productos en Mi pedido.
get_template_part('template-parts/product-configurator');
?>


<?php get_footer(); ?>
