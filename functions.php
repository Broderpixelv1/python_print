<?php
/**
 * Functions para el tema personalizable
 */

if (!defined('ABSPATH')) {
    exit; // Salir si se accede directamente
}

// Configuración básica del tema
function mi_tema_setup() {
    // Soporte para traducciones
    load_theme_textdomain('mi-tema-personalizable', get_template_directory() . '/languages');
    
    // Soporte para título SEO
    add_theme_support('title-tag');
    
    // Soporte para imágenes destacadas
    add_theme_support('post-thumbnails');
    
    // Soporte para menús
    register_nav_menus(array(
        'menu-principal' => __('Menú Principal', 'mi-tema-personalizable'),
        'menu-footer' => __('Menú Footer', 'mi-tema-personalizable')
    ));
    
    // Soporte para widgets
    add_theme_support('widgets');
    
    // Soporte para Elementor
    add_theme_support('elementor');
}
add_action('after_setup_theme', 'mi_tema_setup');

// Estilos y scripts
function mi_tema_scripts() {
    // Estilo principal
    wp_enqueue_style('mi-tema-style', get_stylesheet_uri());
    
    // Estilos adicionales
    wp_enqueue_style('mi-tema-main', get_template_directory_uri() . '/assets/css/main.css');
    
    // Scripts
    wp_enqueue_script('mi-tema-main-js', get_template_directory_uri() . '/assets/js/main.js', array('jquery'), '1.0', true);
}
add_action('wp_enqueue_scripts', 'mi_tema_scripts');

// Soporte para Elementor Full Width
function mi_tema_add_elementor_support() {
    add_theme_support('elementor-page-settings');
}
add_action('after_setup_theme', 'mi_tema_add_elementor_support');

// Registrar áreas de widgets
function mi_tema_widgets_init() {
    register_sidebar(array(
        'name'          => __('Sidebar Principal', 'mi-tema-personalizable'),
        'id'            => 'sidebar-1',
        'description'   => __('Widgets en la barra lateral principal', 'mi-tema-personalizable'),
        'before_widget' => '<section id="%1$s" class="widget %2$s">',
        'after_widget'  => '</section>',
        'before_title'  => '<h2 class="widget-title">',
        'after_title'   => '</h2>',
    ));
    
    register_sidebar(array(
        'name'          => __('Footer Widgets', 'mi-tema-personalizable'),
        'id'            => 'footer-widgets',
        'description'   => __('Widgets en el pie de página', 'mi-tema-personalizable'),
        'before_widget' => '<div id="%1$s" class="footer-widget %2$s col-md-4">',
        'after_widget'  => '</div>',
        'before_title'  => '<h4 class="footer-widget-title">',
        'after_title'   => '</h4>',
    ));
}
add_action('widgets_init', 'mi_tema_widgets_init');

// Permitir SVG
function mi_tema_mime_types($mimes) {
    $mimes['svg'] = 'image/svg+xml';
    return $mimes;
}
add_filter('upload_mimes', 'mi_tema_mime_types');

// Registrar el walker
function register_mega_menu() {
    register_nav_menus(array(
        'menu-principal' => __('Menú Principal', 'mi-tema')
    ));
}
add_action('after_setup_theme', 'register_mega_menu');

// Walker personalizado para el menú mega
class Mega_Menu_Walker extends Walker_Nav_Menu {
    public function start_el(&$output, $item, $depth = 0, $args = null, $id = 0) {
        $classes = empty($item->classes) ? array() : (array) $item->classes;
        $classes[] = 'menu-item-' . $item->ID;
        
        $class_names = join(' ', apply_filters('nav_menu_css_class', array_filter($classes), $item, $args));
        $class_names = $class_names ? ' class="' . esc_attr($class_names) . '"' : '';

        $id = apply_filters('nav_menu_item_id', 'menu-item-'. $item->ID, $item, $args);
        $id = $id ? ' id="' . esc_attr($id) . '"' : '';

        $output .= '<li' . $id . $class_names . '>';

        $atts = array();
        $atts['title']  = !empty($item->attr_title) ? $item->attr_title : '';
        $atts['target'] = !empty($item->target)     ? $item->target     : '';
        $atts['rel']    = !empty($item->xfn)        ? $item->xfn        : '';
        $atts['href']   = !empty($item->url)        ? $item->url        : '';
        $atts['class']  = 'nav-link';

        $atts = apply_filters('nav_menu_link_attributes', $atts, $item, $args, $depth);

        $attributes = '';
        foreach ($atts as $attr => $value) {
            if (!empty($value)) {
                $value = ('href' === $attr) ? esc_url($value) : esc_attr($value);
                $attributes .= ' ' . $attr . '="' . $value . '"';
            }
        }

        $item_output = $args->before;
        $item_output .= '<a'. $attributes .'>';
        $item_output .= $args->link_before . apply_filters('the_title', $item->title, $item->ID) . $args->link_after;
        $item_output .= '</a>';
        $item_output .= $args->after;

        $output .= apply_filters('walker_nav_menu_start_el', $item_output, $item, $depth, $args);
    }

    public function start_lvl(&$output, $depth = 0, $args = null) {
        if ($depth == 0) {
            $output .= '<div class="mega-menu-subcontainer"><div class="col-submenu"><ul class="sub-menu">';
        } else {
            $output .= '<ul class="sub-menu">';
        }
    }

    public function end_lvl(&$output, $depth = 0, $args = null) {
        if ($depth == 0) {
            $output .= '</ul></div><div class="col-minibanner"></div></div>';
        } else {
            $output .= '</ul>';
        }
    }
}

// Habilitar soporte para Header/Footer/Archive de Elementor
function mi_tema_soporte_elementor() {
    add_theme_support('elementor-menus');
    add_theme_support('elementor-theme-builder');
}
add_action('after_setup_theme', 'mi_tema_soporte_elementor');