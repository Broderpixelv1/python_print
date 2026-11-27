
/**
 * Python Print - Página de pago.
 */

document.addEventListener('DOMContentLoaded', function () {

    const formulario = document.querySelector(
        '.python-checkout #order_review'
    );

    if (!formulario) {
        return;
    }

    const boton = formulario.querySelector('#place_order');

    if (!boton) {
        return;
    }

    const textoOriginal = boton.textContent.trim();

    function actualizarBotonPago() {

        const metodo = formulario.querySelector(
            'input[name="payment_method"]:checked'
        );

        if (!metodo) {
            return;
        }

        const texto = metodo.value === 'bacs'
            ? 'Confirmar pedido'
            : boton.getAttribute('data-value') || textoOriginal;

        boton.textContent = texto;
        boton.value = texto;

    }

    formulario.addEventListener('change', function (event) {

        if (event.target.name === 'payment_method') {
            actualizarBotonPago();
        }

    });

    actualizarBotonPago();

});
