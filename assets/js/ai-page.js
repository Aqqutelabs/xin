(function () {
  const config = window.aiPageConfig || {};
  const prompt = document.getElementById('ai-page-prompt');
  const pageSelect = document.getElementById('ai-page-select');
  const provider = document.getElementById('ai-page-provider');
  const generateButton = document.getElementById('generate-ai-page');
  const status = document.getElementById('ai-page-status');
  const count = document.getElementById('prompt-count');
  const draftTitle = document.getElementById('draft-title');
  const draftStatus = document.getElementById('draft-status');
  const draftPreview = document.getElementById('draft-preview');
  const openBuilder = document.getElementById('open-builder');
  const profileTypeOptions = Array.from(document.querySelectorAll('.profile-type-option'));
  if (!prompt || !pageSelect || !generateButton) return;
  let draft = null;
  let selectedProfileType = 'creator';

  function selectedPage() { return pageSelect.options[pageSelect.selectedIndex]; }
  function setProfileType(type) {
    selectedProfileType = type === 'corporate' ? 'corporate' : 'creator';
    profileTypeOptions.forEach(option => {
      const selected = option.dataset.profileType === selectedProfileType;
      option.classList.toggle('active', selected);
      option.setAttribute('aria-pressed', String(selected));
    });
    Array.from(pageSelect.options).forEach(option => { option.hidden = option.dataset.pageType && option.dataset.pageType !== selectedProfileType; });
    const compatible = Array.from(pageSelect.options).find(option => option.dataset.pageType === selectedProfileType);
    if (compatible) {
      pageSelect.value = compatible.value;
      setStatus('');
      openBuilder.disabled = true;
      draft = null;
      draftTitle.textContent = 'Your draft will appear here';
      draftStatus.textContent = 'Waiting';
      draftPreview.innerHTML = '<i class="fa-solid fa-wand-magic-sparkles"></i><p>AI will suggest a complete profile draft based on your prompt.</p>';
    } else {
      pageSelect.value = '';
      setStatus(`Your ${selectedProfileType === 'corporate' ? 'company' : 'personal'} page will be created when you generate the draft.`);
    }
  }
  function setStatus(message, error) { status.textContent = message || ''; status.classList.toggle('error', Boolean(error)); }
  function updateCount() { count.textContent = `${prompt.value.length} / ${config.maxPromptLength || 3000}`; }
  function context() {
    const page = selectedPage();
    return { page_type: page?.dataset.pageType || selectedProfileType, title: page?.dataset.title || '', description: page?.dataset.description || '', theme: page?.dataset.theme || 'default', layout: page?.dataset.layout || 'simple', blocks: [] };
  }
  async function ensurePage() {
    const compatible = Array.from(pageSelect.options).find(option => option.dataset.pageType === selectedProfileType);
    if (compatible) {
      pageSelect.value = compatible.value;
      return compatible;
    }
    setStatus(`Setting up your ${selectedProfileType === 'corporate' ? 'company' : 'personal'} page...`);
    const response = await fetch('api/pages.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: new URLSearchParams({ csrf_token: config.csrf, page_type: selectedProfileType, title: selectedProfileType === 'corporate' ? 'Company Page' : 'Personal Page', slug: selectedProfileType === 'corporate' ? 'company-page' : 'personal-page' })
    });
    const data = await response.json().catch(() => ({ ok: false, error: 'invalid_response' }));
    if (!response.ok || !data.ok || !data.id) throw new Error(data.error || 'page_creation_failed');
    const option = document.createElement('option');
    option.value = data.id;
    option.dataset.pageType = selectedProfileType;
    option.dataset.title = data.title || (selectedProfileType === 'corporate' ? 'Company Page' : 'Personal Page');
    option.dataset.description = data.description || '';
    option.dataset.theme = data.theme || 'default';
    option.dataset.layout = data.layout || 'simple';
    option.textContent = option.dataset.title;
    pageSelect.appendChild(option);
    pageSelect.value = option.value;
    return option;
  }
  function renderDraft(nextDraft) {
    draft = nextDraft;
    const blocks = Array.isArray(nextDraft.blocks) ? nextDraft.blocks : [];
    const socials = Array.isArray(nextDraft.socials) ? nextDraft.socials : [];
    const corporate = nextDraft.corporate && typeof nextDraft.corporate === 'object' ? nextDraft.corporate : null;
    const detailTags = blocks.map(block => `<span>${escapeHtml(block.title || block.type || 'Section')}</span>`).join('');
    const socialTags = socials.map(social => `<span>${escapeHtml(social.platform || 'Social')}</span>`).join('');
    const corporateDetails = corporate ? [
      corporate.company_name,
      ...(Array.isArray(corporate.specialties) ? corporate.specialties : []),
      ...(Array.isArray(corporate.cards) ? corporate.cards.map(card => card.title) : []),
      ...(Array.isArray(corporate.team?.members) ? corporate.team.members.map(member => member.name).filter(Boolean) : [])
    ].filter(Boolean).map(value => `<span>${escapeHtml(value)}</span>`).join('') : '';
    draftStatus.textContent = 'Ready to review';
    draftStatus.classList.add('ready');
    draftTitle.textContent = nextDraft.title || 'Untitled draft';
    draftPreview.innerHTML = `<div class="draft-summary"><h4>${escapeHtml(nextDraft.title || 'Untitled draft')}</h4><p>${escapeHtml(nextDraft.description || 'A page draft is ready to refine in the editor.')}</p><strong class="draft-detail-count">${blocks.length} page sections${socials.length ? ` · ${socials.length} social links` : ''}</strong><div class="draft-tags">${detailTags}${socialTags}${corporateDetails}</div></div>`;
    openBuilder.disabled = false;
  }
  function escapeHtml(value) { return String(value).replace(/[&<>"']/g, char => ({ '&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#039;' }[char])); }
  async function generate() {
    const text = prompt.value.trim();
    if (!text) { setStatus('Describe the page you want first.', true); prompt.focus(); return; }
    generateButton.disabled = true;
    generateButton.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Generating...';
    draftStatus.textContent = 'Generating';
    draftStatus.classList.remove('ready');
    setStatus('Writing your first draft...');
    try {
      const page = await ensurePage();
      const response = await fetch('api/ai-page.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ csrf_token: config.csrf, page_id: page.value, page_type: page.dataset.pageType || 'creator', provider: provider.value, prompt: text, current_state: context() }) });
      const data = await response.json().catch(() => ({ ok: false, error: 'invalid_response' }));
      if (!response.ok || !data.ok || !data.draft) throw new Error(data.error || 'ai_generation_failed');
      renderDraft(data.draft);
      setStatus(`Draft ready. ${data.credits_remaining ?? ''} credits remaining.`.trim());
    } catch (error) {
      draftStatus.textContent = 'Waiting';
      setStatus({ insufficient_credits: 'You do not have enough credits for an AI draft.', gemini_not_configured: 'Gemini is not configured yet.', deepseek_not_configured: 'DeepSeek is not configured yet.', ai_invalid_json: 'The provider returned an invalid page draft.', ai_network_error: 'The AI provider could not be reached. Check the server connection.', ai_provider_auth: 'The AI provider rejected the API key. Check the provider key in .env.', ai_model_not_found: 'The configured AI model was not found. Check the model setting in .env.', ai_provider_billing: 'The AI provider account has no available balance or billing is not enabled.', ai_rate_limited: 'The AI provider rate limit was reached. Try again shortly.', ai_provider_bad_request: 'The AI provider rejected the request. Check the prompt and model configuration.', ai_provider_unavailable: 'The AI provider is temporarily unavailable. Try again shortly.', ai_provider_invalid_response: 'The AI provider returned an unreadable response.' }[error.message] || 'Unable to generate an AI draft.', true);
    } finally {
      generateButton.disabled = false;
      generateButton.innerHTML = '<i class="fa-solid fa-arrow-up"></i> Generate draft';
    }
  }
  prompt.addEventListener('input', updateCount);
  generateButton.addEventListener('click', generate);
  profileTypeOptions.forEach(option => option.addEventListener('click', () => setProfileType(option.dataset.profileType)));
  prompt.addEventListener('keydown', event => { if ((event.ctrlKey || event.metaKey) && event.key === 'Enter') generate(); });
  document.querySelectorAll('.prompt-examples button').forEach(button => button.addEventListener('click', () => { prompt.value = button.dataset.prompt || ''; updateCount(); prompt.focus(); }));
  openBuilder?.addEventListener('click', () => {
    if (!draft) return;
    sessionStorage.setItem(`xinng-ai-draft:${selectedPage().value}`, JSON.stringify(draft));
    window.location.href = `page_builder.php?id=${encodeURIComponent(selectedPage().value)}&ai_draft=1`;
  });
  updateCount();
  setProfileType(selectedPage().dataset.pageType || 'creator');
})();
