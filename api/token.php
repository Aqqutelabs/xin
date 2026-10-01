<?php
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json; charset=utf-8');

if (session_status() !== PHP_SESSION_ACTIVE) session_start();
if (empty($_SESSION['user_id'])) {
	http_response_code(401);
	echo json_encode(['ok' => false, 'error' => 'auth']);
	exit;
}

$pdo = get_db_connection();
if (!$pdo) {
	http_response_code(500);
	echo json_encode(['ok' => false, 'error' => 'db']);
	exit;
}
xinng_ensure_api_token_table($pdo);
$userId = (int)$_SESSION['user_id'];
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$payload = json_decode(file_get_contents('php://input'), true);
if (!is_array($payload)) $payload = $_POST;

if ($method === 'GET') {
	$stmt = $pdo->prepare('SELECT created_at FROM api_tokens WHERE user_id = ? LIMIT 1');
	$stmt->execute([$userId]);
	$createdAt = $stmt->fetchColumn();
	echo json_encode(['ok' => true, 'configured' => $createdAt !== false, 'created_at' => $createdAt !== false ? $createdAt : null]);
	exit;
}

if (!in_array($method, ['POST', 'DELETE'], true)) {
	http_response_code(405);
	echo json_encode(['ok' => false, 'error' => 'method_not_allowed']);
	exit;
}
if (!verify_csrf_token($payload['csrf_token'] ?? null)) {
	http_response_code(403);
	echo json_encode(['ok' => false, 'error' => 'csrf']);
	exit;
}

if ($method === 'DELETE') {
	$stmt = $pdo->prepare('DELETE FROM api_tokens WHERE user_id = ?');
	$stmt->execute([$userId]);
	echo json_encode(['ok' => true, 'revoked' => $stmt->rowCount() > 0]);
	exit;
}

$token = xinng_issue_api_token($pdo, $userId);
echo json_encode(['ok' => true, 'token' => $token, 'message' => 'Copy this token now; it will not be shown again.']);