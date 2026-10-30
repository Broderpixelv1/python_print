document.addEventListener('DOMContentLoaded', function(){
  const $  = (s, c=document) => c.querySelector(s);
  const $$ = (s, c=document) => Array.from(c.querySelectorAll(s));

  const body = document.body;
  const cortina = document.querySelector('.cortina');

  // Botones
  const btnMenu = $('#btn-open-menu');
  const btnSearch = $('#btn-open-search');

  // Contenedores
  const mega = document.querySelector('.mega-menu-container');
  const searchOverlay = $('#search-overlay');

  function openMenu(){
    body.classList.add('menu-open','no-scroll');
    btnMenu?.setAttribute('aria-expanded','true');
    cortina?.classList.add('is-active');
  }
  function closeMenu(){
    body.classList.remove('menu-open');
    btnMenu?.setAttribute('aria-expanded','false');
    if (!body.classList.contains('search-open')) {
      body.classList.remove('no-scroll');
      cortina?.classList.remove('is-active');
    }
  }
  function openSearch(){
    body.classList.add('search-open','no-scroll');
    btnSearch?.setAttribute('aria-expanded','true');
    cortina?.classList.add('is-active');
  }
  function closeSearch(){
    body.classList.remove('search-open');
    btnSearch?.setAttribute('aria-expanded','false');
    if (!body.classList.contains('menu-open')) {
      body.classList.remove('no-scroll');
      cortina?.classList.remove('is-active');
    }
  }

  btnMenu?.addEventListener('click', openMenu);
  btnSearch?.addEventListener('click', openSearch);

  // Cerrar con la X del panel de búsqueda
  $('.search-overlay .close-search')?.addEventListener('click', closeSearch);

  // Cerrar con ESC o clic en cortina
  document.addEventListener('keydown', (e)=>{ if(e.key==='Escape'){ closeMenu(); closeSearch(); }});
  cortina?.addEventListener('click', ()=>{ closeMenu(); closeSearch(); });

  // Cerrar el menú si tocan la "X" pseudo-elemento del panel
  // (capturamos cualquier click en el contenedor en la zona superior derecha)
  if (mega){
    mega.addEventListener('click', (e)=>{
      const rect = mega.getBoundingClientRect();
      // Si se hace click en el área de la "X" (16px de margen) usamos una heurística simple:
      const withinX = e.clientX > (rect.right - 56) && e.clientY < (rect.top + 80);
      if (withinX) closeMenu();
    });
  }

  // --- Acordeón dentro del mega menú SOLO en móvil/tablet ---

function hydrateAccordion() {
  if (!mega || !window.matchMedia('(max-width: 991px)').matches) return;

  const menuItems = $$('.menu-item-has-children', mega);

  menuItems.forEach(li => {
    // Evitar crear botones duplicados
    if (li.querySelector(':scope > .submenu-toggle')) return;

    const link = li.querySelector(':scope > a');

    // Primer nivel: mega-menu-subcontainer
    // Niveles internos: ul.sub-menu
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

    btn.addEventListener('click', () => {
      const isOpen = li.classList.toggle('is-open');

      btn.setAttribute('aria-expanded', String(isOpen));

      if (isOpen) {
        // Cerrar otros elementos abiertos del mismo nivel
        Array.from(li.parentElement.children).forEach(sibling => {
          if (
            sibling !== li &&
            sibling.classList.contains('menu-item-has-children')
          ) {
            closeAccordionItem(sibling);
          }
        });

        panel.style.maxHeight = panel.scrollHeight + 'px';
      } else {
        closeAccordionItem(li);
      }

      // Actualizar la altura de los paneles superiores abiertos
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

function closeAccordionItem(li) {
  li.classList.remove('is-open');

  const button = li.querySelector(':scope > .submenu-toggle');

  const panel =
    li.querySelector(':scope > .mega-menu-subcontainer') ||
    li.querySelector(':scope > ul.sub-menu');

  button?.setAttribute('aria-expanded', 'false');

  if (panel) {
    panel.style.maxHeight = '';
  }

  // Cerrar también los submenús internos
  li.querySelectorAll('.menu-item-has-children.is-open').forEach(child => {
    closeAccordionItem(child);
  });
}

  // Hidratamos acordeón al abrir menú
  btnMenu?.addEventListener('click', hydrateAccordion);

  // Si el usuario redimensiona a desktop, asegúrate de limpiar estados
  const mqDesktop = window.matchMedia('(min-width: 992px)');
  mqDesktop.addEventListener('change', (e)=>{
    if (e.matches){ closeMenu(); closeSearch(); }
  });
});
