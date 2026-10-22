<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config.php';

$failures = [];
$expect = static function (bool $condition, string $message) use (&$failures): void {
    if (!$condition) $failures[] = $message;
};

$expect(xinng_normalize_email('  User@Example.COM ') === 'user@example.com', 'Email normalization failed.');
$expect(xinng_password_error('short') !== null, 'Short passwords must be rejected.');
$expect(xinng_password_error('abcdefgh') !== null, 'Passwords without a number must be rejected.');
$expect(xinng_password_error('12345678') !== null, 'Passwords without a letter must be rejected.');
$expect(xinng_password_error('secure123') === null, 'Valid passwords must be accepted.');

if (session_status() !== PHP_SESSION_ACTIVE) session_start();
unset($_SESSION['_csrf_token']);
$csrf = csrf_token();
$expect(strlen($csrf) === 48, 'CSRF token length is invalid.');
$expect(verify_csrf_token($csrf), 'Generated CSRF token must verify.');
$expect(!verify_csrf_token('invalid'), 'Invalid CSRF token must fail verification.');

$pdo = get_db_connection();
if ($pdo) {
    xinng_ensure_auth_security_tables($pdo);
    $identifier = 'auth-test-' . bin2hex(random_bytes(8)) . '@example.invalid';
    $pdo->beginTransaction();
    try {
        for ($attempt = 0; $attempt < 3; $attempt++) {
            xinng_record_auth_event($pdo, 'auth_test', 'failed', null, $identifier, ['test' => true]);
        }
        $expect(!xinng_auth_rate_limited($pdo, 'auth_test', $identifier, 4, 900), 'Rate limit activated too early.');
        xinng_record_auth_event($pdo, 'auth_test', 'failed', null, $identifier, ['test' => true]);
        $expect(xinng_auth_rate_limited($pdo, 'auth_test', $identifier, 4, 900), 'Rate limit did not activate.');
    } finally {
        if ($pdo->inTransaction()) $pdo->rollBack();
    }
} else {
    fwrite(STDOUT, "SKIP database-backed auth event checks (database unavailable).\n");
}

if ($failures) {
    foreach ($failures as $failure) fwrite(STDERR, "FAIL: {$failure}\n");
    exit(1);
}

fwrite(STDOUT, "Auth security checks passed.\n");
