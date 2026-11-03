
<?php
/**
 * Plantilla personalizada de producto individual
 * Tema: Python Print
 */

defined('ABSPATH') || exit;

get_header();

while (have_posts()) :
    the_post();

    do_action('woocommerce_before_single_product');

    if (post_password_required()) :

        echo get_the_password_form();

    else :

        global $product;
        ?>

        <div class="python-product-page">

            <div class="python-product-layout">

                <!-- Galería del producto -->
                <section class="python-product-gallery">
                    <?php
                    do_action('woocommerce_before_single_product_summary');
                    ?>
                </section>

                <!-- Información principal -->
                <section class="python-product-summary">
                    <?php
                    do_action('woocommerce_single_product_summary');
                    ?>
                </section>

            </div>

            <!-- Información adicional y productos relacionados -->
            <div class="python-product-details">
                <?php
                do_action('woocommerce_after_single_product_summary');
                ?>
            </div>

        </div>

        <?php
    endif;

    do_action('woocommerce_after_single_product');

endwhile;

get_footer();
