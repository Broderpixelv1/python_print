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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Rubik:ital,wght@0,300..900;1,300..900&display=swap" rel="stylesheet">

    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<header class="site-header">
    <div class="grid-container">
        <!-- Botones (móvil/tablet) -->
        <div class="mobile-actions">
            <button class="icon-btn" id="btn-open-menu" aria-expanded="false" aria-controls="mega-menu" aria-label="<?php esc_attr_e('Abrir menú', 'mi-tema-personalizable'); ?>">
                <i class="fa-solid fa-bars"></i>
            </button>
            <button class="icon-btn" id="btn-open-search" aria-expanded="false" aria-controls="search-overlay" aria-label="<?php esc_attr_e('Buscar', 'mi-tema-personalizable'); ?>">
                <i class="fa-solid fa-magnifying-glass"></i>
            </button>
        </div>

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
    <!-- Siempre visible -->
    <div class="help">
        <a href="<?php echo esc_url(home_url('/help')); ?>">
            <span class="muser-icon"><i class="fa-solid fa-comments"></i></span>
            <?php esc_html_e('Ayuda', 'mi-tema-personalizable'); ?>
        </a>
        <small>0995932259</small>
    </div>

    <?php if (is_user_logged_in()): ?>
        <!-- Usuario logueado -->
        <a href="<?php echo esc_url(home_url('/my-projects')); ?>">
            <span class="muser-icon"><i class="fa-solid fa-folder-open"></i></span>
            <?php esc_html_e('My Projects', 'mi-tema-personalizable'); ?>
        </a>

        <?php if (class_exists('WooCommerce')): ?>
            <a href="<?php echo esc_url(wc_get_cart_url()); ?>">
                <span class="muser-icon"><i class="fa-solid fa-cart-shopping"></i></span>
                <?php esc_html_e('Cart', 'mi-tema-personalizable'); ?>
            </a>
        <?php else: ?>
            <a href="<?php echo esc_url(home_url('/cart')); ?>">
                <span class="muser-icon"><i class="fa-solid fa-cart-shopping"></i></span>
                <?php esc_html_e('Cart', 'mi-tema-personalizable'); ?>
            </a>
        <?php endif; ?>

        <a href="<?php echo esc_url(home_url('/my-account')); ?>">
            <span class="muser-icon"><i class="fa-solid fa-user"></i></span>
            <?php esc_html_e('Mi Cuenta', 'mi-tema-personalizable'); ?>
        </a>

        <a href="<?php echo wp_logout_url(); ?>">
            <span class="muser-icon"><i class="fa-solid fa-right-from-bracket"></i></span>
            <?php esc_html_e('Logout', 'mi-tema-personalizable'); ?>
        </a>

    <?php else: ?>
        <!-- Usuario no logueado -->
        <a href="<?php echo esc_url(home_url('/favorites')); ?>">
            <span class="muser-icon"><i class="fa-solid fa-heart"></i></span>
            <?php esc_html_e('My Favorites', 'mi-tema-personalizable'); ?>
        </a>

        <?php if (class_exists('WooCommerce')): ?>
            <a href="<?php echo esc_url(wc_get_cart_url()); ?>">
                <span class="muser-icon"><i class="fa-solid fa-cart-shopping"></i></span>
                <?php esc_html_e('Cart', 'mi-tema-personalizable'); ?>
            </a>
        <?php else: ?>
            <a href="<?php echo esc_url(home_url('/cart')); ?>">
                <span class="muser-icon"><i class="fa-solid fa-cart-shopping"></i></span>
                <?php esc_html_e('Cart', 'mi-tema-personalizable'); ?>
            </a>
        <?php endif; ?>

        <a href="<?php echo wp_login_url(); ?>">
            <span class="muser-icon"><i class="fa-solid fa-circle-user"></i></span>
            <?php esc_html_e('Sign in', 'mi-tema-personalizable'); ?>
        </a>
    <?php endif; ?>
</div>

        <!-- Menú Mega Principal -->
        <nav class="mega-menu-container" id="mega-menu">
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
<!-- Overlay de Búsqueda (móvil/tablet) -->
<div class="search-overlay" id="search-overlay" aria-hidden="true">
  <div class="search-box" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e('Buscar', 'mi-tema-personalizable'); ?>">
    <button class="icon-btn close-search" aria-label="<?php esc_attr_e('Cerrar búsqueda', 'mi-tema-personalizable'); ?>">
      <i class="fa-solid fa-xmark"></i>
    </button>
    <div class="search-form-wrap">
      <?php get_search_form(); ?>
    </div>
  </div>
</div>

<!-- Cortina para el menú mega -->
<div class="cortina"></div>

<!-- Contenido principal -->
<main id="primary" class="site-main">