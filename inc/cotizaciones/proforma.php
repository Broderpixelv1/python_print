<?php
/**
 * Python Print — Panel de edición de proformas.
 */

defined('ABSPATH') || exit;

function python_agregar_panel_proforma() {

    add_meta_box(
        'python_proforma',
        'Preparar proforma · Python Print',
        'python_mostrar_panel_proforma',
        'python_cotizacion',
        'normal',
        'high'
    );
}

add_action('add_meta_boxes', 'python_agregar_panel_proforma');


function python_mostrar_panel_proforma($post) {
    // Recuperar los datos guardados de la proforma.
    $proforma = get_post_meta(
        $post->ID,
        '_python_proforma',
        true
    );

    $proforma = is_array($proforma) ? $proforma : array();
    // Campo de seguridad para guardar la proforma.
    wp_nonce_field(
        'python_guardar_proforma',
        'python_proforma_nonce'
    );

    $productos = get_post_meta(
        $post->ID,
        '_python_productos',
        true
    );

    $productos = is_array($productos) ? $productos : array();

    ?>

    <div class="python-proforma">

        <header class="python-proforma__header">
            <div>
                <span>PYTHON PRINT · PROFORMA</span>
                <h2><?php echo esc_html($post->post_title); ?></h2>
            </div>

            <span class="python-proforma__status">
                En preparación
            </span>
        </header>

        <h3>Productos y combinaciones</h3>

        <div class="python-proforma__products">

            <?php foreach ($productos as $indice => $producto) : ?>

                <div class="python-proforma__product">

                    <div>
                        <strong>
                            <?php echo esc_html(
                                $producto['nombre'] ?? 'Producto'
                            ); ?>
                        </strong>
                        <?php
                            $config = isset($producto['configuracion'])
                                && is_array($producto['configuracion'])
                                ? $producto['configuracion']
                                : array();

                            $campos_producto = array(
                                'tipo'     => 'Tipo',
                                'material' => 'Material',
                                'ancho'    => 'Ancho',
                                'longitud' => 'Largo',
                            );
                            ?>

                            <div class="python-proforma__specs">

                                <?php foreach ($campos_producto as $clave => $etiqueta) : ?>

                                    <?php if (
                                        isset($config[$clave]) &&
                                        is_scalar($config[$clave]) &&
                                        (string) $config[$clave] !== ''
                                    ) : ?>

                                        <p>
                                            <strong><?php echo esc_html($etiqueta); ?>:</strong>
                                            <?php
                                                $valor = $config[$clave];

                                                if (
                                                    in_array($clave, array('ancho', 'longitud'), true)
                                                    && is_numeric($valor)
                                                ) {
                                                    // Las medidas del configurador están expresadas en milímetros.
                                                    $valor = number_format_i18n((float) $valor / 10, 1);
                                                }

                                                echo esc_html((string) $valor);
                                                ?>
                                            <?php echo in_array($clave, array('ancho', 'longitud'), true) ? ' cm' : ''; ?>
                                        </p>

                                    <?php endif; ?>

                                <?php endforeach; ?>

                                <?php if (!empty($config['tallas']) && is_array($config['tallas'])) : ?>

                                    <p><strong>Distribución por tallas:</strong></p>

                                    <ul>
                                        <?php foreach ($config['tallas'] as $fila) : ?>
                                            <?php if (!is_array($fila)) continue; ?>

                                            <li>
                                                <?php echo esc_html((string) ($fila['talla'] ?? 'Sin talla')); ?>:
                                                <?php echo esc_html((string) ($fila['cantidad'] ?? 0)); ?> unidades
                                            </li>

                                        <?php endforeach; ?>
                                    </ul>

                                <?php endif; ?>

                            </div>

                        <?php
                            $config = $producto['configuracion'] ?? array();

                            $cantidad_real = (
                                is_array($config) &&
                                isset($config['cantidad'])
                            )
                                ? (int) $config['cantidad']
                                : (int) ($producto['cantidad'] ?? 0);
                            ?>

                            <p>
                                Cantidad:
                                <strong>
                                    <?php echo esc_html($cantidad_real); ?> unidades
                                </strong>
                            </p>
                    </div>

                    <label>
                        Precio total ($)
                        <input
                            type="number"
                            name="python_precios[<?php echo esc_attr($indice); ?>]"
                            class="python-proforma__price"
                            data-index="<?php echo esc_attr($indice); ?>"
                            min="0"
                            step="0.01"
                            value="<?php echo esc_attr(
                                $proforma['precios'][$indice] ?? 0
                            ); ?>"
                        >
                    </label>

                </div>

            <?php endforeach; ?>

        </div>

        <div class="python-proforma__section">

            <h3>Descuento general</h3>

            <label>
                Código de cupón
                <input
                    type="text"
                    name="python_cupon"
                    id="python-proforma-cupon"
                    placeholder="Código promocional"
                    value="<?php echo esc_attr($proforma['cupon'] ?? ''); ?>"
                >
            </label>

            <div class="python-proforma__grid">

                <label>
                    Tipo de descuento
                    <select
                        id="python-proforma-descuento-tipo"
                        name="python_descuento_tipo"
                    >
                        <option
                            value="porcentaje"
                            <?php selected(
                                $proforma['descuento_tipo'] ?? 'porcentaje',
                                'porcentaje'
                            ); ?>
                        >
                            Porcentaje (%)
                        </option>

                        <option
                            value="fijo"
                            <?php selected(
                                $proforma['descuento_tipo'] ?? 'porcentaje',
                                'fijo'
                            ); ?>
                        >
                            Valor fijo ($)
                        </option>
                    </select>
                </label>

                <label>
                    Descuento
                    <input
                        type="number"
                        name="python_descuento"
                        id="python-proforma-descuento"
                        min="0"
                        step="0.01"
                        value="<?php echo esc_attr($proforma['descuento'] ?? 0)?>"
                    >
                </label>

            </div>

        </div>

        <div class="python-proforma__section">

            <h3>Impuestos y entrega</h3>

            <label>
                Aplicación del IVA
                <select id="python-proforma-iva" name="python_tipo_iva">
                    <option
                        value="sin"
                        <?php selected(
                            $proforma['iva'] ?? 'sin',
                            'sin'
                        ); ?>
                    >Sin IVA</option>
                    <option
                        value="con"
                        <?php selected(
                            $proforma['iva'] ?? 'sin',
                            'con'
                        ); ?>
                    >Con IVA</option>
                </select>
            </label>

            <label>
                Costo de envío ($)
                <input
                    type="number"
                    name="python_envio"
                    id="python-proforma-envio"
                    min="0"
                    step="0.01"
                    value="<?php echo esc_attr($proforma['envio'] ?? 0); ?>"
                >
            </label>

        </div>

        <div class="python-proforma__summary">

            <div>
                <span>Subtotal</span>
                <strong id="python-proforma-subtotal">$0.00</strong>
            </div>

            <div>
                <span>Descuento</span>
                <strong id="python-proforma-descuento-total">$0.00</strong>
            </div>

            <div>
                <span>IVA</span>
                <strong id="python-proforma-iva-total">$0.00</strong>
            </div>

            <div>
                <span>Envío</span>
                <strong id="python-proforma-envio-total">$0.00</strong>
            </div>

            <div class="python-proforma__total">
                <span>Total de la proforma</span>
                <strong id="python-proforma-total">$0.00</strong>
            </div>

        </div>

        <div class="python-proforma__section">

            <h3>Observaciones</h3>

            <textarea
                id="python-proforma-observaciones"
                name="python_observaciones"
                rows="4"
                placeholder="Tiempo de producción, condiciones comerciales..."
            ><?php echo esc_textarea($proforma['observaciones'] ?? ''); ?>
            </textarea>

        </div>

        <p class="description">
            Esta es la estructura inicial del editor.
            Los cálculos y el guardado se conectarán en los siguientes pasos.
        </p>

    </div>

    <?php
}

/**
 * Estilos del editor de proformas.
 */
function python_cargar_estilos_admin_proforma($hook) {

    if (!in_array($hook, array('post.php', 'post-new.php'), true)) {
        return;
    }

    $pantalla = get_current_screen();

    if (!$pantalla || $pantalla->post_type !== 'python_cotizacion') {
        return;
    }

    wp_enqueue_style(
        'python-admin-proforma',
        get_template_directory_uri() . '/assets/css/admin-proforma.css',
        array(),
        filemtime(
            get_template_directory() . '/assets/css/admin-proforma.css'
        )
    );

    wp_enqueue_script(
        'python-admin-proforma',
        get_template_directory_uri() . '/assets/js/admin-proforma.js',
        array(),
        filemtime(
            get_template_directory() . '/assets/js/admin-proforma.js'
        ),
        true
    );
}

add_action('admin_enqueue_scripts', 'python_cargar_estilos_admin_proforma');

/**
 * Guardar los datos comerciales de la proforma.
 */
function python_guardar_proforma($post_id, $post, $update) {

    if (
        !isset($_POST['python_proforma_nonce']) ||
        !wp_verify_nonce(
            sanitize_text_field(wp_unslash($_POST['python_proforma_nonce'])),
            'python_guardar_proforma'
        )
    ) {
        return;
    }

    if (
        (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) ||
        wp_is_post_revision($post_id) ||
        !current_user_can('edit_post', $post_id)
    ) {
        return;
    }

    $productos = get_post_meta(
        $post_id,
        '_python_productos',
        true
    );

    $productos = is_array($productos) ? $productos : array();

    $precios = isset($_POST['python_precios'])
        && is_array($_POST['python_precios'])
        ? wp_unslash($_POST['python_precios'])
        : array();

    $precios_limpios = array();

    foreach ($productos as $indice => $producto) {

        $valor = $precios[$indice] ?? 0;

        $precios_limpios[$indice] = max(
            0,
            (float) wc_format_decimal($valor, 2)
        );
    }

    $tipo_descuento = sanitize_text_field(
        wp_unslash($_POST['python_descuento_tipo'] ?? 'porcentaje')
    );

    if (!in_array($tipo_descuento, array('porcentaje', 'fijo'), true)) {
        $tipo_descuento = 'porcentaje';
    }

    $tipo_iva = sanitize_text_field(
        wp_unslash($_POST['python_tipo_iva'] ?? 'sin')
    );

    if (!in_array($tipo_iva, array('sin', 'con'), true)) {
        $tipo_iva = 'sin';
    }

    $descuento = max(
        0,
        (float) wc_format_decimal(
            wp_unslash($_POST['python_descuento'] ?? 0),
            2
        )
    );

    $envio = max(
        0,
        (float) wc_format_decimal(
            wp_unslash($_POST['python_envio'] ?? 0),
            2
        )
    );

    $datos = array(
        'precios' => $precios_limpios,

        'cupon' => sanitize_text_field(
            wp_unslash($_POST['python_cupon'] ?? '')
        ),

        'descuento_tipo' => $tipo_descuento,
        'descuento'      => $descuento,
        'iva'            => $tipo_iva,
        'envio'          => $envio,

        'observaciones' => sanitize_textarea_field(
            wp_unslash($_POST['python_observaciones'] ?? '')
        ),
    );

    update_post_meta(
        $post_id,
        '_python_proforma',
        $datos
    );
}

add_action(
    'save_post_python_cotizacion',
    'python_guardar_proforma',
    10,
    3
);