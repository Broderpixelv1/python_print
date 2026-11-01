
document.addEventListener('DOMContentLoaded', function() {
  const $ = (s, c = document) => c.querySelector(s);
  const $$ = (s, c = document) => Array.from(c.querySelectorAll(s));

  const body = document.body;
  const cortina = document.querySelector('.cortina');

  // Botones
  const btnMenu = $('#btn-open-menu');
  const btnSearch = $('#btn-open-search');

  // Contenedores
  const mega = document.querySelector('.mega-menu-container');
  const searchOverlay = $('#search-overlay');

  function openMenu() {
    body.classList.add('menu-open', 'no-scroll');
    btnMenu?.setAttribute('aria-expanded', 'true');
    cortina?.classList.add('is-active');
    hydrateAccordion();
  }

  function closeMenu() {
    body.classList.remove('menu-open');
    btnMenu?.setAttribute('aria-expanded', 'false');

    if (!body.classList.contains('search-open')) {
      body.classList.remove('no-scroll');
      cortina?.classList.remove('is-active');
    }
  }

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

  // Botones principales
  btnMenu?.addEventListener('click', () => {
    if (body.classList.contains('menu-open')) {
      closeMenu();
    } else {
      openMenu();
    }
  });
  
  $('#mega-menu .btn-close-menu')?.addEventListener('click', (e) => {
    e.stopPropagation();
    closeMenu();
});

  //boton de cierre menu principal
  $('.btn-close-menu')?.addEventListener('click', (e) => {
    e.stopPropagation();
    closeMenu();
});

  btnSearch?.addEventListener('click', openSearch);

  $('.search-overlay .close-search')?.addEventListener('click', closeSearch);

  // Cerrar con Escape o cortina
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
      closeMenu();
      closeSearch();
    }
  });

  cortina?.addEventListener('click', () => {
    closeMenu();
    closeSearch();
  });

  // Función para cerrar un elemento del acordeón
  function closeAccordionItem(li) {
    li.classList.remove('is-open');

    const button = li.querySelector(':scope > .submenu-toggle');

    const panel =
      li.querySelector(':scope > .mega-menu-subcontainer') ||
      li.querySelector(':scope > ul.sub-menu');

    button?.setAttribute('aria-expanded', 'false');

    if (panel) {
      panel.style.maxHeight = '0px';
    }

    li.querySelectorAll('.menu-item-has-children.is-open').forEach(child => {
      closeAccordionItem(child);
    });
  }

  // Crear acordeón móvil
  function hydrateAccordion() {
    if (!mega || !window.matchMedia('(max-width: 991px)').matches) return;

    const menuItems = $$('.menu-item-has-children', mega);

    menuItems.forEach(li => {
      if (li.querySelector(':scope > .submenu-toggle')) return;

      const link = li.querySelector(':scope > a');

      const panel =
        li.querySelector(':scope > .mega-menu-subcontainer') ||
        li.querySelector(':scope > ul.sub-menu');

      if (!link || !panel) return;

      const btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'submenu-toggle';
      btn.setAttribute('aria-expanded', 'false');
      btn.setAttribute('aria-label', 'Desplegar submenú');
      btn.innerHTML = '<i class="fa-solid fa-chevron-down"></i>';

      link.after(btn);

      // Estado inicial cerrado
      panel.style.maxHeight = '0px';

      btn.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();

        const isOpen = !li.classList.contains('is-open');

        // Cerrar hermanos del mismo nivel
        Array.from(li.parentElement.children).forEach(sibling => {
          if (
            sibling !== li &&
            sibling.classList.contains('menu-item-has-children')
          ) {
            closeAccordionItem(sibling);
          }
        });

        if (isOpen) {
          li.classList.add('is-open');
          btn.setAttribute('aria-expanded', 'true');
          panel.style.maxHeight = panel.scrollHeight + 'px';
        } else {
          closeAccordionItem(li);
        }

        // Actualizar la altura de los paneles superiores
        let parent = li.parentElement.closest('.is-open');

        while (parent) {
          const parentPanel =
            parent.querySelector(':scope > .mega-menu-subcontainer') ||
            parent.querySelector(':scope > ul.sub-menu');

          if (parentPanel) {
            parentPanel.style.maxHeight = parentPanel.scrollHeight + 'px';
          }

          parent = parent.parentElement.closest('.is-open');
        }
      });
    });
  }

  // Limpiar estados al cambiar a escritorio
  const mqDesktop = window.matchMedia('(min-width: 992px)');

  mqDesktop.addEventListener('change', (e) => {
    if (e.matches) {
      closeMenu();
      closeSearch();

      if (mega) {
        mega.querySelectorAll('.submenu-toggle').forEach(btn => btn.remove());

        mega.querySelectorAll('.is-open').forEach(item => {
          item.classList.remove('is-open');
        });

        mega.querySelectorAll('.mega-menu-subcontainer, ul.sub-menu').forEach(panel => {
          panel.style.maxHeight = '';
        });
      }
    }
  });

});
