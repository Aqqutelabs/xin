<?php
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json; charset=utf-8');

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
	header('Allow: POST');
	http_response_code(405);
	echo json_encode(['ok' => false, 'error' => 'method_not_allowed']);
	exit;
}

$payload = json_decode(file_get_contents('php://input'), true);
if (!is_array($payload)) {
	http_response_code(400);
	echo json_encode(['ok' => false, 'error' => 'invalid_json']);
	exit;
}

$name = trim((string)($payload['name'] ?? ''));
$email = trim((string)($payload['email'] ?? ''));
$password = (string)($payload['password'] ?? '');
$requestedSlug = trim((string)($payload['slug'] ?? ''));

if ($name === '' || $email === '' || $password === '') {
	http_response_code(422);
	echo json_encode(['ok' => false, 'error' => 'missing_fields', 'required' => ['name', 'email', 'password']]);
	exit;
}
if (mb_strlen($name, 'UTF-8') > 120 || strlen($email) > 190) {
	http_response_code(422);
	echo json_encode(['ok' => false, 'error' => 'field_too_long']);
	exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
	http_response_code(422);
	echo json_encode(['ok' => false, 'error' => 'invalid_email']);
	exit;
}

$pdo = get_db_connection();
if (!$pdo) {
	http_response_code(500);
	echo json_encode(['ok' => false, 'error' => 'db']);
	exit;
}

try {
	xinng_ensure_short_link_tables($pdo);
	xinng_ensure_page_builder_tables($pdo);
	xinng_ensure_credit_tables($pdo);
	xinng_ensure_api_token_table($pdo);

	if ($requestedSlug !== '') {
		$slugCheck = xinng_validate_page_slug($pdo, $requestedSlug);
		if (!$slugCheck['ok']) {
			http_response_code(422);
			echo json_encode(['ok' => false, 'error' => 'invalid_slug', 'message' => $slugCheck['error']]);
			exit;
		}
		$slug = $slugCheck['slug'];
	} else {
		$baseSlug = xinng_normalize_back_half($name) ?: 'user';
		$baseSlug = trim(substr($baseSlug, 0, 56), '-') ?: 'user';
		$slug = $baseSlug;
		$suffix = 1;
		while (true) {
			$slugCheck = xinng_validate_page_slug($pdo, $slug);
			if ($slugCheck['ok']) {
				$slug = $slugCheck['slug'];
				break;
			}
			$suffix++;
			$slug = $baseSlug . '-' . $suffix;
		}
	}

	$pdo->beginTransaction();
	$passwordHash = password_hash($password, PASSWORD_DEFAULT);
	$stmt = $pdo->prepare('INSERT INTO users (uuid, name, email, password_hash, credit_balance, credits_purchased_total, credits_used_total, created_at, updated_at) VALUES (UUID(), ?, ?, ?, 1000, 0, 0, NOW(), NOW())');
	$stmt->execute([$name, $email, $passwordHash]);
	$userId = (int)$pdo->lastInsertId();
	$apiToken = xinng_issue_api_token($pdo, $userId);

	$stmt = $pdo->prepare('INSERT INTO credit_transactions (user_id, type, amount, reason, reference, created_at) VALUES (?, "signup_bonus", 1000, "Signup bonus", "signup", NOW())');
	$stmt->execute([$userId]);
	$stmt = $pdo->prepare('INSERT INTO pages (user_id, page_type, slug, title, created_at, updated_at) VALUES (?, "creator", ?, ?, NOW(), NOW())');
	$stmt->execute([$userId, $slug, $name]);
	$pageId = (int)$pdo->lastInsertId();
	$pdo->commit();

	try {
		xinng_send_welcome_email($userId, $email, $name);
		xinng_create_notification($pdo, $userId, 'account', 'Welcome to Xinng', 'Your account has been created and is ready to use.', 'dashboard.php');
	} catch (Throwable $sideEffectError) {
		error_log('API signup post-creation action failed: ' . $sideEffectError->getMessage());
	}

	http_response_code(201);
	echo json_encode([
		'ok' => true,
		'user' => [
			'id' => $userId,
			'name' => $name,
			'email' => $email,
			'page_id' => $pageId,
			'page_slug' => $slug,
			'credit_balance' => 1000,
		],
		'api_token' => $apiToken,
		'token_message' => 'Store this token securely. It is only returned during signup.',
	], JSON_UNESCAPED_SLASHES);
} catch (PDOException $e) {
	if ($pdo->inTransaction()) $pdo->rollBack();
	if (stripos($e->getMessage(), 'duplicate') !== false) {
		http_response_code(409);
		echo json_encode(['ok' => false, 'error' => 'email_already_exists']);
		exit;
	}
	error_log('API signup failed: ' . $e->getMessage());
	http_response_code(500);
	echo json_encode(['ok' => false, 'error' => 'signup_failed']);
} catch (Throwable $e) {
	if ($pdo->inTransaction()) $pdo->rollBack();
	error_log('API signup failed: ' . $e->getMessage());
	http_response_code(500);
	echo json_encode(['ok' => false, 'error' => 'signup_failed']);
}