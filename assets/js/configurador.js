
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


    const quantitySection = configurator.querySelector('.python-quantity-section');
    const sizesSection = configurator.querySelector('.python-sizes-section');

    function actualizarTipoEtiqueta(tipo) {

        // Ocultar ambas secciones
        quantitySection.hidden = true;
        sizesSection.hidden = true;

        if (tipo === 'marca') {
            quantitySection.hidden = false;
        }

        if (tipo === 'marca_talla' || tipo === 'talla') {
            sizesSection.hidden = false;
        }
    }


    // =========================================
    // PYTHON PRINT — TALLAS DINÁMICAS
    // =========================================

    const sizesList = configurator.querySelector('.python-sizes-list');
    const addSizeButton = configurator.querySelector('.python-add-size');
    const sizesTotal = configurator.querySelector('#python-sizes-total');

    const tallasDisponibles = [
        'XS', 'S', 'M', 'L', 'XL', 'XXL', '3XL',
        'Personalizada'
    ];

    // Actualizar total de etiquetas
    function actualizarTotalTallas() {

        let total = 0;

        sizesList.querySelectorAll('.python-size-quantity').forEach(function (input) {
            total += Number(input.value) || 0;
        });

        sizesTotal.textContent = total.toLocaleString('es-EC') + ' unidades';
    }

    // Agregar una fila de talla
    function agregarTalla() {

        const row = document.createElement('div');
        row.className = 'python-size-row';

        // Campo talla
        const sizeField = document.createElement('div');
        sizeField.className = 'python-size-field';

        const sizeLabel = document.createElement('label');
        sizeLabel.textContent = 'Talla';

        const sizeSelect = document.createElement('select');
        sizeSelect.className = 'python-configurator__select python-size-select';

        tallasDisponibles.forEach(function (talla) {

            const option = document.createElement('option');
            option.value = talla;
            option.textContent = talla;
            sizeSelect.appendChild(option);

        });

        const customSize = document.createElement('input');
        customSize.type = 'text';
        customSize.className = 'python-configurator__select python-size-custom';
        customSize.placeholder = 'Escribe la talla';
        customSize.hidden = true;

        sizeSelect.addEventListener('change', function () {
            customSize.hidden = sizeSelect.value !== 'Personalizada';
        });

        sizeField.appendChild(sizeLabel);
        sizeField.appendChild(sizeSelect);
        sizeField.appendChild(customSize);

        // Campo cantidad
        const quantityField = document.createElement('div');
        quantityField.className = 'python-size-field';

        const quantityLabel = document.createElement('label');
        quantityLabel.textContent = 'Cantidad';

        const quantityInput = document.createElement('input');
        quantityInput.type = 'number';
        quantityInput.className = 'python-configurator__select python-size-quantity';
        quantityInput.min = 100;
        quantityInput.step = 100;
        quantityInput.value = 100;

        quantityInput.addEventListener('change', function () {

            const cantidad = Number(quantityInput.value);

            if (!Number.isFinite(cantidad) || cantidad < 100) {
                quantityInput.value = 100;
            } else {
                quantityInput.value = Math.ceil(cantidad / 100) * 100;
            }

            actualizarTotalTallas();
        });

        quantityField.appendChild(quantityLabel);
        quantityField.appendChild(quantityInput);

        // Botón eliminar
        const removeButton = document.createElement('button');
        removeButton.type = 'button';
        removeButton.className = 'python-remove-size';
        removeButton.textContent = '×';
        removeButton.setAttribute('aria-label', 'Eliminar talla');

        removeButton.addEventListener('click', function () {
            row.remove();
            actualizarTotalTallas();
        });

        row.appendChild(sizeField);
        row.appendChild(quantityField);
        row.appendChild(removeButton);

        sizesList.appendChild(row);

        actualizarTotalTallas();
    }

    // Botón agregar talla
    addSizeButton.addEventListener('click', agregarTalla);



    typeOptions.forEach(function (option) {

        option.addEventListener('change', function () {

            configurator.querySelectorAll(
                '.python-configurator__choice'
            ).forEach(function (choice) {
                choice.classList.remove('is-selected');
            });

            option.closest('.python-configurator__choice')
                .classList.add('is-selected');
            actualizarTipoEtiqueta(option.value);

        });

    });

    // Abrir configurador
    function openConfigurator(product) {

        productoActualId = Number(product.dataset.productId);
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

        
        // Leer anchos disponibles por material
        let anchosPorMaterial = {};

        try {
            anchosPorMaterial = JSON.parse(product.dataset.anchos || '{}');
        } catch (error) {
            anchosPorMaterial = {};
        }

        const widthSection = configurator.querySelector('.python-width-section');
        const widthOptions = configurator.querySelector('#python-ancho');

        // Reiniciar anchos al abrir otro producto
        widthOptions.replaceChildren();
        widthSection.hidden = true;

        // Generar anchos según el material seleccionado
        function mostrarAnchos(material) {

            const selectAncho = configurator.querySelector('#python-ancho');

            // Limpiar opciones anteriores
            selectAncho.replaceChildren();

            const placeholder = document.createElement('option');
            placeholder.value = '';
            placeholder.textContent = 'Selecciona un ancho';
            selectAncho.appendChild(placeholder);

            const anchos = anchosPorMaterial[material] || [];

            anchos.forEach(function (ancho) {

                const option = document.createElement('option');

                option.value = ancho;
                option.textContent = ancho + ' mm';

                selectAncho.appendChild(option);
            });

            // Mostrar sección si existen anchos
            widthSection.hidden = anchos.length === 0;
        }
                    
            // Selector de longitud
            const lengthSection = configurator.querySelector('.python-length-section');
            const selectLongitud = configurator.querySelector('#python-longitud');

            // Reiniciar longitud al abrir otro producto
            selectLongitud.replaceChildren();

            const placeholderLongitud = document.createElement('option');
            placeholderLongitud.value = '';
            placeholderLongitud.textContent = 'Selecciona una longitud';

            selectLongitud.appendChild(placeholderLongitud);

            // Generar longitudes de 35 a 120 mm, cada 5 mm
            for (let longitud = 35; longitud <= 120; longitud += 5) {

                const option = document.createElement('option');

                option.value = longitud;
                option.textContent = longitud + ' mm';

                selectLongitud.appendChild(option);
            }

            // Mostrar longitud únicamente cuando el producto tenga materiales
            lengthSection.hidden = materiales.length === 0;


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

                // Actualizar anchos al seleccionar material
                mostrarAnchos(material);

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

        // Ocultar cantidad y tallas al abrir otro producto
        actualizarTipoEtiqueta('');

        sizesList.replaceChildren();
        actualizarTotalTallas();


        const cantidadInput = configurator.querySelector('#python-cantidad');

        if (cantidadInput) {
            cantidadInput.value = 100;
        }

        configurator.classList.add('is-open');
        configurator.setAttribute('aria-hidden', 'false');

        document.body.classList.add('no-scroll');
    }

        
    // =========================================
    // VALIDAR CONFIGURACIÓN DEL PRODUCTO
    // =========================================

    const addToOrderButton = configurator.querySelector('#python-add-to-order');

    
    // Limpiar los errores anteriores
    
    function limpiarErrores() {

        configurator.querySelectorAll('.python-error-message').forEach(function (mensaje) {
            mensaje.remove();
        });

        configurator.querySelectorAll('.python-field-error').forEach(function (elemento) {
            elemento.classList.remove('python-field-error');
        });
    }

    
    // =========================================
    // LIMPIAR ERRORES AL CORREGIR CAMPOS
    // =========================================

    function quitarError(elemento) {

        if (!elemento) return;

        elemento.classList.remove('python-field-error');

        elemento.querySelectorAll('.python-error-message').forEach(function (mensaje) {
            mensaje.remove();
        });
    }

    configurator.addEventListener('change', function (event) {

        const campo = event.target;

        // Tipo de etiqueta
        if (campo.matches('input[name="tipo_etiqueta"]')) {
            quitarError(campo.closest('.python-configurator__section'));
        }

        // Material
        if (campo.matches('input[name="python_material"]')) {
            quitarError(configurator.querySelector('.python-material-section'));
        }

        // Ancho
        if (campo.matches('#python-ancho') && campo.value) {
            quitarError(configurator.querySelector('.python-width-section'));
        }

        // Longitud
        if (campo.matches('#python-longitud') && campo.value) {
            quitarError(configurator.querySelector('.python-length-section'));
        }

        // Cantidad de etiquetas
        if (campo.matches('#python-cantidad')) {

            const cantidad = Number(campo.value);

            if (Number.isInteger(cantidad) && cantidad >= 100 && cantidad % 100 === 0) {
                quitarError(configurator.querySelector('.python-quantity-section'));
            }
        }

        // Tallas dinámicas
        if (campo.matches('.python-size-select, .python-size-custom, .python-size-quantity')) {

            const fila = campo.closest('.python-size-row');

            if (fila) {
                const talla = fila.querySelector('.python-size-select');
                const personalizada = fila.querySelector('.python-size-custom');
                const cantidad = Number(fila.querySelector('.python-size-quantity')?.value);

                const nombreValido =
                    talla?.value !== 'Personalizada' ||
                    Boolean(personalizada?.value.trim());

                const cantidadValida =
                    Number.isInteger(cantidad) &&
                    cantidad >= 100 &&
                    cantidad % 100 === 0;

                if (nombreValido && cantidadValida) {
                    quitarError(fila);
                }
            }

            if (configurator.querySelectorAll('.python-size-row').length > 0) {
                quitarError(configurator.querySelector('.python-sizes-section'));
            }
        }
    });

    configurator.addEventListener('input', function (event) {
        if (event.target.matches('.python-size-custom')) {
            event.target.dispatchEvent(new Event('change', { bubbles: true }));
        }
    });

    // Resaltar un campo que necesita atención
    function marcarError(elemento, mensaje) {

        if (!elemento) return;

        elemento.classList.add('python-field-error');

        const aviso = document.createElement('small');
        aviso.className = 'python-error-message';
        aviso.textContent = mensaje;

        elemento.appendChild(aviso);

        elemento.scrollIntoView({
            behavior: 'smooth',
            block: 'center'
        });
    }


    function obtenerNombreTalla(fila) {

        const select = fila.querySelector('.python-size-select');
        const personalizada = fila.querySelector('.python-size-custom');

        if (!select) return '';

        return (
            select.value === 'Personalizada'
                ? personalizada?.value.trim() || ''
                : select.value.trim()
        );
    }

    function validarTallasDuplicadas() {

        const filas = sizesList.querySelectorAll('.python-size-row');
        const tallasRegistradas = new Map();

        let hayDuplicados = false;

        filas.forEach(function (fila) {

            const talla = obtenerNombreTalla(fila);
            if (!talla) return;

            // Evitar diferencias entre mayúsculas, minúsculas y espacios.
            const clave = talla
                .normalize('NFKC')
                .toLocaleUpperCase('es-EC')
                .replace(/\s+/g, ' ')
                .trim();

            if (tallasRegistradas.has(clave)) {

                marcarError(
                    fila,
                    'Esta talla ya fue agregada. Selecciona otra o modifica la cantidad existente.'
                );

                hayDuplicados = true;

            } else {
                tallasRegistradas.set(clave, fila);
            }
        });

        return !hayDuplicados;
    }


    //validar campos del formulario incompletos
    
    function validarConfiguracion() {

        limpiarErrores();

        let esValido = true;
        let primerError = null;

        function registrarError(elemento, mensaje) {

            if (!elemento) return;

            elemento.classList.add('python-field-error');

            const aviso = document.createElement('small');
            aviso.className = 'python-error-message';
            aviso.textContent = mensaje;
            elemento.appendChild(aviso);

            if (!primerError) {
                primerError = elemento;
            }

            esValido = false;
        }

        // 1. TIPO DE ETIQUETA
        const tipo = configurator.querySelector(
            'input[name="tipo_etiqueta"]:checked'
        );

        if (!tipo) {
            const primeraOpcion = configurator.querySelector(
                'input[name="tipo_etiqueta"]'
            );

            registrarError(
                primeraOpcion?.closest('.python-configurator__section'),
                'Selecciona el tipo de etiqueta.'
            );
        }

        // 2. MATERIAL
        const materialSection = configurator.querySelector('.python-material-section');
        const material = configurator.querySelector(
            'input[name="python_material"]:checked'
        );

        if (materialSection && !materialSection.hidden && !material) {
            registrarError(materialSection, 'Selecciona un material.');
        }

        // 3. ANCHO
        const widthSection = configurator.querySelector('.python-width-section');
        const ancho = configurator.querySelector('#python-ancho');

        if (
            widthSection &&
            !widthSection.hidden &&
            (!ancho || !ancho.value)
        ) {
            registrarError(widthSection, 'Selecciona el ancho de la etiqueta.');
        }

        // 4. LONGITUD
        const lengthSection = configurator.querySelector('.python-length-section');
        const longitud = configurator.querySelector('#python-longitud');

        if (
            lengthSection &&
            !lengthSection.hidden &&
            (!longitud || !longitud.value)
        ) {
            registrarError(lengthSection, 'Selecciona la longitud de la etiqueta.');
        }

        // 5. CANTIDAD O TALLAS
        if (tipo?.value === 'marca') {

            const quantitySection = configurator.querySelector('.python-quantity-section');
            const cantidad = Number(
                configurator.querySelector('#python-cantidad')?.value
            );

            if (
                !Number.isInteger(cantidad) ||
                cantidad < 100 ||
                cantidad % 100 !== 0
            ) {
                registrarError(
                    quantitySection,
                    'Ingresa una cantidad válida, en múltiplos de 100.'
                );
            }

        } else if (tipo?.value === 'marca_talla' || tipo?.value === 'talla') {

            const sizesSection = configurator.querySelector('.python-sizes-section');
            const filas = sizesList.querySelectorAll('.python-size-row');

            if (!filas.length) {

                registrarError(
                    sizesSection,
                    'Agrega al menos una talla.'
                );

            } else {

                filas.forEach(function (fila) {

                    const select = fila.querySelector('.python-size-select');
                    const personalizada = fila.querySelector('.python-size-custom');
                    const cantidadInput = fila.querySelector('.python-size-quantity');
                    const cantidad = Number(cantidadInput?.value);

                    if (
                        select?.value === 'Personalizada' &&
                        !personalizada?.value.trim()
                    ) {
                        registrarError(
                            fila,
                            'Escribe el nombre de la talla personalizada.'
                        );
                    }

                    if (
                        !Number.isInteger(cantidad) ||
                        cantidad < 100 ||
                        cantidad % 100 !== 0
                    ) {
                        registrarError(
                            fila,
                            'La cantidad debe ser múltiplo de 100.'
                        );
                    }

                });
                if (!validarTallasDuplicadas()) {
                    esValido = false;
                }
            }
        }

        // 6. DESPLAZAR AL PRIMER ERROR
        if (primerError) {
            primerError.scrollIntoView({
                behavior: 'smooth',
                block: 'center'
            });
        }

        return esValido;
    }

    //COMPROBAR DUPLICADOS DE TALLAS 

    function comprobarDuplicadoEnFila(fila) {

        const talla = obtenerNombreTalla(fila);

        if (!talla) return;

        const normalizar = valor => valor
            .normalize('NFKC')
            .toLocaleUpperCase('es-EC')
            .replace(/\s+/g, ' ')
            .trim();

        const duplicada = Array.from(
            sizesList.querySelectorAll('.python-size-row')
        ).some(function (otraFila) {
            return otraFila !== fila &&
                normalizar(obtenerNombreTalla(otraFila)) === normalizar(talla);
        });

        if (duplicada) {
            alert('La talla "' + talla + '" ya está agregada. Escoge otra talla o modifica su cantidad.');

            marcarError(
                fila,
                'Talla duplicada.'
            );
        }
    }

    sizesList.addEventListener('change', function (event) {

        if (event.target.matches('.python-size-select, .python-size-custom')) {
            comprobarDuplicadoEnFila(event.target.closest('.python-size-row'));
        }

    });

    sizesList.addEventListener('blur', function (event) {

        if (event.target.matches('.python-size-custom')) {
            comprobarDuplicadoEnFila(event.target.closest('.python-size-row'));
        }

    }, true);

    // =========================================
    // DATOS DE LA COMBINACIÓN
    // =========================================

    // Producto que está abierto actualmente
    let productoActualId = null;

    function obtenerDatosCombinacion() {

        const tipo = configurator.querySelector(
            'input[name="tipo_etiqueta"]:checked'
        )?.value || '';

        const material = configurator.querySelector(
            'input[name="python_material"]:checked'
        )?.value || '';

        const ancho = configurator.querySelector('#python-ancho')?.value || '';
        const longitud = configurator.querySelector('#python-longitud')?.value || '';

        const datos = {
            product_id: productoActualId,
            tipo: tipo,
            material: material,
            ancho: ancho,
            longitud: longitud,
            cantidad: 0,
            tallas: []
        };

        if (tipo === 'marca') {

            datos.cantidad = Number(
                configurator.querySelector('#python-cantidad').value
            );

        } else {

            sizesList.querySelectorAll('.python-size-row').forEach(function (fila) {

                const select = fila.querySelector('.python-size-select');
                const personalizada = fila.querySelector('.python-size-custom');

                const talla = select.value === 'Personalizada'
                    ? personalizada.value.trim()
                    : select.value;

                const cantidad = Number(
                    fila.querySelector('.python-size-quantity').value
                );

                datos.tallas.push({
                    talla: talla,
                    cantidad: cantidad
                });

                datos.cantidad += cantidad;
            });
        }

        return datos;
    }


    //evento del crear pedido
    
    
    addToOrderButton.addEventListener('click', async function () {

        if (!validarConfiguracion()) {
            return;
        }

        if (
            typeof pythonPedido === 'undefined' ||
            !pythonPedido.ajaxUrl ||
            !pythonPedido.nonce
        ) {
            alert('No se pudo conectar con el carrito. Recarga la página.');
            return;
        }

        const combinacion = obtenerDatosCombinacion();
        console.log('PRODUCTO ENVIADO:', combinacion.product_id);
        console.log('COMBINACIÓN ENVIADA:', combinacion);

        const textoOriginal = addToOrderButton.innerHTML;

        addToOrderButton.disabled = true;
        addToOrderButton.textContent = 'Agregando a mi pedido...';

        try {

            const formData = new FormData();

            formData.append('action', 'python_agregar_combinacion');
            formData.append('nonce', pythonPedido.nonce);

            // Enviar el objeto con la estructura que espera PHP.
            formData.append('combinacion[product_id]', combinacion.product_id);
            formData.append('combinacion[tipo]', combinacion.tipo);
            formData.append('combinacion[material]', combinacion.material);
            formData.append('combinacion[ancho]', combinacion.ancho);
            formData.append('combinacion[longitud]', combinacion.longitud);
            formData.append('combinacion[cantidad]', combinacion.cantidad);

            combinacion.tallas.forEach(function (fila, index) {
                formData.append(
                    `combinacion[tallas][${index}][talla]`,
                    fila.talla
                );

                formData.append(
                    `combinacion[tallas][${index}][cantidad]`,
                    fila.cantidad
                );
            });

            const respuesta = await fetch(pythonPedido.ajaxUrl, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            });

            const resultado = await respuesta.json();

            if (!respuesta.ok || !resultado.success) {
                throw new Error(
                    resultado.data?.message ||
                    'No se pudo agregar la combinación.'
                );
            }

            // Actualizar fragmentos del carrito si están disponibles.
            if (window.jQuery) {
                window.jQuery(document.body).trigger('wc_fragment_refresh');
            }

            alert(resultado.data.message);

            // Cerrar el configurador después de guardar.
            closeConfigurator();

            console.log('Combinación guardada:', resultado.data);
            console.log('CLAVE DEL CARRITO:', resultado.data.cart_item_key);
            console.log('URL DEL CARRITO:', resultado.data.cart_url);

        } catch (error) {

            console.error('Error al agregar combinación:', error);

            alert(error.message || 'Ocurrió un error al guardar el pedido.');

        } finally {

            addToOrderButton.disabled = false;
            addToOrderButton.innerHTML = textoOriginal;

        }

    });



    //configuracion cantidad de longitud etiqueta
    const cantidadInput = configurator.querySelector('#python-cantidad');
    cantidadInput.addEventListener('change', function () {

        const cantidad = Number(cantidadInput.value);

        // Mínimo 100 y únicamente múltiplos de 100
        if (!Number.isFinite(cantidad) || cantidad < 100) {
            cantidadInput.value = 100;
            return;
        }

        // Redondear hacia arriba al siguiente múltiplo de 100
        cantidadInput.value = Math.ceil(cantidad / 100) * 100;

    });


    
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
