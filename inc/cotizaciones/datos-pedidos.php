<?php
/**
 * Python Print — Información comercial de los pedidos.
 */

defined('ABSPATH') || exit;


/**
 * Mostrar información tributaria en administración.
 */
add_action(
    'woocommerce_admin_order_data_after_billing_address',
    'python_mostrar_facturacion_admin',
    10,
    1
);

function python_mostrar_facturacion_admin($pedido) {

    $tipo = $pedido->get_meta('_python_tipo_cliente');
    $identificacion = $pedido->get_meta('_python_identificacion');
    $nombre_comercial = $pedido->get_meta('_python_nombre_comercial');

    if (!in_array($tipo, array('natural', 'empresa'), true)) {
        return;
    }

    echo '<h3>Información tributaria</h3>';

    echo '<p><strong>Tipo de cliente:</strong> ';
    echo esc_html(
        $tipo === 'empresa' ? 'Empresa' : 'Persona natural'
    );
    echo '</p>';

    if ($identificacion !== '') {

        echo '<p><strong>';
        echo $tipo === 'empresa' ? 'RUC:' : 'Cédula:';
        echo '</strong> ';
        echo esc_html($identificacion);
        echo '</p>';

    }

    if ($tipo === 'empresa' && $nombre_comercial !== '') {

        echo '<p><strong>Nombre comercial:</strong> ';
        echo esc_html($nombre_comercial);
        echo '</p>';

    }

}


/**
 * Mostrar información de entrega en administración.
 */
add_action(
    'woocommerce_admin_order_data_after_shipping_address',
    'python_mostrar_entrega_admin',
    10,
    1
);

function python_mostrar_entrega_admin($pedido) {

    $metodo = $pedido->get_meta('_python_metodo_entrega');
    $referencia = $pedido->get_meta('_python_referencia_entrega');

    if (!in_array($metodo, array('retiro', 'envio'), true)) {
        return;
    }

    echo '<h3>Información de entrega</h3>';

    echo '<p><strong>Método de entrega:</strong> ';
    echo esc_html(
        $metodo === 'retiro'
            ? 'Retiro en Python Print'
            : 'Envío a domicilio'
    );
    echo '</p>';

    if ($metodo === 'envio' && $referencia !== '') {

        echo '<p><strong>Referencia:</strong> ';
        echo esc_html($referencia);
        echo '</p>';

    }

}