<?php
declare(strict_types=1);
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../services/x/XAccountStore.php';
use Xinng\X\XAccountStore;

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
session_start();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Allow: POST'); http_response_code(405); echo json_encode(['error' => 'Use POST.']); exit; }
$payload = json_decode(file_get_contents('php://input'), true);
if (!is_array($payload)) $payload = $_POST;
$userId = xinng_public_api_user_id($payload);
if ($userId <= 0) { http_response_code(422); echo json_encode(['error' => 'missing_user_id']); exit; }
$pdo = get_db_connection();
if (!$pdo) { http_response_code(503); echo json_encode(['error' => 'Database unavailable.']); exit; }
try {
    xinng_ensure_x_account_tables($pdo);
    (new XAccountStore($pdo, X_TOKEN_ENCRYPTION_KEY))->disconnect($userId);
    echo json_encode(['connected' => false]);
} catch (Throwable $error) {
    error_log('X disconnect failed: ' . $error->getMessage());
    http_response_code(503);
    echo json_encode(['error' => 'X account could not be disconnected.']);
}
