(() => {
 'use strict';
 const menu = document.querySelector('.menu-toggle');
 const nav = document.getElementById('main-nav');
 const dropdowns = [...document.querySelectorAll('.product-menu')];
 const closeMenu = () => { menu.setAttribute('aria-expanded', 'false'); nav.classList.remove('is-open'); };
 menu.addEventListener('click', () => { const open = menu.getAttribute('aria-expanded') !== 'true'; menu.setAttribute('aria-expanded', String(open)); nav.classList.toggle('is-open', open); });
 nav.addEventListener('click', event => { if (event.target.closest('a')) { closeMenu(); dropdowns.forEach(item => { item.open = false; }); } });
 document.addEventListener('keydown', event => {
  if (event.key !== 'Escape') return;
  const openDropdown = dropdowns.find(item => item.open);
  if (openDropdown) { openDropdown.open = false; openDropdown.querySelector('summary').focus(); }
  else if (nav.classList.contains('is-open')) { closeMenu(); menu.focus(); }
 });
 document.addEventListener('click', event => {
  dropdowns.forEach(item => { if (!item.contains(event.target)) item.open = false; });
  if (!event.target.closest('.site-header')) closeMenu();
 });
 window.matchMedia('(min-width: 801px)').addEventListener('change', closeMenu);
 // A local event hook: an analytics provider can subscribe without collecting form data.
 const track = (category, action) => document.dispatchEvent(new CustomEvent('xinng:homepage', {detail: {category, action}}));
 document.querySelectorAll('[data-track]').forEach(link => link.addEventListener('click', () => track(link.dataset.track, 'click')));
 document.querySelectorAll('[data-track-form]').forEach(form => form.addEventListener('submit', () => track(form.dataset.trackForm, 'submit')));
 document.querySelectorAll('.faq-list details').forEach(item => item.addEventListener('toggle', () => { if (item.open) track('faq', 'open'); }));
 const slider = document.querySelector('.pages-slider');
 if (slider) {
  const slides = [...slider.querySelectorAll('.pages-slide')];
  const controls = slider.querySelector('.pages-slider-controls');
  const status = slider.querySelector('.pages-slider-status');
  const play = slider.querySelector('[data-slide-play]');
  const motion = window.matchMedia('(prefers-reduced-motion: reduce)');
  const names = slides.map(slide => slide.dataset.title);
  let active = 0;
  let paused = motion.matches;
  let hovered = false;
  let visible = false;
  let timer;
  const render = (announce = false) => {
   slides.forEach((slide, index) => {
    let slot = (index - active + slides.length) % slides.length;
    if (slot > Math.floor((slides.length - 1) / 2)) slot -= slides.length;
    slide.dataset.slot = slot;
    slide.tabIndex = slot === 0 ? 0 : -1;
    slide.setAttribute('aria-hidden', String(slot !== 0));
   });
   status.setAttribute('aria-live', announce ? 'polite' : 'off');
   status.replaceChildren(document.createTextNode(names[active] + ' '));
   const count = document.createElement('span');
   count.textContent = `${String(active + 1).padStart(2, '0')} / ${String(slides.length).padStart(2, '0')}`;
   status.append(count);
  };
  const schedule = () => {
   clearTimeout(timer);
   if (paused || hovered || !visible || document.hidden || slider.contains(document.activeElement)) return;
   timer = setTimeout(() => { active = (active + 1) % slides.length; render(); schedule(); }, 4500);
  };
  const move = direction => { active = (active + direction + slides.length) % slides.length; render(true); schedule(); };
  const updatePlay = () => {
   play.textContent = paused ? 'Play' : 'Pause';
   play.setAttribute('aria-label', paused ? 'Play slideshow' : 'Pause slideshow');
   schedule();
  };
  controls.hidden = false;
  slider.querySelector('[data-slide-prev]').addEventListener('click', () => move(-1));
  slider.querySelector('[data-slide-next]').addEventListener('click', () => move(1));
  play.addEventListener('click', () => { paused = !paused; updatePlay(); });
  slider.addEventListener('mouseenter', () => { hovered = true; schedule(); });
  slider.addEventListener('mouseleave', () => { hovered = false; schedule(); });
  slider.addEventListener('focusin', schedule);
  slider.addEventListener('focusout', () => setTimeout(schedule, 0));
  slider.addEventListener('keydown', event => {
   if (!['ArrowLeft', 'ArrowRight'].includes(event.key)) return;
   event.preventDefault();
   const focusedSlide = event.target.closest('.pages-slide');
   move(event.key === 'ArrowRight' ? 1 : -1);
   if (focusedSlide) slides[active].focus({preventScroll: true});
  });
  let touchStart;
  let swiped = false;
  const stage = slider.querySelector('.pages-slider-stage');
  stage.addEventListener('touchstart', event => { const touch = event.touches[0]; touchStart = {x: touch.clientX, y: touch.clientY}; swiped = false; }, {passive: true});
  stage.addEventListener('touchend', event => {
   if (!touchStart) return;
   const touch = event.changedTouches[0];
   const dx = touch.clientX - touchStart.x;
   const dy = touch.clientY - touchStart.y;
   if (Math.abs(dx) > 45 && Math.abs(dx) > Math.abs(dy)) { swiped = true; move(dx < 0 ? 1 : -1); }
   touchStart = null;
  }, {passive: true});
  stage.addEventListener('touchcancel', () => { touchStart = null; });
  stage.addEventListener('click', event => { if (swiped) { event.preventDefault(); swiped = false; } }, true);
  document.addEventListener('visibilitychange', schedule);
  motion.addEventListener('change', () => { paused = motion.matches; updatePlay(); });
  new IntersectionObserver(entries => { visible = entries[0].isIntersecting; schedule(); }, {threshold: .25}).observe(slider);
  updatePlay();
 }
 const form = document.querySelector('[data-claim]');
 if (!form) return;
 const input = document.getElementById('home-slug');
 const status = document.getElementById('claim-status');
 let controller;
 let checking = false;
 const showStatus = (message, error = false) => { status.textContent = message; status.classList.toggle('error', error); };
 input.addEventListener('input', () => { controller?.abort(); checking = false; input.removeAttribute('aria-invalid'); showStatus('Use 3–64 letters, numbers, hyphens or underscores.'); });
 form.addEventListener('submit', async event => {
  event.preventDefault();
  if (checking || !form.reportValidity()) return;
  const slug = input.value.trim().toLowerCase();
  input.value = slug;
  if (JSON.parse(form.dataset.reserved || '[]').includes(slug)) {
   input.setAttribute('aria-invalid', 'true');
   showStatus('That address is reserved. Try your name or business name.', true);
   input.focus();
   return;
  }
  checking = true;
  controller = new AbortController();
  const activeController = controller;
  const timeout = setTimeout(() => activeController.abort(), 8000);
  showStatus('Checking your address…');
  track('pages', 'address_check');
  try {
   const response = await fetch('check_slug_post.php', {method: 'POST', body: new URLSearchParams({slug}), signal: activeController.signal, headers: {'Accept': 'application/json'}});
   if (!response.ok) throw new Error('unavailable');
   const result = await response.json();
   if (input.value !== slug) return;
   if (result.ok && result.available) {
    showStatus('Available! Taking you to signup…');
    track('pages', 'registration_start');
    HTMLFormElement.prototype.submit.call(form);
   } else if (['db', 'rate_limited'].includes(result.error)) {
    showStatus('We’ll check availability during signup. Taking you there…');
    HTMLFormElement.prototype.submit.call(form);
   } else {
    input.setAttribute('aria-invalid', 'true');
    showStatus((result.error || 'That address is unavailable.') + ' Try adding your name, location or “studio”.', true);
    input.focus();
   }
  } catch (error) {
   if (controller !== activeController || input.value !== slug) return;
   showStatus('We’ll check availability during signup. Taking you there…');
   HTMLFormElement.prototype.submit.call(form);
  } finally {
   clearTimeout(timeout);
   if (controller === activeController) checking = false;
  }
 });
})();
