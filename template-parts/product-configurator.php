
<?php
/**
 * Popup configurador de productos
 */

defined('ABSPATH') || exit;
?>

<div id="python-configurator" class="python-configurator" aria-hidden="true">

    <div class="python-configurator__overlay"></div>

    <div class="python-configurator__dialog"
         role="dialog"
         aria-modal="true"
         aria-labelledby="python-configurator-title">

        <button type="button"
                class="python-configurator__close"
                aria-label="Cerrar configurador">
            &times;
        </button>

        <div class="python-configurator__layout">

            <!-- Columna izquierda: fotografía -->
            <div class="python-configurator__gallery">
                <img
                    class="python-configurator__image"
                    src=""
                    alt=""
                >
            </div>

            <!-- Columna derecha: configuración -->
            <div class="python-configurator__content">

                <h2 id="python-configurator-title"
                    class="python-configurator__title">
                    Configurar producto
                </h2>

                <div class="python-configurator__options">
                    <!-- Aquí incorporaremos las opciones -->
                </div>

            </div>

        </div>

    </div>

</div>
