
<?php
/**
 * Tarjeta de producto para el catálogo Python Print
 */

defined('ABSPATH') || exit;

global $product;

if (!$product || !$product->is_visible()) {
    return;
}
?>

<article <?php wc_product_class('python-product-card', $product); ?>>
        
        <?php
        // Obtener materiales asignados al producto
        $materiales = wc_get_product_terms(
            $product->get_id(),
            'pa_material',
            array('fields' => 'names')
        );
        ?>


        <a class="python-product-card__link"
            href="<?php echo esc_url(get_permalink()); ?>"
            data-tipo-marca="<?php echo esc_attr($product->get_meta('_python_tipo_marca')); ?>"
            data-tipo-marca-talla="<?php echo esc_attr($product->get_meta('_python_tipo_marca_talla')); ?>"
            data-tipo-talla="<?php echo esc_attr($product->get_meta('_python_tipo_talla')); ?>"
            data-materiales="<?php echo esc_attr(wp_json_encode($materiales)); ?>">

        <div class="python-product-card__image">
            <?php echo $product->get_image('woocommerce_thumbnail'); ?>
        </div>

        <div class="python-product-card__content">
            <h2 class="python-product-card__title">
                <?php echo esc_html(get_the_title()); ?>
            </h2>
        </div>

    </a>

</article>
