/**
 * Python Print — Cálculos de proformas.
 */
document.addEventListener('DOMContentLoaded', function () {

    const panel = document.querySelector('.python-proforma');

    if (!panel) return;

    const precios = panel.querySelectorAll('.python-proforma__price');

    const descuentoTipo = document.getElementById(
        'python-proforma-descuento-tipo'
    );

    const descuentoInput = document.getElementById(
        'python-proforma-descuento'
    );

    const ivaSelect = document.getElementById(
        'python-proforma-iva'
    );

    const envioInput = document.getElementById(
        'python-proforma-envio'
    );

    const campos = {
        subtotal: document.getElementById('python-proforma-subtotal'),
        descuento: document.getElementById('python-proforma-descuento-total'),
        iva: document.getElementById('python-proforma-iva-total'),
        envio: document.getElementById('python-proforma-envio-total'),
        total: document.getElementById('python-proforma-total')
    };

    const numero = valor => {
        const n = Number.parseFloat(valor);
        return Number.isFinite(n) ? Math.max(0, n) : 0;
    };

    const dinero = valor => {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD'
        }).format(valor);
    };

    function calcularProforma() {

        let subtotal = 0;

        precios.forEach(input => {
            subtotal += numero(input.value);
        });

        const descuentoIngresado = numero(descuentoInput.value);

        let descuento = 0;

        if (descuentoTipo.value === 'porcentaje') {
            descuento = subtotal * Math.min(descuentoIngresado, 100) / 100;
        } else {
            descuento = Math.min(descuentoIngresado, subtotal);
        }

        const baseImponible = Math.max(0, subtotal - descuento);

        // Tasa de referencia. Debe confirmarse antes de emitir la proforma.
        const tasaIva = 0.15;

        const iva = ivaSelect.value === 'con'
            ? baseImponible * tasaIva
            : 0;

        const envio = numero(envioInput.value);

        const total = baseImponible + iva + envio;

        campos.subtotal.textContent = dinero(subtotal);
        campos.descuento.textContent = '-' + dinero(descuento);
        campos.iva.textContent = dinero(iva);
        campos.envio.textContent = dinero(envio);
        campos.total.textContent = dinero(total);
    }

    panel.addEventListener('input', calcularProforma);
    panel.addEventListener('change', calcularProforma);

    calcularProforma();
});