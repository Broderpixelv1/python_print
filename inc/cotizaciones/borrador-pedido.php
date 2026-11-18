
<?php
/**
 * Python Print — Borrador persistente de Mi pedido.
 */

defined('ABSPATH') || exit;

/**
 * Guardar las combinaciones del carrito
 * en la cuenta del cliente.
 */
function python_guardar_borrador_pedido() {

    if (!is_user_logged_in()) {
        return;
    }

    if (!function_exists('WC') || !WC()->cart) {
        return;
    }

    // Evitar sobrescribir el borrador durante su recuperación.
    if (doing_action('woocommerce_cart_loaded_from_session')) {
        return;
    }

    $usuario = wp_get_current_user();

    if (!in_array('customer', (array) $usuario->roles, true)) {
        return;
    }

    $combinaciones = array();

    foreach (WC()->cart->get_cart() as $item) {

        if (empty($item['python_configuracion'])) {
            continue;
        }

        $combinaciones[] = array(
            'product_id' => absint($item['product_id']),
            'configuracion' => $item['python_configuracion'],
            'combinacion_id' => isset($item['python_combinacion_id'])
                ? sanitize_text_field($item['python_combinacion_id'])
                : wp_generate_uuid4(),
        );
    }

    update_user_meta(
        $usuario->ID,
        '_python_borrador_pedido',
        $combinaciones
    );
}

/**
 * Guardar el borrador después de modificar
 * el carrito mediante el configurador.
 */
add_action(
    'woocommerce_add_to_cart',
    'python_guardar_borrador_pedido',
    20
);

add_action(
    'woocommerce_cart_item_removed',
    'python_guardar_borrador_pedido',
    20
);

add_action(
    'woocommerce_cart_item_restored',
    'python_guardar_borrador_pedido',
    20
);


/**
 * Recuperar el borrador del cliente cuando
 * WooCommerce termina de cargar el carrito.
 */
add_action('woocommerce_cart_loaded_from_session', function ($cart) {

    if (!is_user_logged_in() || !function_exists('WC')) {
        return;
    }

    $usuario = wp_get_current_user();

    if (!in_array('customer', (array) $usuario->roles, true)) {
        return;
    }

    // No sustituir un carrito que ya contiene productos.
    if (!empty($cart->get_cart())) {
        return;
    }

    $borrador = get_user_meta(
        $usuario->ID,
        '_python_borrador_pedido',
        true
    );

    if (!is_array($borrador) || empty($borrador)) {
        return;
    }

    foreach ($borrador as $item) {

        if (
            empty($item['product_id']) ||
            empty($item['configuracion'])
        ) {
            continue;
        }

        $producto = wc_get_product(absint($item['product_id']));

        if (!$producto || $producto->get_status() !== 'publish') {
            continue;
        }

        $cart->add_to_cart(
            $producto->get_id(),
            1,
            0,
            array(),
            array(
                'python_configuracion' => $item['configuracion'],
                'python_combinacion_id' => !empty($item['combinacion_id'])
                    ? $item['combinacion_id']
                    : wp_generate_uuid4(),
            )
        );
    }

}, 20);
