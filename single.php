<?php
get_header();
?>

<main id="primary" class="site-main container">
    <div class="content-area">
        <?php
        while (have_posts()) : the_post();
            get_template_part('template-parts/content', 'single');
        endwhile;
        ?>
    </div>
    
    <?php if (is_active_sidebar('sidebar-1')) : ?>
        <aside class="sidebar">
            <?php dynamic_sidebar('sidebar-1'); ?>
        </aside>
    <?php endif; ?>
</main>

<?php
get_footer();