
/**
 * Python Print - Página de pago.
 */

jQuery(function ($) {

    const formulario = $('.python-checkout #order_review');

    if (!formulario.length) {
        return;
    }

    function actualizarBotonPago() {

        const metodo = formulario.find(
            'input[name="payment_method"]:checked'
        );

        const boton = formulario.find('#place_order');

        if (!metodo.length || !boton.length) {
            return;
        }

        if (metodo.val() === 'bacs') {

            boton.text('Confirmar pedido');
            boton.val('Confirmar pedido');

        } else {

            const texto = metodo.attr('data-order_button_text')
                || boton.attr('data-value');

            if (texto) {
                boton.text(texto);
                boton.val(texto);
            }

        }

    }

    // Inicialización de la página.
    actualizarBotonPago();

    // Cambio de método de pago.
    formulario.on(
        'change',
        'input[name="payment_method"]',
        actualizarBotonPago
    );

    // Eventos nativos de WooCommerce.
    $(document.body).on(
        'payment_method_selected updated_checkout',
        actualizarBotonPago
    );

    // Después de cargar todos los recursos.
    $(window).on('load', actualizarBotonPago);

});
