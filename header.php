<?php
/**
 * The header for our theme
 *
 * @package Mi Tema Personalizable
 */
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<header class="site-header">
    <div class="grid-container">
        <!-- Logo -->
        <div class="logo">
            <?php 
            if (has_custom_logo()) {
                the_custom_logo();
            } else {
                echo '<span><strong>'.get_bloginfo('name').'</strong></span>';
            }
            ?>
        </div>

        <!-- Buscador -->
        <div class="search-bar">
            <?php get_search_form(); ?>
        </div>

        <!-- Menú de usuario -->
        <div class="user-menu">
            <div class="help">
                <span>🗨️</span>
                <a href="<?php echo esc_url(home_url('/help')); ?>"><?php esc_html_e('Help is here', 'mi-tema-personalizable'); ?></a>
                <small>1.866.207.4955</small>
            </div>
            
            <?php if (is_user_logged_in()): ?>
                <a href="<?php echo esc_url(home_url('/my-projects')); ?>"><?php esc_html_e('My Projects', 'mi-tema-personalizable'); ?></a>
                <a href="<?php echo esc_url(home_url('/favorites')); ?>"><?php esc_html_e('My Favorites', 'mi-tema-personalizable'); ?></a>
                <a href="<?php echo wp_logout_url(); ?>"><?php esc_html_e('Logout', 'mi-tema-personalizable'); ?></a>
            <?php else: ?>
                <a href="<?php echo wp_login_url(); ?>"><?php esc_html_e('Sign in', 'mi-tema-personalizable'); ?></a>
            <?php endif; ?>
            
            <?php if (class_exists('WooCommerce')): ?>
                <a href="<?php echo esc_url(wc_get_cart_url()); ?>">🛒 <?php esc_html_e('Cart', 'mi-tema-personalizable'); ?></a>
            <?php else: ?>
                <a href="<?php echo esc_url(home_url('/cart')); ?>">🛒 <?php esc_html_e('Cart', 'mi-tema-personalizable'); ?></a>
            <?php endif; ?>
        </div>

        <!-- Menú Mega Principal -->
        <nav class="mega-menu-container">
            <?php
            wp_nav_menu(array(
                'theme_location' => 'menu-principal',
                'container'      => false,
                'menu_class'     => '',
                'walker'         => new Mega_Menu_Walker(),
                'fallback_cb'    => false
            ));
            ?>
        </nav>
    </div>
</header>

<!-- Cortina para el menú mega -->
<div class="cortina"></div>

<!-- Contenido principal -->
<main id="primary" class="site-main">