(function () {
  document.querySelectorAll('[data-credit-checkout]').forEach(function (root) {
    const paystackKey = root.dataset.paystackKey || '';
    const userEmail = root.dataset.userEmail || '';
    const message = root.querySelector('.credit-checkout-message');
    function showError(text) { if (message) message.textContent = text; }
    async function startPaystack(packageId) {
      if (!paystackKey) return showError('Paystack is not configured yet.');
      if (!userEmail) return showError('Your account email is required for payment.');
      try {
        const response = await fetch('paystack_init.php', { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:new URLSearchParams({package:packageId}) });
        const data = await response.json();
        if (!data.ok) return showError(data.error || 'Unable to start payment.');
        if (typeof PaystackPop === 'undefined') return showError('Payment checkout failed to load.');
        PaystackPop.setup({ key:paystackKey, email:userEmail, amount:data.amount, ref:data.reference, currency:'NGN', callback:function (result) { window.location.href = 'paystack_verify.php?reference=' + encodeURIComponent(result.reference) + '&package=' + encodeURIComponent(packageId); }, onClose:function () { showError('Payment was cancelled.'); } }).openIframe();
      } catch (error) { showError('Payment request failed.'); }
    }
    async function startCrypto(packageId) {
      try {
        const response = await fetch('nowpayments_init.php', { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:new URLSearchParams({package:packageId}) });
        const data = await response.json();
        if (!data.ok) return showError(data.error === 'not_configured' ? 'Crypto payments are not configured yet.' : (data.error || 'Unable to start crypto payment.'));
        window.location.href = data.invoice_url;
      } catch (error) { showError('Crypto payment request failed.'); }
    }
    root.querySelectorAll('[data-paystack-package]').forEach(function (button) { button.addEventListener('click', function () { startPaystack(button.dataset.paystackPackage); }); });
    root.querySelectorAll('[data-nowpayments-package]').forEach(function (button) { button.addEventListener('click', function () { startCrypto(button.dataset.nowpaymentsPackage); }); });
  });
})();