<?php
/**
 * Python Print — Integración de proformas
 * con pedidos de WooCommerce.
 *
 * Responsabilidades:
 * - Validar proformas aprobadas.
 * - Generar pedidos de WooCommerce.
 * - Transferir productos y configuraciones.
 * - Conservar los importes aprobados.
 * - Evitar pedidos duplicados.
 * - Gestionar la relación entre cotización y pedido.
 */

defined('ABSPATH') || exit;

/**
 * Validar una proforma para generar un pedido.
 */
function python_validar_proforma_para_pedido($cotizacion_id) {

    // Comprobar disponibilidad de WooCommerce.
    if (!function_exists('wc_create_order')) {
        return new WP_Error(
            'woocommerce_no_disponible',
            'El sistema de pedidos no está disponible.'
        );
    }

    // Recuperar la cotización del cliente.
    $detalle = python_obtener_detalle_cotizacion(
        $cotizacion_id
    );

    if (!$detalle) {
        return new WP_Error(
            'cotizacion_no_autorizada',
            'No tienes acceso a esta cotización.'
        );
    }

    // Comprobar el estado comercial.
    if ($detalle['estado'] !== 'aprobada') {
        return new WP_Error(
            'proforma_no_aprobada',
            'Debes aprobar la proforma antes de proceder al pago.'
        );
    }

    $proforma = $detalle['proforma'];

    // Recuperar la respuesta comercial del cliente.
    $respuesta = get_post_meta(
        $detalle['id'],
        '_python_respuesta_proforma',
        true
    );

    // Validar la versión aprobada.
    if (
        !is_array($respuesta) ||
        empty($proforma['version']) ||
        ($respuesta['estado'] ?? '') !== 'aprobada' ||
        ($respuesta['version'] ?? '') !== $proforma['version'] ||
        (int) ($respuesta['cliente_id'] ?? 0) !== get_current_user_id()
    ) {
        return new WP_Error(
            'version_no_valida',
            'No se pudo verificar la versión aprobada.'
        );
    }

    // Recuperar los datos comerciales publicados.
    $productos = $proforma['productos'] ?? array();
    $precios   = $proforma['precios'] ?? array();
    $totales   = $proforma['totales'] ?? array();

    // Validar la estructura comercial.
    if (
        !is_array($productos) ||
        empty($productos) ||
        !is_array($precios) ||
        !is_array($totales)
    ) {
        return new WP_Error(
            'proforma_incompleta',
            'La proforma no contiene información comercial válida.'
        );
    }

    // Validar los importes financieros.
    $campos_totales = array(
        'subtotal',
        'descuento',
        'base_imponible',
        'tasa_iva',
        'iva',
        'envio',
        'total',
    );

    foreach ($campos_totales as $campo) {

        if (
            !isset($totales[$campo]) ||
            !is_numeric($totales[$campo]) ||
            !is_finite((float) $totales[$campo]) ||
            (float) $totales[$campo] < 0
        ) {
            return new WP_Error(
                'totales_no_validos',
                'Los importes de la proforma no son válidos.'
            );
        }
    }

    if ((float) $totales['total'] <= 0) {
        return new WP_Error(
            'total_no_valido',
            'El importe del pedido debe ser superior a cero.'
        );
    }

    // Validar cada producto y su precio aprobado.
    foreach ($productos as $indice => $producto) {

        if (
            !is_array($producto) ||
            empty($producto['producto_id']) ||
            !isset($precios[$indice]) ||
            !is_numeric($precios[$indice]) ||
            !is_finite((float) $precios[$indice]) ||
            (float) $precios[$indice] <= 0
        ) {
            return new WP_Error(
                'producto_no_valido',
                'Existe un producto con información comercial incorrecta.'
            );
        }
    }

    // Devolver la información validada.
    return array(
        'cotizacion_id' => $detalle['id'],
        'numero'        => $detalle['numero'],
        'cliente_id'    => get_current_user_id(),
        'version'       => $proforma['version'],
        'productos'     => $productos,
        'precios'       => $precios,
        'totales'       => $totales,
        'proforma'      => $proforma,
    );
}

/**
 * Recuperar el pedido asociado a una proforma.
 *
 * Devuelve:
 * - WC_Order: pedido existente.
 * - false: todavía no existe un pedido asociado.
 * - WP_Error: existe una inconsistencia.
 */
function python_obtener_pedido_proforma($datos) {

    $cotizacion_id = $datos['cotizacion_id'];

    // Recuperar el identificador del pedido asociado.
    $pedido_id = absint(
        get_post_meta(
            $cotizacion_id,
            '_python_pedido_id',
            true
        )
    );

    // La cotización todavía no tiene un pedido.
    if (!$pedido_id) {
        return false;
    }

    // Recuperar el pedido mediante WooCommerce.
    $pedido = wc_get_order($pedido_id);

    if (!$pedido instanceof WC_Order) {
        return new WP_Error(
            'pedido_no_encontrado',
            'No se pudo recuperar el pedido asociado.'
        );
    }

    // Verificar el propietario del pedido.
    if (
        (int) $pedido->get_customer_id() !==
        (int) $datos['cliente_id']
    ) {
        return new WP_Error(
            'pedido_no_autorizado',
            'No tienes acceso al pedido asociado.'
        );
    }

    // Verificar la relación con la cotización.
    if (
        (int) $pedido->get_meta('_python_cotizacion_id') !==
        (int) $cotizacion_id
    ) {
        return new WP_Error(
            'pedido_incompatible',
            'El pedido no corresponde a esta cotización.'
        );
    }

    // Verificar la versión comercial.
    if (
        $pedido->get_meta('_python_proforma_version') !==
        $datos['version']
    ) {
        return new WP_Error(
            'pedido_version_incorrecta',
            'El pedido no corresponde a la versión aprobada.'
        );
    }

    // Devolver el pedido existente.
    return $pedido;
}

/**
 * Distribuir los importes comerciales aprobados
 * entre los productos del pedido.
 */
function python_distribuir_importes_proforma($datos) {

    $totales = $datos['totales'];
    $productos = $datos['productos'];
    $precios = $datos['precios'];

    // Convertir dólares a centavos enteros.
    $centavos = static function ($valor) {
        return (int) round((float) $valor * 100);
    };

    $subtotal = $centavos($totales['subtotal']);
    $descuento = $centavos($totales['descuento']);
    $base = $centavos($totales['base_imponible']);
    $iva = $centavos($totales['iva']);
    $envio = $centavos($totales['envio']);
    $total = $centavos($totales['total']);

    // Comprobar la coherencia financiera.
    if (
        $subtotal <= 0 ||
        $descuento > $subtotal ||
        $base !== ($subtotal - $descuento) ||
        $total !== ($base + $iva + $envio) ||
        ($base === 0 && $iva !== 0)
    ) {
        return new WP_Error(
            'importes_inconsistentes',
            'Los importes de la proforma no coinciden.'
        );
    }

    // Comprobar que los precios coincidan con el subtotal.
    $subtotal_productos = 0;

    foreach ($productos as $indice => $producto) {
        $subtotal_productos += $centavos($precios[$indice]);
    }

    if ($subtotal_productos !== $subtotal) {
        return new WP_Error(
            'subtotal_inconsistente',
            'Los precios de los productos no coinciden con el subtotal.'
        );
    }

    // Preparar la distribución proporcional.
    $lineas = array();

    $subtotal_acumulado = 0;
    $descuento_acumulado = 0;

    $base_acumulada = 0;
    $iva_acumulado = 0;

    foreach ($productos as $indice => $producto) {

        $precio = $centavos($precios[$indice]);

        $subtotal_acumulado += $precio;

        // Distribuir el descuento proporcionalmente.
        $descuento_objetivo = (int) round(
            $descuento * ($subtotal_acumulado / $subtotal)
        );

        $descuento_linea =
            $descuento_objetivo - $descuento_acumulado;

        $descuento_acumulado += $descuento_linea;

        // Base imponible de esta combinación.
        $base_linea = $precio - $descuento_linea;

        $base_acumulada += $base_linea;

        // Distribuir el IVA proporcionalmente.
        $iva_objetivo = $base > 0
            ? (int) round(
                $iva * ($base_acumulada / $base)
            )
            : 0;

        $iva_linea = $iva_objetivo - $iva_acumulado;

        $iva_acumulado += $iva_linea;

        // Guardar los importes en dólares.
        $lineas[$indice] = array(
            'subtotal'  => $precio / 100,
            'descuento' => $descuento_linea / 100,
            'total'     => $base_linea / 100,
            'iva'       => $iva_linea / 100,
        );
    }

    // Verificar que no existan diferencias de redondeo.
    if (
        $descuento_acumulado !== $descuento ||
        $base_acumulada !== $base ||
        $iva_acumulado !== $iva
    ) {
        return new WP_Error(
            'error_distribucion',
            'No fue posible distribuir los importes correctamente.'
        );
    }

    return $lineas;
}

/**
 * Preparar las líneas de productos personalizados
 * de un pedido generado desde una proforma.
 */
function python_preparar_productos_pedido($datos, $lineas, $tasa_id = 0) {

    $items = array();

    foreach ($datos['productos'] as $indice => $producto) {

        // Recuperar el producto original de WooCommerce.
        $producto_id = absint($producto['producto_id']);

        $wc_producto = wc_get_product($producto_id);

        if (!$wc_producto || !$wc_producto->exists()) {
            return new WP_Error(
                'producto_no_disponible',
                'Uno de los productos de la proforma ya no está disponible.'
            );
        }

        // Recuperar la configuración personalizada.
        $config = $producto['configuracion'] ?? array();

        $config = is_array($config)
            ? $config
            : array();

        // Recuperar la cantidad real de unidades.
        $cantidad = (int) (
            $config['cantidad'] ??
            $producto['cantidad'] ??
            0
        );

        if ($cantidad <= 0) {
            return new WP_Error(
                'cantidad_no_valida',
                'Existe un producto con una cantidad incorrecta.'
            );
        }

        // Recuperar los importes distribuidos.
        $importes = $lineas[$indice] ?? null;

        if (!$importes) {
            return new WP_Error(
                'importes_no_disponibles',
                'No se encontraron los importes del producto.'
            );
        }

        // Crear una línea independiente del pedido.
        $item = new WC_Order_Item_Product();

        $item->set_product($wc_producto);

        // Conservar el nombre de la proforma publicada.
        $item->set_name(
            $producto['nombre'] ?? $wc_producto->get_name()
        );

        $item->set_quantity($cantidad);

        // Importes totales de la combinación.
        $item->set_subtotal($importes['subtotal']);

        $item->set_total($importes['total']);

        // Conservar el IVA aprobado.
        if ((float) $importes['iva'] > 0) {

            if (!$tasa_id) {
                return new WP_Error(
                    'tasa_iva_no_disponible',
                    'No se encontró la tasa de IVA correspondiente.'
                );
            }

            $iva_subtotal = round(
                $importes['subtotal'] *
                $datos['totales']['tasa_iva'],
                2
            );

            $item->set_taxes(array(
                'subtotal' => array(
                    $tasa_id => $iva_subtotal,
                ),
                'total' => array(
                    $tasa_id => $importes['iva'],
                ),
            ));

        } else {

            $item->set_taxes(array(
                'subtotal' => array(),
                'total'    => array(),
            ));
        }

        // Guardar la configuración original.
        $item->add_meta_data(
            '_python_configuracion',
            $config,
            true
        );

        // Identificar la combinación original.
        $item->add_meta_data(
            '_python_combinacion',
            $indice,
            true
        );

        // Conservar el descuento comercial.
        $item->add_meta_data(
            '_python_descuento',
            $importes['descuento'],
            true
        );

        // Preparar los datos visibles para el cliente.
        $campos = array(
            'tipo'     => 'Tipo',
            'material' => 'Material',
            'ancho'    => 'Ancho',
            'longitud' => 'Largo',
        );

        foreach ($campos as $clave => $etiqueta) {

            $valor = $config[$clave] ?? '';

            if (!is_scalar($valor) || $valor === '') {
                continue;
            }

            // Convertir milímetros a centímetros.
            if (
                in_array(
                    $clave,
                    array('ancho', 'longitud'),
                    true
                ) &&
                is_numeric($valor)
            ) {
                $valor = number_format_i18n(
                    (float) $valor / 10,
                    1
                ) . ' cm';
            }

            $item->add_meta_data(
                $etiqueta,
                (string) $valor,
                true
            );
        }

        // Conservar la distribución por tallas.
        if (
            !empty($config['tallas']) &&
            is_array($config['tallas'])
        ) {

            $tallas = array();

            foreach ($config['tallas'] as $fila) {

                if (!is_array($fila)) {
                    continue;
                }

                $tallas[] =
                    ($fila['talla'] ?? 'Sin talla') .
                    ': ' .
                    (int) ($fila['cantidad'] ?? 0) .
                    ' unidades';
            }

            if (!empty($tallas)) {

                $item->add_meta_data(
                    'Distribución por tallas',
                    implode(' | ', $tallas),
                    true
                );
            }
        }

        // Incorporar la línea preparada.
        $items[] = $item;
    }

    return $items;
}

/**
 * Recuperar la tasa de IVA correspondiente
 * a la proforma aprobada.
 */
function python_obtener_tasa_iva_proforma($datos) {

    // Si la proforma no aplica IVA.
    if (($datos['proforma']['iva'] ?? 'sin') !== 'con') {
        return 0;
    }

    if (!wc_tax_enabled()) {
        return new WP_Error(
            'impuestos_desactivados',
            'Los impuestos de WooCommerce están desactivados.'
        );
    }

    // Recuperar la tasa aprobada.
    $porcentaje = (float) $datos['totales']['tasa_iva'] * 100;

    // Buscar tarifas estándar de Ecuador.
    $tasas = WC_Tax::find_rates(array(
        'country'   => 'EC',
        'state'     => '',
        'postcode'  => '',
        'city'      => '',
        'tax_class' => '',
    ));

    foreach ($tasas as $tasa_id => $tasa) {

        if (
            abs((float) $tasa['rate'] - $porcentaje) < 0.0001 &&
            ($tasa['compound'] ?? 'no') === 'no'
        ) {
            return (int) $tasa_id;
        }
    }

    return new WP_Error(
        'tasa_iva_no_encontrada',
        'No existe una tarifa de IVA compatible con la proforma.'
    );
}

/**
 * Preparar el envío aprobado en la proforma.
 */
function python_preparar_envio_pedido($datos) {

    $costo_envio = (float) $datos['totales']['envio'];

    if ($costo_envio <= 0) {
        return false;
    }

    $envio = new WC_Order_Item_Shipping();

    $envio->set_method_title('Envío cotizado');

    $envio->set_method_id('python_envio_cotizado');

    $envio->set_total($costo_envio);

    // Conservar el tratamiento tributario de la proforma.
    $envio->set_taxes(array(
        'total' => array(),
    ));

    return $envio;
}