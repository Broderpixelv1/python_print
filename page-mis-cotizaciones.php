<?php
/**
 * Template Name: Mis cotizaciones Python Print
 */

defined('ABSPATH') || exit;

// Evitar almacenar en caché información privada.
nocache_headers();

get_header();
?>

<main class="python-quotes-page">

    <div class="python-quotes-container">

        <header class="python-quotes-header">

            <h1>Mis cotizaciones</h1>

            <p>
                Consulta tus solicitudes y revisa
                las proformas de tus productos.
            </p>

        </header>

        <?php if (!is_user_logged_in()) : ?>

            <div class="python-quotes-empty">

                <h2>Inicia sesión</h2>

                <p>
                    Accede a tu cuenta para consultar
                    tus cotizaciones.
                </p>

                <button
                    type="button"
                    class="python-login-trigger"
                    data-python-login-open
                    aria-controls="python-login-modal"
                >
                    Iniciar sesión
                </button>

            </div>

        <?php else : ?>

            <?php
            $cotizaciones = python_obtener_mis_cotizaciones();

            $cotizacion_id = isset($_GET['cotizacion'])
                && is_string($_GET['cotizacion'])
                ? absint($_GET['cotizacion'])
                : 0;
            ?>

            <?php if (isset($_GET['cotizacion'])) : ?>

                <?php
                $detalle = python_obtener_detalle_cotizacion(
                    $cotizacion_id
                );
                ?>

                <?php if (!$detalle) : ?>

                    <div class="python-quotes-empty">

                        <h2>Cotización no disponible</h2>

                        <p>
                            La cotización no existe o no tienes
                            autorización para consultarla.
                        </p>

                        <a
                            class="python-quote-card__button"
                            href="<?php echo esc_url(get_permalink()); ?>"
                        >
                            Volver a mis cotizaciones
                        </a>

                    </div>

                <?php else : ?>

                    <?php
                    get_template_part(
                        'template-parts/detalle-cotizacion',
                        null,
                        array('detalle' => $detalle)
                    );
                    ?>

                <?php endif; ?>

            <?php elseif (empty($cotizaciones)) : ?>

                <div class="python-quotes-empty">

                    <h2>Todavía no tienes cotizaciones</h2>

                    <p>
                        Tus solicitudes de cotización
                        aparecerán aquí.
                    </p>

                </div>

            <?php else : ?>

                <div class="python-quotes-list">

                    <?php
                    $estados = array(
                        'pendiente'      => 'Pendiente de revisión',
                        'en_preparacion' => 'En preparación',
                        'enviada'        => 'Proforma disponible',
                        'aprobada'       => 'Aprobada',
                        'rechazada'      => 'Rechazada',
                        'vencida'        => 'Vencida',
                        'cancelada'      => 'Cancelada',
                        'pagada'         => 'Pagada',
                    );
                    ?>

                    <?php foreach ($cotizaciones as $cotizacion) : ?>

                        <?php
                        $detalle = python_obtener_detalle_cotizacion(
                            $cotizacion->ID
                        );

                        if (!$detalle) {
                            continue;
                        }

                        $estado = $detalle['estado'];

                        $estado_texto = $estados[$estado]
                            ?? 'Estado desconocido';

                        $tiene_proforma = $detalle['tiene_proforma'];

                        $total = $detalle['proforma']['totales']['total']
                            ?? null;

                        $url_detalle = add_query_arg(
                            'cotizacion',
                            $detalle['id'],
                            get_permalink()
                        );
                        ?>

                        <article class="python-quote-card">

                            <div class="python-quote-card__header">

                                <div class="python-quote-card__identity">

                                    <h2 class="python-quote-card__number">
                                        <?php echo esc_html($detalle['numero']); ?>
                                    </h2>

                                    <time class="python-quote-card__date">
                                        <?php
                                        echo esc_html(
                                            get_the_date(
                                                'j \d\e F \d\e Y',
                                                $cotizacion
                                            )
                                        );
                                        ?>
                                    </time>

                                </div>

                                <span class="python-quote-status python-quote-status--<?php
                                    echo esc_attr($estado);
                                ?>">
                                    <?php echo esc_html($estado_texto); ?>
                                </span>

                            </div>

                            <div class="python-quote-card__body">

                                <div class="python-quote-card__products">

                                    <?php
                                    $productos = $tiene_proforma
                                        ? ($detalle['proforma']['productos'] ?? array())
                                        : $detalle['productos'];
                                    ?>

                                    <?php foreach ($productos as $producto) : ?>

                                        <p>
                                            <?php
                                            echo esc_html(
                                                $producto['nombre'] ?? 'Producto'
                                            );
                                            ?>
                                        </p>

                                    <?php endforeach; ?>

                                </div>

                            </div>

                            <div class="python-quote-card__footer">

                                <div class="python-quote-card__total">

                                    <span>Importe total</span>

                                    <strong>

                                        <?php if ($tiene_proforma && $total !== null) : ?>

                                            <?php
                                            echo esc_html(
                                                '$' . number_format(
                                                    (float) $total,
                                                    2,
                                                    '.',
                                                    ','
                                                )
                                            );
                                            ?>

                                        <?php else : ?>

                                            Por cotizar

                                        <?php endif; ?>

                                    </strong>

                                </div>

                                <a
                                    class="python-quote-card__button"
                                    href="<?php echo esc_url($url_detalle); ?>"
                                >
                                    <?php
                                    echo $tiene_proforma
                                        ? 'Ver proforma'
                                        : 'Ver solicitud';
                                    ?>
                                </a>

                            </div>

                        </article>

                    <?php endforeach; ?>

                </div>

            <?php endif; ?>

        <?php endif; ?>

    </div>

</main>

<?php get_footer(); ?>