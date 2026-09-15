<?php
require_once __DIR__ . '/config.php';
session_start();

header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'auth']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'bad_request']);
    exit;
}

if (empty(NOWPAYMENTS_API_KEY) || empty(NOWPAYMENTS_IPN_SECRET)) {
    http_response_code(503);
    echo json_encode(['ok' => false, 'error' => 'not_configured']);
    exit;
}

$packageId = trim((string)($_POST['package'] ?? ''));
$package = xinng_credit_package($packageId);
if (!$package) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'invalid_package']);
    exit;
}

$pdo = get_db_connection();
if (!$pdo) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'server']);
    exit;
}

$userId = (int)$_SESSION['user_id'];
xinng_ensure_credit_tables($pdo);
$stmt = $pdo->prepare('SELECT email FROM users WHERE id = ? AND deleted_at IS NULL LIMIT 1');
$stmt->execute([$userId]);
$user = $stmt->fetch();
if (!$user || empty($user['email'])) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'auth']);
    exit;
}

$reference = 'xinng-now-' . $userId . '-' . time() . '-' . bin2hex(random_bytes(4));
$stmt = $pdo->prepare('INSERT INTO credit_transactions (user_id, type, amount, reason, reference, payment_gateway, payment_amount, payment_currency, status, created_at) VALUES (?, "purchase", ?, "Pending NOWPayments purchase", ?, "nowpayments", ?, ?, "pending", NOW())');
$stmt->execute([$userId, (int)$package['credits'], $reference, (int)$package['price'], NOWPAYMENTS_PRICE_CURRENCY]);
$transactionId = (int)$pdo->lastInsertId();

$payload = [
    'price_amount' => (float)$package['price'],
    'price_currency' => strtolower(NOWPAYMENTS_PRICE_CURRENCY),
    'order_id' => $reference,
    'order_description' => $package['name'] . ' - ' . $package['credits'] . ' credits',
    'ipn_callback_url' => xinng_public_base_url() . '/nowpayments_ipn.php',
    'success_url' => xinng_public_base_url() . '/credits.php?success=1',
    'cancel_url' => xinng_public_base_url() . '/credits.php?error=payment_cancelled',
];
$result = xinng_nowpayments_request('POST', '/invoice', $payload);
$invoiceUrl = (string)($result['data']['invoice_url'] ?? '');
if (!$result['ok'] || $invoiceUrl === '') {
    $stmt = $pdo->prepare('UPDATE credit_transactions SET status = ? WHERE id = ? AND user_id = ?');
    $stmt->execute(['failed', $transactionId, $userId]);
    http_response_code(502);
    echo json_encode(['ok' => false, 'error' => 'payment_request_failed']);
    exit;
}

echo json_encode([
    'ok' => true,
    'invoice_url' => $invoiceUrl,
    'reference' => $reference,
]);
