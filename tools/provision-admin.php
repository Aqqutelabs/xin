<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once dirname(__DIR__) . '/config.php';

$email = xinng_normalize_email((string)($argv[1] ?? ''));
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    fwrite(STDERR, "Usage: php tools/provision-admin.php user@example.com\n");
    exit(1);
}

$pdo = get_db_connection();
if (!$pdo) exit(2);
xinng_ensure_workspace_tables($pdo);
xinng_ensure_admin_tables($pdo);

$stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
$stmt->execute([$email]);
$userId = (int)$stmt->fetchColumn();
if ($userId > 0) {
    $pdo->prepare('UPDATE users SET role = "admin", status = "active", deleted_at = NULL, updated_at = NOW() WHERE id = ?')->execute([$userId]);
    fwrite(STDOUT, "Updated administrator {$userId}.\n");
    exit;
}

$pdo->beginTransaction();
try {
    $unknownPassword = bin2hex(random_bytes(48));
    $hash = password_hash($unknownPassword, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare('INSERT INTO users (uuid, name, email, password_hash, credit_balance, credits_purchased_total, credits_used_total, status, role, created_at, updated_at) VALUES (UUID(), ?, ?, ?, 1000, 0, 0, "active", "admin", NOW(), NOW())');
    $stmt->execute(['Aqqute Admin', $email, $hash]);
    $userId = (int)$pdo->lastInsertId();
    xinng_create_organization($pdo, $userId, 'Aqqute Admin Workspace');
    $pdo->commit();
    fwrite(STDOUT, "Created administrator {$userId}. Use password recovery to activate access.\n");
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    throw $e;
}
