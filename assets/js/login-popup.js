
document.addEventListener('DOMContentLoaded', () => {

    const modal = document.getElementById('python-login-modal');
    if (!modal) return;

    const dialog = modal.querySelector('.python-login-modal__dialog');
    const form = document.getElementById('python-login-form');
    const email = document.getElementById('python-login-email');
    const message = document.getElementById('python-login-message');
    const submit = form.querySelector('button[type="submit"]');

    let previousFocus = null;

    function showMessage(text, isError = false) {
        message.textContent = text;
        message.hidden = !text;
        message.style.color = isError ? '#c62828' : '#287a45';
    }

    function openModal(event) {
        if (event) event.preventDefault();

        previousFocus = document.activeElement;
        modal.hidden = false;
        document.body.style.overflow = 'hidden';

        showMessage('');
        email.value = '';

        email.focus();
    }

    function closeModal() {
        modal.hidden = true;
        document.body.style.overflow = '';
        showMessage('');

        if (previousFocus && previousFocus.isConnected) {
            previousFocus.focus();
        }
    }

    document.querySelectorAll('[data-python-login-open]').forEach(button => {
        button.addEventListener('click', openModal);
    });

    modal.querySelectorAll('[data-python-login-close]').forEach(button => {
        button.addEventListener('click', closeModal);
    });

    document.addEventListener('keydown', event => {
        if (modal.hidden) return;

        if (event.key === 'Escape') {
            closeModal();
            return;
        }

        if (event.key === 'Tab') {
            const focusables = [...dialog.querySelectorAll(
                'button:not([disabled]), input:not([disabled]), a[href]'
            )].filter(el => el.getClientRects().length);

            if (!focusables.length) return;

            const first = focusables[0];
            const last = focusables[focusables.length - 1];

            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault();
                last.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault();
                first.focus();
            }
        }
    });

    form.addEventListener('submit', async event => {
        event.preventDefault();

        if (!form.reportValidity()) return;

        if (
            typeof pythonLogin === 'undefined' ||
            !pythonLogin.ajaxUrl ||
            !pythonLogin.nonce
        ) {
            showMessage('No se pudo conectar con WordPress. Actualiza la página.', true);
            return;
        }

        submit.disabled = true;
        submit.textContent = 'Enviando enlace...';
        showMessage('');

        const body = new URLSearchParams();
        body.append('action', 'python_solicitar_acceso');
        body.append('nonce', pythonLogin.nonce);
        body.append('email', email.value.trim());
        body.append('redirect_to', window.location.href);

        try {
            const response = await fetch(pythonLogin.ajaxUrl, {
                method: 'POST',
                credentials: 'same-origin',
                body
            });

            const result = await response.json();

            if (!response.ok || !result.success) {
                showMessage(
                    result.data?.mensaje || 'No se pudo enviar el enlace.',
                    true
                );
                return;
            }

            showMessage(
                result.data?.mensaje ||
                'Si es posible procesar la solicitud, recibirás un enlace en tu correo.'
            );

        } catch (error) {
            showMessage(
                'No se pudo conectar con el servidor. Inténtalo nuevamente.',
                true
            );
        } finally {
            submit.disabled = false;
            submit.textContent = 'Continuar con correo';
        }
    });

});
