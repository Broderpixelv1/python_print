
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
