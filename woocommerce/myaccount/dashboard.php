<?php
/**
 * Python Print — Inicio de Mi cuenta.
 */

defined('ABSPATH') || exit;

$cliente = wp_get_current_user();

$nombre = $cliente->first_name
    ?: $cliente->display_name;

$secciones = array(

    array(
        'titulo' => 'Mis cotizaciones',
        'descripcion' => 'Consulta tus solicitudes y aprueba tus proformas.',
        'url' => home_url('/mis-cotizaciones/'),
        'icono' => 'dashicons-media-document',
    ),

    array(
        'titulo' => 'Mis pedidos',
        'descripcion' => 'Consulta el estado y los detalles de tus pedidos.',
        'url' => wc_get_account_endpoint_url('orders'),
        'icono' => 'dashicons-archive',
    ),

    array(
        'titulo' => 'Direcciones',
        'descripcion' => 'Gestiona tus datos de facturación y entrega.',
        'url' => wc_get_account_endpoint_url('edit-address'),
        'icono' => 'dashicons-location',
    ),

    array(
        'titulo' => 'Mis datos',
        'descripcion' => 'Actualiza tu información personal.',
        'url' => wc_get_account_endpoint_url('edit-account'),
        'icono' => 'dashicons-admin-users',
    ),

);

?>

<section class="python-account-dashboard">

    <header class="python-account-dashboard__header">

        <h1>
            ¡Hola, <?php echo esc_html($nombre); ?>!
        </h1>

        <p>
            Gestiona tus cotizaciones, pedidos e
            información personal desde un solo lugar.
        </p>

    </header>

    <div class="python-account-dashboard__grid">

        <?php foreach ($secciones as $seccion) : ?>

            <a
                class="python-account-dashboard__card"
                href="<?php echo esc_url($seccion['url']); ?>"
            >

                <span
                    class="dashicons <?php echo esc_attr($seccion['icono']); ?>"
                    aria-hidden="true"
                ></span>

                <h2>
                    <?php echo esc_html($seccion['titulo']); ?>
                </h2>

                <p>
                    <?php echo esc_html($seccion['descripcion']); ?>
                </p>

                <span class="python-account-dashboard__link">
                    Ingresar &rarr;
                </span>

            </a>

        <?php endforeach; ?>

    </div>

</section>

<?php do_action('woocommerce_account_dashboard'); ?>