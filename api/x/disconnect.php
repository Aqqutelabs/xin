<?php
declare(strict_types=1);
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../services/x/XAccountStore.php';
use Xinng\X\XAccountStore;

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
session_start();
if (empty($_SESSION['user_id'])) { http_response_code(401); echo json_encode(['error' => 'Sign in required.']); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Allow: POST'); http_response_code(405); echo json_encode(['error' => 'Use POST.']); exit; }
if (!verify_csrf_token($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '')) { http_response_code(403); echo json_encode(['error' => 'Invalid CSRF token.']); exit; }
$pdo = get_db_connection();
if (!$pdo) { http_response_code(503); echo json_encode(['error' => 'Database unavailable.']); exit; }
try {
    xinng_ensure_x_account_tables($pdo);
    (new XAccountStore($pdo, X_TOKEN_ENCRYPTION_KEY))->disconnect((int)$_SESSION['user_id']);
    echo json_encode(['connected' => false]);
} catch (Throwable $error) {
    error_log('X disconnect failed: ' . $error->getMessage());
    http_response_code(503);
    echo json_encode(['error' => 'X account could not be disconnected.']);
}
