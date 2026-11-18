
<?php
/** Python Print — Editor de datos para cotización (interfaz; no envía solicitudes). */
defined('ABSPATH') || exit;
$pp_user = wp_get_current_user();
$pp_logged = is_user_logged_in();
$pp_privacy = get_privacy_policy_url();
$pp_terms_id = function_exists('wc_get_page_id') ? wc_get_page_id('terms') : -1;
$pp_terms = $pp_terms_id > 0 ? get_permalink($pp_terms_id) : '';
$pp_metodo_pago = $pp_logged
    ? get_user_meta($pp_user->ID, 'python_metodo_pago', true)
    : '';

$pp_metodo_entrega = $pp_logged
    ? get_user_meta($pp_user->ID, 'python_metodo_entrega', true)
    : 'retiro';

$pp_referencia_entrega = $pp_logged
    ? get_user_meta($pp_user->ID, 'python_referencia_entrega', true)
    : '';

$pp_meta = static function($key) use ($pp_logged, $pp_user) {
    return $pp_logged ? (string) get_user_meta($pp_user->ID, $key, true) : '';
};
?>
<div id="python-quote-form" class="python-quote-modal" hidden>
  <div class="python-quote-modal__backdrop" data-quote-close></div>
  <section class="python-quote-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="python-quote-title" tabindex="-1">
    <header class="python-quote-modal__header">
      <div><small>PYTHON PRINT · MI PEDIDO</small><h2 id="python-quote-title">Completar datos</h2></div>
      <button type="button" class="python-quote-modal__close" data-quote-close aria-label="Cerrar ventana">×</button>
    </header>
    <form id="python-quote-request" novalidate>
      <div class="python-quote-modal__body">
        <section class="python-quote-modal__section" data-quote-panel="perfil" hidden>
          <h3>Mi perfil</h3><p>Estos datos se usarán para contactarte sobre la cotización.</p>
            <div class="python-access-box">
                <h4>Accede a tu cuenta</h4>
                <p>
                    Recibe un enlace seguro en tu correo para ingresar
                    a Python Print sin contraseña.
                </p>

                <button
                    type="button"
                    id="python-request-access"
                    class="python-access-button">
                    Continuar con correo electrónico
                </button>

                <p
                    id="python-access-message"
                    class="python-access-message"
                    role="status"
                    aria-live="polite"
                    hidden>
                </p>
            </div>
          <div class="python-quote-fields">
            <label>Nombres *<input name="nombres" autocomplete="given-name" maxlength="100" required value="<?php echo esc_attr($pp_logged ? $pp_user->first_name : ''); ?>"></label>
            <label>Apellidos *<input name="apellidos" autocomplete="family-name" maxlength="100" required value="<?php echo esc_attr($pp_logged ? $pp_user->last_name : ''); ?>"></label>
            <label class="python-quote-full">Correo electrónico *<input type="email" name="email" autocomplete="email" maxlength="190" required value="<?php echo esc_attr($pp_logged ? $pp_user->user_email : ''); ?>"></label>
            <label class="python-quote-full">WhatsApp / Teléfono *<input type="tel" name="telefono" autocomplete="tel" maxlength="25" required value="<?php echo esc_attr($pp_meta('billing_phone')); ?>"></label>
          </div>
        </section>
        <section class="python-quote-modal__section" data-quote-panel="pago" hidden>
          <h3>Método de pago preferido</h3><p>Solo es una preferencia; no se realizará ningún cobro ahora.</p>
          <div class="python-quote-options">
            <label>
                <input
                    type="radio"
                    name="metodo_pago"
                    value="tarjeta"
                    <?php checked($pp_metodo_pago, 'tarjeta'); ?>
                >
                Tarjeta de crédito o débito
            </label>

            <label>
                <input
                    type="radio"
                    name="metodo_pago"
                    value="transferencia"
                    <?php checked($pp_metodo_pago, 'transferencia'); ?>
                >
                Transferencia bancaria
            </label>
          </div>
        </section>
        <section class="python-quote-modal__section" data-quote-panel="entrega" hidden>
          <h3>Envío o entrega</h3>
          <div class="python-quote-options">
            
            <label>
                <input
                    type="radio"
                    name="metodo_entrega"
                    value="retiro"
                    <?php checked($pp_metodo_entrega, 'retiro'); ?>
                >
                Retiro en Python Print
            </label>

            <label>
                <input
                    type="radio"
                    name="metodo_entrega"
                    value="envio"
                    <?php checked($pp_metodo_entrega, 'envio'); ?>
                >
                Envío a domicilio
            </label>
          </div>
          <div class="python-quote-fields python-quote-shipping" hidden>
            <label>Provincia *<input name="provincia" maxlength="100" value="<?php echo esc_attr($pp_meta('shipping_state')); ?>"></label>
            <label>Ciudad *<input name="ciudad" maxlength="100" value="<?php echo esc_attr($pp_meta('shipping_city')); ?>"></label>
            <label class="python-quote-full">Dirección de entrega *<input name="direccion" autocomplete="street-address" maxlength="250" value="<?php echo esc_attr($pp_meta('shipping_address_1')); ?>"></label>
            <label class="python-quote-full">Referencia<input name="referencia" maxlength="250" value="<?php echo esc_attr($pp_referencia_entrega); ?>"></label>
          </div>
          <p class="python-quote-help">El costo y plazo de envío se confirmarán en la proforma.</p>
        </section>
        <section class="python-quote-modal__section" data-quote-panel="facturacion" hidden>
          <h3>Datos de facturación</h3>
          <div class="python-quote-options">
            <label><input type="radio" name="tipo_cliente" value="natural" checked> Persona natural</label>
            <label><input type="radio" name="tipo_cliente" value="empresa"> Empresa</label>
          </div>
          <div class="python-quote-fields python-quote-natural">
            <label class="python-quote-full">Número de cédula *<input name="cedula" inputmode="numeric" pattern="[0-9]{10}" maxlength="10" title="Ingresa 10 dígitos"></label>
          </div>
          <div class="python-quote-fields python-quote-company" hidden>
            <label class="python-quote-full">Razón social *<input name="razon_social" maxlength="150"></label>
            <label>Nombre comercial<input name="nombre_comercial" maxlength="150"></label>
            <label>RUC *<input name="ruc" inputmode="numeric" pattern="[0-9]{13}" maxlength="13" title="Ingresa 13 dígitos"></label>
          </div>
          <div class="python-quote-consents">
            <label><input type="checkbox" name="privacidad"> <span>He leído la <?php if ($pp_privacy) : ?><a href="<?php echo esc_url($pp_privacy); ?>" target="_blank" rel="noopener">política de privacidad</a><?php else : ?>política de privacidad<?php endif; ?> y autorizo el tratamiento de mis datos para gestionar esta solicitud. *</span></label>
            <label><input type="checkbox" name="terminos"> <span>Acepto los <?php if ($pp_terms) : ?><a href="<?php echo esc_url($pp_terms); ?>" target="_blank" rel="noopener">términos y condiciones</a><?php else : ?>términos y condiciones<?php endif; ?> del servicio. *</span></label>
          </div>
          <?php if (!$pp_privacy || !$pp_terms) : ?><p class="python-quote-help">Configura las páginas de privacidad y términos antes de activar las solicitudes.</p><?php endif; ?>
        </section>
        <p id="python-quote-error" class="python-quote-error" role="alert" hidden></p>
      </div>
      <footer class="python-quote-modal__footer">
        <button type="button" class="python-quote-cancel" data-quote-close>Cancelar</button>
        <button type="button" id="python-quote-save" class="python-quote-save">Guardar cambios</button>
      </footer>
    </form>
  </section>
</div>
