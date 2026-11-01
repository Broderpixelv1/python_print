
document.addEventListener('DOMContentLoaded', function () {

    const $ = (selector, context = document) =>
        context.querySelector(selector);

    const $$ = (selector, context = document) =>
        Array.from(context.querySelectorAll(selector));

    const body = document.body;
    const header = $('.site-header');
    const mega = $('#mega-menu');
    const cortina = $('.cortina');

    const btnSearch = $('#btn-open-search');
    const searchOverlay = $('#search-overlay');

    if (!header || !mega) return;

    const originalMenu = $(':scope > ul', mega);

    let categoryNav = null;
    let panel = null;
    let panelContent = null;
    let activeButton = null;

    /* =================================================
       CREAR BARRA DE CATEGORÍAS
       ================================================= */

    function createMobileNavigation() {

        if (!originalMenu || categoryNav) return;

        categoryNav = document.createElement('nav');
        categoryNav.className = 'mobile-category-nav';
        categoryNav.setAttribute('aria-label', 'Categorías principales');

        const grid = $('.grid-container', header);

        if (grid) {
            grid.insertAdjacentElement('afterend', categoryNav);
        }

        const topItems = $$('.menu-item', originalMenu)
            .filter(item => item.parentElement === originalMenu);

        topItems.forEach(item => {

            const link = $(':scope > a', item);
            if (!link) return;

            const submenu = $(
                ':scope > .mega-menu-subcontainer, :scope > ul.sub-menu',
                item
            );

            if (submenu) {

                const button = document.createElement('button');

                button.type = 'button';
                button.className = 'mobile-category-button';
                button.textContent = link.textContent.trim();
                button.setAttribute('aria-expanded', 'false');

                button.addEventListener('click', function () {
                    openCategory(item, button);
                });

                categoryNav.appendChild(button);

            } else {

                const anchor = document.createElement('a');

                anchor.href = link.href;
                anchor.textContent = link.textContent.trim();

                categoryNav.appendChild(anchor);

            }

        });

    }

    /* =================================================
       CREAR ESTRUCTURA DEL PANEL
       ================================================= */

    function createPanel() {

        if (panel) return;

        panel = document.createElement('div');
        panel.className = 'mobile-menu-panel';

        const heading = document.createElement('div');
        heading.className = 'mobile-panel-heading';

        const headingText = document.createElement('div');

        const title = document.createElement('h2');
        title.className = 'mobile-panel-title';

        const subtitle = document.createElement('p');
        subtitle.textContent = 'Explora nuestras categorías y productos';

        headingText.appendChild(title);
        headingText.appendChild(subtitle);

        heading.appendChild(headingText);

        panelContent = document.createElement('div');
        panelContent.className = 'mobile-panel-content';

        panel.appendChild(heading);
        panel.appendChild(panelContent);

        mega.appendChild(panel);

    }

    /* =================================================
       ABRIR UNA CATEGORÍA
       ================================================= */

    function openCategory(item, button) {

        const submenu = $(
            ':scope > .mega-menu-subcontainer, :scope > ul.sub-menu',
            item
        );

        if (!submenu) return;

        createPanel();

        const link = $(':scope > a', item);
        const categoryName = link
            ? link.textContent.trim()
            : 'Categorías';

        $('.mobile-panel-title', panel).textContent = categoryName;

        panelContent.innerHTML = '';

        const copy = submenu.cloneNode(true);

        panelContent.appendChild(copy);

        /* Limpiar clases y estilos heredados del menú de escritorio */
        $$('.is-open', panelContent).forEach(element => {
            element.classList.remove('is-open');
        });

        $$('[style]', panelContent).forEach(element => {
            element.removeAttribute('style');
        });

        /* Mostrar todos los niveles de navegación */
        $$('.mega-menu-subcontainer, ul.sub-menu', panelContent)
            .forEach(element => {
                element.style.display = 'block';
                element.style.maxHeight = 'none';
                element.style.opacity = '1';
                element.style.visibility = 'visible';
            });

        if (activeButton) {
            activeButton.classList.remove('is-active');
            activeButton.setAttribute('aria-expanded', 'false');
        }

        activeButton = button;
        activeButton.classList.add('is-active');
        activeButton.setAttribute('aria-expanded', 'true');

        openMenu();

    }

    /* =================================================
       ABRIR Y CERRAR PANEL
       ================================================= */

    function openMenu() {

        body.classList.add('menu-open', 'no-scroll');

        cortina?.classList.add('is-active');

    }

    function closeMenu() {

        body.classList.remove('menu-open');

        cortina?.classList.remove('is-active');

        if (!body.classList.contains('search-open')) {
            body.classList.remove('no-scroll');
        }

        if (activeButton) {
            activeButton.classList.remove('is-active');
            activeButton.setAttribute('aria-expanded', 'false');
            activeButton = null;
        }

    }

    /* =================================================
       BOTÓN DE CERRAR
       ================================================= */

    $('.btn-close-menu', mega)?.addEventListener('click', function (event) {
        event.preventDefault();
        event.stopPropagation();
        closeMenu();
    });

    /* =================================================
       BUSCADOR
       ================================================= */

    function openSearch() {

        body.classList.add('search-open', 'no-scroll');
        btnSearch?.setAttribute('aria-expanded', 'true');
        cortina?.classList.add('is-active');

    }

    function closeSearch() {

        body.classList.remove('search-open');
        btnSearch?.setAttribute('aria-expanded', 'false');

        if (!body.classList.contains('menu-open')) {
            body.classList.remove('no-scroll');
            cortina?.classList.remove('is-active');
        }

    }

    btnSearch?.addEventListener('click', openSearch);

    $('.search-overlay .close-search')?.addEventListener(
        'click',
        closeSearch
    );

    /* =================================================
       CERRAR AL TOCAR LA CORTINA
       ================================================= */

    cortina?.addEventListener('click', function () {
        closeMenu();
        closeSearch();
    });

    /* =================================================
       CERRAR CON ESCAPE
       ================================================= */

    document.addEventListener('keydown', function (event) {

        if (event.key === 'Escape') {
            closeMenu();
            closeSearch();
        }

    });

    /* =================================================
       INICIALIZAR
       ================================================= */

    createMobileNavigation();

    /* =================================================
       CAMBIO ENTRE MÓVIL Y ESCRITORIO
       ================================================= */

    const mobileQuery = window.matchMedia('(max-width: 991px)');

    mobileQuery.addEventListener('change', function (event) {

        closeMenu();
        closeSearch();

        if (event.matches) {
            createMobileNavigation();
        }

    });

});
