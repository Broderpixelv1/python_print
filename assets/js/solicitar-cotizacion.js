
document.addEventListener('DOMContentLoaded', () => {
  const modal = document.getElementById('python-quote-form');
  const form = document.getElementById('python-quote-request');
  const save = document.getElementById('python-quote-save');
  const quoteButton = document.getElementById('python-open-quote');
  if (!modal || !form || !save || !quoteButton) return;

  const panels = [...modal.querySelectorAll('[data-quote-panel]')];
  const error = document.getElementById('python-quote-error');
  const title = document.getElementById('python-quote-title');
  const dialog = modal.querySelector('.python-quote-modal__dialog');
  const field = name => form.elements.namedItem(name);
  const val = name => String(field(name)?.value || '').trim();
  const choice = name => form.querySelector(`input[name="${name}"]:checked`)?.value || '';
  const sectionNames = { perfil: 'Mi perfil', pago: 'Método de pago', entrega: 'Envío o entrega', facturacion: 'Datos de facturación' };

  const completed = {
    perfil: false,
    pago: false,
    entrega: false,
    facturacion: false
  };

  // Reconocer un perfil previamente guardado.
  if (document.body.classList.contains('logged-in')) {
      const nombres = form.elements.namedItem('nombres')?.value.trim();
      const apellidos = form.elements.namedItem('apellidos')?.value.trim();
      const email = form.elements.namedItem('email')?.value.trim();
      const telefono = form.elements.namedItem('telefono')?.value.trim();

      completed.perfil = Boolean(
          nombres && apellidos && email && telefono
      );
  }

  let active = 'perfil';
  let previousFocus = null;

  function conditions() {
    const company = choice('tipo_cliente') === 'empresa';
    modal.querySelector('.python-quote-company').hidden = !company;
    modal.querySelector('.python-quote-natural').hidden = company;
    field('cedula').required = !company;
    field('razon_social').required = company;
    field('ruc').required = company;
    const shipping = choice('metodo_entrega') === 'envio';
    modal.querySelector('.python-quote-shipping').hidden = !shipping;
    ['provincia', 'ciudad', 'direccion'].forEach(name => field(name).required = shipping);
  }

  function message(text) {
    error.textContent = text;
    error.hidden = !text;
  }

  function openSection(name) {
    if (!sectionNames[name]) return;
    previousFocus = document.activeElement;
    active = name;
    modal.hidden = false;
    document.body.classList.add('python-quote-modal-open');
    panels.forEach(panel => { panel.hidden = panel.dataset.quotePanel !== name; });
    title.textContent = sectionNames[name];
    message('');
    conditions();
    quoteButton.setAttribute('aria-expanded', 'true');
    dialog.focus();
  }

  function closeModal() {
    modal.hidden = true;
    document.body.classList.remove('python-quote-modal-open');
    quoteButton.setAttribute('aria-expanded', 'false');
    message('');
    previousFocus?.focus?.();
  }

  function validate(name) {
        conditions();
        const panel = panels.find(p => p.dataset.quotePanel === name);
        const inputs = [...panel.querySelectorAll('input, select, textarea')].filter(input => !input.closest('[hidden]') && !input.disabled);
        for (const input of inputs) {
            if (!input.checkValidity()) {
                const label = input.closest('label');
                const labelText = label?.textContent?.replace(/\s+/g, ' ').trim() || input.name;
                message(`Revisa el campo: ${labelText}`);
                input.focus();
                input.reportValidity();
                return false;
            }
        }
        if (name === 'pago' && !choice('metodo_pago')) {
            message('Selecciona tarjeta o transferencia.');
            return false;
        }
        if (name === 'facturacion') {
            if (!field('privacidad').checked) { message('Debes aceptar la política de privacidad.'); return false; }
            if (!field('terminos').checked) { message('Debes aceptar los términos y condiciones.'); return false; }
        }
        message('');
        return true;
    }

  function updateSidebar() {
        const identity = document.querySelector('.python-quote-profile__identity');
        if (completed.perfil && identity) {
            const name = identity.querySelector('strong');
            const email = identity.querySelector('small');
            if (name) name.textContent = `${val('nombres')} ${val('apellidos')}`;
            if (email) email.textContent = val('email');
        }
        const payment = document.getElementById('python-quote-payment-summary');
        if (payment && completed.pago) payment.textContent = choice('metodo_pago') === 'tarjeta' ? 'Tarjeta de crédito o débito' : 'Transferencia bancaria';
        const shipping = document.getElementById('python-quote-shipping-summary');
        if (shipping && completed.entrega) shipping.textContent = choice('metodo_entrega') === 'envio' ? `Envío a ${val('ciudad')}, ${val('provincia')} · ${val('direccion')}` : 'Retiro en Python Print';
        const billing = document.getElementById('python-quote-billing-summary');
        if (billing && completed.facturacion) billing.textContent = choice('tipo_cliente') === 'empresa' ? `${val('razon_social')} · RUC ${val('ruc')}` : `Persona natural · Cédula ${val('cedula')}`;
    }

    document.querySelectorAll('[data-quote-section]').forEach(button => {
        button.addEventListener('click', () => openSection(button.dataset.quoteSection));
    });
    modal.querySelectorAll('[data-quote-close]').forEach(button => button.addEventListener('click', closeModal));
    document.addEventListener('keydown', event => {
        if (modal.hidden) return;
        if (event.key === 'Escape') closeModal();
        if (event.key === 'Tab') {
            const focusables = [...dialog.querySelectorAll('button:not([disabled]), input:not([disabled]), a[href], select, textarea')].filter(el => !el.closest('[hidden]') && el.getClientRects().length);
            if (!focusables.length) return;
            const first = focusables[0], last = focusables[focusables.length - 1];
            if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last.focus(); }
            else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first.focus(); }
        }
    });
    form.querySelectorAll('input[name="tipo_cliente"], input[name="metodo_entrega"]').forEach(input => input.addEventListener('change', conditions));
    
    // funciona para guardar cambios de registro 

    save.addEventListener('click', async () => {

        if (!validate(active)) return;

        // Guardar método de pago, entrega y facturación en WordPress.
        if (active !== 'perfil') {

            if (!document.body.classList.contains('logged-in')) {
                message('Debes iniciar sesión para guardar estos datos.');
                return;
            }

            if (
                typeof pythonCotizaciones === 'undefined' ||
                !pythonCotizaciones.ajaxUrl ||
                !pythonCotizaciones.perfilNonce
            ) {
                message('No se pudo conectar con WordPress. Actualiza la página.');
                return;
            }

            const body = new URLSearchParams();

            body.append('action', 'python_guardar_datos_cotizacion_cliente');
            body.append('nonce', pythonCotizaciones.perfilNonce);
            body.append('seccion', active);

            if (active === 'pago') {
                body.append('metodo_pago', choice('metodo_pago'));
            }

            if (active === 'entrega') {
                body.append('metodo_entrega', choice('metodo_entrega'));

                if (choice('metodo_entrega') === 'envio') {
                    ['provincia', 'ciudad', 'direccion', 'referencia'].forEach(name => {
                        body.append(name, val(name));
                    });
                }
            }

            if (active === 'facturacion') {
                body.append('tipo_cliente', choice('tipo_cliente'));

                if (choice('tipo_cliente') === 'natural') {
                    body.append('cedula', val('cedula'));
                } else {
                    ['ruc', 'razon_social', 'nombre_comercial'].forEach(name => {
                        body.append(name, val(name));
                    });
                }
            }

            save.disabled = true;

            const textoOriginal = save.textContent;
            save.textContent = 'Guardando...';

            message('');

            try {
                const response = await fetch(pythonCotizaciones.ajaxUrl, {
                    method: 'POST',
                    credentials: 'same-origin',
                    body
                });

                const result = await response.json();

                if (!response.ok || !result.success) {
                    message(
                        result.data?.mensaje ||
                        'No fue posible guardar los datos.'
                    );
                    return;
                }

                completed[active] = true;
                updateSidebar();
                closeModal();

            } catch (error) {
                message('Error de conexión. Inténtalo nuevamente.');

            } finally {
                save.disabled = false;
                save.textContent = textoOriginal;
            }

            return;
        }

        // Los invitados pueden completar sus datos temporalmente.
        // La cuenta se verificará mediante el enlace por correo.
        if (!document.body.classList.contains('logged-in')) {
            completed.perfil = true;
            updateSidebar();
            closeModal();
            return;
        }

        if (
            typeof pythonCotizaciones === 'undefined' ||
            !pythonCotizaciones.perfilAction ||
            !pythonCotizaciones.perfilNonce
        ) {
            message('No se pudo conectar con WordPress. Actualiza la página.');
            return;
        }

        const emailCuenta = document.querySelector(
            '.python-quote-profile__identity small'
        )?.textContent?.trim();

        // El correo de una cuenta verificada no puede cambiarse aquí.
        if (
            emailCuenta &&
            val('email').toLowerCase() !== emailCuenta.toLowerCase()
        ) {
            message('Para cambiar tu correo electrónico necesitaremos verificar la nueva dirección.');
            return;
        }

        save.disabled = true;
        const textoOriginal = save.textContent;
        save.textContent = 'Guardando...';
        message('');

        const body = new URLSearchParams();

        body.append('action', pythonCotizaciones.perfilAction);
        body.append('nonce', pythonCotizaciones.perfilNonce);
        body.append('nombres', val('nombres'));
        body.append('apellidos', val('apellidos'));
        body.append('telefono', val('telefono'));

        try {
            const response = await fetch(pythonCotizaciones.ajaxUrl, {
                method: 'POST',
                credentials: 'same-origin',
                body
            });

            const result = await response.json();

            if (!response.ok || !result.success) {
                message(
                    result.data?.mensaje ||
                    'No fue posible guardar tu perfil.'
                );
                return;
            }

            completed.perfil = true;
            updateSidebar();
            closeModal();

        } catch (error) {
            message('Error de conexión. Inténtalo nuevamente.');
        } finally {
            save.disabled = false;
            save.textContent = textoOriginal;
        }
    });


  quoteButton.addEventListener('click', () => {
    for (const name of ['perfil', 'pago', 'entrega', 'facturacion']) {
      if (!completed[name] || !validate(name)) {
            openSection(name);
            if (completed[name]) validate(name);
            else message(`Completa la sección «${sectionNames[name]}» antes de solicitar tu cotización.`);
            return;
       }
    }
    // La solicitud todavía no se envía ni se guarda en WordPress.
    openSection('facturacion');
    message('Tus datos están completos. El envío de cotizaciones se habilitará cuando conectemos WordPress.');
  });
  form.addEventListener('submit', event => event.preventDefault());
    // Recuperar los datos guardados del cliente.
    if (choice('metodo_pago')) {
        completed.pago = true;
    }

    if (choice('metodo_entrega') === 'retiro') {
        completed.entrega = true;
    }

    if (
        choice('metodo_entrega') === 'envio' &&
        val('provincia') &&
        val('ciudad') &&
        val('direccion')
    ) {
        completed.entrega = true;
    }

    // Recuperar los datos de facturación guardados.
    if (
        choice('tipo_cliente') === 'natural' &&
        /^[0-9]{10}$/.test(val('cedula'))
    ) {
        completed.facturacion = true;
    }

    if (
        choice('tipo_cliente') === 'empresa' &&
        /^[0-9]{13}$/.test(val('ruc')) &&
        val('razon_social')
    ) {
        completed.facturacion = true;
    }

    conditions();
    updateSidebar();


  // Python Print — Solicitar acceso por correo.
  const accessButton = document.getElementById('python-request-access');
  const accessMessage = document.getElementById('python-access-message');

  if (accessButton && accessMessage) {
    accessButton.addEventListener('click', async () => {

      const email = val('email');

      if (!email || !field('email').checkValidity()) {
        message('Primero ingresa un correo electrónico válido.');
        field('email').focus();
        return;
      }

      if (typeof pythonCotizaciones === 'undefined') {
        accessMessage.textContent =
          'No se pudo iniciar la solicitud. Actualiza la página.';
        accessMessage.hidden = false;
        return;
      }

      accessButton.disabled = true;
      accessButton.textContent = 'Enviando enlace...';
      accessMessage.hidden = true;

      const body = new URLSearchParams();
      body.append('action', pythonCotizaciones.accesoAction);
      body.append('nonce', pythonCotizaciones.accesoNonce);
      body.append('email', email);

      try {
        const response = await fetch(pythonCotizaciones.ajaxUrl, {
          method: 'POST',
          credentials: 'same-origin',
          body
        });

        const result = await response.json();

        accessMessage.textContent =
          result.data?.mensaje ||
          'No fue posible procesar la solicitud.';

        accessMessage.hidden = false;

      } catch (error) {
        accessMessage.textContent =
          'No se pudo conectar con el servidor. Inténtalo nuevamente.';
        accessMessage.hidden = false;
      } finally {
        accessButton.disabled = false;
        accessButton.textContent = 'Continuar con correo electrónico';
      }
    });
  }

});
