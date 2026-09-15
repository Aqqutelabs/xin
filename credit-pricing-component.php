<?php
$packages = $packages ?? xinng_credit_packages();
$pricingContext = $pricingContext ?? 'dashboard';
?>
<section class="credit-pricing-component" data-credit-checkout data-paystack-key="<?= e(PAYSTACK_PUBLIC_KEY) ?>" data-user-email="<?= e($userEmail ?? '') ?>">
  <div class="credit-pricing-heading">
    <div>
      <p class="credit-pricing-eyebrow">Credit wallet</p>
      <h2><?= $pricingContext === 'dashboard' ? 'Keep your workspace moving' : 'Simple pricing for your next campaign' ?></h2>
      <p>Buy credits once and use them across pages, QR codes, short links, and campaign actions.</p>
    </div>
    <?php if ($pricingContext === 'dashboard'): ?>
      <a class="credit-pricing-link" href="credits.php">View activity <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i></a>
    <?php endif; ?>
  </div>
  <div class="credit-package-grid">
    <?php foreach ($packages as $package): ?>
      <article class="credit-package-card<?= $package['id'] === 'growth' ? ' is-featured' : '' ?>">
        <div class="credit-package-topline">
          <span class="credit-package-name"><?= e($package['name']) ?></span>
          <?php if ($package['id'] === 'growth'): ?><span class="credit-package-badge">Best value</span><?php endif; ?>
        </div>
        <p class="credit-package-description"><?= e($package['description']) ?></p>
        <div class="credit-package-price"><strong><?= number_format($package['credits']) ?></strong><span>credits</span></div>
        <div class="credit-package-cost">₦<?= number_format($package['price']) ?> <span>one-time</span></div>
        <div class="credit-package-actions">
          <button class="primary-btn credit-package-button" type="button" data-paystack-package="<?= e($package['id']) ?>">Pay with Paystack</button>
          <button class="ghost-btn credit-package-button" type="button" data-nowpayments-package="<?= e($package['id']) ?>">Pay with crypto</button>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
  <div class="credit-checkout-message" role="status" aria-live="polite"></div>
</section>