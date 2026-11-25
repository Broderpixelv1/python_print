
<?php
/**
 * Python Print — Popup de avisos comerciales.
 */

defined('ABSPATH') || exit;

$aviso = $args['aviso'] ?? '';

$avisos = array(

    'actualizada' => array(

        'icono' => 'dashicons-update',

        'titulo' => 'Tu proforma ha sido actualizada',

        'mensaje' => 'Esta versión ya no está disponible. Consulta la versión más reciente de tu propuesta comercial para continuar con tu pedido.',

        'boton' => 'Consultar proforma actual',

    ),

    'vencida' => array(

        'icono' => 'dashicons-clock',

        'titulo' => 'Tu proforma ha vencido',

        'mensaje' => 'El plazo de vigencia de esta proforma ha terminado. Comunícate con nuestro equipo para solicitar una actualización.',

        'boton' => 'Entendido',

    ),

    'no_disponible' => array(

        'icono' => 'dashicons-info-outline',

        'titulo' => 'Proforma no disponible',

        'mensaje' => 'No es posible completar esta operación en este momento. Revisa el estado actual de tu cotización.',

        'boton' => 'Entendido',

    ),

);

if (!isset($avisos[$aviso])) {
    return;
}

$contenido = $avisos[$aviso];

?>

<dialog
    id="python-proforma-popup"
    class="python-proforma-popup"
    aria-labelledby="python-proforma-popup-titulo"
    aria-describedby="python-proforma-popup-mensaje"
>

    <div class="python-proforma-popup__content">

        <form method="dialog">

            <button
                type="submit"
                class="python-proforma-popup__close"
                aria-label="Cerrar aviso"
            >

                &times;

            </button>

        </form>

        <div class="python-proforma-popup__icon">

            <span
                class="dashicons <?php echo esc_attr(
                    $contenido['icono']
                ); ?>"
                aria-hidden="true"
            ></span>

        </div>

        <h2 id="python-proforma-popup-titulo">

            <?php echo esc_html(
                $contenido['titulo']
            ); ?>

        </h2>

        <p id="python-proforma-popup-mensaje">

            <?php echo esc_html(
                $contenido['mensaje']
            ); ?>

        </p>

        <form
            method="dialog"
            class="python-proforma-popup__actions"
        >

            <button
                type="submit"
                class="python-proforma-popup__button"
                autofocus
            >

                <?php echo esc_html(
                    $contenido['boton']
                ); ?>

            </button>

        </form>

        <a
            class="python-proforma-popup__back"
            href="<?php echo esc_url(
                home_url('/mis-cotizaciones/')
            ); ?>"
        >

            Volver a mis cotizaciones

        </a>

    </div>

</dialog>

<script>
document.addEventListener('DOMContentLoaded', () => {

    const popup = document.getElementById(
        'python-proforma-popup'
    );

    if (!popup) return;

    if (typeof popup.showModal !== 'function') {
        popup.setAttribute('open', '');
        return;
    }

    popup.addEventListener('close', () => {

        const url = new URL(window.location.href);

        url.searchParams.delete('aviso');

        window.history.replaceState(
            window.history.state,
            '',
            url.toString()
        );

    });

    popup.showModal();

});
</script>
