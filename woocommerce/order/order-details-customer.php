<?php
/**
 * Python Print — Datos del cliente en el pedido.
 */

defined('ABSPATH') || exit;

$facturacion = array(
    'Nombre completo' => trim(
        $order->get_billing_first_name() . ' ' .
        $order->get_billing_last_name()
    ),
    'Razón social'       => $order->get_billing_company(),
    'Dirección'          => $order->get_billing_address_1(),
    'Dirección adicional'=> $order->get_billing_address_2(),
    'Ciudad'             => $order->get_billing_city(),
    'Provincia'          => $order->get_billing_state(),
    'Código postal'      => $order->get_billing_postcode(),
    'Teléfono'           => $order->get_billing_phone(),
    'Correo electrónico' => $order->get_billing_email(),
);

$envio = array(
    'Destinatario' => trim(
        $order->get_shipping_first_name() . ' ' .
        $order->get_shipping_last_name()
    ),
    'Empresa'             => $order->get_shipping_company(),
    'Dirección'           => $order->get_shipping_address_1(),
    'Dirección adicional' => $order->get_shipping_address_2(),
    'Ciudad'              => $order->get_shipping_city(),
    'Provincia'           => $order->get_shipping_state(),
    'Código postal'       => $order->get_shipping_postcode(),
    'Teléfono'            => $order->get_shipping_phone(),
);

// Convertir los códigos de país y provincia en nombres.
$paises = WC()->countries->get_countries();

foreach (array('billing', 'shipping') as $tipo) {

    $pais = $order->{"get_{$tipo}_country"}();
    $provincia = $order->{"get_{$tipo}_state"}();

    $provincias = $pais
        ? WC()->countries->get_states($pais)
        : array();

    $datos = &$facturacion;

    if ($tipo === 'shipping') {
        $datos = &$envio;
    }

    $datos['Provincia'] = $provincias[$provincia] ?? $provincia;
    $datos['País'] = $paises[$pais] ?? $pais;

    unset($datos);
}

$mostrar_datos = static function ($datos) {

    foreach ($datos as $etiqueta => $valor) {

        if (trim((string) $valor) === '') {
            continue;
        }
        ?>

        <div class="python-customer-field">

            <span class="python-customer-field__label">
                <?php echo esc_html($etiqueta); ?>
            </span>

            <span class="python-customer-field__value">
                <?php echo esc_html($valor); ?>
            </span>

        </div>

        <?php
    }
};

$mostrar_envio = !wc_ship_to_billing_address_only()
    && $order->needs_shipping_address();

?>

<section class="woocommerce-customer-details">

    <div class="python-customer-addresses">

        <div class="python-customer-address">

            <h2 class="woocommerce-column__title">
                Dirección de facturación
            </h2>

            <address>
                <?php $mostrar_datos($facturacion); ?>
            </address>

        </div>

        <?php if ($mostrar_envio) : ?>

            <div class="python-customer-address">

                <h2 class="woocommerce-column__title">
                    Dirección de envío
                </h2>

                <address>
                    <?php $mostrar_datos($envio); ?>
                </address>

            </div>

        <?php endif; ?>

    </div>

    <?php do_action(
        'woocommerce_order_details_after_customer_details',
        $order
    ); ?>

</section>