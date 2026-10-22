<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
$baseUrl = rtrim(xinng_public_base_url(), '/');
$userId = filter_var($_SESSION['user_id'] ?? null, FILTER_VALIDATE_INT);
if (!$userId) {
	header('Location: ' . $baseUrl . '/signin.php', true, 303);
	exit;
}

$reference = trim((string)($_GET['reference'] ?? ''));
if ($reference === '' || strlen($reference) > 190) {
	header('Location: ' . $baseUrl . '/wallet/plans?error=invalid_payment', true, 303);
	exit;
}

$pdo = get_db_connection();
if (!$pdo) {
	header('Location: ' . $baseUrl . '/wallet/plans?error=server', true, 303);
	exit;
}

try {
	xinng_ensure_subscription_billing_tables($pdo);
	$paymentStatement = $pdo->prepare("SELECT id,user_id,plan_id,billing_interval,amount,currency,status,subscription_id FROM payments WHERE user_id = ? AND provider_reference = ? AND payment_provider = 'paystack' LIMIT 1");
	$paymentStatement->execute([(int)$userId, $reference]);
	$payment = $paymentStatement->fetch();
	if (!$payment) {
		header('Location: ' . $baseUrl . '/wallet/plans?error=payment_not_found', true, 303);
		exit;
	}
	if ($payment['status'] === 'successful') {
		header('Location: ' . $baseUrl . '/wallet/plans?subscription_success=1', true, 303);
		exit;
	}
	if ($payment['status'] !== 'pending') {
		header('Location: ' . $baseUrl . '/wallet/plans?error=payment_failed', true, 303);
		exit;
	}
	if (empty(PAYSTACK_SECRET_KEY)) {
		header('Location: ' . $baseUrl . '/wallet/plans?error=payments_not_configured', true, 303);
		exit;
	}

	$curl = curl_init('https://api.paystack.co/transaction/verify/' . rawurlencode($reference));
	curl_setopt_array($curl, [
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_HTTPHEADER => [
			'Authorization: Bearer ' . PAYSTACK_SECRET_KEY,
			'Content-Type: application/json',
		],
		CURLOPT_TIMEOUT => 20,
	]);
	$response = curl_exec($curl);
	$httpCode = (int)curl_getinfo($curl, CURLINFO_HTTP_CODE);
	$curlError = curl_error($curl);
	curl_close($curl);
	if ($response === false || $curlError !== '' || $httpCode !== 200) {
		error_log('Subscription payment verification failed: ' . ($curlError ?: 'Paystack returned HTTP ' . $httpCode));
		header('Location: ' . $baseUrl . '/wallet/plans?error=verification_failed', true, 303);
		exit;
	}

	$verification = json_decode($response, true);
	$data = is_array($verification) ? ($verification['data'] ?? null) : null;
	if (empty($verification['status']) || !is_array($data) || ($data['status'] ?? '') !== 'success' || (string)($data['reference'] ?? '') !== $reference) {
		$pdo->prepare("UPDATE payments SET status = 'failed', updated_at = NOW() WHERE id = ? AND status = 'pending'")
			->execute([(int)$payment['id']]);
		header('Location: ' . $baseUrl . '/wallet/plans?error=payment_failed', true, 303);
		exit;
	}

	$paidAmount = (int)($data['amount'] ?? 0);
	$paidCurrency = strtoupper((string)($data['currency'] ?? ''));
	if ($paidAmount !== (int)round((float)$payment['amount'] * 100) || $paidCurrency !== strtoupper((string)$payment['currency'])) {
		$pdo->prepare("UPDATE payments SET status = 'failed', updated_at = NOW() WHERE id = ? AND status = 'pending'")
			->execute([(int)$payment['id']]);
		header('Location: ' . $baseUrl . '/wallet/plans?error=verification_failed', true, 303);
		exit;
	}

	$interval = (string)$payment['billing_interval'];
	if (!in_array($interval, ['monthly', 'yearly'], true) || !(int)$payment['plan_id']) {
		throw new RuntimeException('Subscription payment does not have a valid plan period.');
	}
	$period = $interval === 'yearly' ? '1 YEAR' : '1 MONTH';
	$pdo->beginTransaction();
	$lockedPaymentStatement = $pdo->prepare('SELECT status FROM payments WHERE id = ? AND user_id = ? LIMIT 1 FOR UPDATE');
	$lockedPaymentStatement->execute([(int)$payment['id'], (int)$userId]);
	$lockedStatus = $lockedPaymentStatement->fetchColumn();
	if ($lockedStatus === 'successful') {
		$pdo->commit();
		header('Location: ' . $baseUrl . '/wallet/plans?subscription_success=1', true, 303);
		exit;
	}
	if ($lockedStatus !== 'pending') {
		$pdo->rollBack();
		header('Location: ' . $baseUrl . '/wallet/plans?error=payment_failed', true, 303);
		exit;
	}

	$userLockStatement = $pdo->prepare('SELECT id FROM users WHERE id = ? AND deleted_at IS NULL LIMIT 1 FOR UPDATE');
	$userLockStatement->execute([(int)$userId]);
	if (!$userLockStatement->fetchColumn()) {
		$pdo->rollBack();
		header('Location: ' . $baseUrl . '/wallet/plans?error=server', true, 303);
		exit;
	}

	$planStatement = $pdo->prepare('SELECT id FROM plans WHERE id = ? AND is_active = 1 LIMIT 1');
	$planStatement->execute([(int)$payment['plan_id']]);
	if (!$planStatement->fetchColumn()) throw new RuntimeException('The selected plan is no longer active.');

	$pdo->prepare("UPDATE subscriptions SET status = 'cancelled', cancelled_at = NOW(), ends_at = NOW(), updated_at = NOW() WHERE user_id = ? AND status IN ('active','trialing','past_due') AND (ends_at IS NULL OR ends_at > NOW())")
		->execute([(int)$userId]);
	$pdo->prepare("INSERT INTO subscriptions (user_id, plan_id, status, payment_provider, billing_interval, starts_at, ends_at, renews_at, created_at, updated_at) VALUES (?, ?, 'active', 'paystack', ?, NOW(), DATE_ADD(NOW(), INTERVAL {$period}), DATE_ADD(NOW(), INTERVAL {$period}), NOW(), NOW())")
		->execute([(int)$userId, (int)$payment['plan_id'], $interval]);
	$subscriptionId = (int)$pdo->lastInsertId();
	$pdo->prepare("UPDATE payments SET subscription_id = ?, status = 'successful', paid_at = NOW(), updated_at = NOW() WHERE id = ? AND status = 'pending'")
		->execute([$subscriptionId, (int)$payment['id']]);
	$pdo->commit();
	header('Location: ' . $baseUrl . '/wallet/plans?subscription_success=1', true, 303);
	exit;
} catch (Throwable $error) {
	if ($pdo->inTransaction()) $pdo->rollBack();
	error_log('Subscription payment completion failed: ' . $error->getMessage());
	header('Location: ' . $baseUrl . '/wallet/plans?error=server', true, 303);
	exit;
}
