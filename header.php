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
            <?php if (false) : ?>
            <a href="<?php echo esc_url(home_url('/favorites')); ?>"
               aria-label="<?php esc_attr_e('Favoritos', 'mi-tema-personalizable'); ?>">
                <i class="fa-solid fa-heart"></i>
            </a>
            <?php endif; ?>

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

                <button
                    type="button"
                    class="python-mobile-account-trigger"
                    aria-expanded="false"
                    aria-controls="python-mobile-account-dropdown"
                    aria-label="Abrir menú de mi cuenta"
                >
                    <i class="fa-solid fa-circle-user"></i>
                </button>

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

        <!--menu user respopnsivo -->


        <?php if (is_user_logged_in()): ?>

        <div
            id="python-mobile-account-dropdown"
            class="python-mobile-account-dropdown"
            hidden
        >
            <div class="python-account-dropdown__header">
                <strong>
                    <?php echo esc_html(wp_get_current_user()->display_name); ?>
                </strong>
                <small>
                    <?php echo esc_html(wp_get_current_user()->user_email); ?>
                </small>
            </div>

            <a href="<?php echo esc_url(wc_get_page_permalink('myaccount')); ?>">
                <i class="fa-regular fa-user"></i>
                Mi perfil
            </a>

            <a href="<?php echo esc_url(home_url('/mi-pedido/')); ?>">
                <i class="fa-solid fa-cart-shopping"></i>
                Carrito
            </a>

            <a href="<?php echo esc_url(home_url('/mis-cotizaciones/')); ?>">
                <i class="fa-regular fa-file-lines"></i>
                Mis cotizaciones
            </a>

            <a href="<?php echo esc_url(wc_get_account_endpoint_url('orders')); ?>">
                <i class="fa-solid fa-box"></i>
                Mis pedidos
            </a>

            <div class="python-account-dropdown__divider"></div>

            <a
                class="python-account-logout"
                href="<?php echo esc_url(wp_logout_url(home_url('/'))); ?>"
            >
                <i class="fa-solid fa-arrow-right-from-bracket"></i>
                Cerrar sesión
            </a>
        </div>

        <?php endif; ?>

        <!-- Menú de usuario de escritorio -->
        <div class="user-menu">

            <div class="help">
                <a href="<?php echo esc_url('https://wa.me/593995932259'); ?>>">
                    <span class="muser-icon"><i class="fa-solid fa-comments"></i></span>
                    <?php esc_html_e('Ayuda', 'mi-tema-personalizable'); ?>
                </a>
                <small>0995932259</small>
            </div>

            <?php if (is_user_logged_in()): ?>


                <!-- Favoritos -->
                 <?php if (false) : ?>
                <a href="<?php echo esc_url(home_url('/favorites')); ?>">
                    <span class="muser-icon">
                        <i class="fa-solid fa-heart"></i>
                    </span>
                    Favoritos
                </a>
                <?php endif; ?>

                <!-- Mi pedido -->
                <a href="<?php echo esc_url(home_url('/mi-pedido/')); ?>">
                    <span class="muser-icon">
                        <i class="fa-solid fa-cart-shopping"></i>
                    </span>
                    Carrito
                </a>

                <!-- Menú desplegable del cliente -->
                <div class="python-account-menu">

                    <button
                        type="button"
                        class="python-account-trigger"
                        aria-expanded="false"
                        aria-haspopup="true"
                        aria-controls="python-account-dropdown"
                    >
                        <span class="muser-icon">
                            <i class="fa-solid fa-circle-user"></i>
                        </span>

                        <span>Mi cuenta</span>

                        <i class="fa-solid fa-chevron-down python-account-chevron"></i>
                    </button>

                    <div
                        id="python-account-dropdown"
                        class="python-account-dropdown"
                        hidden
                    >
                        <div class="python-account-dropdown__header">
                            <strong>
                                <?php echo esc_html(wp_get_current_user()->display_name); ?>
                            </strong>

                            <small>
                                <?php echo esc_html(wp_get_current_user()->user_email); ?>
                            </small>
                        </div>

                        <a href="<?php echo esc_url(wc_get_page_permalink('myaccount')); ?>">
                            <i class="fa-regular fa-user"></i>
                            Mi perfil
                        </a>

                        <a href="<?php echo esc_url(home_url('/mis-cotizaciones/')); ?>">
                            <i class="fa-regular fa-file-lines"></i>
                            Mis cotizaciones
                        </a>

                        <a href="<?php echo esc_url(wc_get_account_endpoint_url('orders')); ?>">
                            <i class="fa-solid fa-box"></i>
                            Mis pedidos
                        </a>

                        <div class="python-account-dropdown__divider"></div>

                        <a
                            class="python-account-logout"
                            href="<?php echo esc_url(wp_logout_url(home_url('/'))); ?>"
                        >
                            <i class="fa-solid fa-arrow-right-from-bracket"></i>
                            Cerrar sesión
                        </a>
                    </div>

                </div>


            <?php else: ?>
                <?php if (false) : ?>
                <a href="<?php echo esc_url(home_url('/favorites')); ?>">
                    <span class="muser-icon"><i class="fa-solid fa-heart"></i></span>
                    <?php esc_html_e('Favoritos', 'mi-tema-personalizable'); ?>
                </a>
                <?php endif; ?>

                <?php if (class_exists('WooCommerce')): ?>
                    <a href="<?php echo esc_url(wc_get_cart_url()); ?>">
                <?php else: ?>
                    <a href="<?php echo esc_url(home_url('/cart')); ?>">
                <?php endif; ?>
                    <span class="muser-icon"><i class="fa-solid fa-cart-shopping"></i></span>
                    <?php esc_html_e('Carrito', 'mi-tema-personalizable'); ?>
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
                    <?php esc_html_e('Iniciar sesión', 'mi-tema-personalizable'); ?>
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