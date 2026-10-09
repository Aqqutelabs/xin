(() => {
  const page = document.querySelector('.pb-public-page');
  if (!page) return;

  const toast = page.querySelector('[data-toast]');
  let toastTimer;
  const showToast = (message) => {
    toast.textContent = message;
    toast.classList.add('is-visible');
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => toast.classList.remove('is-visible'), 2200);
  };
  const copyText = async (text) => {
    try {
      await navigator.clipboard.writeText(text);
    } catch {
      const input = document.createElement('textarea');
      input.value = text;
      input.setAttribute('readonly', '');
      input.style.position = 'fixed';
      input.style.opacity = '0';
      document.body.append(input);
      input.select();
      document.execCommand('copy');
      input.remove();
    }
    showToast('Link copied');
  };
  const share = async (title, url) => {
    if (navigator.share) {
      try {
        await navigator.share({ title, url });
        return;
      } catch (error) {
        if (error.name === 'AbortError') return;
      }
    }
    await copyText(url);
  };

  page.addEventListener('click', async (event) => {
    const kebab = event.target.closest('[data-kebab]');
    if (kebab) {
      const menu = kebab.parentElement.querySelector('[data-popover]');
      const opening = menu.hidden;
      page.querySelectorAll('[data-popover]').forEach((popover) => { popover.hidden = true; });
      page.querySelectorAll('[data-kebab]').forEach((button) => button.setAttribute('aria-expanded', 'false'));
      menu.hidden = !opening;
      kebab.setAttribute('aria-expanded', String(opening));
      return;
    }

    const copyButton = event.target.closest('[data-copy-url]');
    if (copyButton) {
      await copyText(copyButton.dataset.copyUrl);
      copyButton.closest('[data-popover]').hidden = true;
      copyButton.closest('.pb-kebab-wrap').querySelector('[data-kebab]').setAttribute('aria-expanded', 'false');
      return;
    }

    const shareButton = event.target.closest('[data-share-url], [data-share-page]');
    if (shareButton) {
      const url = shareButton.dataset.shareUrl || page.dataset.pageUrl;
      await share(shareButton.dataset.shareTitle || page.dataset.pageTitle, url);
      const menu = shareButton.closest('[data-popover]');
      if (menu) {
        menu.hidden = true;
        menu.closest('.pb-kebab-wrap').querySelector('[data-kebab]').setAttribute('aria-expanded', 'false');
      }
      return;
    }

    const videoButton = event.target.closest('[data-youtube-id]');
    if (videoButton) {
      const frame = document.createElement('iframe');
      frame.src = `https://www.youtube-nocookie.com/embed/${encodeURIComponent(videoButton.dataset.youtubeId)}?autoplay=1&rel=0`;
      frame.title = videoButton.getAttribute('aria-label') || 'YouTube video';
      frame.allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share';
      frame.allowFullscreen = true;
      frame.loading = 'lazy';
      videoButton.replaceWith(frame);
      return;
    }

    if (event.target.closest('[data-scroll-subscribe]')) {
      page.querySelector('[data-block-type="subscribe"]')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
      return;
    }

    const bioToggle = event.target.closest('[data-bio-toggle]');
    if (bioToggle) {
      const bio = page.querySelector('[data-bio]');
      const expanded = bio.classList.toggle('is-expanded');
      bioToggle.setAttribute('aria-expanded', String(expanded));
      bioToggle.textContent = expanded ? 'less' : 'more';
      return;
    }

    if (event.target.closest('[data-dismiss-promo]')) {
      sessionStorage.setItem('xinng-promo-dismissed', '1');
      page.querySelector('[data-promo]').hidden = true;
    }
  });

  document.addEventListener('click', (event) => {
    if (!event.target.closest('.pb-kebab-wrap')) {
      page.querySelectorAll('[data-popover]').forEach((popover) => { popover.hidden = true; });
      page.querySelectorAll('[data-kebab]').forEach((button) => button.setAttribute('aria-expanded', 'false'));
    }
  });
  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
      page.querySelectorAll('[data-popover]').forEach((popover) => { popover.hidden = true; });
      page.querySelectorAll('[data-kebab]').forEach((button) => button.setAttribute('aria-expanded', 'false'));
    }
  });

  const promo = page.querySelector('[data-promo]');
  if (promo && sessionStorage.getItem('xinng-promo-dismissed') === '1') promo.hidden = true;
  const bio = page.querySelector('[data-bio]');
  const bioToggle = page.querySelector('[data-bio-toggle]');
  if (bio && bioToggle) {
    requestAnimationFrame(() => {
      if (bio.scrollHeight > bio.clientHeight + 2) bioToggle.hidden = false;
    });
  }
})();