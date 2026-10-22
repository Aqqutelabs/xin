<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
$userId = filter_var($_SESSION['user_id'] ?? null, FILTER_VALIDATE_INT);
$baseUrl = rtrim(xinng_public_base_url(), '/');
if (!$userId) {
	header('Location: ' . $baseUrl . '/signin.php', true, 303);
	exit;
}

$pdo = get_db_connection();
if (!$pdo) {
	http_response_code(503);
	exit('Subscription plans are temporarily unavailable.');
}

xinng_ensure_credit_tables($pdo);
xinng_ensure_subscription_billing_tables($pdo);
$planStatement = $pdo->query('SELECT id,name,slug,price_monthly,price_yearly,currency,max_pages,max_links_per_page,custom_domain_enabled,analytics_enabled,remove_branding,qr_customization_enabled,team_enabled FROM plans WHERE is_active = 1 ORDER BY price_monthly ASC, id ASC');
$plans = $planStatement->fetchAll();
$currentStatement = $pdo->prepare("SELECT s.id AS subscription_id,s.status,s.starts_at,s.ends_at,s.renews_at,s.billing_interval,p.id AS plan_id,p.slug,p.name,p.price_monthly,p.price_yearly,p.currency FROM subscriptions s JOIN plans p ON p.id=s.plan_id WHERE s.user_id=? AND s.status IN ('active','trialing','past_due') AND (s.ends_at IS NULL OR s.ends_at>NOW()) ORDER BY FIELD(s.status,'active','trialing','past_due'),s.starts_at DESC,s.id DESC LIMIT 1");
$currentStatement->execute([(int)$userId]);
$currentSubscription = $currentStatement->fetch() ?: null;
$currentPlan = null;
if ($currentSubscription) {
	foreach ($plans as $plan) {
		if ((int)$plan['id'] === (int)$currentSubscription['plan_id']) {
			$currentPlan = $plan;
			break;
		}
	}
}
if (!$currentPlan) {
	foreach ($plans as $plan) {
		if ((string)$plan['slug'] === 'free') {
			$currentPlan = $plan;
			break;
		}
	}
}
$billingInterval = (string)($_GET['interval'] ?? 'monthly');
if (!in_array($billingInterval, ['monthly', 'yearly'], true)) $billingInterval = 'monthly';
$errors = [
	'auth' => 'Please sign in again to choose a subscription.',
	'csrf' => 'Your session expired. Reload the page and try again.',
	'invalid_plan' => 'That subscription plan is no longer available.',
	'payments_not_configured' => 'Online subscription payments are not configured yet.',
	'unsupported_currency' => 'Online subscription checkout currently supports plans priced in NGN only.',
	'payment_not_found' => 'The subscription payment could not be found.',
	'payment_failed' => 'The subscription payment was not completed.',
	'verification_failed' => 'We could not verify that payment with Paystack.',
	'invalid_payment' => 'The payment reference is invalid.',
	'server' => 'A server error occurred while processing your subscription.',
];
$error = $errors[(string)($_GET['error'] ?? '')] ?? null;
$subscriptionSucceeded = isset($_GET['subscription_success']) && $currentSubscription !== null;
$userName = trim((string)($_SESSION['user_name'] ?? 'User')) ?: 'User';
$currentPlanName = (string)($currentPlan['name'] ?? 'Free');
$currentPlanSlug = (string)($currentPlan['slug'] ?? 'free');
$currentPlanStatus = $currentSubscription ? ucfirst((string)$currentSubscription['status']) : 'Current plan';
$currentPlanRenewal = !empty($currentSubscription['renews_at']) ? date('M j, Y', strtotime((string)$currentSubscription['renews_at'])) : null;
$displaySlug = strtolower(preg_replace('/[^a-z0-9]+/i', '', $userName)) ?: 'user';
$currencySymbols = ['NGN' => '₦', 'USD' => '$', 'GBP' => '£', 'EUR' => '€'];
$planDescriptions = [
	'free' => 'A simple way to get started.',
	'pro' => 'More room and tools for growing creators.',
	'business' => 'Advanced features for growing businesses.',
];
$planFeatures = static function (array $plan): array {
	$features = [
		(int)$plan['max_pages'] . ' page' . ((int)$plan['max_pages'] === 1 ? '' : 's'),
		number_format((int)$plan['max_links_per_page']) . ' links per page',
	];
	foreach ([
		'analytics_enabled' => 'Analytics and insights',
		'remove_branding' => 'Remove Xinng branding',
		'qr_customization_enabled' => 'QR code customization',
		'custom_domain_enabled' => 'Custom domain',
		'team_enabled' => 'Team collaboration',
	] as $column => $label) {
		if (!empty($plan[$column])) $features[] = $label;
	}
	return $features;
};
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <base href="<?= e($baseUrl) ?>/">
  <title>Choose Your Plan — <?= e($APP_NAME) ?></title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  <link rel="stylesheet" href="assets/css/dashboard.css">
  <link rel="stylesheet" href="assets/css/workspace-sidebar.css?v=20261019e">
  <link rel="stylesheet" href="assets/css/wallet-dashboard.css?v=20261021a">
  <link rel="stylesheet" href="assets/css/wallet-plans.css?v=20261021a">
  <?php require __DIR__ . '/includes/google-analytics.php'; ?>
</head>
<body class="wallet-screen wallet-plans-screen workspace-screen workspace-shell-page">
  <div class="dashboard no-preview">
    <?php
      $sidebarActive = 'plans';
      $sidebarUserName = $userName;
      $sidebarDisplayName = $displaySlug;
      $sidebarBottom = [
          'eyebrow' => 'CURRENT PLAN',
          'title' => $currentPlanName,
          'body' => $currentPlanRenewal ? 'Renews ' . $currentPlanRenewal . '.' : 'Choose the plan that fits your workspace.',
          'action_label' => 'Open wallet',
          'action_href' => 'credits.php',
      ];
      include __DIR__ . '/includes/workspace-sidebar.php';
    ?>
    <main class="main">
      <?php $workspaceHeaderTitle = 'Choose Your Plan'; $workspaceHeaderSubtitle = 'Select the plan that fits your needs. Upgrade or downgrade anytime.'; include __DIR__ . '/includes/workspace-shell-header.php'; ?>
      <div class="content wallet-content wallet-plans-content" id="workspace">
        <a class="wallet-plans-back" href="credits.php"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Back to wallet</a>
        <?php if ($error): ?><div class="wallet-alert error" role="alert"><i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i><span><?= e($error) ?></span></div><?php endif; ?>
        <section class="wallet-plans-heading" aria-label="Subscription billing options">
          <div><span class="wallet-plans-kicker">SUBSCRIPTIONS</span><p>Plan features and prices are shown from the plans available on your account.</p></div>
          <div class="wallet-billing-switch" role="group" aria-label="Billing period">
            <button type="button" data-billing-period="monthly" aria-pressed="<?= $billingInterval === 'monthly' ? 'true' : 'false' ?>">Monthly</button>
            <button type="button" data-billing-period="yearly" aria-pressed="<?= $billingInterval === 'yearly' ? 'true' : 'false' ?>">Yearly <span>Save 2 months</span></button>
          </div>
        </section>

        <?php if (!$plans): ?>
          <div class="wallet-alert error" role="alert"><i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i><span>No active subscription plans are configured. Please contact support.</span></div>
        <?php else: ?>
          <section class="wallet-plan-grid" aria-label="Available subscription plans">
            <?php foreach ($plans as $plan):
              $planId = (int)$plan['id'];
              $planSlug = (string)$plan['slug'];
              $currency = strtoupper((string)$plan['currency']);
              $currencySymbol = $currencySymbols[$currency] ?? $currency . ' ';
              $monthlyPrice = (float)$plan['price_monthly'];
              $yearlyPrice = (float)$plan['price_yearly'];
              $isCurrent = $currentSubscription && $currentPlan && (int)$currentPlan['id'] === $planId;
              $planFeaturesForCard = $planFeatures($plan);
              $planDescription = $planDescriptions[$planSlug] ?? 'A plan designed for your workspace.';
              $isFeatured = $planSlug === 'pro';
            ?>
              <article class="wallet-plan-card<?= $isFeatured ? ' featured' : '' ?><?= $isCurrent ? ' current' : '' ?>">
                <div class="wallet-plan-card-top"><span class="wallet-plan-icon"><i class="fa-solid fa-bullseye" aria-hidden="true"></i></span><?php if ($isFeatured): ?><span class="wallet-plan-badge">Popular</span><?php elseif ($isCurrent): ?><span class="wallet-plan-current"><?= e($currentPlanStatus) ?></span><?php endif; ?></div>
                <h2><?= e($plan['name']) ?></h2>
                <p class="wallet-plan-description"><?= e($planDescription) ?></p>
                <div class="wallet-plan-price"><strong data-plan-price data-currency-symbol="<?= e($currencySymbol) ?>" data-monthly="<?= e(number_format($monthlyPrice, 0, '.', ',')) ?>" data-yearly="<?= e(number_format($yearlyPrice, 0, '.', ',')) ?>"><?= e($currencySymbol . number_format($billingInterval === 'yearly' ? $yearlyPrice : $monthlyPrice, 0, '.', ',')) ?></strong><span data-plan-period><?= $billingInterval === 'yearly' ? 'per year' : 'per month' ?></span></div>
                <p class="wallet-plan-billed" data-plan-billed><?= $yearlyPrice > 0 ? e($currencySymbol . number_format($yearlyPrice, 0, '.', ',') . ' billed yearly; renew manually.') : 'No payment required.' ?></p>
                <ul><?php foreach ($planFeaturesForCard as $feature): ?><li><i class="fa-solid fa-check" aria-hidden="true"></i><span><?= e($feature) ?></span></li><?php endforeach; ?></ul>
                <button class="wallet-plan-cta<?= $isCurrent ? ' selected' : '' ?>" type="button" data-select-plan data-plan-id="<?= $planId ?>" data-plan-name="<?= e($plan['name']) ?>" data-plan-slug="<?= e($planSlug) ?>" data-monthly-price="<?= e((string)$monthlyPrice) ?>" data-yearly-price="<?= e((string)$yearlyPrice) ?>" data-current="<?= $isCurrent ? '1' : '0' ?>"<?= $isCurrent ? ' disabled aria-current="true"' : '' ?>><?= $isCurrent ? 'Current plan' : ($monthlyPrice <= 0 && $yearlyPrice <= 0 ? 'Choose Free' : 'Select Plan') ?></button>
              </article>
            <?php endforeach; ?>
          </section>
        <?php endif; ?>

        <p class="wallet-plan-renewal-note">Plan changes take effect immediately. Paid plans are charged once for the selected term through Paystack and do not automatically renew; renew manually when the term ends.</p>
        <div class="wallet-plan-checkout-message" data-plan-message role="status" aria-live="polite"></div>
      </div>
    </main>
  </div>

  <?php if ($subscriptionSucceeded): ?>
    <div class="wallet-success-overlay" data-wallet-success>
      <section class="wallet-success-dialog" role="dialog" aria-modal="true" aria-labelledby="subscription-success-title">
        <span class="wallet-success-check"><i class="fa-solid fa-check" aria-hidden="true"></i></span>
        <h2 id="subscription-success-title">Subscription Successful!</h2>
        <p><?= e($currentPlanName) ?> is now active on your workspace<?= $currentPlanRenewal ? ' through ' . e($currentPlanRenewal) : '' ?>.</p>
        <a class="wallet-success-action" href="credits.php">Go to Wallet</a>
      </section>
    </div>
  <?php endif; ?>

  <script src="https://js.paystack.co/v1/inline.js"></script>
  <script>
    window.XinngSubscriptionCheckout = {
      baseUrl: <?= json_encode($baseUrl, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
      csrfToken: <?= json_encode(csrf_token(), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
      publicKey: <?= json_encode(PAYSTACK_PUBLIC_KEY, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
      interval: <?= json_encode($billingInterval) ?>
    };
  </script>
  <script src="assets/js/wallet-plans.js?v=20261021a"></script>
</body>
</html>
