<?php
require_once __DIR__ . '/config.php';
session_start();

if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_unset();
    session_destroy();
    header('Location: signin.php');
    exit;
}

if (empty($_SESSION['user_id'])) {
    header('Location: signin.php');
    exit;
}

$user_id = (int)$_SESSION['user_id'];
$pdo = get_db_connection();
$creditBalance = 0;
$userEmail = '';
$transactions = [];
$packages = xinng_credit_packages();
$notice = null;
$error = null;
function credits_initials(string $name): string {
    $clean = preg_replace('/[^A-Za-z0-9 ]/', '', $name);
    $parts = array_values(array_filter(explode(' ', trim($clean))));
    return strtoupper(count($parts) >= 2 ? substr($parts[0], 0, 1) . substr($parts[1], 0, 1) : substr($clean ?: 'X', 0, 2));
}
$userName = $_SESSION['user_name'] ?? 'User';
$displaySlug = strtolower(preg_replace('/[^a-z0-9]+/i', '', $userName ?: 'user'));

if (isset($_GET['success'])) {
    $notice = 'Payment completed successfully. Your credit balance has been updated.';
}
if (isset($_GET['error'])) {
    $messages = [
        'invalid_package' => 'The selected package is invalid.',
        'notfound' => 'Payment record not found.',
        'payment_failed' => 'Payment was not successful.',
        'verification_failed' => 'Unable to verify the payment with Paystack.',
        'payment_cancelled' => 'Payment was cancelled.',
        'missing_secret' => 'Paystack secret key is not configured. Set PAYSTACK_SECRET_KEY in config.php.',
        'auth' => 'Please sign in to complete payment.',
        'bad_request' => 'Failed to start the payment request.',
        'server' => 'A server error occurred while processing payment.',
    ];
    $error = $messages[$_GET['error']] ?? 'Could not complete the payment.';
}

if ($pdo) {
    xinng_ensure_credit_tables($pdo);
    $stmt = $pdo->prepare('SELECT credit_balance, email FROM users WHERE id = ? AND deleted_at IS NULL LIMIT 1');
    $stmt->execute([$user_id]);
    $row = $stmt->fetch();
    if ($row) {
        $creditBalance = (int)$row['credit_balance'];
        $userEmail = (string)$row['email'];
    }

    $stmt = $pdo->prepare('SELECT id, type, amount, reason, reference, payment_gateway, payment_amount, payment_currency, status, created_at FROM credit_transactions WHERE user_id = ? ORDER BY created_at DESC LIMIT 8');
    $stmt->execute([$user_id]);
    $transactions = $stmt->fetchAll();
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Credits — <?= e($APP_NAME) ?></title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  <link rel="stylesheet" href="<?= e(xinng_public_base_url()) ?>/assets/css/dashboard.css?v=20261017-credits-workspace">
  <link rel="stylesheet" href="<?= e(xinng_public_base_url()) ?>/assets/css/credit-pricing.css?v=20261017-credits-workspace">
</head>
<body class="credits-screen workspace-screen">
  <div class="dashboard no-preview">
    <aside class="sidebar" aria-label="Dashboard navigation">
      <div class="brand-slot"><a class="xinng-brand" href="<?= e(xinng_public_base_url()) ?>/index.php" aria-label="Xinng home"><img class="xinng-brand__image" src="<?= e(xinng_brand_logo_url(xinng_public_base_url())) ?>" alt="Xinng" width="1736" height="906"></a></div>
      <div class="account"><div class="account-main"><span class="avatar"><?= e(credits_initials($userName)) ?></span><span><?= e($displaySlug ?: $userName) ?></span><span aria-hidden="true">v</span></div><a class="icon-btn" href="?action=logout" title="Logout" aria-label="Logout"><i class="fa-solid fa-arrow-right-from-bracket"></i></a></div>
      <nav><div class="nav-section"><div class="nav-heading"><span>Workspace</span><span><i class="fa-solid fa-chevron-up"></i></span></div>
        <a class="nav-item" href="dashboard.php"><span class="nav-icon"><i class="fa-solid fa-link"></i></span>URL Links</a>
        <a class="nav-item" href="pages.php"><span class="nav-icon"><i class="fa-regular fa-file-lines"></i></span>Pages</a>
        <a class="nav-item" href="events.php"><span class="nav-icon"><i class="fa-solid fa-calendar-days"></i></span>Events</a>
        <a class="nav-item" href="qr_codes.php"><span class="nav-icon"><i class="fa-solid fa-qrcode"></i></span>QR Codes</a>
        <a class="nav-item" href="insights.php"><span class="nav-icon"><i class="fa-solid fa-chart-line"></i></span>Insights</a>
        <a class="nav-item active" href="credits.php"><span class="nav-icon"><i class="fa-solid fa-wallet"></i></span>Credits</a>
      </div></nav>
      <div class="credits-sidebar-card"><span>Available credits</span><strong><?= number_format($creditBalance) ?></strong><small>Shared across your Xing workspace</small></div>
    </aside>

    <main class="main">
      <header class="main-header"><div class="header-title"><h1>Credits</h1><span>Fund premium actions across your Xing workspace.</span></div><div class="header-actions"><a class="secondary-header-link" href="#credit-packages"><i class="fa-solid fa-plus"></i>Buy credits</a></div></header>
      <div class="content credits-content">
        <section class="workspace-intro credits-intro">
          <div class="pricing-hero-copy"><span class="workspace-eyebrow">CREDIT WALLET</span><h2>Keep every page, QR code, and campaign moving.</h2><p>Buy credits once and use them anywhere in Xing. Pay securely with Paystack or cryptocurrency.</p></div>
          <div class="wallet-card"><span>Current balance</span><strong><?= number_format($creditBalance) ?></strong><small>credits available</small></div>
        </section>

      <?php if ($notice): ?><div class="notice"><?= e($notice) ?></div><?php endif; ?>
      <?php if ($error): ?><div class="error"><?= e($error) ?></div><?php endif; ?>

      <div id="credit-packages"><?php $pricingContext = 'credits'; include __DIR__ . '/credit-pricing-component.php'; ?></div>

      <section class="transactions-panel">
        <div class="transactions-header">
          <div>
            <h2>Recent credit activity</h2>
            <p>Track purchases, pending payments, and credit adjustments in one place.</p>
          </div>
          <span class="transaction-count"><?= number_format(count($transactions)) ?> recent</span>
        </div>

        <?php if (empty($transactions)): ?>
          <div class="empty-state">No credit activity yet. Make a purchase to see your history.</div>
        <?php else: ?>
          <div class="transaction-table-wrap">
            <table class="transaction-table">
              <thead>
                <tr>
                  <th>Date</th>
                  <th>Type</th>
                  <th>Credits</th>
                  <th>Paid</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($transactions as $txn): ?>
                  <tr>
                    <td><?= e(date('M j, Y H:i', strtotime($txn['created_at'] ?? 'now'))) ?></td>
                    <td><?= e(ucfirst(str_replace('_', ' ', $txn['type']))) ?></td>
                    <td><?= $txn['type'] === 'purchase' ? number_format((int)($txn['amount'] ?? 0)) : '-' ?></td>
                    <td><?= $txn['payment_gateway'] ? e(strtoupper($txn['payment_gateway'])) . ' ' . e($txn['payment_currency'] ?? 'NGN') . ' ' . number_format((int)($txn['payment_amount'] ?? $txn['amount'] ?? 0)) : '-' ?></td>
                    <td><span class="status-pill <?= e($txn['status'] ?? 'completed') ?>"><?= e(ucfirst($txn['status'] ?? 'completed')) ?></span></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </section>
      </div>
    </main>
  </div>

  <script src="https://js.paystack.co/v1/inline.js"></script>
  <script src="<?= e(xinng_public_base_url()) ?>/assets/js/credit-checkout.js"></script>
</body>
</html>
