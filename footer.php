<footer class="site-footer">
    <div class="container">
        <div class="footer-widgets">
            <?php if (is_active_sidebar('footer-widgets')) : ?>
                <div class="widgets-row">
                    <?php dynamic_sidebar('footer-widgets'); ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="footer-bottom">
            <div class="footer-menu">
                <?php
                wp_nav_menu(array(
                    'theme_location' => 'menu-footer',
                    'menu_class'     => 'footer-nav',
                    'depth'         => 1
                ));
                ?>
            </div>

            <div class="site-info">
                <p>&copy; <?php echo date('Y'); ?> <?php bloginfo('name'); ?>. <?php esc_html_e('Todos los derechos reservados.', 'mi-tema-personalizable'); ?></p>
            </div>
        </div>
    </div>
</footer>

<?php wp_footer(); ?>
</body>
</html>