<?php
/**
 * Template Name: Full Width (para Elementor)
 */
get_header();
?>

<main id="primary" class="site-main full-width">
    <?php
    while (have_posts()) : the_post();
        the_content(); // Elementor controlará todo el contenido
    endwhile;
    ?>
</main>

<?php
get_footer();