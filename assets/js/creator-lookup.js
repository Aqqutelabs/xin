(() => {
 'use strict';
 const form = document.getElementById('handle-form');
 if (!form) return;
 const handle = document.getElementById('lookup-handle');
 const button = form.querySelector('button');
 const status = document.getElementById('lookup-status');
 const preview = document.getElementById('lookup-preview');
 const calculator = document.getElementById('creator-form');
 let request = null;
 let profile = null;
 const track = action => document.dispatchEvent(new CustomEvent('xinng:creator', {detail:{action}}));
 const cancel = () => {
  request?.abort(); request = null; profile = null; preview.hidden = true; button.disabled = false; status.textContent = '';
 };
 button.disabled = false;
 handle.addEventListener('input', cancel);
 document.getElementById('lookup-manual').addEventListener('click', () => {
  cancel(); track('manual_mode_started'); calculator.elements.namedItem('followers').focus({preventScroll:true});
 });
 form.addEventListener('submit', async event => {
  event.preventDefault(); if (!form.reportValidity()) return;
  cancel();
  const current = new AbortController(); request = current;
  const timeout = setTimeout(() => current.abort(), 15000);
  button.disabled = true; status.textContent = 'Looking up public follower counts…'; track('handle_entered');
  try {
   const response = await fetch(form.dataset.endpoint, {method:'POST', headers:{'Content-Type':'application/json','X-CSRF-Token':form.dataset.csrf}, body:JSON.stringify({handle:handle.value.trim()}), signal:current.signal});
   const data = await response.json();
   if (request !== current) return;
   if (!response.ok) throw new Error(data.error || 'Lookup failed. Please use manual entry.');
   profile = data.profile;
   document.getElementById('lookup-name').textContent = `${profile.display_name} (@${profile.handle})`;
   document.getElementById('lookup-counts').textContent = `${profile.followers.toLocaleString('en')} followers. ` + (profile.verified_followers === null ? 'Verified-follower count unavailable; the labelled Xinng estimate will be used.' : `${profile.verified_followers.toLocaleString('en')} verified followers.`) + ' Source: X API public profile.';
   preview.hidden = false; status.textContent = 'Review the numbers before applying them.'; track('handle_lookup_success');
  } catch (error) {
   if (request === current) { status.textContent = current.signal.aborted ? 'Lookup timed out. You can enter your numbers manually.' : error.message; track('handle_lookup_failed'); }
  } finally { clearTimeout(timeout); if (request === current) { request = null; button.disabled = false; } }
 });
 document.getElementById('lookup-apply').addEventListener('click', () => {
  if (!profile) return;
  const set = (name, value) => { const input = calculator.elements.namedItem(name); input.value = String(value); input.dispatchEvent(new Event('input', {bubbles:true})); };
  const unknown = calculator.elements.namedItem('verified_unknown');
  unknown.checked = profile.verified_followers === null;
  unknown.dispatchEvent(new Event('input', {bubbles:true}));
  set('followers', profile.followers);
  if (profile.verified_followers !== null) set('verified_followers', profile.verified_followers);
  set('avg_impressions_per_post', '');
  set('gross_impressions_90d', '');
  // Offer the handle for sharing without opting the user into publishing it.
  const shareHandle = document.getElementById('share-handle');
  if (shareHandle) { shareHandle.value = '@' + profile.handle; shareHandle.dispatchEvent(new Event('input', {bubbles:true})); }
  preview.hidden = true; profile = null;
  status.textContent = 'Numbers applied. Review the editable inputs below.'; track('handle_values_applied');
  calculator.elements.namedItem('followers').focus();
 });
})();
