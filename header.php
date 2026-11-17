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

        <!-- Acciones móviles: izquierda -->
        <div class="mobile-header-left">
            <a class="mobile-help" href="<?php echo esc_url(home_url('/help')); ?>">
                <i class="fa-solid fa-comments"></i>
                <span><?php esc_html_e('Ayuda', 'mi-tema-personalizable'); ?></span>
            </a>

            <button
                class="icon-btn"
                id="btn-open-search"
                type="button"
                aria-expanded="false"
                aria-controls="search-overlay"
                aria-label="<?php esc_attr_e('Buscar', 'mi-tema-personalizable'); ?>"
            >
                <i class="fa-solid fa-magnifying-glass"></i>
            </button>
        </div>

        <!-- Logo -->
        <div class="logo">
            <?php
            if (has_custom_logo()) {
                the_custom_logo();
            } else {
                echo '<span><strong>' . esc_html(get_bloginfo('name')) . '</strong></span>';
            }
            ?>
        </div>

        <!-- Buscador de escritorio -->
        <div class="search-bar">
            <?php get_search_form(); ?>
        </div>

        <!-- Acciones móviles: derecha -->
        <div class="mobile-header-right">

            <a href="<?php echo esc_url(home_url('/favorites')); ?>"
               aria-label="<?php esc_attr_e('Favoritos', 'mi-tema-personalizable'); ?>">
                <i class="fa-solid fa-heart"></i>
            </a>

            <?php if (class_exists('WooCommerce')): ?>
                <a href="<?php echo esc_url(wc_get_cart_url()); ?>"
                   aria-label="<?php esc_attr_e('Carrito', 'mi-tema-personalizable'); ?>">
                    <i class="fa-solid fa-cart-shopping"></i>
                </a>
            <?php else: ?>
                <a href="<?php echo esc_url(home_url('/cart')); ?>"
                   aria-label="<?php esc_attr_e('Carrito', 'mi-tema-personalizable'); ?>">
                    <i class="fa-solid fa-cart-shopping"></i>
                </a>
            <?php endif; ?>

            <?php if (is_user_logged_in()): ?>
                <a href="<?php echo esc_url(home_url('/my-account')); ?>"
                   aria-label="<?php esc_attr_e('Mi cuenta', 'mi-tema-personalizable'); ?>">
                    <i class="fa-solid fa-user"></i>
                </a>
            <?php else: ?>
                <button
                    type="button"
                    class="python-login-trigger"
                    data-python-login-open
                    aria-haspopup="dialog"
                    aria-controls="python-login-modal"
                    aria-label="<?php esc_attr_e('Iniciar sesión', 'mi-tema-personalizable'); ?>"
                >
                    <i class="fa-solid fa-circle-user"></i>
                </button>
            <?php endif; ?>

        </div>

        <!-- Menú de usuario de escritorio -->
        <div class="user-menu">

            <div class="help">
                <a href="<?php echo esc_url(home_url('/help')); ?>">
                    <span class="muser-icon"><i class="fa-solid fa-comments"></i></span>
                    <?php esc_html_e('Ayuda', 'mi-tema-personalizable'); ?>
                </a>
                <small>0995932259</small>
            </div>

            <?php if (is_user_logged_in()): ?>

                <a href="<?php echo esc_url(home_url('/my-projects')); ?>">
                    <span class="muser-icon"><i class="fa-solid fa-folder-open"></i></span>
                    <?php esc_html_e('My Projects', 'mi-tema-personalizable'); ?>
                </a>

                <?php if (class_exists('WooCommerce')): ?>
                    <a href="<?php echo esc_url(wc_get_cart_url()); ?>">
                <?php else: ?>
                    <a href="<?php echo esc_url(home_url('/cart')); ?>">
                <?php endif; ?>
                    <span class="muser-icon"><i class="fa-solid fa-cart-shopping"></i></span>
                    <?php esc_html_e('Cart', 'mi-tema-personalizable'); ?>
                </a>

                <a href="<?php echo esc_url(home_url('/my-account')); ?>">
                    <span class="muser-icon"><i class="fa-solid fa-user"></i></span>
                    <?php esc_html_e('Mi Cuenta', 'mi-tema-personalizable'); ?>
                </a>

                <a href="<?php echo esc_url(wp_logout_url()); ?>">
                    <span class="muser-icon"><i class="fa-solid fa-right-from-bracket"></i></span>
                    <?php esc_html_e('Logout', 'mi-tema-personalizable'); ?>
                </a>

            <?php else: ?>

                <a href="<?php echo esc_url(home_url('/favorites')); ?>">
                    <span class="muser-icon"><i class="fa-solid fa-heart"></i></span>
                    <?php esc_html_e('My Favorites', 'mi-tema-personalizable'); ?>
                </a>

                <?php if (class_exists('WooCommerce')): ?>
                    <a href="<?php echo esc_url(wc_get_cart_url()); ?>">
                <?php else: ?>
                    <a href="<?php echo esc_url(home_url('/cart')); ?>">
                <?php endif; ?>
                    <span class="muser-icon"><i class="fa-solid fa-cart-shopping"></i></span>
                    <?php esc_html_e('Cart', 'mi-tema-personalizable'); ?>
                </a>

                <button
                    type="button"
                    class="python-login-trigger"
                    data-python-login-open
                    aria-haspopup="dialog"
                    aria-controls="python-login-modal"
                >
                    <span class="muser-icon">
                        <i class="fa-solid fa-circle-user"></i>
                    </span>
                    <?php esc_html_e('Sign in', 'mi-tema-personalizable'); ?>
                </button>

            <?php endif; ?>

        </div>

    </div>

    <!-- El JavaScript construirá aquí la barra horizontal móvil -->

    <!-- Menú original de WordPress: se conserva para escritorio y se reutiliza en móvil -->
    <nav class="mega-menu-container" id="mega-menu" aria-label="<?php esc_attr_e('Menú principal', 'mi-tema-personalizable'); ?>">

        <button type="button" class="btn-close-menu" aria-label="<?php esc_attr_e('Cerrar menú', 'mi-tema-personalizable'); ?>">
            <i class="fa-solid fa-xmark"></i>
        </button>

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