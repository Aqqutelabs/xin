(function () {
  const topUp = document.querySelector('#top-up-options');
  const modal = document.querySelector('[data-bank-transfer-modal]');
  const packageSelect = document.querySelector('#bank-package');
  const amountInput = document.querySelector('#bank-amount');
  const toast = document.querySelector('.wallet-toast');
  let lastFocused = null;

  function showToast(message) {
    if (!toast) return;
    toast.textContent = message;
    toast.hidden = false;
    window.clearTimeout(showToast.timer);
    showToast.timer = window.setTimeout(function () { toast.hidden = true; }, 4200);
  }

  document.querySelectorAll('[data-wallet-target]').forEach(function (button) {
    button.addEventListener('click', function () {
      if (!topUp) return;
      topUp.hidden = false;
      topUp.scrollIntoView({ behavior: 'smooth', block: 'start' });
      window.XinngAnalytics?.track('wallet_top_up_open', { component: 'quick_action', location: 'wallet' });
    });
  });

  function openModal(button) {
    if (!modal) return;
    lastFocused = document.activeElement;
    if (button?.dataset.package && packageSelect) {
      packageSelect.value = button.dataset.package;
      packageSelect.dispatchEvent(new Event('change'));
    }
    modal.hidden = false;
    document.body.classList.add('wallet-modal-open');
    modal.querySelector('.wallet-modal-close')?.focus();
    window.XinngAnalytics?.track('bank_transfer_open', { component: 'quick_action', location: 'wallet' });
  }

  function closeModal() {
    if (!modal) return;
    modal.hidden = true;
    document.body.classList.remove('wallet-modal-open');
    if (lastFocused instanceof HTMLElement) lastFocused.focus();
  }

  document.querySelectorAll('[data-wallet-success]').forEach(function (dialog) {
    document.body.classList.add('wallet-modal-open');
    const action = dialog.querySelector('.wallet-success-action');
    action?.focus();
  });

  packageSelect?.addEventListener('change', function () {
    const selected = packageSelect.selectedOptions[0];
    if (amountInput && selected) amountInput.value = selected.dataset.price || '';
  });

  document.querySelectorAll('[data-bank-transfer-open]').forEach(function (button) {
    button.addEventListener('click', function () { openModal(button); });
  });
  document.querySelectorAll('[data-bank-transfer-close]').forEach(function (button) {
    button.addEventListener('click', closeModal);
  });
  document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape' && modal && !modal.hidden) closeModal();
  });

  document.querySelectorAll('[data-coming-soon]').forEach(function (button) {
    button.addEventListener('click', function () { showToast(button.dataset.comingSoon || 'Coming soon.'); });
  });
})();
