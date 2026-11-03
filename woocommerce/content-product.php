
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

    <a class="python-product-card__link"
       href="<?php echo esc_url(get_permalink()); ?>">

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
