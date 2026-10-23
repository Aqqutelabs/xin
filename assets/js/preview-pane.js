(() => {
  const pane = document.querySelector('[data-preview-pane]');
  if (!pane) return;

  const headerActions = document.querySelector('.main-header .header-actions, .builder-topbar .builder-actions');
  if (!headerActions) return;
  const header = headerActions.closest('.main-header, .builder-topbar');

  if (!pane.id) pane.id = 'workspace-preview-pane';
  const label = pane.dataset.previewLabel || 'preview';
  const narrow = window.matchMedia(`(max-width: ${Number(pane.dataset.previewBreakpoint || 1180)}px)`);

  const close = document.createElement('button');
  close.type = 'button';
  close.className = 'preview-pane-close';
  close.setAttribute('aria-label', `Close ${label}`);
  close.setAttribute('aria-controls', pane.id);
  close.innerHTML = '<i class="fa-solid fa-xmark" aria-hidden="true"></i>';
  pane.appendChild(close);

  const backdrop = document.createElement('button');
  backdrop.type = 'button';
  backdrop.className = 'preview-pane-backdrop';
  backdrop.setAttribute('aria-label', `Close ${label}`);
  document.body.appendChild(backdrop);

  let desktopOpen = pane.dataset.previewDefaultOpen === 'true';
  let mobileOpen = false;
  const updateTop = () => {
    const top = narrow.matches ? 0 : Math.max(0, Math.round(header?.getBoundingClientRect().bottom || 0));
    document.body.style.setProperty('--preview-top', `${top}px`);
  };
  const render = (focusControl = false) => {
    updateTop();
    const open = narrow.matches ? mobileOpen : desktopOpen;
    document.body.classList.toggle('preview-pane-desktop-layout', !narrow.matches);
    document.body.classList.toggle('preview-pane-closed', !narrow.matches && !desktopOpen);
    document.body.classList.toggle('preview-pane-mobile-open', narrow.matches && mobileOpen);
    pane.setAttribute('aria-hidden', open ? 'false' : 'true');
    backdrop.hidden = !(narrow.matches && mobileOpen);
    close.setAttribute('aria-expanded', open ? 'true' : 'false');
    if (focusControl && open) close.focus();
  };
  const setOpen = (open, focusControl = false) => {
    if (narrow.matches) mobileOpen = open;
    else {
      desktopOpen = open;
    }
    render(focusControl);
  };
  render();
  close.addEventListener('click', () => setOpen(false));
  backdrop.addEventListener('click', () => setOpen(false));
  document.addEventListener('xinng:preview-open', () => setOpen(true));
  document.addEventListener('click', event => {
    if (event.target.closest('[data-preview-open], .qr-card, .page-list-card, .link-card, .event-row')) setOpen(true);
  });
  document.addEventListener('input', event => {
    if (event.target.closest('.builder-workspace, .ai-content')) setOpen(true);
  });
  document.addEventListener('change', event => {
    if (event.target.closest('.builder-workspace, .ai-content, [data-preview-open]')) setOpen(true);
  });
  document.addEventListener('keydown', event => {
    if ((event.key === 'Enter' || event.key === ' ') && event.target.closest('[data-preview-open], .event-row')) setOpen(true);
    if (event.key === 'Escape' && narrow.matches && mobileOpen) setOpen(false);
  });
  narrow.addEventListener('change', () => { mobileOpen = false; render(); });
  window.addEventListener('resize', updateTop);
  window.addEventListener('scroll', updateTop, true);
})();
