<?php
declare(strict_types=1);
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../services/x/XAccountStore.php';
require_once __DIR__ . '/../../services/x/TwitterApiIoService.php';

use Xinng\X\XAccountStore;
use Xinng\X\XApiException;
use Xinng\X\TwitterApiIoService;

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function x_publish_error(int $status, string $message): never
{
    http_response_code($status);
    echo json_encode(['error' => $message]);
    exit;
}

session_start();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    x_publish_error(405, 'Use POST.');
}
if ((int)($_SERVER['CONTENT_LENGTH'] ?? 0) > 8192) x_publish_error(413, 'Post request too large.');
$raw = file_get_contents('php://input', false, null, 0, 8193);
if (!is_string($raw) || strlen($raw) > 8192) x_publish_error(413, 'Post request too large.');
try {
    $body = json_decode($raw, true, 8, JSON_THROW_ON_ERROR);
    if (!is_array($body) || ($body['confirm'] ?? false) !== true || !is_string($body['text'] ?? null)) {
        x_publish_error(422, 'Confirm the post and provide its text.');
    }
    if (!verify_csrf_token(is_string($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null) ? $_SERVER['HTTP_X_CSRF_TOKEN'] : null)) {
        x_publish_error(403, 'The request expired. Reload the dashboard and try again.');
    }
    $userId = xinng_public_api_user_id($body);
    if ($userId <= 0) x_publish_error(422, 'Provide a user_id.');
    $pdo = get_db_connection();
    if (!$pdo) x_publish_error(503, 'Database unavailable.');
    xinng_ensure_x_account_tables($pdo);
    $store = new XAccountStore($pdo, X_TOKEN_ENCRYPTION_KEY);
    $loginCookie = $store->sessionCookie($userId);
    $proxy = $store->proxy($userId);
    if ($loginCookie === null || $proxy === null) x_publish_error(409, 'Connect an X account first.');
    $post = (new TwitterApiIoService(TWITTERAPI_IO_API_KEY))->publish($loginCookie, $proxy, $body['text']);
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
