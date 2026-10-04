<?php
declare(strict_types=1);
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../services/x/XOAuthService.php';

use Xinng\X\XApiException;
use Xinng\X\XOAuthService;

session_start();
$userId = xinng_public_api_user_id();
if ($userId <= 0) { http_response_code(422); echo 'Provide a user_id.'; exit; }
try {
    $service = new XOAuthService(X_CLIENT_ID, X_CLIENT_SECRET, X_REDIRECT_URI);
    $state = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
    $verifier = rtrim(strtr(base64_encode(random_bytes(48)), '+/', '-_'), '=');
    $challenge = rtrim(strtr(base64_encode(hash('sha256', $verifier, true)), '+/', '-_'), '=');
    $_SESSION['x_oauth_state'] = $state;
    $_SESSION['x_oauth_verifier'] = $verifier;
    $_SESSION['x_oauth_started_at'] = time();
    $_SESSION['x_oauth_user_id'] = $userId;
    header('Location: ' . $service->authorizationUrl($state, $challenge), true, 302);
    exit;
} catch (XApiException $error) {
    http_response_code($error->status);
    echo htmlspecialchars($error->getMessage(), ENT_QUOTES, 'UTF-8');
}
