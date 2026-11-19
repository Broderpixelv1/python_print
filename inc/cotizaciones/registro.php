
<?php
/**
 * Python Print — Registro de cotizaciones.
 */

defined('ABSPATH') || exit;

function python_registrar_cotizaciones() {

    register_post_type('python_cotizacion', array(
        'labels' => array(
            'name'          => 'Cotizaciones',
            'singular_name' => 'Cotización',
            'menu_name'     => 'Cotizaciones',
            'add_new_item'  => 'Nueva cotización',
            'edit_item'     => 'Editar cotización',
        ),
        'public'             => false,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'show_in_rest'       => false,
        'menu_icon'          => 'dashicons-media-spreadsheet',
        'supports'           => array('title'),
        'capability_type'    => 'post',
        'map_meta_cap'       => true,
        'capabilities'       => array(
            'create_posts' => 'do_not_allow',
        ),
    ));
}

add_action('init', 'python_registrar_cotizaciones');

/**
 * Python Print — Panel de detalles de cotización.
 */
function python_agregar_panel_cotizacion() {

    add_meta_box(
        'python_detalles_cotizacion',
        'Detalles de la solicitud',
        'python_mostrar_detalles_cotizacion',
        'python_cotizacion',
        'normal',
        'high'
    );
}

add_action('add_meta_boxes', 'python_agregar_panel_cotizacion');


function python_mostrar_detalles_cotizacion($post) {

    $datos = get_post_meta(
        $post->ID,
        '_python_datos_cliente',
        true
    );

    $productos = get_post_meta(
        $post->ID,
        '_python_productos',
        true
    );

    $estado = get_post_meta(
        $post->ID,
        '_python_estado',
        true
    );

    $datos = is_array($datos) ? $datos : array();
    $productos = is_array($productos) ? $productos : array();

    $campos = array(
        'nombres'          => 'Nombres',
        'apellidos'        => 'Apellidos',
        'email'            => 'Correo electrónico',
        'telefono'         => 'Teléfono',
        'metodo_pago'      => 'Método de pago',
        'metodo_entrega'   => 'Método de entrega',
        'provincia'        => 'Provincia',
        'ciudad'           => 'Ciudad',
        'direccion'        => 'Dirección',
        'referencia'       => 'Referencia',
        'tipo_cliente'     => 'Tipo de cliente',
        'cedula'           => 'Cédula',
        'razon_social'     => 'Razón social',
        'nombre_comercial' => 'Nombre comercial',
        'ruc'              => 'RUC',
    );

    echo '<h2>Estado de la solicitud</h2>';
    echo '<p><strong>' . esc_html(
        $estado === 'pendiente_revision'
            ? 'Pendiente de revisión'
            : ($estado ?: 'Sin estado')
    ) . '</strong></p>';

    echo '<h2>Datos del cliente</h2>';
    echo '<table class="widefat striped"><tbody>';

    foreach ($campos as $clave => $etiqueta) {

        $valor = $datos[$clave] ?? '';

        if ($valor === '' || !is_scalar($valor)) {
            continue;
        }

        echo '<tr>';
        echo '<th style="width:200px;">' . esc_html($etiqueta) . '</th>';
        echo '<td>' . esc_html((string) $valor) . '</td>';
        echo '</tr>';
    }

    echo '</tbody></table>';

    echo '<h2>Productos solicitados</h2>';

    if (empty($productos)) {
        echo '<p>No hay productos registrados.</p>';
        return;
    }

    foreach ($productos as $indice => $producto) {

        if (!is_array($producto)) {
            continue;
        }

        echo '<div style="margin:15px 0;padding:15px;border:1px solid #ddd;background:#fff;">';

        echo '<h3>' . esc_html(
            ($indice + 1) . '. ' . ($producto['nombre'] ?? 'Producto')
        ) . '</h3>';

        echo '<p><strong>Cantidad:</strong> ' .
            esc_html((string) ($producto['cantidad'] ?? 0)) .
            '</p>';

        $configuracion = $producto['configuracion'] ?? array();

        if (is_array($configuracion)) {

            echo '<table class="widefat striped"><tbody>';

            foreach ($configuracion as $clave => $valor) {

                if (is_array($valor)) {
                    $valor = wp_json_encode(
                        $valor,
                        JSON_UNESCAPED_UNICODE
                    );
                }

                if (!is_scalar($valor)) {
                    continue;
                }

                echo '<tr>';
                echo '<th style="width:200px;">' .
                    esc_html(ucfirst(str_replace('_', ' ', $clave))) .
                    '</th>';

                echo '<td>' . esc_html((string) $valor) . '</td>';
                echo '</tr>';
            }

            echo '</tbody></table>';
        }

        echo '</div>';
    }
}