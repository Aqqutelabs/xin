<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../services/ai/AiPageGenerator.php';

header('Content-Type: application/json; charset=utf-8');

if (($_SERVER['REQUEST_METHOD'] ?? 'POST') !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'method_not_allowed']);
    exit;
}

$payload = json_decode(file_get_contents('php://input'), true);
if (!is_array($payload)) $payload = $_POST;
$userId = xinng_public_api_user_id($payload);
if ($userId <= 0) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'error' => 'missing_user_id']);
    exit;
}
$pageId = (int)($payload['page_id'] ?? 0);
$prompt = trim((string)($payload['prompt'] ?? ''));
$provider = strtolower(trim((string)($payload['provider'] ?? AI_DEFAULT_PROVIDER)));
$requestedPageType = strtolower(trim((string)($payload['page_type'] ?? 'creator')));
$currentState = is_array($payload['current_state'] ?? null) ? $payload['current_state'] : [];

if ($pageId <= 0 || $prompt === '') {
    http_response_code(422);
    echo json_encode(['ok' => false, 'error' => 'missing_input']);
    exit;
}
if (mb_strlen($prompt, 'UTF-8') > AI_MAX_PROMPT_LENGTH) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'error' => 'prompt_too_long', 'max_length' => AI_MAX_PROMPT_LENGTH]);
    exit;
}
if (!in_array($provider, ['automatic', 'gemini', 'deepseek'], true)) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'error' => 'unsupported_provider']);
    exit;
}
$requestedPageType = $requestedPageType === 'corporate' ? 'corporate' : 'creator';

$pdo = get_db_connection();
if (!$pdo) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'db']);
    exit;
}
xinng_ensure_credit_tables($pdo);

$stmt = $pdo->prepare('SELECT id, page_type, title, description FROM pages WHERE id = ? AND user_id = ? AND deleted_at IS NULL LIMIT 1');
$stmt->execute([$pageId, $userId]);
$page = $stmt->fetch();
if (!$page) {
    http_response_code(404);
    echo json_encode(['ok' => false, 'error' => 'not_found']);
    exit;
}
$pageType = ($page['page_type'] ?? $requestedPageType) === 'corporate' ? 'corporate' : 'creator';

$cost = AI_PAGE_GENERATION_COST;
if (xinng_ensure_credit_balance($pdo, $userId) < $cost) {
    http_response_code(402);
    echo json_encode(['ok' => false, 'error' => 'insufficient_credits', 'cost' => $cost]);
    exit;
}

try {
    $generator = new XinngAiPageGenerator();
    $resolvedProvider = $generator->resolveProvider($provider);
    $draft = $generator->generate($provider, $prompt, $pageType, $currentState + [
        'page_type' => $page['page_type'] ?? $pageType,
        'title' => $page['title'] ?? '',
        'description' => $page['description'] ?? '',
    ]);

    if ($cost > 0 && !xinng_charge_credits($pdo, $userId, $cost, 'AI page generation', 'ai-page:' . $pageId . ':' . bin2hex(random_bytes(8)))) {
        http_response_code(402);
        echo json_encode(['ok' => false, 'error' => 'credit_charge_failed']);
        exit;
    }

    echo json_encode([
        'ok' => true,
        'provider' => $resolvedProvider,
        'draft' => $draft,
        'credits_remaining' => xinng_ensure_credit_balance($pdo, $userId),
    ], JSON_UNESCAPED_SLASHES);
} catch (Throwable $e) {
    error_log('AI page generation failed: ' . $e->getMessage());
    $error = in_array($e->getMessage(), ['gemini_not_configured', 'deepseek_not_configured', 'unsupported_provider', 'ai_invalid_json', 'ai_network_error', 'ai_provider_bad_request', 'ai_provider_auth', 'ai_model_not_found', 'ai_provider_billing', 'ai_rate_limited', 'ai_provider_unavailable', 'ai_provider_invalid_response'], true)
        ? $e->getMessage()
        : 'ai_generation_failed';
    http_response_code(502);
    echo json_encode(['ok' => false, 'error' => $error]);
}
