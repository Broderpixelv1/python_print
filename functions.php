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
        'menu-footer' => __('Menú Footer', 'mi-tema-personalizable'),
        'menu-footer-help' => __('Menú help', 'mi-tema-personalizable'),
        'menu-footer-about' => __('Menú about', 'mi-tema-personalizable'),
        'menu-footer-policies' => __('Menú policies', 'mi-tema-personalizable')
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
    wp_enqueue_style('mi-tema-main-css', get_template_directory_uri() . '/assets/css/main.css',array(),'7.1.5');
    wp_enqueue_style('mi-tema-menu', get_template_directory_uri() . '/assets/css/menu.css');
    wp_enqueue_style('mi-tema-header', get_template_directory_uri() . '/assets/css/header.css');

    
// CSS del catálogo de productos
if (function_exists('is_product_category') && (is_product_category() || is_shop())) {

    wp_enqueue_style(
        'python-print-catalogo',
        get_template_directory_uri() . '/assets/css/catalogo.css',
        array(),
        filemtime(get_template_directory() . '/assets/css/catalogo.css')
    );

}
    
    // CSS exclusivo para las fichas de producto WooCommerce
    if (is_product()) {
        wp_enqueue_style(
            'python-single-product',
            get_template_directory_uri() . '/assets/css/single-product.css',
            array('mi-tema-main-css'),
            '1.0.0'
        );
    }

    // Scripts
    wp_enqueue_script('mi-tema-main-js', get_template_directory_uri() . '/assets/js/main.js', array('jquery'), '1.0', true);
    wp_enqueue_script('mi-tema-menu-js', get_template_directory_uri() . '/assets/js/menu-responsivo.js', array('jquery'), '1.1', true);
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

//habilitamos la pagina de productos de woocommerce
add_theme_support('woocommerce');

//vamos a crear un carrucel de categorias padre de woocommerce
function carrusel_categorias_shortcode() {
    ob_start();
    // Obtener categoría "Sin categoría" para excluirla por su ID
    $exclude_slugs = ['sin-categorizar', 'inspiracion'];
    $exclude_ids = [];

foreach ($exclude_slugs as $slug) {
    $term = get_term_by('slug', $slug, 'product_cat');
    if ($term) {
        $exclude_ids[] = $term->term_id;
    }
}

    $terms = get_terms([
        'taxonomy'   => 'product_cat',
        'hide_empty' => false,
        'parent' => intval($atts['parent']),
        'exclude' => $exclude_ids,
        
    ]);

if (!empty($terms) && !is_wp_error($terms)) {
    ?>
    <div class="swiper category-carousel">
        <div class="swiper-wrapper">
            <?php foreach ($terms as $term):
                $thumb_id = get_term_meta($term->term_id, 'thumbnail_id', true);
                $img_url = wp_get_attachment_url($thumb_id);
                $term_link = get_term_link($term); // Obtener URL de la categoría
            ?>
                <div class="swiper-slide">
                    <a href="<?php echo esc_url($term_link); ?>" class="category-card">
                        <img src="<?php echo esc_url($img_url); ?>" alt="<?php echo esc_attr($term->name); ?>">
                        <h4><?php echo esc_html($term->name); ?></h4>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="swiper-scrollbar"></div>
    </div>
    <?php
}


    return ob_get_clean();
}
add_shortcode('carrusel_categorias', 'carrusel_categorias_shortcode');


//encolamos swiper.js
function cargar_swiper() {
    wp_enqueue_style('swiper-css', 'https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.css');
    wp_enqueue_script('swiper-js', 'https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.js', [], null, true);
    wp_add_inline_script('swiper-js', "
    document.addEventListener('DOMContentLoaded', function () {
       new Swiper('.category-carousel', {
            slidesPerView: 6,           // Muestra solo 6 a la vez
            spaceBetween: 20,
            grabCursor: true,
            speed: 600,
            freeMode: false,            // Desactiva scroll libre (mejor con scroll normal)
            scrollbar: {
                el: '.swiper-scrollbar',
                draggable: true,
                hide: false,
                dragSize: 'auto'
            },
             preventClicks: false,
             preventClicksPropagation: false,
            breakpoints: {
                0:    { slidesPerView: 2 },
                480:  { slidesPerView: 3 },
                768:  { slidesPerView: 4 },
                1024: { slidesPerView: 6 }
            }
        });


    });
");

}
add_action('wp_enqueue_scripts', 'cargar_swiper');

//carrusel de subcategorias de productos con navegacion horizontal

function carrusel_subcategorias_shortcode($atts) {
    $atts = shortcode_atts([
        'parent' => 0,       // ID de categoría padre
        'visible' => 4       // Cantidad de subcategorías visibles
    ], $atts, 'carrusel_subcategorias');

    $terms = get_terms([
        'taxonomy' => 'product_cat',
        'hide_empty' => false,
        'parent' => intval($atts['parent']),
    ]);

    if (!empty($terms) && !is_wp_error($terms)) {
        ob_start();
        ?>
        <div class="swiper subcategory-carousel">
            <div class="swiper-wrapper">
                <?php foreach ($terms as $term):
                    $thumb_id = get_term_meta($term->term_id, 'thumbnail_id', true);
                    $img_url = wp_get_attachment_url($thumb_id);
                    $term_link = get_term_link($term);
                ?>
                    <div class="swiper-slide">
                        <a href="<?php echo esc_url($term_link); ?>" class="subcategory-card">
                            <img src="<?php echo esc_url($img_url); ?>" alt="<?php echo esc_attr($term->name); ?>">
                            <h4><?php echo esc_html($term->name); ?></h4>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Flechas de navegación -->
            <div class="swiper-button-prev"></div>
            <div class="swiper-button-next"></div>
        </div>

        <script>
        document.addEventListener('DOMContentLoaded', function () {
            new Swiper('.subcategory-carousel', {
                slidesPerView: <?php echo intval($atts['visible']); ?>,
                spaceBetween: 20,
                navigation: {
                    nextEl: '.swiper-button-next',
                    prevEl: '.swiper-button-prev',
                },
                speed: 500,
                grabCursor: true,
                breakpoints: {
                    0:    { slidesPerView: 1 },
                    480:  { slidesPerView: 2 },
                    768:  { slidesPerView: 3 },
                    1024: { slidesPerView: <?php echo intval($atts['visible']); ?> }
                }
            });
        });
        </script>
        <?php
        return ob_get_clean();
    }

    return '<p>No subcategories found.</p>';
}
add_shortcode('carrusel_subcategorias', 'carrusel_subcategorias_shortcode');


// =========================================
// PYTHON PRINT — INTEGRAR CONFIGURADOR
// =========================================

function python_print_cargar_configurador() {

    // Solo cargar en categorías y tienda WooCommerce, ahora en pagina mi pedido
    if (function_exists('is_product_category') &&
    (is_product_category() || is_shop() || is_page('mi-pedido'))) {

        // Insertar estructura del popup en el footer
        add_action('wp_footer', function() {
            get_template_part('template-parts/product-configurator');
        });

        // Cargar estilos
        wp_enqueue_style(
            'python-configurador',
            get_template_directory_uri() . '/assets/css/configurador.css',
            array(),
            '1.0.0'
        );

        // Cargar JavaScript
        wp_enqueue_script(
            'python-configurador',
            get_template_directory_uri() . '/assets/js/configurador.js',
            array(),
            '1.0.0',
            true
        );

        
        wp_localize_script(
            'python-configurador',
            'pythonPedido',
            array(
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce'   => wp_create_nonce('python_configurador'),
            )
        );

    }
}
add_action('wp_enqueue_scripts', 'python_print_cargar_configurador');

//creamos la seccion de configuracion del producto


/**
 * PYTHON PRINT
 * Opciones del configurador en WooCommerce
 */

// Mostrar opciones en la edición del producto
add_action('woocommerce_product_options_general_product_data', 'python_print_opciones_producto');

function python_print_opciones_producto() {

    echo '<div class="options_group">';

    echo '<p class="form-field"><strong>Configurador Python Print</strong></p>';

    woocommerce_wp_checkbox(array(
        'id'          => '_python_tipo_marca',
        'label'       => 'Solo marca',
        'description' => 'Permitir etiquetas únicamente con marca.',
    ));

    woocommerce_wp_checkbox(array(
        'id'          => '_python_tipo_marca_talla',
        'label'       => 'Marca + talla',
        'description' => 'Permitir etiquetas con marca y talla.',
    ));

    woocommerce_wp_checkbox(array(
        'id'          => '_python_tipo_talla',
        'label'       => 'Solo talla',
        'description' => 'Permitir etiquetas únicamente de talla.',
    ));

    echo '</div>';
}

// Guardar las opciones del producto
add_action('woocommerce_admin_process_product_object', 'python_print_guardar_opciones_producto');

function python_print_guardar_opciones_producto($product) {

    $campos = array(
        '_python_tipo_marca',
        '_python_tipo_marca_talla',
        '_python_tipo_talla',
    );

    foreach ($campos as $campo) {

        $valor = isset($_POST[$campo]) ? 'yes' : 'no';

        $product->update_meta_data($campo, $valor);
    }
}


/**
 * PYTHON PRINT
 * Anchos disponibles por material
 */

// Campo al crear un material
add_action('pa_material_add_form_fields', function () {
    ?>
    <div class="form-field">
        <label for="python_anchos">
            Anchos disponibles (cm)
        </label>

        <input type="text"
               name="python_anchos"
               id="python_anchos"
               placeholder="1.5, 2, 2.5, 3">

        <p>
            Separa los anchos con comas.
            Ejemplo: 1.5, 2, 2.5, 3
        </p>
    </div>
    <?php
});

// Campo al editar un material
add_action('pa_material_edit_form_fields', function ($term) {

    $anchos = get_term_meta(
        $term->term_id,
        'python_anchos',
        true
    );
    ?>
    <tr class="form-field">
        <th scope="row">
            <label for="python_anchos">
                Anchos disponibles (cm)
            </label>
        </th>

        <td>
            <input type="text"
                   name="python_anchos"
                   id="python_anchos"
                   value="<?php echo esc_attr($anchos); ?>"
                   placeholder="1.5, 2, 2.5, 3">

            <p class="description">
                Separa los anchos con comas.
            </p>
        </td>
    </tr>
    <?php
});

// Guardar anchos
function python_guardar_anchos_material($term_id) {

    if (!isset($_POST['python_anchos'])) {
        return;
    }

    if (!current_user_can('manage_woocommerce')) {
        return;
    }

    $anchos = sanitize_text_field(
        wp_unslash($_POST['python_anchos'])
    );

    update_term_meta(
        $term_id,
        'python_anchos',
        $anchos
    );
}

add_action('created_pa_material', 'python_guardar_anchos_material');
add_action('edited_pa_material', 'python_guardar_anchos_material');


/**
 * PYTHON PRINT
 * Guardar una combinación personalizada en el carrito.
 */
add_action('wp_ajax_python_agregar_combinacion', 'python_agregar_combinacion');
add_action('wp_ajax_nopriv_python_agregar_combinacion', 'python_agregar_combinacion');

function python_guardar_combinacion($modo = 'agregar') {

    check_ajax_referer('python_configurador', 'nonce');

    if (!function_exists('WC') || !WC()->cart) {
        wp_send_json_error(array(
            'message' => 'El carrito no está disponible.'
        ), 500);
    }
    
    /*
    * Asegurar que el carrito AJAX utiliza
    * una sesión persistente de WooCommerce.
    */
    if (WC()->session) {
        WC()->session->set_customer_session_cookie(true);

        
        // Identificar la combinación que se está editando.
        $cart_item_key_edicion = '';

        if ($modo === 'editar') {

            $cart_item_key_edicion = isset($_POST['cart_item_key'])
                ? wc_clean(wp_unslash($_POST['cart_item_key']))
                : '';

            if (
                !$cart_item_key_edicion ||
                !WC()->cart->get_cart_item($cart_item_key_edicion)
            ) {
                wp_send_json_error(array(
                    'message' => 'No encontramos la combinación que deseas editar.'
                ), 404);
            }
        }

    }
                
    $datos = isset($_POST['combinacion'])
        ? wp_unslash($_POST['combinacion'])
        : array();

    if (!is_array($datos)) {
        wp_send_json_error(array(
            'message' => 'Datos de configuración inválidos.'
        ), 400);
    }

    $product_id = isset($datos['product_id'])
        ? absint($datos['product_id'])
        : 0;

    $producto = wc_get_product($product_id);

    /*
    if (!$producto || !$producto->is_purchasable() || !$producto->is_in_stock()) {
        wp_send_json_error(array(
            'message' => 'Este producto no está disponible.'
        ), 400);
    }*/
    
    
    if (!$producto) {
        wp_send_json_error(array(
            'message' => 'El producto no existe. ID recibido: ' . $product_id
        ), 400);
    }

    
    if (!$producto->is_purchasable()) {

        wp_send_json_error(array(
            'message' => sprintf(
                'Diagnóstico: ID=%d | Tipo=%s | Estado=%s | Precio=%s | Marca=%s | Marca+talla=%s | Talla=%s',
                $product_id,
                $producto->get_type(),
                $producto->get_status(),
                $producto->get_price() === '' ? 'VACÍO' : $producto->get_price(),
                $producto->get_meta('_python_tipo_marca') ?: 'VACÍO',
                $producto->get_meta('_python_tipo_marca_talla') ?: 'VACÍO',
                $producto->get_meta('_python_tipo_talla') ?: 'VACÍO'
            )
        ), 400);
    }


    if (!$producto->is_in_stock()) {
        wp_send_json_error(array(
            'message' => 'El producto está marcado como agotado.'
        ), 400);
    }


    // Validar tipo de etiqueta.
    $tipo = isset($datos['tipo'])
        ? sanitize_key($datos['tipo'])
        : '';

    $tipos_permitidos = array(
        'marca'      => '_python_tipo_marca',
        'marca_talla' => '_python_tipo_marca_talla',
        'talla'      => '_python_tipo_talla',
    );

    if (
        !isset($tipos_permitidos[$tipo]) ||
        $producto->get_meta($tipos_permitidos[$tipo]) !== 'yes'
    ) {
        wp_send_json_error(array(
            'message' => 'El tipo de etiqueta no está permitido.'
        ), 400);
    }

    // Material y medidas.
    $material = isset($datos['material'])
        ? sanitize_text_field($datos['material'])
        : '';

    $ancho = isset($datos['ancho'])
        ? sanitize_text_field($datos['ancho'])
        : '';

    $longitud = isset($datos['longitud'])
        ? absint($datos['longitud'])
        : 0;

    // Validar longitud: de 35 a 120 mm, cada 5 mm.
    if ($longitud < 35 || $longitud > 120 || $longitud % 5 !== 0) {
        wp_send_json_error(array(
            'message' => 'Selecciona una longitud válida.'
        ), 400);
    }

    // Comprobar que el material pertenezca al producto.
    $materiales = wc_get_product_terms(
        $product_id,
        'pa_material',
        array('fields' => 'all')
    );

    $material_valido = false;
    $ancho_valido = false;

    if (!is_wp_error($materiales)) {
        foreach ($materiales as $termino) {

            if ($termino->name !== $material) {
                continue;
            }

            $material_valido = true;

            $anchos = array_map(
                'trim',
                explode(',', (string) get_term_meta(
                    $termino->term_id,
                    'python_anchos',
                    true
                ))
            );

            $ancho_valido = in_array($ancho, $anchos, true);
            break;
        }
    }

    if (!$material_valido || !$ancho_valido) {
        wp_send_json_error(array(
            'message' => 'El material o ancho seleccionado no es válido.'
        ), 400);
    }

    // Cantidades y tallas.
    $cantidad = 0;
    $tallas = array();

    if ($tipo === 'marca') {

        $cantidad = isset($datos['cantidad'])
            ? absint($datos['cantidad'])
            : 0;

        if ($cantidad < 100 || $cantidad % 100 !== 0) {
            wp_send_json_error(array(
                'message' => 'La cantidad debe ser múltiplo de 100.'
            ), 400);
        }

    } else {

        if (
            empty($datos['tallas']) ||
            !is_array($datos['tallas'])
        ) {
            wp_send_json_error(array(
                'message' => 'Agrega al menos una talla.'
            ), 400);
        }

        $tallas_registradas = array();

        foreach ($datos['tallas'] as $fila) {

            if (!is_array($fila)) {
                wp_send_json_error(array(
                    'message' => 'Datos de talla inválidos.'
                ), 400);
            }

            $talla = isset($fila['talla'])
                ? sanitize_text_field($fila['talla'])
                : '';

            $unidades = isset($fila['cantidad'])
                ? absint($fila['cantidad'])
                : 0;

            if (
                $talla === '' ||
                $unidades < 100 ||
                $unidades % 100 !== 0
            ) {
                wp_send_json_error(array(
                    'message' => 'Revisa las tallas y sus cantidades.'
                ), 400);
            }

            $clave_talla = function_exists('mb_strtoupper')
                ? mb_strtoupper($talla, 'UTF-8')
                : strtoupper($talla);

            $clave_talla = preg_replace('/\s+/u', ' ', trim($clave_talla));

            if (isset($tallas_registradas[$clave_talla])) {
                wp_send_json_error(array(
                    'message' => 'Hay tallas duplicadas.'
                ), 400);
            }

            $tallas_registradas[$clave_talla] = true;

            $tallas[] = array(
                'talla' => $talla,
                'cantidad' => $unidades,
            );

            $cantidad += $unidades;
        }
    }

    // Cada combinación tendrá su propia línea en el carrito.
    $cart_item_data = array(
        'python_configuracion' => array(
            'tipo' => $tipo,
            'material' => $material,
            'ancho' => $ancho,
            'longitud' => $longitud,
            'cantidad' => $cantidad,
            'tallas' => $tallas,
        ),
        'python_combinacion_id' => wp_generate_uuid4(),
    );

    wc_clear_notices();
//
   
    if ($modo === 'editar') {

        // Recuperar la combinación existente.
        $item_actual = WC()->cart->get_cart_item($cart_item_key_edicion);

        // Impedir que se cambie el producto original.
        if (
            !$item_actual ||
            (int) $item_actual['product_id'] !== $product_id ||
            empty($item_actual['python_configuracion'])
        ) {
            wp_send_json_error(array(
                'message' => 'La combinación original no es válida.'
            ), 400);
        }

        // Actualizar solamente la configuración.
        WC()->cart->cart_contents[$cart_item_key_edicion]['python_configuracion']
            = $cart_item_data['python_configuracion'];

        // Mantener la clave y el identificador originales.
        $cart_item_key = $cart_item_key_edicion;

        WC()->cart->calculate_totals();

    } else {

        // Crear una combinación nueva.
        $cart_item_key = WC()->cart->add_to_cart(
            $product_id,
            1,
            0,
            array(),
            $cart_item_data
        );
    }
 //   
    if (!$cart_item_key) {

        $errores = wc_get_notices('error');

        $mensajes = array();

        foreach ($errores as $error) {
            if (!empty($error['notice'])) {
                $mensajes[] = wp_strip_all_tags($error['notice']);
            }
        }

        wc_clear_notices();

        wp_send_json_error(array(
            'message' => !empty($mensajes)
                ? implode(' ', $mensajes)
                : 'WooCommerce rechazó la combinación. Revisa el tipo y estado del producto.'
        ), 400);
    }

    // Guardar explícitamente la sesión del carrito.
    WC()->cart->set_session();

    
    $carrito_sesion = WC()->session
        ? WC()->session->get('cart', array())
        : array();

    error_log(
        'PYTHON PRINT - SESIÓN AJAX: ' .
        wp_json_encode(array_map(
            function ($item) {
                return array(
                    'product_id' => $item['product_id'] ?? 0,
                    'configuracion' => isset($item['python_configuracion']),
                );
            },
            $carrito_sesion
        ))
    );


    if (WC()->session) {
        WC()->session->set_customer_session_cookie(true);
        WC()->session->save_data();
    }

    wp_send_json_success(array(
        'message' => $modo === 'editar'
        ? 'Combinación actualizada correctamente.'
        : 'Combinación agregada a Mi pedido.',
        'cart_item_key' => $cart_item_key,
        'cart_url' => wc_get_cart_url(),
        'cantidad' => $cantidad,
    ));
}


/**
 * Agregar una combinación nueva al carrito.
 */
function python_agregar_combinacion() {
    python_guardar_combinacion('agregar');
}


/**
 * Actualizar una combinación existente.
 */
function python_actualizar_combinacion() {
    python_guardar_combinacion('editar');
}

add_action(
    'wp_ajax_python_actualizar_combinacion',
    'python_actualizar_combinacion'
);

add_action(
    'wp_ajax_nopriv_python_actualizar_combinacion',
    'python_actualizar_combinacion'
);

/**
 * PYTHON PRINT
 * Permitir agregar productos configurables sin precio
 * únicamente cuando se utilizan para cotización.
 */
add_filter('woocommerce_is_purchasable', 'python_producto_cotizable', 10, 2);




/**
 * PYTHON PRINT
 * Permitir recuperar combinaciones guardadas
 * en la sesión de WooCommerce.
 */
add_filter(
    'woocommerce_is_purchasable',
    'python_producto_cotizable',
    10,
    2
);

function python_producto_cotizable($purchasable, $product) {

    if ($purchasable || !$product) {
        return $purchasable;
    }

    if (
        !$product->is_type('simple') ||
        $product->get_status() !== 'publish' ||
        !$product->is_in_stock()
    ) {
        return false;
    }

    $es_configurable =
        $product->get_meta('_python_tipo_marca') === 'yes' ||
        $product->get_meta('_python_tipo_marca_talla') === 'yes' ||
        $product->get_meta('_python_tipo_talla') === 'yes';

    if (!$es_configurable) {
        return false;
    }

    // Autorizar nuestro AJAX de configuración.
    if (
        wp_doing_ajax() &&
        isset($_REQUEST['action']) &&
        $_REQUEST['action'] === 'python_agregar_combinacion'
    ) {
        return true;
    }

    // Comprobar la sesión original, antes de que
    // WooCommerce reconstruya el carrito.
    if (function_exists('WC') && WC()->session) {

        $carrito_guardado = WC()->session->get('cart', array());

        if (is_array($carrito_guardado)) {

            foreach ($carrito_guardado as $item) {

                if (
                    isset(
                        $item['product_id'],
                        $item['python_configuracion']
                    ) &&
                    (int) $item['product_id'] === (int) $product->get_id()
                ) {
                    return true;
                }
            }
        }
    }

    return false;
}



/**
 * PYTHON PRINT
 * Mostrar detalles de cada combinación en el carrito.
 */
add_filter(
    'woocommerce_get_item_data',
    'python_mostrar_datos_combinacion',
    10,
    2
);

function python_mostrar_datos_combinacion($item_data, $cart_item) {

    if (empty($cart_item['python_configuracion'])) {
        return $item_data;
    }

    $config = $cart_item['python_configuracion'];

    $tipos = array(
        'marca'      => 'Solo marca',
        'marca_talla' => 'Marca + talla',
        'talla'      => 'Solo talla',
    );

    $item_data[] = array(
        'key'   => 'Tipo de etiqueta',
        'value' => $tipos[$config['tipo']] ?? $config['tipo'],
    );

    $item_data[] = array(
        'key'   => 'Material',
        'value' => $config['material'],
    );

    $item_data[] = array(
        'key'   => 'Ancho',
        'value' => $config['ancho'] . ' mm',
    );

    $item_data[] = array(
        'key'   => 'Longitud',
        'value' => $config['longitud'] . ' mm',
    );

    if (!empty($config['tallas'])) {

        foreach ($config['tallas'] as $fila) {

            $item_data[] = array(
                'key'   => 'Talla ' . $fila['talla'],
                'value' => $fila['cantidad'] . ' unidades',
            );
        }
    }

    $item_data[] = array(
        'key'   => 'Total de etiquetas',
        'value' => $config['cantidad'] . ' unidades',
    );

    return $item_data;
}


//temporal


/**
 * PYTHON PRINT — DIAGNÓSTICO TEMPORAL
 * Mostrar el contenido real del carrito.
 */
add_action('wp_footer', function () {

    if (!function_exists('is_cart') || !is_cart()) {
        return;
    }

    if (!function_exists('WC') || !WC()->cart) {
        echo '<pre>PYTHON PRINT: carrito no disponible</pre>';
        return;
    }

    $carrito = WC()->cart->get_cart();

    echo '<pre style="
        padding:20px;
        margin:20px;
        background:#f5f5f5;
        color:#222;
        border:1px solid #ccc;
        white-space:pre-wrap;
        overflow-wrap:anywhere;
    ">';

    echo 'PYTHON PRINT - DIAGNÓSTICO DEL CARRITO' . "\n\n";
    echo 'Productos encontrados: ' . count($carrito) . "\n\n";

    foreach ($carrito as $item) {

        echo 'Producto ID: ' . ($item['product_id'] ?? '') . "\n";
        echo 'Cantidad WooCommerce: ' . ($item['quantity'] ?? '') . "\n";

        if (isset($item['python_configuracion'])) {
            echo 'Configuración: ' .
                esc_html(wp_json_encode($item['python_configuracion'])) .
                "\n";
        } else {
            echo 'Sin configuración personalizada' . "\n";
        }

        echo "\n";
    }

    echo '</pre>';
});



add_action('woocommerce_cart_loaded_from_session', function ($cart) {

    if (!defined('WP_DEBUG') || !WP_DEBUG) {
        return;
    }

    $productos = array();

    foreach ($cart->get_cart() as $item) {
        $productos[] = array(
            'product_id' => $item['product_id'] ?? 0,
            'configuracion' => isset($item['python_configuracion']),
        );
    }

    error_log(
        'PYTHON PRINT - CARRITO RECUPERADO: ' .
        wp_json_encode($productos)
    );

}, 100);


/**
 * PYTHON PRINT
 * Impedir el pago de combinaciones pendientes
 * de cotización.
 */
add_filter('woocommerce_cart_needs_payment', function ($needs_payment, $cart) {

    if (!$cart) {
        return $needs_payment;
    }

    foreach ($cart->get_cart() as $item) {

        if (!empty($item['python_configuracion'])) {
            return false;
        }
    }

    return $needs_payment;

}, 10, 2);


/**
 * Bloquear el checkout convencional cuando
 * existan combinaciones pendientes de cotización.
 */
add_action('woocommerce_check_cart_items', function () {

    if (!WC()->cart || !is_checkout()) {
        return;
    }

    foreach (WC()->cart->get_cart() as $item) {

        if (!empty($item['python_configuracion'])) {

            wc_add_notice(
                'Tu pedido contiene productos personalizados pendientes de cotización. Solicita tu cotización antes de finalizar la compra.',
                'error'
            );

            return;
        }
    }
});


/**
 * PYTHON PRINT
 * Detectar combinaciones pendientes de cotización.
 */
function python_carrito_requiere_cotizacion() {

    if (!function_exists('WC') || !WC()->cart) {
        return false;
    }

    foreach (WC()->cart->get_cart() as $item) {
        if (!empty($item['python_configuracion'])) {
            return true;
        }
    }

    return false;
}


/**
 * Bloquear checkout clásico.
 */
add_action('woocommerce_checkout_process', function () {

    if (python_carrito_requiere_cotizacion()) {
        wc_add_notice(
            'Este pedido contiene productos personalizados pendientes de cotización. No puedes finalizar una compra hasta recibir una cotización.',
            'error'
        );
    }

});


/**
 * Bloquear checkout mediante bloques de WooCommerce.
 */
add_action(
    'woocommerce_store_api_checkout_update_order_from_request',
    function ($order, $request) {

        if (python_carrito_requiere_cotizacion()) {
            throw new \Automattic\WooCommerce\StoreApi\Exceptions\RouteException(
                'python_cotizacion_pendiente',
                'Este pedido contiene productos personalizados pendientes de cotización.',
                400
            );
        }

    },
    10,
    2
);


/**
 * PYTHON PRINT
 * Estilos de la página Mi pedido.
 */
add_action('wp_enqueue_scripts', function () {

    if (is_page('mi-pedido')) {

        $archivo = get_template_directory() . '/assets/css/mi-pedido.css';

        wp_enqueue_style(
            'python-mi-pedido',
            get_template_directory_uri() . '/assets/css/mi-pedido.css',
            array(),
            file_exists($archivo) ? filemtime($archivo) : '1.0.0'
        );
    }

});


/**
 * PYTHON PRINT
 * Eliminar una combinación de Mi pedido.
 */
add_action('template_redirect', function () {

    if (!is_page('mi-pedido')) {
        return;
    }

    if (!isset($_GET['python_remove_item'])) {
        return;
    }

    $cart_item_key = sanitize_text_field(
        wp_unslash($_GET['python_remove_item'])
    );

    $nonce = isset($_GET['_wpnonce'])
        ? sanitize_text_field(wp_unslash($_GET['_wpnonce']))
        : '';

    if (!wp_verify_nonce($nonce, 'python_remove_' . $cart_item_key)) {
        wp_die('Solicitud no autorizada.');
    }

    if (!function_exists('WC') || !WC()->cart) {
        wp_die('El carrito no está disponible.');
    }

    WC()->cart->remove_cart_item($cart_item_key);

    wp_safe_redirect(get_permalink());
    exit;
});


/**
 * Python Print — Sistema de cotizaciones.
 */
require_once get_template_directory()
    . '/inc/cotizaciones/registro.php';

    /**
 * Python Print — Procesamiento de solicitudes de cotización.
 */
require_once get_template_directory() . '/inc/cotizaciones/solicitudes.php';

/**
 * Python Print — Acceso de clientes sin contraseña.
 */
require_once get_template_directory() . '/inc/cotizaciones/acceso-clientes.php';

/**
 * Python Print — Perfil de clientes.
 */
require_once get_template_directory() . '/inc/cotizaciones/perfil-clientes.php';

/**
 * Python Print — Formulario de cotizaciones.
 */
add_action('wp_enqueue_scripts', function () {

    if (!is_page('mi-pedido')) {
        return;
    }

    $archivo = get_template_directory()
        . '/assets/js/solicitar-cotizacion.js';

    wp_enqueue_script(
        'python-solicitar-cotizacion',
        get_template_directory_uri()
            . '/assets/js/solicitar-cotizacion.js',
        array(),
        file_exists($archivo) ? filemtime($archivo) : '1.0.0',
        true
    );
    
    wp_localize_script(
        'python-solicitar-cotizacion',
        'pythonCotizaciones',
        array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('python_cotizacion_nonce'),
            'accesoAction' => 'python_solicitar_acceso',
            'accesoNonce'  => wp_create_nonce('python_acceso_cliente'),
            'perfilAction' => 'python_guardar_perfil_cliente',
            'perfilNonce'  => wp_create_nonce('python_perfil_cliente'),
        )
    );

});

//logn in
add_action('wp_footer', function () {
    get_template_part('template-parts/login-popup');
});

add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style(
        'python-login-popup',
        get_template_directory_uri() . '/assets/css/login-popup.css',
        array(),
        filemtime(get_template_directory() . '/assets/css/login-popup.css')
    );
});

add_action('wp_enqueue_scripts', function () {

    if (is_user_logged_in()) {
        return;
    }

    wp_enqueue_script(
        'python-login-popup',
        get_template_directory_uri() . '/assets/js/login-popup.js',
        array(),
        filemtime(get_template_directory() . '/assets/js/login-popup.js'),
        true
    );

    wp_localize_script(
        'python-login-popup',
        'pythonLogin',
        array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('python_acceso_cliente'),
        )
    );

});

//menu desplegable user
add_action('wp_enqueue_scripts', function () {
    if (!is_user_logged_in()) return;

    wp_enqueue_script(
        'python-menu-cuenta',
        get_template_directory_uri() . '/assets/js/menu-cuenta.js',
        array(),
        filemtime(get_template_directory() . '/assets/js/menu-cuenta.js'),
        true
    );
});