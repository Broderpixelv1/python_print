
document.addEventListener('DOMContentLoaded', function () {

    const configurator = document.getElementById('python-configurator');

    if (!configurator) return;

    const overlay = configurator.querySelector('.python-configurator__overlay');
    const closeButton = configurator.querySelector('.python-configurator__close');
    const productImage = configurator.querySelector('.python-configurator__image');
    const productTitle = configurator.querySelector('.python-configurator__title');

    // Abrir configurador
    function openConfigurator(product) {

        const image = product.querySelector('img');
        const title = product.querySelector('.python-product-card__title');

        if (image && productImage) {
            productImage.src = image.src;
            productImage.alt = image.alt || '';
        }

        if (title && productTitle) {
            productTitle.textContent = title.textContent.trim();
        }

        configurator.classList.add('is-open');
        configurator.setAttribute('aria-hidden', 'false');

        document.body.classList.add('no-scroll');
    }

    // Cerrar configurador
    function closeConfigurator() {

        configurator.classList.remove('is-open');
        configurator.setAttribute('aria-hidden', 'true');

        document.body.classList.remove('no-scroll');
    }

    // Detectar clic en tarjetas del catálogo
    document.addEventListener('click', function (event) {

        const productCard = event.target.closest('.python-product-card__link');

        if (!productCard) return;

        event.preventDefault();

        openConfigurator(productCard);
    });

    // Cerrar con la X
    closeButton?.addEventListener('click', closeConfigurator);

    // Cerrar al pulsar la cortina
    overlay?.addEventListener('click', closeConfigurator);

    // Cerrar con Escape
    document.addEventListener('keydown', function (event) {

        if (event.key === 'Escape' && configurator.classList.contains('is-open')) {
            closeConfigurator();
        }

    });

});
