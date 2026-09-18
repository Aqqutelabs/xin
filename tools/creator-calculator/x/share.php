<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
require_once __DIR__ . '/../../../services/creator-calculator/XCalculator.php';
require_once __DIR__ . '/../../../services/creator-calculator/ShareResult.php';
require_once __DIR__ . '/../../../services/creator-calculator/ShareRepository.php';

use Xinng\CreatorCalculator\XCalculator;
use Xinng\CreatorCalculator\ShareResult;
use Xinng\CreatorCalculator\ShareRepository;

function share_error(int $status, string $message): void {
    http_response_code($status);
    echo json_encode(['error' => $message]);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Allow: POST'); share_error(405, 'Use POST to create a result.'); }
if ((int)($_SERVER['CONTENT_LENGTH'] ?? 0) > 16384) share_error(413, 'Request is too large.');
session_start();
$csrf = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
if (!is_string($csrf) || empty($_SESSION['creator_share_csrf']) || !hash_equals($_SESSION['creator_share_csrf'], $csrf)) share_error(403, 'Refresh the calculator and try again.');
$raw = file_get_contents('php://input', false, null, 0, 16385);
if (strlen($raw) > 16384) share_error(413, 'Request is too large.');
try {
    $body = json_decode($raw, true, 32, JSON_THROW_ON_ERROR);
    if (!is_array($body) || !is_array($body['inputs'] ?? null) || !is_array($body['options'] ?? null) || !is_string($body['handle'] ?? '')) throw new InvalidArgumentException('Invalid sharing request.');
    $payload = ShareResult::select((new XCalculator())->calculateQualification($body['inputs']), $body['options'], $body['handle'] ?? '');
} catch (JsonException | InvalidArgumentException $error) { share_error(422, $error->getMessage()); }
// Only explicit share creation persists data. Limit repeated writes in this session.
$recent = array_filter($_SESSION['creator_share_times'] ?? [], static fn($time) => $time > time() - 3600);
if (count($recent) >= 30) share_error(429, 'You have created many cards recently. Please try again later.');
$_SESSION['creator_share_times'] = [...$recent, time()];
session_write_close();
try {
    $repository = ShareRepository::connect();
    $repository->ensureSchema();
    $token = $repository->save($payload);
    echo json_encode(['token' => $token, 'card' => $payload], JSON_THROW_ON_ERROR);
} catch (Throwable $error) {
    error_log('Creator result storage failed: ' . $error->getMessage());
    share_error(503, 'Sharing is temporarily unavailable. Your calculation still works; please try again later.');
}
