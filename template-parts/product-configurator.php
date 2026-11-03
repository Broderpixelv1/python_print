
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
            <!--configuracion del producto-->
                <div class="python-configurator__options">

                    <section class="python-configurator__section">

                        <h3 class="python-configurator__heading">
                            ¿Qué tipo de etiqueta necesitas?
                        </h3>

                        <p class="python-configurator__description">
                            Selecciona la información que llevará tu etiqueta.
                        </p>

                        <div class="python-configurator__choices">

                            <label class="python-configurator__choice">
                                <input type="radio" name="tipo_etiqueta" value="marca">
                                <span class="python-configurator__choice-content">
                                    <strong>Solo marca</strong>
                                    <small>Tu logotipo o nombre de marca.</small>
                                </span>
                            </label>

                            <label class="python-configurator__choice">
                                <input type="radio" name="tipo_etiqueta" value="marca_talla">
                                <span class="python-configurator__choice-content">
                                    <strong>Marca + talla</strong>
                                    <small>Marca y talla en una misma etiqueta.</small>
                                </span>
                            </label>

                            <label class="python-configurator__choice">
                                <input type="radio" name="tipo_etiqueta" value="talla">
                                <span class="python-configurator__choice-content">
                                    <strong>Solo talla</strong>
                                    <small>Etiquetas independientes de tallas.</small>
                                </span>
                            </label>

                        </div>

                        
                        <section class="python-configurator__section python-material-section" hidden>

                            <h3 class="python-configurator__heading">
                                ¿Qué material prefieres?
                            </h3>

                            <p class="python-configurator__description">
                                Selecciona el material de tus etiquetas.
                            </p>

                            <div class="python-configurator__choices python-material-options">
                            </div>

                        </section>

                        
                        <section class="python-configurator__section python-width-section" hidden>

                            <h3 class="python-configurator__heading">
                                ¿Qué ancho necesitas?
                            </h3>

                            <p class="python-configurator__description">
                                Selecciona el ancho de tu etiqueta en milimetros.
                            </p>

                            <div class="python-width-options">
                                <select
                                    id="python-ancho"
                                    name="python_ancho"
                                    class="python-configurator__select"
                                >
                                    <option value="">Selecciona un ancho</option>
                                </select>
                            </div>

                        </section>


                    </section>

                </div>
            <!--fin configuracion del producto-->
            </div>

        </div>

    </div>

</div>
