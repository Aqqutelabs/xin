(() => {
 'use strict';
 const form = document.getElementById('creator-form');
 if (!form) return;
 const rules = JSON.parse(document.getElementById('calculator-rules').textContent);
 const field = name => form.elements.namedItem(name);
 const followers = field('followers');
 const average = field('avg_impressions_per_post');
 const verified = field('verified_followers');
 const posts = field('posts_per_week');
 const status = document.getElementById('form-status');
 const submit = form.querySelector('[type=submit]');
 const number = value => new Intl.NumberFormat('en', {maximumFractionDigits: 0}).format(value);
 let autoAverage = average.value === '';
 const defaultVerifiedRatio = Number(form.dataset.verifiedRatio);
 let trackedVerifiedRatio = followers.value !== '' && Number(followers.value) > 0 && verified.value !== ''
  ? Math.min(1, Math.max(0, Number(verified.value) / Number(followers.value))) : defaultVerifiedRatio;
 let controller;
 let debounce;
 let hasResult = !document.getElementById('calculated-result').hidden;
 const money = value => new Intl.NumberFormat('en-US', {style:'currency',currency:'USD'}).format(value);
 const refresh = () => {
  if (autoAverage) average.value = followers.value === '' ? '' : String(Number((Number(followers.value) * Number(form.dataset.defaultAverage)).toFixed(2)));
  const ratio = Number(form.dataset.verifiedRatio);
  document.getElementById('verified-help').textContent = verified.value === ''
   ? `Using ${ratio * 100}% of followers (${number(Math.floor(Number(followers.value) * ratio))}) as a Xinng estimate.`
   : followers.value !== '' && Number(followers.value) > 0 && verified.value !== ''
    ? `${(Number(verified.value) / Number(followers.value) * 100).toFixed(1)}% verified followers.`
    : 'Use your verified follower count, if known.';
 };
 const invalidate = () => {
  document.dispatchEvent(new CustomEvent('creator:invalidated'));
  clearTimeout(debounce);
  controller?.abort();
  controller = null;
  submit.disabled = false;
  form.removeAttribute('aria-busy');
  if (!document.getElementById('calculated-result').hidden) status.textContent = 'Numbers changed. Check your progress again to update the result.';
  else status.textContent = '';
 };
 form.addEventListener('input', event => {
  invalidate();
  if (event.target === verified && verified.value !== '' && verified.validity.valid && Number(followers.value) > 0) {
   trackedVerifiedRatio = Math.min(1, Number(verified.value) / Number(followers.value));
  }
  if (event.target === followers && (followers.value === '' || followers.validity.valid)) {
   const ratio = verified.value === '' ? defaultVerifiedRatio : trackedVerifiedRatio;
   verified.value = followers.value === '' ? '' : String(Math.floor(Number(followers.value) * ratio));
   verified.removeAttribute('aria-invalid');
   document.getElementById('error-verified_followers').textContent = '';
  }
  if (event.target === average) autoAverage = average.value === '';
  event.target.removeAttribute('aria-invalid');
  const error = document.getElementById(`error-${event.target.name}`);
  if (error) error.textContent = '';
  refresh();
  if (hasResult) debounce = setTimeout(() => { if (form.checkValidity()) calculate(false); else status.textContent = 'Complete the highlighted account inputs to update your estimate.'; }, 200);
 });
 const render = result => {
  hasResult = true;
  document.getElementById('empty-result').hidden = true;
  document.getElementById('calculated-result').hidden = false;
  const revenue = result.revenue;
  document.getElementById('earnings-title').textContent = revenue.title;
  document.getElementById('earnings-monthly').textContent = result.qualification_status === 'thresholds_met' ? `${money(revenue.monthly.low)} - ${money(revenue.monthly.high)}` : '$0';
  document.getElementById('impression-targets').hidden = result.qualified_impressions_pass;
  ['verified_time', 'impression_time'].forEach(key => { document.getElementById(key.replaceAll('_', '-')).textContent = result.progress_display.growth_stats[key]; });
  document.getElementById('threshold-summary').textContent = result.progress_display.threshold_summary;
  document.getElementById('result-heading').textContent = result.qualification_status === 'thresholds_met' ? 'You appear to meet the measurable thresholds.' : "You're not there yet. Let's see the gaps.";
  document.getElementById('progress-value').textContent = `${Math.floor(result.qualification_progress)}%`;
  const metrics = [
   ['followers', result.verified_followers, rules.verified_followers_required, result.verified_followers_gap, result.verified_followers_pass],
   ['impressions', result.estimated_qualified_impressions_90d, rules.qualified_impressions_required, result.qualified_impressions_gap, result.qualified_impressions_pass],
  ];
  metrics.forEach(([id, value, target, gap, passed]) => {
   document.getElementById(`${id}-value`).textContent = number(value);
   document.getElementById(`${id}-progress`).value = Math.min(target, value);
   document.getElementById(`${id}-state`).textContent = passed ? 'Threshold met' : 'Below threshold';
   document.getElementById(`${id}-gap`).textContent = passed ? '' : `${number(Math.ceil(gap))} more needed.`;
  });

 };
 const calculate = async (focusResult) => {
  clearTimeout(debounce);
  if (!form.checkValidity()) return;
  controller?.abort();
  const request = new AbortController();
  controller = request;
  const timeout = setTimeout(() => request.abort(), 15000);
  submit.disabled = true;
  form.setAttribute('aria-busy', 'true');
  status.textContent = 'Checking your progress…';
  form.querySelectorAll('.field-error').forEach(item => { item.textContent = ''; });
  form.querySelectorAll('[aria-invalid]').forEach(item => item.removeAttribute('aria-invalid'));
  try {
   const response = await fetch(form.action, {method: 'POST', body: new FormData(form), headers: {Accept: 'application/json'}, signal: request.signal});
   if (![200, 422].includes(response.status)) throw new Error('request');
   const data = await response.json();
   if (controller !== request) return;
   if (response.status === 422) {
    Object.entries(data.errors).forEach(([name, message]) => {
     const input = field(name);
     if (!input) return;
     input.setAttribute('aria-invalid', 'true');
     document.getElementById(`error-${name}`).textContent = message;
     const details = input.closest('details');
     if (details) details.open = true;
    });
    status.textContent = 'Please check the highlighted fields.';
    if (focusResult) form.querySelector('[aria-invalid=true]')?.focus();
    return;
   }
   render(data.result);
   document.dispatchEvent(new CustomEvent('creator:calculated', {detail: {inputs: Object.fromEntries(new FormData(form))}}));
   status.textContent = 'Your progress is ready.';
   if (focusResult) document.getElementById('result').focus({preventScroll: true});
   if (focusResult && window.matchMedia('(max-width: 650px)').matches) document.getElementById('result').scrollIntoView({block: 'start'});
  } catch {
   if (controller === request) status.textContent = 'We could not complete the calculation. Please try again.';
  } finally {
   clearTimeout(timeout);
   if (controller === request) { controller = null; submit.disabled = false; form.removeAttribute('aria-busy'); }
  }
 };
 form.addEventListener('submit', event => { event.preventDefault(); if (form.reportValidity()) calculate(true); });
 refresh();
})();
