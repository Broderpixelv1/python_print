<?php
/**
 * Python Print — Edición de datos personales.
 */

defined('ABSPATH') || exit;

do_action('woocommerce_before_edit_account_form');
?>

<section class="python-account-profile">

    <header class="python-account-dashboard__header">

        <h1>Mis datos</h1>

        <p>
            Administra tu información personal
            y los datos de acceso a tu cuenta.
        </p>

    </header>

    <form
        class="woocommerce-EditAccountForm edit-account"
        action=""
        method="post"
        <?php do_action('woocommerce_edit_account_form_tag'); ?>
    >

        <?php do_action('woocommerce_edit_account_form_start'); ?>

        <div class="python-profile-grid">

            <div class="python-profile-field">

                <label for="account_first_name">
                    Nombre <span class="required">*</span>
                </label>

                <input
                    type="text"
                    name="account_first_name"
                    id="account_first_name"
                    autocomplete="given-name"
                    value="<?php echo esc_attr($user->first_name); ?>"
                    required
                >

            </div>

            <div class="python-profile-field">

                <label for="account_last_name">
                    Apellidos <span class="required">*</span>
                </label>

                <input
                    type="text"
                    name="account_last_name"
                    id="account_last_name"
                    autocomplete="family-name"
                    value="<?php echo esc_attr($user->last_name); ?>"
                    required
                >

            </div>

            <div class="python-profile-field python-profile-field--full">

                <label for="account_display_name">
                    Nombre visible <span class="required">*</span>
                </label>

                <input
                    type="text"
                    name="account_display_name"
                    id="account_display_name"
                    value="<?php echo esc_attr($user->display_name); ?>"
                    required
                >

                <small>
                    Así aparecerá tu nombre dentro de tu cuenta.
                </small>

            </div>

            <div class="python-profile-field python-profile-field--full">

                <label for="account_email">
                    Correo electrónico
                </label>

                <input
                    type="email"
                    id="account_email"
                    value="<?php echo esc_attr($user->user_email); ?>"
                    readonly
                >

                <small>
                    Este correo se utiliza para acceder a tu cuenta.
                    Si necesitas cambiarlo, contacta con nosotros.
                </small>

            </div>

        </div>

        <div class="python-profile-security">

            <strong>Acceso a tu cuenta</strong>

            <p>
                No necesitas recordar una contraseña.
                Puedes acceder mediante un enlace
                enviado a tu correo electrónico.
            </p>

        </div>

        <?php do_action('woocommerce_edit_account_form'); ?>

        <?php wp_nonce_field(
            'save_account_details',
            'save-account-details-nonce'
        ); ?>

        <input
            type="hidden"
            name="action"
            value="save_account_details"
        >

        <button
            type="submit"
            class="python-profile-save"
            name="save_account_details"
            value="Guardar cambios"
        >
            Guardar cambios
        </button>

        <?php do_action('woocommerce_edit_account_form_end'); ?>

    </form>

</section>

<?php do_action('woocommerce_after_edit_account_form'); ?>