<?php
require_once __DIR__ . '/config.php';

header('Content-Type: application/json; charset=utf-8');

if (empty(NOWPAYMENTS_IPN_SECRET)) {
    http_response_code(503);
    echo json_encode(['ok' => false]);
    exit;
}

$rawBody = file_get_contents('php://input');
$signature = (string)($_SERVER['HTTP_X_NOWPAYMENTS_SIG'] ?? '');
if ($rawBody === false || $rawBody === '' || $signature === '') {
    http_response_code(400);
    echo json_encode(['ok' => false]);
    exit;
}

$payload = json_decode($rawBody, true);
if (!is_array($payload)) {
    http_response_code(400);
    echo json_encode(['ok' => false]);
    exit;
}

$signingPayload = $payload;
function xinng_sort_ipn_payload(array &$value): void {
    foreach ($value as &$child) {
        if (is_array($child)) xinng_sort_ipn_payload($child);
    }
    unset($child);
    ksort($value);
}
xinng_sort_ipn_payload($signingPayload);
$expectedSignature = hash_hmac('sha512', json_encode($signingPayload, JSON_UNESCAPED_SLASHES), NOWPAYMENTS_IPN_SECRET);
if (!hash_equals($expectedSignature, $signature)) {
    http_response_code(403);
    echo json_encode(['ok' => false]);
    exit;
}

$reference = trim((string)($payload['order_id'] ?? ''));
$paymentId = trim((string)($payload['payment_id'] ?? ''));
if ($reference === '' || $paymentId === '') {
    http_response_code(400);
    echo json_encode(['ok' => false]);
    exit;
}

$pdo = get_db_connection();
if (!$pdo) {
    http_response_code(500);
    echo json_encode(['ok' => false]);
    exit;
}

xinng_ensure_credit_tables($pdo);
$stmt = $pdo->prepare('SELECT * FROM credit_transactions WHERE reference = ? AND payment_gateway = "nowpayments" LIMIT 1');
$stmt->execute([$reference]);
$transaction = $stmt->fetch();
if (!$transaction) {
    http_response_code(404);
    echo json_encode(['ok' => false]);
    exit;
}

if ($transaction['status'] === 'completed') {
    echo json_encode(['ok' => true]);
    exit;
}

$package = null;
foreach (xinng_credit_packages() as $candidate) {
    if ((int)$candidate['credits'] === (int)$transaction['amount']) {
        $package = $candidate;
        break;
    }
}
if (!$package) {
    http_response_code(400);
    echo json_encode(['ok' => false]);
    exit;
}

$paymentStatus = strtolower((string)($payload['payment_status'] ?? ''));
if (in_array($paymentStatus, ['failed', 'expired', 'refunded'], true)) {
    $stmt = $pdo->prepare('UPDATE credit_transactions SET status = ? WHERE id = ? AND status = "pending"');
    $stmt->execute(['failed', (int)$transaction['id']]);
    echo json_encode(['ok' => true]);
    exit;
}

if (!in_array($paymentStatus, ['finished', 'confirmed'], true)) {
    echo json_encode(['ok' => true]);
    exit;
}

$verified = xinng_nowpayments_request('GET', '/payment/' . rawurlencode($paymentId));
$payment = $verified['data'];
if (!$verified['ok'] || !is_array($payment)) {
    http_response_code(502);
    echo json_encode(['ok' => false]);
    exit;
}

$verifiedStatus = strtolower((string)($payment['payment_status'] ?? ''));
$priceAmount = (float)($payment['price_amount'] ?? $payload['price_amount'] ?? 0);
$priceCurrency = strtoupper((string)($payment['price_currency'] ?? $payload['price_currency'] ?? ''));
$orderId = (string)($payment['order_id'] ?? $reference);
if (!in_array($verifiedStatus, ['finished', 'confirmed'], true)
    || $orderId !== $reference
    || $priceCurrency !== NOWPAYMENTS_PRICE_CURRENCY
    || abs($priceAmount - (float)$package['price']) > 0.01) {
    http_response_code(400);
    echo json_encode(['ok' => false]);
    exit;
}

try {
    $completed = xinng_complete_credit_purchase($pdo, $transaction, $package, $priceAmount, $priceCurrency, 'nowpayments');
    if (!$completed) {
        http_response_code(409);
        echo json_encode(['ok' => false]);
        exit;
    }
    echo json_encode(['ok' => true]);
} catch (Throwable $e) {
    error_log('NOWPayments credit completion failed: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false]);
}
