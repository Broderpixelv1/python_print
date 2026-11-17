
<?php
/**
 * Python Print — Popup de acceso para clientes.
 */
defined('ABSPATH') || exit;

if (is_user_logged_in()) {
    return;
}
?>

<div
    id="python-login-modal"
    class="python-login-modal"
    role="presentation"
    hidden
>
    <div
        class="python-login-modal__backdrop"
        data-python-login-close
    ></div>

    <div
        class="python-login-modal__dialog"
        role="dialog"
        aria-modal="true"
        aria-labelledby="python-login-title"
        tabindex="-1"
    >
        <button
            type="button"
            class="python-login-modal__close"
            data-python-login-close
            aria-label="Cerrar"
        >
            &times;
        </button>

        <div class="python-login-modal__brand">
            Python<span>Print</span>
        </div>

        <h2 id="python-login-title">
            ¡Hola de nuevo!
        </h2>

        <p>
            Ingresa o crea tu cuenta para gestionar
            tus pedidos y cotizaciones.
        </p>

        <form id="python-login-form">

            <label for="python-login-email">
                Correo electrónico
            </label>

            <input
                type="email"
                id="python-login-email"
                name="email"
                placeholder="tu@correo.com"
                autocomplete="email"
                required
            >

            <button
                type="submit"
                class="python-login-modal__submit"
            >
                Continuar con correo
            </button>

            <p
                id="python-login-message"
                class="python-login-modal__message"
                role="status"
                aria-live="polite"
                hidden
            ></p>

        </form>

        <div class="python-login-modal__footer">
            <span>🔒</span>
            Acceso seguro sin contraseña
        </div>
    </div>
</div>
