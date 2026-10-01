<?php
declare(strict_types=1);
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../services/x/XAccountStore.php';
require_once __DIR__ . '/../../services/x/XOAuthService.php';
require_once __DIR__ . '/../../services/x/XPostService.php';

use Xinng\X\XAccountStore;
use Xinng\X\XApiException;
use Xinng\X\XOAuthService;
use Xinng\X\XPostService;

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function x_publish_error(int $status, string $message): never
{
    http_response_code($status);
    echo json_encode(['error' => $message]);
    exit;
}

session_start();
if (empty($_SESSION['user_id'])) x_publish_error(401, 'Sign in required.');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    x_publish_error(405, 'Use POST.');
}
if (!verify_csrf_token($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '')) x_publish_error(403, 'Invalid CSRF token.');
if ((int)($_SERVER['CONTENT_LENGTH'] ?? 0) > 8192) x_publish_error(413, 'Post request too large.');
$raw = file_get_contents('php://input', false, null, 0, 8193);
if (!is_string($raw) || strlen($raw) > 8192) x_publish_error(413, 'Post request too large.');
try {
    $body = json_decode($raw, true, 8, JSON_THROW_ON_ERROR);
    if (!is_array($body) || ($body['confirm'] ?? false) !== true || !is_string($body['text'] ?? null)) {
        x_publish_error(422, 'Confirm the post and provide its text.');
    }
    $pdo = get_db_connection();
    if (!$pdo) x_publish_error(503, 'Database unavailable.');
    xinng_ensure_x_account_tables($pdo);
    $store = new XAccountStore($pdo, X_TOKEN_ENCRYPTION_KEY);
    $token = $store->accessToken((int)$_SESSION['user_id']);
    if ($token === null) x_publish_error(401, 'Connect an X account first.');
    try {
        $post = (new XPostService())->publish($token, $body['text']);
    } catch (XApiException $error) {
        if ($error->status !== 401) throw $error;
        $refreshToken = $store->refreshToken((int)$_SESSION['user_id']);
        if ($refreshToken === null) throw $error;
        $tokens = (new XOAuthService(X_CLIENT_ID, X_CLIENT_SECRET, X_REDIRECT_URI))->refreshAccessToken($refreshToken);
        $store->updateTokens((int)$_SESSION['user_id'], $tokens);
        $post = (new XPostService())->publish((string)($tokens['access_token'] ?? ''), $body['text']);
    }
    echo json_encode(['ok' => true, 'post' => [
        'id' => $post['id'],
        'url' => 'https://x.com/i/status/' . rawurlencode($post['id']),
    ]], JSON_THROW_ON_ERROR);
} catch (XApiException $error) {
    x_publish_error($error->status, $error->getMessage());
} catch (JsonException) {
    x_publish_error(422, 'Invalid post request.');
} catch (Throwable $error) {
    error_log('X publish failed: ' . $error->getMessage());
    x_publish_error(503, 'X publishing is unavailable.');
}
