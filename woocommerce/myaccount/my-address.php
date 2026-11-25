<?php
/**
 * Python Print — Datos de facturación y entrega.
 */

defined('ABSPATH') || exit;

$cliente_id = get_current_user_id();
$cliente = wp_get_current_user();

$meta = static function ($clave) use ($cliente_id) {
    return get_user_meta($cliente_id, $clave, true);
};

$tipo = $meta('python_tipo_cliente');
$entrega = $meta('python_metodo_entrega');

$facturacion = array(
    'Nombre completo' => trim(
        $cliente->first_name . ' ' .
        $cliente->last_name
    ),
    'Tipo de cliente' => $tipo === 'empresa'
        ? 'Empresa'
        : ($tipo === 'natural' ? 'Persona natural' : ''),
    'Teléfono / WhatsApp' => $meta('billing_phone'),
    'Correo electrónico' => $cliente->user_email,
);

if ($tipo === 'empresa') {

    $facturacion['RUC'] = $meta('python_ruc');
    $facturacion['Razón social'] = $meta('billing_company');
    $facturacion['Nombre comercial'] = $meta('python_nombre_comercial');

} elseif ($tipo === 'natural') {

    $facturacion['Cédula'] = $meta('python_cedula');

}

$envio = array(
    'Método de entrega' => $entrega === 'retiro'
        ? 'Retiro en el establecimiento'
        : ($entrega === 'envio' ? 'Envío a domicilio' : ''),
);

if ($entrega !== 'retiro') {

    $envio['Provincia'] = $meta('shipping_state');
    $envio['Ciudad'] = $meta('shipping_city');
    $envio['Dirección'] = $meta('shipping_address_1');
    $envio['Referencia'] = $meta('python_referencia_entrega');

}

$tarjetas = array(

    array(
        'titulo' => 'Datos de facturación',
        'seccion' => 'facturacion',
        'icono' => 'dashicons-media-document',
        'datos' => $facturacion,
    ),

    array(
        'titulo' => 'Dirección de entrega',
        'seccion' => 'entrega',
        'icono' => 'dashicons-location',
        'datos' => $envio,
    ),

);

?>

<section class="python-account-addresses">

    <header class="python-account-dashboard__header">

        <h1>Mis direcciones</h1>

        <p>
            Consulta tus datos de facturación y entrega
            para tus próximos pedidos.
        </p>

    </header>

    <div class="python-account-addresses__grid">

        <?php foreach ($tarjetas as $tarjeta) : ?>

            <article class="python-account-addresses__card">

                <span
                    class="dashicons <?php echo esc_attr($tarjeta['icono']); ?>"
                    aria-hidden="true"
                ></span>

                <h2>
                    <?php echo esc_html($tarjeta['titulo']); ?>
                </h2>

                <div class="python-account-addresses__fields">

                    <?php foreach ($tarjeta['datos'] as $etiqueta => $valor) : ?>

                        <div class="python-customer-field">

                            <span class="python-customer-field__label">
                                <?php echo esc_html($etiqueta); ?>
                            </span>

                            <span class="python-customer-field__value">
                                <?php echo esc_html(
                                    $valor !== '' ? $valor : 'Sin registrar'
                                ); ?>
                            </span>

                        </div>

                    <?php endforeach; ?>

                </div>

                <button
                    type="button"
                    class="python-account-addresses__edit"
                    data-quote-section="<?php echo esc_attr($tarjeta['seccion']); ?>"
                >
                    <span
                        class="dashicons dashicons-edit"
                        aria-hidden="true"
                    ></span>

                    Editar datos
                </button>

            </article>

        <?php endforeach; ?>

    </div>

</section>

<?php
get_template_part('template-parts/formulario-cotizacion');
?>