<?php
declare(strict_types=1);
require_once __DIR__ . '/../../../config.php';
require_once __DIR__ . '/../../../services/creator-calculator/XHandleLookup.php';
use Xinng\CreatorCalculator\XHandleLookup;
use Xinng\CreatorCalculator\LookupException;
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
function lookup_error(int $status, string $message): void { http_response_code($status); echo json_encode(['error' => $message]); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Allow: POST'); lookup_error(405, 'Use POST for lookup.'); }
if ((int)($_SERVER['CONTENT_LENGTH'] ?? 0) > 1024) lookup_error(413, 'Request too large.');
session_start();
if (empty($_SESSION['creator_share_csrf']) || !hash_equals($_SESSION['creator_share_csrf'], $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '')) lookup_error(403, 'Refresh the calculator and try again.');
$raw = file_get_contents('php://input', false, null, 0, 1025);
if (strlen($raw) > 1024) lookup_error(413, 'Request too large.');
try {
    $body = json_decode($raw, true, 8, JSON_THROW_ON_ERROR);
    if (!is_array($body) || !is_string($body['handle'] ?? null)) lookup_error(422, 'Enter an X handle.');
    $handle = XHandleLookup::normalize($body['handle']);
    $recent = array_filter($_SESSION['creator_lookup_times'] ?? [], static fn($t) => $t > time() - 3600);
    if (count($recent) >= 15) lookup_error(429, 'Too many lookups. Please use manual entry or try later.');
    $_SESSION['creator_lookup_times'] = [...$recent, time()];
    session_write_close();
    $profile = (new XHandleLookup((string)(getenv('X_API_BEARER_TOKEN') ?: '')))->lookup($handle);
    echo json_encode(['profile' => $profile], JSON_THROW_ON_ERROR);
} catch (LookupException $error) { lookup_error($error->status, $error->getMessage()); }
catch (JsonException $error) { lookup_error(422, 'Invalid lookup request.'); }
catch (Throwable $error) { lookup_error(503, 'Lookup is unavailable. Please enter your numbers manually.'); }
