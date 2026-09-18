<?php
declare(strict_types=1);
require_once __DIR__ . '/../../../services/creator-calculator/ShareRepository.php';
$token = $_GET['token'] ?? '';
$card = null;
$unavailable = false;
if (is_string($token) && preg_match('/\A[a-f0-9]{48}\z/D', $token)) {
    try {
        $repository = \Xinng\CreatorCalculator\ShareRepository::connect();
        $card = $repository->find($token);
    } catch (Throwable $error) { $unavailable = true; }
}
if (!$card) http_response_code($unavailable ? 503 : 404);
header('X-Robots-Tag: noindex, nofollow');
header('Referrer-Policy: no-referrer');
$script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME']);
$base = preg_replace('~/tools/creator-calculator/x/result\.php$~', '', $script);
$e = static fn($value): string => htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>X creator snapshot — Xinng</title><meta name="robots" content="noindex,nofollow"><link rel="stylesheet" href="<?= $e($base) ?>/assets/css/ember-tokens.css"><link rel="stylesheet" href="<?= $e($base) ?>/assets/css/creator-calculator.css"><script src="<?= $e($base) ?>/assets/js/creator-card.js?v=<?= filemtime(__DIR__ . '/../../../assets/js/creator-card.js') ?>" defer></script><script src="<?= $e($base) ?>/assets/js/creator-share.js?v=<?= filemtime(__DIR__ . '/../../../assets/js/creator-share.js') ?>" defer></script></head>
<body><main class="wrap public-result" data-share-base="<?= $e($base) ?>">
<?php if ($card): ?>
<canvas id="share-canvas" width="1080" height="1350" aria-label="Shared X creator result card" role="img" hidden></canvas>
<div class="sr-only"><h1><?= $e($card['outcome']['headline'] ?? $card['rows'][0]['label']) ?></h1><p><?= $e($card['outcome']['value'] ?? $card['rows'][0]['value']) ?></p></div>
<button type="button" class="primary-button" id="share-download" disabled>Download image</button><p id="share-status" role="status"></p>
<script type="application/json" id="public-share-data"><?= json_encode($card, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?></script>
<?php else: ?><h1><?= $unavailable ? 'Temporarily unavailable' : 'Result not found' ?></h1><p><?= $unavailable ? 'Please try this link again later.' : 'This result link is invalid or no longer available.' ?></p><?php endif; ?>
<p><a href="<?= $e($base) ?>/tools/creator-calculator/x/">Calculate your own progress →</a></p>
</main></body></html>
