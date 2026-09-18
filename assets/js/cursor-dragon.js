(() => {
 'use strict';
 const dragon = document.querySelector('[data-cursor-dragon]');
 if (dragon) {
  const portrait = dragon.querySelector('img');
  const base = new URL('.', portrait.src).href;
  const motion = window.matchMedia('(prefers-reduced-motion: reduce)');
  const frames = new Map();
  let desired = 'stare';
  let visible = false;
  let pointer = null;
  let pending = 0;
  const show = name => {
   desired = name;
   const frame = frames.get(name);
   if (frame?.complete && frame.naturalWidth && portrait.getAttribute('src') !== base + name + '.png') portrait.src = frame.src;
  };
  const preload = () => {
   if (frames.size) return;
   ['stare', '0', '45', '90', '135', '180', '225', '270', '315'].forEach(name => {
    const frame = new Image();
    frames.set(name, frame);
    frame.onload = () => { if (desired === name) show(name); };
    frame.src = base + name + '.png';
   });
  };
  const update = () => {
   pending = 0;
   if (!visible || !pointer || motion.matches) { show('stare'); return; }
   const bounds = dragon.getBoundingClientRect();
   const {x, y} = pointer;
   if (x >= bounds.left && x <= bounds.right && y >= bounds.top && y <= bounds.bottom) { show('stare'); return; }
   // Supplied portraits run clockwise: 0 looks up, 90 looks right.
   const angle = (Math.atan2(x - (bounds.left + bounds.width / 2), (bounds.top + bounds.height / 2) - y) * 180 / Math.PI + 360) % 360;
   show(String((Math.round(angle / 45) * 45) % 360));
  };
  const schedule = () => { if (!pending) pending = requestAnimationFrame(update); };
  document.addEventListener('pointermove', event => {
   if (event.pointerType === 'touch') return;
   pointer = {x: event.clientX, y: event.clientY};
   if (visible) schedule();
  }, {passive: true});
  const reset = () => { pointer = null; schedule(); };
  document.documentElement.addEventListener('pointerleave', reset);
  window.addEventListener('blur', reset);
  window.addEventListener('scroll', () => { if (visible) schedule(); }, {passive: true});
  window.addEventListener('resize', schedule);
  motion.addEventListener('change', schedule);
  new IntersectionObserver(entries => {
   visible = entries[0].isIntersecting;
   if (visible) preload();
   schedule();
  }, {rootMargin: '200px'}).observe(dragon);
 }
})();
