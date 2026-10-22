<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config.php';
$pdo = get_db_connection();
if (!$pdo) exit(2);
xinng_ensure_admin_tables($pdo);

$stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
$stmt->execute(['aqqute.dev@gmail.com']);
$adminId = (int)$stmt->fetchColumn();
if ($adminId <= 0 || !xinng_admin_user($pdo, $adminId)) {
    fwrite(STDERR, "FAIL: requested administrator is not active.\n");
    exit(1);
}

$ordinaryId = (int)$pdo->query('SELECT id FROM users WHERE role = "user" AND status = "active" AND deleted_at IS NULL LIMIT 1')->fetchColumn();
if ($ordinaryId > 0 && xinng_admin_user($pdo, $ordinaryId)) {
    fwrite(STDERR, "FAIL: ordinary user received administrator access.\n");
    exit(1);
}

fwrite(STDOUT, "Admin access checks passed.\n");
