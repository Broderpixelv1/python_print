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
  function hydrateAccordion(){
    if (!mega) return;
    const menuRoot = mega.querySelector('.menu, nav .menu, .overlay-menu') || mega;
    if (!menuRoot) return;

    $$('.menu-item-has-children', menuRoot).forEach(li=>{
      if (li.querySelector('.submenu-toggle')) return; // evitar duplicados
      const a = li.querySelector(':scope > a');
      const ul = li.querySelector(':scope > ul');
      if (!a || !ul) return;

      const btn = document.createElement('button');
      btn.className = 'submenu-toggle';
      btn.setAttribute('aria-expanded','false');
      btn.setAttribute('aria-label','<?php echo esc_js(__('Desplegar submenú', 'mi-tema-personalizable')); ?>');
      btn.innerHTML = '<i class="fa-solid fa-chevron-down"></i>';
      a.after(btn);

      // Estado inicial cerrado
      ul.style.maxHeight = '';

      btn.addEventListener('click', ()=>{
        const isOpen = li.classList.toggle('is-open');
        btn.setAttribute('aria-expanded', String(isOpen));
        ul.style.maxHeight = isOpen ? (ul.scrollHeight + 'px') : '';
        // Acordeón exclusivo: cerramos hermanos
        const siblings = Array.from(li.parentElement.children).filter(n=>n!==li && n.classList.contains('menu-item-has-children') && n.classList.contains('is-open'));
        siblings.forEach(s=>{
          s.classList.remove('is-open');
          const sull = s.querySelector(':scope > ul');
          const sbtn = s.querySelector(':scope > .submenu-toggle');
          if (sull) sull.style.maxHeight = '';
          if (sbtn) sbtn.setAttribute('aria-expanded','false');
        });
      });
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
