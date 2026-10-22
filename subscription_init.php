<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
header('Content-Type: application/json; charset=utf-8');

$respond = static function (int $status, array $payload): void {
	http_response_code($status);
	echo json_encode($payload);
	exit;
};

$userId = filter_var($_SESSION['user_id'] ?? null, FILTER_VALIDATE_INT);
if (!$userId) $respond(401, ['ok' => false, 'error' => 'auth']);
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') $respond(405, ['ok' => false, 'error' => 'bad_request']);
if (!verify_csrf_token(isset($_POST['csrf_token']) ? (string)$_POST['csrf_token'] : null)) {
	$respond(403, ['ok' => false, 'error' => 'csrf']);
}

$planId = filter_var($_POST['plan_id'] ?? null, FILTER_VALIDATE_INT);
$interval = (string)($_POST['billing_interval'] ?? 'monthly');
if (!$planId || !in_array($interval, ['monthly', 'yearly'], true)) {
	$respond(400, ['ok' => false, 'error' => 'invalid_plan']);
}

$pdo = get_db_connection();
if (!$pdo) $respond(503, ['ok' => false, 'error' => 'server']);

try {
	xinng_ensure_subscription_billing_tables($pdo);
	$planStatement = $pdo->prepare('SELECT id, name, slug, price_monthly, price_yearly, currency, is_active FROM plans WHERE id = ? AND is_active = 1 LIMIT 1');
	$planStatement->execute([(int)$planId]);
	$plan = $planStatement->fetch();
	if (!$plan) $respond(404, ['ok' => false, 'error' => 'invalid_plan']);

	$monthlyPrice = (float)$plan['price_monthly'];
	$yearlyPrice = (float)$plan['price_yearly'];
	$amount = $interval === 'yearly' ? $yearlyPrice : $monthlyPrice;
	if ($amount < 0 || ($amount === 0.0 && ($monthlyPrice !== 0.0 || $yearlyPrice !== 0.0))) {
		$respond(400, ['ok' => false, 'error' => 'invalid_plan']);
	}

	if ($monthlyPrice === 0.0 && $yearlyPrice === 0.0) {
		$pdo->beginTransaction();
		$userLock = $pdo->prepare('SELECT id FROM users WHERE id = ? AND deleted_at IS NULL LIMIT 1 FOR UPDATE');
		$userLock->execute([(int)$userId]);
		if (!$userLock->fetchColumn()) {
			$pdo->rollBack();
			$respond(401, ['ok' => false, 'error' => 'auth']);
		}
		$pdo->prepare("UPDATE subscriptions SET status = 'cancelled', cancelled_at = NOW(), ends_at = NOW(), updated_at = NOW() WHERE user_id = ? AND status IN ('active','trialing','past_due') AND (ends_at IS NULL OR ends_at > NOW())")
			->execute([(int)$userId]);
		$pdo->prepare("INSERT INTO subscriptions (user_id, plan_id, status, payment_provider, billing_interval, starts_at, ends_at, renews_at, created_at, updated_at) VALUES (?, ?, 'active', 'manual', NULL, NOW(), NULL, NULL, NOW(), NOW())")
			->execute([(int)$userId, (int)$plan['id']]);
		$pdo->commit();
		$respond(200, ['ok' => true, 'free' => true, 'plan' => (string)$plan['name']]);
	}

	if (strtoupper((string)$plan['currency']) !== 'NGN') {
		$respond(400, ['ok' => false, 'error' => 'unsupported_currency']);
	}
	if (empty(PAYSTACK_PUBLIC_KEY) || empty(PAYSTACK_SECRET_KEY)) {
		$respond(503, ['ok' => false, 'error' => 'payments_not_configured']);
	}
	$userStatement = $pdo->prepare('SELECT email FROM users WHERE id = ? AND deleted_at IS NULL LIMIT 1');
	$userStatement->execute([(int)$userId]);
	$user = $userStatement->fetch();
	if (!$user || empty($user['email'])) $respond(401, ['ok' => false, 'error' => 'auth']);

	$reference = 'xinng-sub-' . (int)$userId . '-' . time() . '-' . bin2hex(random_bytes(6));
	$paymentStatement = $pdo->prepare("INSERT INTO payments (user_id, plan_id, billing_interval, amount, currency, payment_provider, provider_reference, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, 'paystack', ?, 'pending', NOW(), NOW())");
	$paymentStatement->execute([(int)$userId, (int)$plan['id'], $interval, $amount, strtoupper((string)$plan['currency']), $reference]);

	$respond(200, [
		'ok' => true,
		'reference' => $reference,
		'amount' => (int)round($amount * 100),
		'currency' => strtoupper((string)$plan['currency']),
		'email' => (string)$user['email'],
		'plan' => (string)$plan['name'],
		'interval' => $interval,
	]);
} catch (Throwable $error) {
	if ($pdo->inTransaction()) $pdo->rollBack();
	error_log('Subscription checkout initialization failed: ' . $error->getMessage());
	$respond(500, ['ok' => false, 'error' => 'server']);
}
