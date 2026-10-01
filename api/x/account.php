<?php
declare(strict_types=1);
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../services/x/XAccountStore.php';
use Xinng\X\XAccountStore;

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
session_start();
if (empty($_SESSION['user_id'])) { http_response_code(401); echo json_encode(['error' => 'Sign in required.']); exit; }
$pdo = get_db_connection();
if (!$pdo) { http_response_code(503); echo json_encode(['error' => 'Database unavailable.']); exit; }
try {
    xinng_ensure_x_account_tables($pdo);
    $account = (new XAccountStore($pdo, X_TOKEN_ENCRYPTION_KEY))->find((int)$_SESSION['user_id']);
    echo json_encode(['connected' => $account !== null, 'account' => $account], JSON_THROW_ON_ERROR);
} catch (Throwable $error) {
    error_log('X account status failed: ' . $error->getMessage());
    http_response_code(503);
    echo json_encode(['error' => 'X account status unavailable.']);
}
