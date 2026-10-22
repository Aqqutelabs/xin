<?php
declare(strict_types=1);

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

$userId = (int) $_SESSION['user_id'];
$userName = trim((string) ($_SESSION['user_name'] ?? 'User')) ?: 'User';
$pdo = get_db_connection();
$packages = xinng_credit_packages();
$creditBalance = 0;
$creditsUsedThisCycle = 0;
$creditsAddedThisCycle = 0;
$userEmail = '';
$transactions = [];
$subscription = null;
$notice = null;
$error = null;
$topUpSuccessCredits = filter_var($_GET['credits'] ?? null, FILTER_VALIDATE_INT);
$bankSetting = static function (string $name, string $fallback): string {
    $value = getenv($name);
    if ($value !== false) return (string) $value;
    if (array_key_exists($name, $_ENV)) return (string) $_ENV[$name];
    if (array_key_exists($name, $_SERVER)) return (string) $_SERVER[$name];
    return $fallback;
};
$bankName = trim($bankSetting('BANK_TRANSFER_BANK_NAME', 'Optimus Bank'));
$bankAccountName = trim($bankSetting('BANK_TRANSFER_ACCOUNT_NAME', 'Aqqute Labs Ltd.')) ?: 'Aqqute Labs Ltd.';
$bankAccountNumber = trim($bankSetting('BANK_TRANSFER_ACCOUNT_NUMBER', '1000325795'));
$bankTransferEnabled = $bankName !== '' && $bankAccountNumber !== '';
$bankReference = 'XNG-BT-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));

$monthStart = date('Y-m-01 00:00:00');
$chartStart = date('Y-m-01 00:00:00', strtotime('-5 months'));
$chartMonths = [];
for ($index = 5; $index >= 0; $index--) {
    $key = date('Y-m', strtotime("-{$index} months"));
    $chartMonths[$key] = [
        'label' => date('M', strtotime($key . '-01')),
        'used' => 0,
        'added' => 0,
    ];
}

$tableExists = static function (PDO $connection, string $table): bool {
    $statement = $connection->prepare('SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?');
    $statement->execute([$table]);
    return (bool) $statement->fetchColumn();
};

if (isset($_GET['success'])) {
    $notice = 'Payment completed successfully. Your credit balance has been updated.';
} elseif (isset($_GET['bank_submitted'])) {
    $notice = 'Receipt received. The transfer is pending verification; no credits have been added yet.';
}

if (isset($_GET['error'])) {
    $messages = [
        'invalid_package' => 'The selected package is invalid.',
        'notfound' => 'Payment record not found.',
        'payment_failed' => 'Payment was not successful.',
        'verification_failed' => 'Unable to verify the payment with Paystack.',
        'payment_cancelled' => 'Payment was cancelled.',
        'missing_secret' => 'Paystack is not configured yet.',
        'auth' => 'Please sign in to complete payment.',
        'bad_request' => 'Failed to start the payment request.',
        'server' => 'A server error occurred while processing payment.',
    ];
    $error = $messages[(string) $_GET['error']] ?? 'Could not complete the payment.';
}

if ($pdo) {
    xinng_ensure_credit_tables($pdo);

    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && ($_POST['action'] ?? '') === 'submit_bank_transfer') {
        if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
            $error = 'This form expired. Reload the page and try again.';
        } elseif (!$bankTransferEnabled) {
            $error = 'Bank-transfer details have not been configured yet.';
        } else {
            $package = xinng_credit_package(trim((string) ($_POST['package'] ?? '')));
            $submittedReference = strtoupper(trim((string) ($_POST['transfer_reference'] ?? '')));
            $submittedAmount = filter_var($_POST['payment_amount'] ?? null, FILTER_VALIDATE_INT);
            $receipt = $_FILES['receipt'] ?? null;
            $allowedTypes = [
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'application/pdf' => 'pdf',
            ];

            if (!$package) {
                $error = 'Choose a valid credit package.';
            } elseif ($submittedAmount === false || $submittedAmount !== (int) $package['price']) {
                $error = 'Enter the exact transfer amount for the selected credit package.';
            } elseif (!preg_match('/^XNG-BT-[0-9]{8}-[A-F0-9]{6}$/', $submittedReference)) {
                $error = 'The transfer reference is invalid. Reload the page and try again.';
            } elseif (!is_array($receipt) || (int) ($receipt['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
                $error = 'Attach the bank receipt as a JPG, PNG, or PDF.';
            } elseif ((int) ($receipt['size'] ?? 0) < 1 || (int) $receipt['size'] > 5 * 1024 * 1024) {
                $error = 'The receipt must be smaller than 5 MB.';
            } else {
                $temporaryPath = (string) ($receipt['tmp_name'] ?? '');
                $mime = $temporaryPath !== '' ? (new finfo(FILEINFO_MIME_TYPE))->file($temporaryPath) : false;
                if (!$mime || !isset($allowedTypes[$mime])) {
                    $error = 'Only JPG, PNG, and PDF receipts are accepted.';
                } else {
                    $uploadDirectory = __DIR__ . '/uploads/bank-receipts';
                    if (!is_dir($uploadDirectory) && !mkdir($uploadDirectory, 0750, true) && !is_dir($uploadDirectory)) {
                        $error = 'The receipt could not be stored. Please try again.';
                    } else {
                        $storedName = date('YmdHis') . '-' . bin2hex(random_bytes(12)) . '.' . $allowedTypes[$mime];
                        $storedPath = $uploadDirectory . '/' . $storedName;
                        if (!move_uploaded_file($temporaryPath, $storedPath)) {
                            $error = 'The receipt could not be stored. Please try again.';
                        } else {
                            try {
                                $pdo->beginTransaction();
                                $duplicate = $pdo->prepare('SELECT id FROM credit_transactions WHERE user_id = ? AND reference = ? LIMIT 1 FOR UPDATE');
                                $duplicate->execute([$userId, $submittedReference]);
                                if ($duplicate->fetchColumn()) {
                                    throw new RuntimeException('That transfer reference has already been submitted.');
                                }

                                $transaction = $pdo->prepare("INSERT INTO credit_transactions (user_id, type, amount, reason, reference, payment_gateway, payment_amount, payment_currency, status, created_at) VALUES (?, 'purchase', ?, 'Bank transfer awaiting verification', ?, 'bank_transfer', ?, 'NGN', 'pending', NOW())");
                                $transaction->execute([$userId, (int) $package['credits'], $submittedReference, $submittedAmount]);
                                $transactionId = (int) $pdo->lastInsertId();

                                $receiptInsert = $pdo->prepare("INSERT INTO bank_transfer_receipts (user_id, credit_transaction_id, package_id, expected_amount, currency, receipt_path, original_filename, status, created_at, updated_at) VALUES (?, ?, ?, ?, 'NGN', ?, ?, 'pending', NOW(), NOW())");
                                $receiptInsert->execute([
                                    $userId,
                                    $transactionId,
                                    (string) $package['id'],
                                    $submittedAmount,
                                    'uploads/bank-receipts/' . $storedName,
                                    mb_substr(basename((string) ($receipt['name'] ?? 'receipt')), 0, 190),
                                ]);
                                $pdo->commit();
                                header('Location: credits.php?bank_submitted=1');
                                exit;
                            } catch (Throwable $exception) {
                                if ($pdo->inTransaction()) {
                                    $pdo->rollBack();
                                }
                                @unlink($storedPath);
                                $error = $exception instanceof RuntimeException
                                    ? $exception->getMessage()
                                    : 'The receipt could not be submitted. Please try again.';
                            }
                        }
                    }
                }
            }
        }
    }

    $statement = $pdo->prepare('SELECT credit_balance, email FROM users WHERE id = ? AND deleted_at IS NULL LIMIT 1');
    $statement->execute([$userId]);
    $user = $statement->fetch();
    if ($user) {
        $creditBalance = max(0, (int) $user['credit_balance']);
        $userEmail = (string) $user['email'];
    }

    $cycleStatement = $pdo->prepare("SELECT COALESCE(SUM(CASE WHEN type = 'deduction' AND amount < 0 AND status = 'completed' THEN ABS(amount) ELSE 0 END), 0) AS used, COALESCE(SUM(CASE WHEN amount > 0 AND status = 'completed' THEN amount ELSE 0 END), 0) AS added FROM credit_transactions WHERE user_id = ? AND created_at >= ?");
    $cycleStatement->execute([$userId, $monthStart]);
    $cycle = $cycleStatement->fetch() ?: [];
    $creditsUsedThisCycle = (int) ($cycle['used'] ?? 0);
    $creditsAddedThisCycle = (int) ($cycle['added'] ?? 0);

    $historyStatement = $pdo->prepare('SELECT id, type, amount, reason, reference, payment_gateway, payment_amount, payment_currency, status, created_at FROM credit_transactions WHERE user_id = ? ORDER BY created_at DESC LIMIT 6');
    $historyStatement->execute([$userId]);
    $transactions = $historyStatement->fetchAll();

    $chartStatement = $pdo->prepare("SELECT DATE_FORMAT(created_at, '%Y-%m') AS period, COALESCE(SUM(CASE WHEN type = 'deduction' AND amount < 0 AND status = 'completed' THEN ABS(amount) ELSE 0 END), 0) AS used, COALESCE(SUM(CASE WHEN amount > 0 AND status = 'completed' THEN amount ELSE 0 END), 0) AS added FROM credit_transactions WHERE user_id = ? AND created_at >= ? GROUP BY DATE_FORMAT(created_at, '%Y-%m') ORDER BY period");
    $chartStatement->execute([$userId, $chartStart]);
    foreach ($chartStatement->fetchAll() as $chartRow) {
        $key = (string) $chartRow['period'];
        if (isset($chartMonths[$key])) {
            $chartMonths[$key]['used'] = (int) $chartRow['used'];
            $chartMonths[$key]['added'] = (int) $chartRow['added'];
        }
    }

    try {
        if ($tableExists($pdo, 'subscriptions') && $tableExists($pdo, 'plans')) {
            $subscriptionStatement = $pdo->prepare("SELECT s.status, s.starts_at, s.ends_at, s.renews_at, p.name AS plan_name, p.slug AS plan_slug, p.price_monthly, p.currency FROM subscriptions s JOIN plans p ON p.id = s.plan_id WHERE s.user_id = ? AND s.status IN ('active','trialing','past_due') ORDER BY FIELD(s.status,'active','trialing','past_due'), s.created_at DESC LIMIT 1");
            $subscriptionStatement->execute([$userId]);
            $subscription = $subscriptionStatement->fetch() ?: null;
        }
    } catch (Throwable $exception) {
        $subscription = null;
    }
}

$cyclePool = max(1, $creditBalance + $creditsUsedThisCycle);
$creditRemainingPercent = min(100, max(0, (int) round(($creditBalance / $cyclePool) * 100)));
$chartMaximum = 1;
foreach ($chartMonths as $chartMonth) {
    $chartMaximum = max($chartMaximum, $chartMonth['used'], $chartMonth['added']);
}
$usedPoints = [];
$addedPoints = [];
$pointIndex = 0;
foreach ($chartMonths as $chartMonth) {
    $x = 4 + ($pointIndex * 18.4);
    $usedY = 35 - (($chartMonth['used'] / $chartMaximum) * 27);
    $addedY = 35 - (($chartMonth['added'] / $chartMaximum) * 27);
    $usedPoints[] = number_format($x, 1, '.', '') . ',' . number_format($usedY, 1, '.', '');
    $addedPoints[] = number_format($x, 1, '.', '') . ',' . number_format($addedY, 1, '.', '');
    $pointIndex++;
}
$planName = $subscription ? (string) $subscription['plan_name'] : 'No active plan';
$planStatus = $subscription ? ucfirst((string) $subscription['status']) : 'Not subscribed';
$renewalDate = !empty($subscription['renews_at']) ? date('M j, Y', strtotime((string) $subscription['renews_at'])) : null;
$displaySlug = strtolower(preg_replace('/[^a-z0-9]+/i', '', $userName ?: 'user'));
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Wallet — <?= e($APP_NAME) ?></title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  <link rel="stylesheet" href="<?= e(xinng_public_base_url()) ?>/assets/css/dashboard.css?v=20261017-credits-workspace">
  <link rel="stylesheet" href="assets/css/workspace-sidebar.css?v=20261019e">
  <link rel="stylesheet" href="<?= e(xinng_public_base_url()) ?>/assets/css/credit-pricing.css?v=20261017-credits-workspace">
  <link rel="stylesheet" href="assets/css/wallet-dashboard.css?v=20261021a">
  <?php require __DIR__ . '/includes/google-analytics.php'; ?>
</head>
<body class="credits-screen wallet-screen workspace-screen workspace-shell-page">
  <div class="dashboard no-preview">
    <?php
      $sidebarActive = 'credits';
      $sidebarUserName = $userName;
      $sidebarDisplayName = $displaySlug ?: $userName;
      $sidebarBottom = [
          'eyebrow' => 'CREDITS REMAINING',
          'title' => number_format($creditBalance),
          'body' => $creditRemainingPercent . '% of your current credit pool remains.',
      ];
      include __DIR__ . '/includes/workspace-sidebar.php';
    ?>

    <main class="main">
      <?php $workspaceHeaderTitle = 'Wallet'; $workspaceHeaderSubtitle = 'Manage credits, your subscription, and payment activity.'; include __DIR__ . '/includes/workspace-shell-header.php'; ?>

      <div class="content wallet-content" id="workspace">
        <?php if ($notice): ?><div class="wallet-alert success" role="status"><i class="fa-solid fa-circle-check" aria-hidden="true"></i><span><?= e($notice) ?></span></div><?php endif; ?>
        <?php if ($error): ?><div class="wallet-alert error" role="alert"><i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i><span><?= e($error) ?></span></div><?php endif; ?>

        <section class="wallet-summary-grid" aria-label="Wallet summary">
          <article class="wallet-summary-card wallet-balance-card">
            <div class="wallet-card-heading"><span class="wallet-card-icon red"><i class="fa-solid fa-bolt" aria-hidden="true"></i></span><div><small>Available credits</small><strong><?= number_format($creditBalance) ?></strong></div></div>
            <div class="wallet-depletion-copy"><span>Credit depletion</span><b><?= $creditRemainingPercent ?>% remaining</b></div>
            <div class="wallet-depletion-track" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?= $creditRemainingPercent ?>" aria-label="Credits remaining"><span style="width:<?= $creditRemainingPercent ?>%"></span></div>
            <p><?= number_format($creditsUsedThisCycle) ?> used this month from a <?= number_format($cyclePool) ?> credit pool.</p>
          </article>

          <article class="wallet-summary-card">
            <div class="wallet-card-heading"><span class="wallet-card-icon orange"><i class="fa-solid fa-chart-line" aria-hidden="true"></i></span><div><small>Used this month</small><strong><?= number_format($creditsUsedThisCycle) ?></strong></div></div>
            <div class="wallet-summary-meta"><span>Credits added</span><b>+<?= number_format($creditsAddedThisCycle) ?></b></div>
            <p>Only completed usage and credit additions are included.</p>
          </article>

          <article class="wallet-summary-card wallet-plan-card">
            <div class="wallet-card-heading"><span class="wallet-card-icon purple"><i class="fa-solid fa-layer-group" aria-hidden="true"></i></span><div><small>Current plan</small><strong><?= e($planName) ?></strong></div><span class="wallet-status<?= $subscription ? ' active' : '' ?>"><?= e($planStatus) ?></span></div>
            <div class="wallet-summary-meta"><span><?= $renewalDate ? 'Next renewal' : 'Subscription' ?></span><b><?= $renewalDate ? e($renewalDate) : 'Choose a plan' ?></b></div>
            <a class="wallet-text-button" href="wallet/plans">View plan details <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
          </article>
        </section>

        <section class="wallet-actions" aria-label="Wallet actions">
          <button class="wallet-action-card" type="button" data-wallet-target="#top-up-options"><span class="wallet-action-icon"><i class="fa-solid fa-credit-card" aria-hidden="true"></i></span><span><strong>Top up credits</strong><small>Pay with Paystack, crypto, or transfer</small></span><i class="fa-solid fa-chevron-right" aria-hidden="true"></i></button>
          <a class="wallet-action-card" href="wallet/plans"><span class="wallet-action-icon"><i class="fa-solid fa-layer-group" aria-hidden="true"></i></span><span><strong>Choose a subscription</strong><small>Compare Free, Pro, and Business plans</small></span><i class="fa-solid fa-chevron-right" aria-hidden="true"></i></a>
          <button class="wallet-action-card" type="button" data-coming-soon="Invite & Earn is one of the three wallet pages we will build next."><span class="wallet-action-icon green"><i class="fa-solid fa-user-plus" aria-hidden="true"></i></span><span><strong>Invite &amp; earn</strong><small>Earn Xinng credits from qualified referrals</small></span><i class="fa-solid fa-chevron-right" aria-hidden="true"></i></button>
        </section>

        <section class="wallet-dashboard-grid">
          <article class="wallet-panel wallet-usage-panel">
            <div class="wallet-panel-heading"><div><h2>Credit activity</h2><p>Credits added and consumed over the last six months.</p></div><span class="wallet-period">6 months</span></div>
            <div class="wallet-chart-legend"><span class="used">Credits used</span><span class="added">Credits added</span></div>
            <div class="wallet-chart" aria-label="Six month credit activity chart">
              <svg viewBox="0 0 100 42" preserveAspectRatio="none" role="img" aria-label="Credits used and added by month">
                <g class="wallet-chart-grid"><line x1="0" y1="8" x2="100" y2="8"></line><line x1="0" y1="21.5" x2="100" y2="21.5"></line><line x1="0" y1="35" x2="100" y2="35"></line></g>
                <polyline class="wallet-line used" points="<?= e(implode(' ', $usedPoints)) ?>"></polyline>
                <polyline class="wallet-line added" points="<?= e(implode(' ', $addedPoints)) ?>"></polyline>
              </svg>
              <div class="wallet-chart-labels"><?php foreach ($chartMonths as $chartMonth): ?><span><?= e($chartMonth['label']) ?></span><?php endforeach; ?></div>
            </div>
            <div class="wallet-chart-totals"><div><span>Available now</span><strong><?= number_format($creditBalance) ?></strong></div><div><span>Used this month</span><strong><?= number_format($creditsUsedThisCycle) ?></strong></div><div><span>Added this month</span><strong><?= number_format($creditsAddedThisCycle) ?></strong></div></div>
          </article>

          <article class="wallet-panel wallet-activity-panel">
            <div class="wallet-panel-heading"><div><h2>Recent activity</h2><p>Your latest credit transactions.</p></div><button type="button" data-coming-soon="The full Transaction History page is next.">View all</button></div>
            <div class="wallet-activity-list">
              <?php if (!$transactions): ?><div class="wallet-empty"><i class="fa-regular fa-clock" aria-hidden="true"></i><p>No credit activity yet.</p><span>Your purchases and usage will appear here.</span></div><?php endif; ?>
              <?php foreach ($transactions as $transaction):
                  $amount = (int) ($transaction['amount'] ?? 0);
                  $status = (string) ($transaction['status'] ?? 'completed');
                  $isPositive = $amount > 0;
                  $activityClass = $status === 'pending' ? 'pending' : ($isPositive ? 'positive' : 'negative');
              ?>
                <div class="wallet-activity-row">
                  <span class="wallet-activity-arrow <?= e($activityClass) ?>"><i class="fa-solid <?= $isPositive ? 'fa-arrow-down' : 'fa-arrow-up' ?>" aria-hidden="true"></i></span>
                  <span class="wallet-activity-copy"><strong><?= e($transaction['reason'] ?: ucfirst(str_replace('_', ' ', (string) $transaction['type']))) ?></strong><small><?= e(date('M j · g:i A', strtotime((string) $transaction['created_at']))) ?><?= $status !== 'completed' ? ' · ' . e(ucfirst($status)) : '' ?></small></span>
                  <b class="wallet-activity-amount <?= e($activityClass) ?>"><?= $isPositive ? '+' : '−' ?><?= number_format(abs($amount)) ?><small> credits</small></b>
                </div>
              <?php endforeach; ?>
            </div>
          </article>
        </section>

        <section class="wallet-top-up" id="top-up-options" hidden>
          <?php $pricingContext = 'credits'; include __DIR__ . '/credit-pricing-component.php'; ?>
        </section>

        <div class="wallet-toast" role="status" aria-live="polite" hidden></div>
      </div>
    </main>
  </div>

  <?php if (isset($_GET['success'])): ?>
    <div class="wallet-success-overlay" data-wallet-success>
      <section class="wallet-success-dialog" role="dialog" aria-modal="true" aria-labelledby="top-up-success-title">
        <span class="wallet-success-check"><i class="fa-solid fa-check" aria-hidden="true"></i></span>
        <h2 id="top-up-success-title">Top Up Successful!</h2>
        <p><?= $topUpSuccessCredits && $topUpSuccessCredits > 0 ? number_format($topUpSuccessCredits) . ' credits have been added to your wallet.' : 'Your payment was successful and your credits have been added to your wallet.' ?></p>
        <a class="wallet-success-action" href="credits.php">Go to Wallet</a>
      </section>
    </div>
  <?php endif; ?>

  <div class="wallet-modal" data-bank-transfer-modal hidden>
    <button class="wallet-modal-backdrop" type="button" aria-label="Close bank transfer" data-bank-transfer-close></button>
    <section class="wallet-modal-card" role="dialog" aria-modal="true" aria-labelledby="bank-transfer-title">
      <button class="wallet-modal-close" type="button" aria-label="Close bank transfer" data-bank-transfer-close><i class="fa-solid fa-xmark" aria-hidden="true"></i></button>
      <span class="wallet-modal-eyebrow">BANK TRANSFER</span>
      <h2 id="bank-transfer-title">Transfer to Aqqute Labs</h2>
      <p>Choose a credit package, make the exact transfer, and upload the receipt. Credits are added only after verification.</p>

      <?php if ($bankTransferEnabled): ?>
        <dl class="wallet-bank-details">
          <div><dt>Bank</dt><dd><?= e($bankName) ?></dd></div>
          <div><dt>Account name</dt><dd><?= e($bankAccountName) ?></dd></div>
          <div><dt>Account number</dt><dd><?= e($bankAccountNumber) ?></dd></div>
          <div><dt>Your reference</dt><dd><?= e($bankReference) ?></dd></div>
        </dl>
        <form class="wallet-bank-form" method="post" enctype="multipart/form-data">
          <input type="hidden" name="action" value="submit_bank_transfer">
          <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
          <input type="hidden" name="transfer_reference" value="<?= e($bankReference) ?>">
          <label for="bank-package">Credit package</label>
          <select id="bank-package" name="package" required>
            <?php foreach ($packages as $package): ?><option value="<?= e($package['id']) ?>" data-price="<?= (int) $package['price'] ?>"><?= e($package['name']) ?> — <?= number_format((int) $package['credits']) ?> credits for ₦<?= number_format((int) $package['price']) ?></option><?php endforeach; ?>
          </select>
          <label for="bank-amount">Amount transferred (NGN)</label>
          <input id="bank-amount" name="payment_amount" type="number" min="1" step="1" value="<?= (int) $packages[0]['price'] ?>" required>
          <label for="bank-receipt">Payment receipt</label>
          <input id="bank-receipt" name="receipt" type="file" accept="image/jpeg,image/png,application/pdf" required>
          <small>JPG, PNG, or PDF. Maximum file size: 5 MB.</small>
          <button class="wallet-submit-transfer" type="submit">Send receipt for verification</button>
        </form>
      <?php else: ?>
        <div class="wallet-bank-unavailable"><i class="fa-solid fa-circle-info" aria-hidden="true"></i><div><strong>Bank details are being configured.</strong><p>Add the bank name and account number to the server environment before accepting receipts.</p></div></div>
      <?php endif; ?>
    </section>
  </div>

  <script src="https://js.paystack.co/v1/inline.js"></script>
  <script src="<?= e(xinng_public_base_url()) ?>/assets/js/credit-checkout.js"></script>
  <script src="assets/js/wallet-dashboard.js?v=20261021a"></script>
</body>
</html>
