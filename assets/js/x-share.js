(() => {
  'use strict';
  document.querySelectorAll('[data-x-share]').forEach(button => {
    button.addEventListener('click', event => {
      event.preventDefault();
      const url = button.dataset.xShare || window.location.href;
      if (!url) return;
      const text = button.dataset.xShareText || 'Check this out from Xinng.';
      const intent = 'https://x.com/intent/post?' + new URLSearchParams({ text, url });
      window.open(intent, '_blank', 'noopener,noreferrer');
    });
  });
})();
