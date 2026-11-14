
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
                    <div class="python-order-columns" aria-hidden="true">
                        <span>PRODUCTO</span>
                        <span>CANTIDADES</span>
                        <span>TOTAL</span>
                        <span></span>
                    </div>

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

                        if (is_wp_error($terminos)) {
                            $terminos = array();
                        }

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
                                <h2><?php echo esc_html($product->get_name()); ?></h2>

                                <?php if ($config) :
                                    $tipos_etiqueta = array(
                                        'marca' => 'Solo marca',
                                        'marca_talla' => 'Marca + talla',
                                        'talla' => 'Solo talla',
                                    );
                                    $tipo_etiqueta = $tipos_etiqueta[$config['tipo'] ?? ''] ?? 'No especificado';
                                ?>
                                    <span class="python-order-item__type"><?php echo esc_html($tipo_etiqueta); ?></span>
                                    <p class="python-order-item__material">
                                        <?php echo esc_html($config['material'] ?? ''); ?>
                                        · <?php echo esc_html($config['ancho'] ?? ''); ?> × <?php echo esc_html($config['longitud'] ?? ''); ?> mm
                                    </p>
                                <?php else : ?>
                                    <p class="python-order-item__material">Producto sin personalización</p>
                                <?php endif; ?>
                            </div>

                            <div class="python-order-item__quantities">
                                <?php if ($config && !empty($config['tallas'])) : ?>
                                    <?php foreach ($config['tallas'] as $fila) : ?>
                                        <p>Talla <?php echo esc_html($fila['talla']); ?>: <?php echo esc_html($fila['cantidad']); ?> unidades</p>
                                    <?php endforeach; ?>
                                <?php elseif ($config) : ?>
                                    <p><?php echo esc_html($config['cantidad']); ?> unidades</p>
                                <?php else : ?>
                                    <p><?php echo esc_html($cart_item['quantity']); ?> unidades</p>
                                <?php endif; ?>
                            </div>

                            <div class="python-order-item__total" aria-label="Total de unidades">
                                <?php echo esc_html($config ? $config['cantidad'] : $cart_item['quantity']); ?>
                            </div>

                            <div class="python-order-item__actions">
                                <?php if ($config) : ?>
                                    <button
                                        type="button"
                                        class="python-order-item__edit"
                                        data-cart-key="<?php echo esc_attr($cart_item_key); ?>"
                                        data-product-id="<?php echo esc_attr($product->get_id()); ?>"
                                        data-config="<?php echo esc_attr(wp_json_encode($config)); ?>"
                                        aria-label="Editar combinación"
                                        title="Editar combinación"
                                    >
                                        <svg viewBox="0 0 24 24" width="23" height="23" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L9 17l-4 1 1-4L16.5 3.5Z"/></svg>
                                    </button>
                                <?php endif; ?>
                                <a
                                    href="<?php echo esc_url(wp_nonce_url(add_query_arg(array('python_remove_item' => $cart_item_key), get_permalink()), 'python_remove_' . $cart_item_key)); ?>"
                                    class="python-order-item__remove"
                                    onclick="return confirm('¿Eliminar esta combinación de tu pedido?');"
                                    aria-label="Eliminar combinación"
                                    title="Eliminar combinación"
                                >
                                    <svg viewBox="0 0 24 24" width="23" height="23" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><path d="M5 5l14 14M19 5 5 19"/></svg>
                                </a>
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
