<?php
declare(strict_types=1);
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../services/x/XOAuthService.php';
require_once __DIR__ . '/../../services/x/XAccountStore.php';

use Xinng\X\XAccountStore;
use Xinng\X\XApiException;
use Xinng\X\XOAuthService;

session_start();
$fail = static function (string $message, int $status = 400): never {
    http_response_code($status);
    echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
    exit;
};
$userId = (int)($_SESSION['x_oauth_user_id'] ?? 0);
$state = (string)($_GET['state'] ?? '');
$expectedState = (string)($_SESSION['x_oauth_state'] ?? '');
$verifier = (string)($_SESSION['x_oauth_verifier'] ?? '');
$startedAt = (int)($_SESSION['x_oauth_started_at'] ?? 0);
unset($_SESSION['x_oauth_state'], $_SESSION['x_oauth_verifier'], $_SESSION['x_oauth_started_at'], $_SESSION['x_oauth_user_id']);
if ($expectedState === '' || $state === '' || !hash_equals($expectedState, $state) || $startedAt < time() - 600) $fail('The X authorization request expired. Start again.');
if ($userId <= 0) $fail('The X connection request has no valid user_id.');
if (isset($_GET['error'])) $fail('X authorization was cancelled.');
$code = (string)($_GET['code'] ?? '');
if ($code === '') $fail('X did not return an authorization code.');
try {
    $service = new XOAuthService(X_CLIENT_ID, X_CLIENT_SECRET, X_REDIRECT_URI);
    $tokens = $service->exchangeCode($code, $verifier);
    $account = $service->currentUser((string)($tokens['access_token'] ?? ''));
    $pdo = get_db_connection();
    if (!$pdo) $fail('The account could not be saved because the database is unavailable.', 503);
    xinng_ensure_x_account_tables($pdo);
    (new XAccountStore($pdo, X_TOKEN_ENCRYPTION_KEY))->save($userId, $account, $tokens);
    header('Location: ../../dashboard.php?x_connected=1', true, 302);
    exit;
} catch (XApiException $error) {
    $fail($error->getMessage(), $error->status);
} catch (Throwable $error) {
    error_log('X callback failed: ' . $error->getMessage());
    $fail('The X account could not be connected.', 503);
}
