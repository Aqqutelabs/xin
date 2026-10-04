<?php
declare(strict_types=1);
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../services/x/XAccountStore.php';
require_once __DIR__ . '/../../services/x/XOAuthService.php';
require_once __DIR__ . '/../../services/x/XAnalyticsStore.php';

use Xinng\X\XAccountStore;
use Xinng\X\XAnalyticsStore;
use Xinng\X\XApiException;
use Xinng\X\XOAuthService;

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function x_analytics_error(int $status, string $message): never
{
    http_response_code($status);
    echo json_encode(['error' => $message]);
    exit;
}

session_start();
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('Allow: GET');
    x_analytics_error(405, 'Use GET.');
}
$pdo = get_db_connection();
if (!$pdo) x_analytics_error(503, 'Database unavailable.');
$userId = xinng_public_api_user_id();
if ($userId <= 0) x_analytics_error(422, 'Provide a user_id.');
try {
    xinng_ensure_x_account_tables($pdo);
    xinng_ensure_x_post_metric_tables($pdo);
    $accounts = new XAccountStore($pdo, X_TOKEN_ENCRYPTION_KEY);
    $account = $accounts->find($userId);
    if (!$account) x_analytics_error(409, 'Connect an X account first.');
    $token = $accounts->accessToken($userId);
    if ($token === null) x_analytics_error(409, 'Connect an X account first.');
    $oauth = new XOAuthService(X_CLIENT_ID, X_CLIENT_SECRET, X_REDIRECT_URI);
    try {
        $posts = $oauth->recentPosts($token, (string)$account['x_user_id']);
    } catch (XApiException $error) {
        if ($error->status !== 401) throw $error;
        $refreshToken = $accounts->refreshToken($userId);
        if ($refreshToken === null) throw $error;
        $tokens = $oauth->refreshAccessToken($refreshToken);
        $accounts->updateTokens($userId, $tokens);
        $posts = $oauth->recentPosts((string)($tokens['access_token'] ?? ''), (string)$account['x_user_id']);
    }
    $analytics = new XAnalyticsStore($pdo);
    $analytics->savePosts($userId, $posts);
    echo json_encode(['ok' => true, 'account' => [
        'username' => $account['username'],
        'display_name' => $account['display_name'],
    ], 'posts' => $analytics->latest($userId)], JSON_THROW_ON_ERROR);
} catch (XApiException $error) {
    x_analytics_error($error->status, $error->getMessage());
} catch (JsonException) {
    x_analytics_error(502, 'X returned invalid analytics data.');
} catch (Throwable $error) {
    error_log('X analytics failed: ' . $error->getMessage());
    x_analytics_error(503, 'X analytics is unavailable.');
}
