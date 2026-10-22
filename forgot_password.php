<?php
require_once __DIR__.'/config.php';
session_start();
$errors = [];
$sent = false;
$display_link = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Invalid request.';
    } else {
        $email = xinng_normalize_email((string)($_POST['email'] ?? ''));
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Provide a valid email address.';
        } else {
            $pdo = get_db_connection();
            if ($pdo) {
                try {
                    xinng_ensure_password_reset_table($pdo);
                    xinng_ensure_auth_security_tables($pdo);
                    if (xinng_auth_rate_limited($pdo, 'password_reset_request', $email, 3, 900)) {
                        $sent = true;
                        xinng_record_auth_event($pdo, 'password_reset_request', 'blocked', null, $email, ['reason' => 'rate_limit']);
                    } else {
                    $stmt = $pdo->prepare('SELECT id, name FROM users WHERE email = ? AND status = "active" LIMIT 1');
                    $stmt->execute([$email]);
                    $user = $stmt->fetch();
                    // Always show success to avoid account enumeration
                    $sent = true;
                    if ($user) {
                        $token = bin2hex(random_bytes(32));
                        $token_hash = hash('sha256', $token);
                        $expires = date('Y-m-d H:i:s', time() + 900);
                        $pdo->prepare('DELETE FROM password_resets WHERE user_id = ? OR expires_at < NOW()')->execute([(int)$user['id']]);
                        $ins = $pdo->prepare('INSERT INTO password_resets (user_id, token_hash, expires_at, created_at) VALUES (?, ?, ?, NOW())');
                        $ins->execute([(int)$user['id'], $token_hash, $expires]);
                        $resetUrl = xinng_public_base_url() . '/reset_password.php?token=' . urlencode($token);
                        $mailOk = xinng_send_password_reset_email((int)$user['id'], $email, $resetUrl);
                        if (!$mailOk && $APP_ENV !== 'production') {
                          $display_link = $resetUrl;
                        }
                    }
                    xinng_record_auth_event($pdo, 'password_reset_request', 'requested', $user ? (int)$user['id'] : null, $email);
                    }
                } catch (Throwable $e) {
                    error_log($e->getMessage());
                    $errors[] = 'Server error. Try again later.';
                }
            } else {
                $errors[] = 'Unable to connect to the database.';
            }
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Reset password — <?= htmlspecialchars($APP_NAME) ?></title>
  <link rel="stylesheet" href="assets/css/brand.css">
  <link rel="stylesheet" href="assets/css/auth.css?v=<?= filemtime(__DIR__ . '/assets/css/auth.css') ?>">
  <?php require __DIR__ . '/includes/google-analytics.php'; ?>
</head>
<body class="auth-page">
  <main class="auth-wrap auth-wrap--single">
    <section class="auth-card">
      <div class="auth-head">
        <a class="xinng-brand xinng-brand--auth" href="<?= e(xinng_public_base_url()) ?>/index.php" aria-label="Xinng home"><img class="xinng-brand__image" src="<?= e(xinng_brand_logo_url(xinng_public_base_url())) ?>" alt="Xinng" width="1736" height="906"></a>
        <h1>Reset your password</h1>
        <p class="small">Enter the email for your account and we'll send a reset link.</p>
      </div>

      <?php foreach ($errors as $err): ?>
        <div class="error" role="alert"><?= htmlspecialchars($err) ?></div>
      <?php endforeach; ?>

      <?php if ($sent): ?>
        <div class="notice" role="status">If an account exists for that email, a reset link has been sent.</div>
        <?php if ($display_link): ?>
          <div class="small" style="margin-top:8px">Development reset link: <a href="<?= htmlspecialchars($display_link) ?>">Open reset link</a></div>
        <?php endif; ?>
      <?php else: ?>
        <form method="post" class="auth-form" novalidate>
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
          <label for="reset-email">Email address</label>
          <input id="reset-email" name="email" type="email" autocomplete="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">

          <div class="form-foot">
            <button class="btn brand-gradient-btn" type="submit">Send reset link</button>
          </div>
        </form>
      <?php endif; ?>

      <div style="text-align:center;margin-top:12px" class="small">Remembered? <a href="signin.php">Sign in</a></div>
    </section>
  </main>
</body>
</html>
