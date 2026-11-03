
document.addEventListener('DOMContentLoaded', function () {

    const configurator = document.getElementById('python-configurator');

    if (!configurator) return;

    const overlay = configurator.querySelector('.python-configurator__overlay');
    const closeButton = configurator.querySelector('.python-configurator__close');
    const productImage = configurator.querySelector('.python-configurator__image');
    const productTitle = configurator.querySelector('.python-configurator__title');


    // Selección del tipo de etiqueta
    const typeOptions = configurator.querySelectorAll(
        'input[name="tipo_etiqueta"]'
    );

    typeOptions.forEach(function (option) {

        option.addEventListener('change', function () {

            configurator.querySelectorAll(
                '.python-configurator__choice'
            ).forEach(function (choice) {
                choice.classList.remove('is-selected');
            });

            option.closest('.python-configurator__choice')
                .classList.add('is-selected');

        });

    });

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

        // Leer opciones habilitadas en WooCommerce
        const availableTypes = {
            marca: product.dataset.tipoMarca === 'yes',
            marca_talla: product.dataset.tipoMarcaTalla === 'yes',
            talla: product.dataset.tipoTalla === 'yes'
        };

        
        // Obtener materiales desde WooCommerce
        const materialSection = configurator.querySelector('.python-material-section');
        const materialOptions = configurator.querySelector('.python-material-options');

        let materiales = [];

        try {
            materiales = JSON.parse(product.dataset.materiales || '[]');
        } catch (error) {
            materiales = [];
        }

        // Limpiar materiales del producto anterior
        materialOptions.replaceChildren();

        materiales.forEach(function (material) {

            const label = document.createElement('label');
            label.className = 'python-configurator__choice';

            const input = document.createElement('input');
            input.type = 'radio';
            input.name = 'python_material';
            input.value = material;

            const content = document.createElement('span');
            content.className = 'python-configurator__choice-content';

            const title = document.createElement('strong');
            title.textContent = material;

            content.appendChild(title);
            label.appendChild(input);
            label.appendChild(content);

            input.addEventListener('change', function () {

                materialOptions.querySelectorAll('.python-configurator__choice')
                    .forEach(function (choice) {
                        choice.classList.remove('is-selected');
                    });

                label.classList.add('is-selected');
            });

            materialOptions.appendChild(label);
        });

        // Mostrar sección únicamente si hay materiales
        materialSection.hidden = materiales.length === 0;


        // Mostrar únicamente las opciones permitidas
        typeOptions.forEach(function (option) {

            const choice = option.closest('.python-configurator__choice');
            const enabled = availableTypes[option.value] === true;

            choice.style.display = enabled ? 'flex' : 'none';

            // Reiniciar selección al abrir otro producto
            option.checked = false;
            choice.classList.remove('is-selected');

        });

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
