(() => {
 'use strict';
 const panel = document.querySelector('[data-share-base]');
 if (!panel) return;
 const base = panel.dataset.shareBase;
 const canvas = document.getElementById('share-canvas');
 const status = document.getElementById('share-status');
 const download = document.getElementById('share-download');
 const initial = document.getElementById('share-initial-inputs');
 let inputs = initial ? JSON.parse(initial.textContent) : null;
 let generation = 0;
 let blob = null;
 let resultUrl = null;
 const loadImage = src => new Promise((resolve, reject) => {
  const image = new Image(); image.onload = () => resolve(image); image.onerror = () => reject(new Error('The brand images could not load. Please try again.')); image.src = src;
 });
 let assets;
 const getAssets = () => {
  if (!assets) assets = Promise.all([loadImage(base + '/assets/images/logo.svg'), loadImage(base + '/assets/images/drag-xinng/stare.png')]).catch(error => { assets = null; throw error; });
  return assets;
 };
 const draw = async card => {
  const [, dragon] = await getAssets();
  const cardContent = card.outcome || {headline: card.rows[0].label, value: card.rows[0].value};
  const surface = window.CreatorCard.draw({...cardContent, handle: card.handle || ''}, dragon);
  const png = await new Promise(resolve => surface.toBlob(resolve, 'image/png'));
  if (!png) throw new Error('Could not create the PNG. Please try again.');
  return {surface, png};
 };
 const display = rendered => {
  canvas.width = rendered.surface.width; canvas.height = rendered.surface.height;
  canvas.getContext('2d').drawImage(rendered.surface, 0, 0);
  canvas.hidden = false; blob = rendered.png; download.disabled = false;
 };
 download.addEventListener('click', () => {
  if (!blob) return;
  const url = URL.createObjectURL(blob); const link = document.createElement('a');
  link.href = url; link.download = 'xinng-creator-snapshot.png'; link.click();
  setTimeout(() => URL.revokeObjectURL(url), 10000);
 });
 const publicData = document.getElementById('public-share-data');
 if (publicData) {
  draw(JSON.parse(publicData.textContent)).then(display).catch(error => { status.textContent = error.message; });
  return;
 }
 const create = document.getElementById('share-create');
 const copy = document.getElementById('share-copy');
 const xLink = document.getElementById('share-x');
 const actions = document.getElementById('share-actions');
 const invalidate = () => {
  actions.hidden = true;
  generation++; blob = null; resultUrl = null; canvas.hidden = true; download.disabled = true; copy.disabled = true; xLink.hidden = true; xLink.removeAttribute('href');
  document.getElementById('share-link-wrap').hidden = true;
  document.getElementById('share-link').value = '';
  create.disabled = !inputs;
  status.textContent = '';
 };
 create.disabled = !inputs;
 document.addEventListener('creator:invalidated', () => { inputs = null; invalidate(); status.textContent = 'Update your calculation before creating a new card.'; });
 document.addEventListener('creator:calculated', event => { inputs = event.detail.inputs; panel.hidden = false; invalidate(); });
 create.addEventListener('click', async () => {
  if (!inputs) return;
  const handle = String(inputs.handle || '').trim();
  if (!/^@?[A-Za-z0-9_]{1,15}$/.test(handle)) {
   status.textContent = 'Enter your X handle above to create a share card.';
   document.getElementById('creator-handle').focus();
   return;
  }
  const options = {handle: Boolean(handle), earnings: true, progress: true, timeline: true};
  const current = ++generation;
  create.disabled = true; status.textContent = 'Creating your public snapshot…';
  try {
   // Check required artwork before creating a persistent public link.
   await getAssets();
   if (current !== generation) return;
   const response = await fetch(base + '/tools/creator-calculator/x/share.php', {method:'POST', headers:{'Content-Type':'application/json','X-CSRF-Token':panel.dataset.shareCsrf}, body:JSON.stringify({inputs, options, handle})});
   const data = await response.json();
   if (!response.ok) throw new Error(data.error || 'Could not create the card. Please try again.');
   if (current !== generation) return;
   resultUrl = new URL(base + '/tools/creator-calculator/x/r/' + data.token, location.origin).href;
   document.getElementById('share-link').value = resultUrl;
   document.getElementById('share-link-wrap').hidden = false;
   copy.disabled = false; actions.hidden = false;
   xLink.href = 'https://twitter.com/intent/tweet?' + new URLSearchParams({text:'My X creator result.',url:resultUrl}); xLink.hidden = false;
   const rendered = await draw(data.card);
   if (current !== generation) return;
   display(rendered); status.textContent = 'Ready to share.';
  } catch (error) { if (current === generation) status.textContent = resultUrl ? 'Your public link is ready, but the image could not render. You can still copy the link.' : error.message; }
  finally { if (current === generation) create.disabled = !inputs; }
 });
 copy.addEventListener('click', async () => {
  if (!resultUrl) return;
  try { await navigator.clipboard.writeText(resultUrl); status.textContent = 'Result link copied.'; }
  catch { const input = document.getElementById('share-link'); input.focus(); input.select(); status.textContent = 'Copy the selected result link.'; }
 });
})();
