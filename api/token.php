<?php
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json; charset=utf-8');

$pdo = get_db_connection();
if (!$pdo) {
	http_response_code(500);
	echo json_encode(['ok' => false, 'error' => 'db']);
	exit;
}
xinng_ensure_api_token_table($pdo);
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$payload = json_decode(file_get_contents('php://input'), true);
if (!is_array($payload)) $payload = $_POST;
$userId = xinng_public_api_user_id($payload);
if ($userId <= 0) {
	http_response_code(422);
	echo json_encode(['ok' => false, 'error' => 'missing_user_id']);
	exit;
}

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
if ($method === 'DELETE') {
	$stmt = $pdo->prepare('DELETE FROM api_tokens WHERE user_id = ?');
	$stmt->execute([$userId]);
	echo json_encode(['ok' => true, 'revoked' => $stmt->rowCount() > 0]);
	exit;
}

$token = xinng_issue_api_token($pdo, $userId);
echo json_encode(['ok' => true, 'token' => $token, 'message' => 'Copy this token now; it will not be shown again.']);