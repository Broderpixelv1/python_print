<footer class="site-footer">
    <div class="footer-container">
        <div class="footer-widgets">
            <?php if (is_active_sidebar('footer-widgets')) : ?>
                <div class="widgets-row">
                    <?php dynamic_sidebar('footer-widgets'); ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="footer-bottom">
            
            <div class="footer-menu">
                <div class="footer-box">
                    <p>You need it. We print it. You love it.</p>
                    <p>VistaPrint is here to help every step of the way.</p>
                </div>
                <div class="footer-box">
                    <?php  wp_nav_menu(array(
                    'theme_location' => 'menu-footer-help',
                    'menu_class'     => 'footer-help',
                    'depth'         => 1
                    ));
                    ?>
                </div>
                <div class="footer-box">
                    <?php wp_nav_menu(array(
                    'theme_location' => 'menu-footer-about',
                    'menu_class'     => 'footer-about',
                    'depth'         => 1
                    ));
                     ?>
                </div>
                 <div class="footer-box"></div>
                
            </div>

            <div class="site-info">
                <div class="col-info">
                     <?php
                wp_nav_menu(array(
                    'theme_location' => 'menu-footer-policies',
                    'menu_class'     => 'footer-policies',
                    'depth'         => 1
                        ));
                    ?>
                    <p>&copy; <?php echo date('Y'); ?> <?php bloginfo('name'); ?>. <?php esc_html_e('Todos los derechos reservados.', 'mi-tema-personalizable'); ?></p>
                </div>
                <div class="col-info">
                    <div class="payment-icons">
                        
                        <i class="fa-brands fa-cc-visa"></i>
                        <i class="fa-brands fa-cc-mastercard"></i>
                        <i class="fa-brands fa-cc-amex"></i>
                        <i class="fa-brands fa-cc-discover"></i>
                        <i class="fa-brands fa-cc-diners-club"></i>
                        <i class="fa-brands fa-cc-paypal"></i>
                        
                    </div>
                </div>
                <div class="col-info">
                    <div class="social-icons">
                        <a href="https://facebook.com/tu_pagina" target="_blank">
                            <i class="fa-brands fa-facebook"></i>
                        </a>
                        <a href="https://instagram.com/tu_pagina" target="_blank">
                            <i class="fa-brands fa-instagram"></i>
                        </a>
                        <a href="https://tiktok.com/@tu_usuario" target="_blank">
                            <i class="fa-brands fa-tiktok"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</footer>

<?php wp_footer(); ?>
</body>
</html>