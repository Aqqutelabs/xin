(function () {
  const checkout = window.XinngSubscriptionCheckout;
  const message = document.querySelector('[data-plan-message]');
  const periodButtons = document.querySelectorAll('[data-billing-period]');
  let billingInterval = checkout?.interval || 'monthly';

  if (document.querySelector('[data-wallet-success]')) document.body.classList.add('wallet-modal-open');
  document.querySelector('[data-wallet-success] .wallet-success-action')?.focus();

  function showMessage(text, isError) {
    if (!message) return;
    message.textContent = text;
    message.setAttribute('role', isError ? 'alert' : 'status');
  }

  function updatePeriod(interval) {
    billingInterval = interval;
    periodButtons.forEach(function (button) {
      button.setAttribute('aria-pressed', button.dataset.billingPeriod === interval ? 'true' : 'false');
    });
    document.querySelectorAll('[data-plan-price]').forEach(function (price) {
      const amount = price.dataset[interval] || '0';
      price.textContent = (price.dataset.currencySymbol || '') + amount;
    });
    document.querySelectorAll('[data-plan-period]').forEach(function (label) {
      label.textContent = interval === 'yearly' ? 'per year' : 'per month';
    });
  }

  periodButtons.forEach(function (button) {
    button.addEventListener('click', function () {
      updatePeriod(button.dataset.billingPeriod || 'monthly');
    });
  });

  document.querySelectorAll('[data-select-plan]').forEach(function (button) {
    button.addEventListener('click', async function () {
      if (!checkout) return showMessage('Subscription checkout is unavailable. Refresh and try again.', true);
      if (button.dataset.current === '1') return;
      const amount = Number(button.dataset[billingInterval + 'Price'] || 0);
      if (amount > 0 && !checkout.publicKey) return showMessage('Online subscription payments are not configured yet.', true);
      button.disabled = true;
      showMessage('Preparing your plan…', false);
      try {
        const response = await fetch(checkout.baseUrl + '/subscription_init.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: new URLSearchParams({
            csrf_token: checkout.csrfToken,
            plan_id: button.dataset.planId || '',
            billing_interval: billingInterval
          })
        });
        const data = await response.json();
        if (!response.ok || !data.ok) {
          const errors = {
            auth: 'Please sign in again to choose a subscription.',
            csrf: 'Your session expired. Reload the page and try again.',
            invalid_plan: 'That subscription plan is no longer available.',
            payments_not_configured: 'Online subscription payments are not configured yet.',
            unsupported_currency: 'Paystack checkout currently supports plans priced in NGN only.',
            server: 'A server error occurred while preparing checkout.'
          };
          showMessage(errors[data.error] || 'Unable to start subscription checkout.', true);
          button.disabled = false;
          return;
        }
        if (data.free) {
          window.location.assign(checkout.baseUrl + '/wallet/plans?subscription_success=1');
          return;
        }
        if (typeof window.PaystackPop === 'undefined') {
          showMessage('Paystack checkout failed to load. Please refresh and try again.', true);
          button.disabled = false;
          return;
        }
        window.PaystackPop.setup({
          key: checkout.publicKey,
          email: data.email,
          amount: data.amount,
          ref: data.reference,
          currency: data.currency,
          callback: function (result) {
            window.location.assign(checkout.baseUrl + '/subscription_verify.php?reference=' + encodeURIComponent(result.reference));
          },
          onClose: function () {
            showMessage('Checkout was closed. No subscription changes were made.', false);
            button.disabled = false;
          }
        }).openIframe();
      } catch (error) {
        showMessage('Subscription checkout could not be started. Please try again.', true);
        button.disabled = false;
      }
    });
  });
})();
