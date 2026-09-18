<?php
declare(strict_types=1);
require_once __DIR__ . '/../../../services/creator-calculator/XCalculator.php';
require_once __DIR__ . '/../../../includes/brand.php';

use Xinng\CreatorCalculator\XCalculator;
use Xinng\CreatorCalculator\InputValidationException;
use Xinng\CreatorCalculator\ProgressPresenter;
require_once __DIR__ . '/../../../services/creator-calculator/ProgressPresenter.php';

require_once __DIR__ . '/../../../services/creator-calculator/ResultCard.php';
$calculator = new XCalculator();
$rules = $calculator->getRequirements();
$revenueModel = (new \Xinng\CreatorCalculator\RevenueEstimator())->getModel();
$values = $_SERVER['REQUEST_METHOD'] === 'POST' ? $_POST : [];
session_start();
if (empty($_SESSION['creator_share_csrf'])) $_SESSION['creator_share_csrf'] = bin2hex(random_bytes(24));
$shareCsrf = $_SESSION['creator_share_csrf'];
session_write_close();
$errors = [];
$result = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try { $result = $calculator->calculateQualification($values); $result['progress_display'] = ProgressPresenter::describe($result, $rules); $result['card'] = \Xinng\CreatorCalculator\ResultCard::build($result, $rules); }
    catch (InputValidationException $exception) { $errors = $exception->errors; http_response_code(422); }
    if (str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json')) {
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        echo json_encode(['result' => $result, 'errors' => $errors], JSON_THROW_ON_ERROR);
        exit;
    }
}
$script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
$base = preg_replace('~/tools/creator-calculator/x(?:/index\.php|/)?$~', '', $script);
$e = static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$value = static fn(string $key, $default = ''): string => $e(is_scalar($values[$key] ?? $default) ? ($values[$key] ?? $default) : '');
$number = static fn($n): string => number_format((float) $n, 0);
$money = static fn($amount): string => '$' . number_format((float) $amount, 2);
$passed = ($result['qualification_status'] ?? '') === 'thresholds_met';
$error = static function (string $key) use ($errors, $e): void { echo '<span class="field-error" id="error-' . $key . '">' . $e($errors[$key] ?? '') . '</span>'; };
?>
<!doctype html>
<html lang="en">
<head>
 <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
 <title>X Creator Calculator — Xinng</title>
 <meta name="description" content="Check your progress toward X monetization. Compare your verified followers and estimated qualifying impressions with the current requirements. Free, with no account needed.">
 <meta name="theme-color" content="#fff9f7">
 <link rel="icon" href="<?= $e($base) ?>/assets/images/logo-icon.svg">
 <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
 <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
 <link rel="stylesheet" href="<?= $e($base) ?>/assets/css/ember-tokens.css"><link rel="stylesheet" href="<?= $e($base) ?>/assets/css/brand.css">
 <link rel="stylesheet" href="<?= $e($base) ?>/assets/css/creator-calculator.css?v=<?= filemtime(__DIR__ . '/../../../assets/css/creator-calculator.css') ?>">
 <script src="<?= $e($base) ?>/assets/js/creator-card.js?v=<?= filemtime(__DIR__ . '/../../../assets/js/creator-card.js') ?>" defer></script>
 <script src="<?= $e($base) ?>/assets/js/creator-calculator.js?v=<?= filemtime(__DIR__ . '/../../../assets/js/creator-calculator.js') ?>" defer></script>
 <script src="<?= $e($base) ?>/assets/js/cursor-dragon.js?v=<?= filemtime(__DIR__ . '/../../../assets/js/cursor-dragon.js') ?>" defer></script>
 <script src="<?= $e($base) ?>/assets/js/creator-share.js?v=<?= filemtime(__DIR__ . '/../../../assets/js/creator-share.js') ?>" defer></script>
</head>
<body>
 <a class="skip-link" href="#calculator">Skip to calculator</a>
 <header class="site-header"><div class="wrap navigation"><a class="xinng-brand" href="<?= $e($base) ?>/index.php" aria-label="Xinng home"><img class="xinng-brand__image" src="<?= $e(xinng_brand_logo_url($base)) ?>" alt="Xinng" width="1736" height="906"></a><nav aria-label="Main navigation"><a href="#methodology">How it works</a><a href="<?= $e($base) ?>/tools/teleprompter/">Teleprompter</a></nav></div></header>
 <main class="wrap">
  <section class="hero"><p class="eyebrow">XINNG TOOLS <span>/</span> CREATOR CALCULATOR</p><div class="hero-heading"><div><h1>How long until X<br>starts <em>paying you?</em></h1><p>Check your monetization progress and see the audience and reach you still need FREE</p></div><div class="platform-badge"><span aria-hidden="true">𝕏</span><div>Made for X<small>Original Content Rewards</small></div></div></div></section>
  <div class="calculator-layout" id="calculator">
   <section class="input-card" aria-labelledby="inputs-heading"><div class="card-heading"><span class="step">01</span><div><h2 id="inputs-heading">Your account, in numbers.</h2><p>A few details to find your starting point.</p></div></div>
    <form method="post" action="<?= $e($base) ?>/tools/creator-calculator/x/" id="creator-form" data-default-average="<?= $rules['assumptions']['impressions_per_follower'] ?>" data-verified-ratio="<?= $rules['assumptions']['verified_follower_ratio'] ?>">
     <div class="field"><label for="creator-handle">X handle <span>(optional)</span></label><input id="creator-handle" name="handle" value="<?= $value('handle') ?>" placeholder="@username" maxlength="16" pattern="@?[a-zA-Z0-9_]{1,15}" autocomplete="off" spellcheck="false" aria-describedby="handle-help"><small id="handle-help">Appears on your shared result when entered.</small></div>
     <div class="input-grid">
      <div class="field"><label for="followers">Total followers</label><input id="followers" name="followers" type="number" min="0" max="1000000000" step="1" value="<?= $value('followers') ?>" placeholder="10,000" required aria-describedby="error-followers"><?php $error('followers'); ?></div>
      <div class="field"><label for="verified_followers">Verified followers</label><input id="verified_followers" name="verified_followers" type="number" min="0" max="1000000000" step="1" value="<?= $value('verified_followers') ?>" placeholder="650" aria-describedby="verified-help error-verified_followers"><small id="verified-help">Leave blank to estimate 40% of your followers.</small><?php $error('verified_followers'); ?></div>
     </div>
     <div class="field"><label for="avg_impressions_per_post">Average impressions per post</label><input id="avg_impressions_per_post" name="avg_impressions_per_post" type="number" min="0" max="1000000000" step="any" value="<?= $value('avg_impressions_per_post') ?>" placeholder="Calculated from your followers" aria-describedby="average-help error-avg_impressions_per_post"><small id="average-help">Starts at <?= $number($rules['assumptions']['impressions_per_follower'] * 100) ?>% of your followers. Adjust to your actual average. This is a Xinng assumption.</small><?php $error('avg_impressions_per_post'); ?></div>
     <div class="field"><div class="field-heading"><label for="posts_per_week">Posts per week</label><input class="compact-number" id="posts_per_week" name="posts_per_week" type="number" min="0" max="100" step="1" value="<?= $value('posts_per_week', 7) ?>" required aria-describedby="error-posts_per_week"></div><?php $error('posts_per_week'); ?></div>
     <details class="advanced" <?= array_intersect(array_keys($errors), ['weekly_follower_growth', 'gross_impressions_90d', 'country', 'qualified_impression_percent', 'original_content_percent']) ? 'open' : '' ?>><summary>Fine-tune my estimate <span aria-hidden="true">+</span></summary><div class="advanced-fields">
      <div class="field"><label for="weekly_follower_growth">How many followers do you gain per week? <span>(optional)</span></label><input id="weekly_follower_growth" name="weekly_follower_growth" type="number" min="0" max="1000000000" step="any" value="<?= $value('weekly_follower_growth') ?>" aria-describedby="growth-help error-weekly_follower_growth"><small id="growth-help">Leave blank to use estimated growth. Enter your actual weekly growth for better accuracy.</small><?php $error('weekly_follower_growth'); ?></div>
      <div class="field"><label for="qualified_impression_percent">Qualifying impressions (%)</label><input id="qualified_impression_percent" name="qualified_impression_percent" type="number" min="0" max="100" step="any" value="<?= $value('qualified_impression_percent', $rules['assumptions']['qualified_impression_ratio'] * 100) ?>" aria-describedby="qualified-help error-qualified_impression_percent"><small id="qualified-help">Estimated share of gross impressions that qualify as verified/Premium Home Timeline exposure. Excludes replies and ineligible exposure; <?= $number($rules['assumptions']['qualified_impression_ratio'] * 100) ?>% is a Xinng assumption.</small><?php $error('qualified_impression_percent'); ?></div>
      <div class="field"><label for="original_content_percent">How much of what you post is original? (%)</label><input id="original_content_percent" name="original_content_percent" type="number" min="0" max="100" step="any" value="<?= $value('original_content_percent', $rules['assumptions']['original_content_ratio'] * 100) ?>" aria-describedby="original-help error-original_content_percent"><small id="original-help">Keep this estimate separate from qualifying reach. It does not change the two thresholds checked here.</small><?php $error('original_content_percent'); ?></div>
      <div class="field"><label for="gross_impressions_90d">Actual gross impressions in the last <?= $rules['window_days'] ?> days <span>(optional)</span></label><input id="gross_impressions_90d" name="gross_impressions_90d" type="number" min="0" max="1000000000000" step="1" value="<?= $value('gross_impressions_90d') ?>" placeholder="Use your analytics total" aria-describedby="actual-help error-gross_impressions_90d"><small id="actual-help">Replaces the estimate from your posting activity. We still apply your qualifying-impression percentage.</small><?php $error('gross_impressions_90d'); ?></div>
      <div class="field"><label for="country">Country <span>(optional)</span></label><input id="country" name="country" maxlength="80" value="<?= $value('country') ?>" autocomplete="country-name" aria-describedby="country-help error-country"><small id="country-help">Country eligibility is not checked in this calculation.</small><?php $error('country'); ?></div>
     </div></details>
     <button class="primary-button" type="submit">Check my progress <span aria-hidden="true">→</span></button><p id="form-status" role="status"><?= $errors ? 'Please check the highlighted fields.' : '' ?></p><p class="privacy-note">No account connection. Your numbers are not saved unless you choose to create a public share card.</p>
    </form>
   </section>
   <section class="result-card" aria-labelledby="result-heading" id="result" tabindex="-1">
    <p class="eyebrow">YOUR STARTING POINT</p><h2 id="result-heading"><?= $result ? ($passed ? 'You appear to meet the measurable thresholds.' : "You're not there yet. Let's see the gaps.") : 'A little clarity for your next step.' ?></h2>
    <div id="empty-result" <?= $result ? 'hidden' : '' ?>><p>Enter your numbers to compare your account with X's audience and impression requirements.</p><div class="target-list"><span><strong><?= $number($rules['verified_followers_required']) ?></strong> verified followers</span><span><strong><?= $number($rules['qualified_impressions_required']) ?></strong> qualifying impressions / <?= $rules['window_days'] ?> days</span></div></div>
    <div id="calculated-result" <?= !$result ? 'hidden' : '' ?>>
     <article class="earnings-panel" aria-labelledby="earnings-title"><h3 id="earnings-title"><?= $e($result['revenue']['title'] ?? '') ?></h3><strong class="earnings-range" id="earnings-monthly"><?= !$passed ? '$0' : $money($result['revenue']['monthly']['low']) . '&ndash;' . $money($result['revenue']['monthly']['high']) ?></strong><small><a href="#revenue-methodology">How the Xinng estimate is modelled</a></small></article>
     <div class="progress-overview"><div class="progress-number"><strong id="progress-value"><?= $result ? $number(floor($result['qualification_progress'])) : '0' ?>%</strong><span>estimated progress</span></div><div class="impression-time"><strong id="impression-time"><?= $e($result['progress_display']['growth_stats']['impression_time'] ?? '') ?></strong><span>To 500k impressions (est.)</span></div></div>
     <?php foreach (['followers' => ['Verified followers', 'verified_followers', 'verified_followers_gap', 'verified_followers_pass', $rules['verified_followers_required']], 'impressions' => ['Estimated qualifying impressions', 'estimated_qualified_impressions_90d', 'qualified_impressions_gap', 'qualified_impressions_pass', $rules['qualified_impressions_required']]] as $id => [$label, $key, $gap, $pass, $target]): ?>
     <div class="metric"><div class="metric-heading"><h3><?= $label ?></h3><span id="<?= $id ?>-state"><?= $result && $result[$pass] ? 'Threshold met' : 'Below threshold' ?></span></div><p><strong id="<?= $id ?>-value"><?= $number($result[$key] ?? 0) ?></strong> / <?= $number($target) ?></p><progress id="<?= $id ?>-progress" max="<?= $target ?>" value="<?= min($target, $result[$key] ?? 0) ?>" aria-label="<?= $label ?> progress"></progress><small id="<?= $id ?>-gap"><?= $result && $result[$pass] ? '' : $number(ceil($result[$gap] ?? $target)) . ' more needed.' ?></small></div>
     <?php endforeach; ?>
     <dl class="growth-numbers"><div><dt>To 500 verified followers (est.)</dt><dd id="verified-time"><?= $e($result['progress_display']['growth_stats']['verified_time'] ?? '') ?></dd></div></dl>
     <div class="pace-analysis" id="impression-targets" aria-label="Impression threshold targets" <?= !empty($result['qualified_impressions_pass']) ? 'hidden' : '' ?>><article><h3>What would meet the impression threshold?</h3><p id="threshold-summary"><?= $e($result['progress_display']['threshold_summary'] ?? '') ?></p></article></div>
    </div>
    <div class="result-share" id="share-panel" data-share-base="<?= $e($base) ?>" data-share-csrf="<?= $e($shareCsrf) ?>" <?= !$result ? 'hidden' : '' ?>>
     <button class="primary-button" id="share-create" type="button" disabled>Share result</button>
     <canvas id="share-canvas" width="1080" height="1350" role="img" aria-label="Your X creator result card" hidden></canvas>
     <div class="share-actions" id="share-actions" hidden><button id="share-download" type="button" disabled>Download image</button><button id="share-copy" type="button" disabled>Copy result link</button><a id="share-x" target="_blank" rel="noopener noreferrer" hidden>Share on X</a></div>
     <p id="share-status" role="status"></p><p id="share-link-wrap" hidden><label for="share-link">Public result link</label><input id="share-link" readonly></p>
     <noscript><p>Enable JavaScript to share your result.</p></noscript>
    </div>
   </section>
  </div>

  <section class="methodology" id="methodology"><div><p class="eyebrow">BEHIND THE NUMBERS</p><h2>Clear assumptions.<br>A useful starting point.</h2><p>We compare two measurable requirements. Your results are estimates, not an official X eligibility decision.</p><div class="methodology-dragon" data-cursor-dragon><img src="<?= $e($base) ?>/assets/images/drag-xinng/stare.png" alt="Xinng the dragon" width="1254" height="1254" loading="lazy" decoding="async" draggable="false"></div></div><div class="methodology-details"><details id="revenue-methodology"><summary>How are earnings estimated?</summary><p>X does not publish a universal fixed payout rate. This planning model uses illustrative, uncalibrated USD rates per 1,000 qualifying impressions on original content: conservative $<?= $revenueModel['usd_per_thousand']['conservative'] ?>, estimated $<?= $revenueModel['usd_per_thousand']['low'] ?>&ndash;$<?= $revenueModel['usd_per_thousand']['high'] ?>, and strong $<?= $revenueModel['usd_per_thousand']['strong'] ?>. These are Xinng assumptions, not observed or promised payouts.</p><p>Projected qualifying impressions are converted to a <?= $revenueModel['month_days'] ?>-day month and multiplied by original-content share once. Annual estimates are monthly estimates multiplied by 12. Country does not change these assumptions. Accounts below either measurable threshold show $0 estimated earnings. Scenarios use future posting pace, while actual recent analytics remain unchanged.</p><p>Model: <?= $e($revenueModel['version']) ?>. Actual payouts can be lower, including zero.</p></details><details><summary>Does this result mean I qualify for payments?</summary><p><strong>A progress check, not an approval.</strong><br>X also applies membership, age, account, country, content and payout requirements. Meeting these two measurable thresholds does not guarantee admission or payment.</p><p>These are mathematical targets, not posting recommendations. Quality, original content and X's rules still matter. Follower requirements are separate.</p></details><details open><summary>How are impressions estimated?</summary><p>Average impressions per post × posts per week × (<?= $rules['window_days'] ?> ÷ 7) gives estimated gross impressions for a <?= $rules['window_days'] ?>-day period. Your qualifying percentage is applied to that total. An actual analytics total takes priority when supplied.</p><p>Current qualification uses the recent total; sustainable pace uses your average reach and posts per week. Impressions fall out of the window after <?= $rules['window_days'] ?> days, so waiting longer cannot fix a below-threshold pace.</p></details><details><summary>How is the timeline calculated?</summary><p>With an actual recent total and a sufficient future pace, we model a range for reaching the impression threshold. The earliest bound assumes no old impressions expire before the gap is filled. The latest assumes they expire immediately. A middle estimate assumes the old impressions are spread evenly across the window. New impressions accrue at a steady rate, so the model stays within one rolling window.</p><p>This is an assumption-based range, not a confidence interval or an X approval date. Verified followers must also meet their threshold; without follower-growth history, we cannot predict when a follower gap will close.</p></details><details><summary>What if I don't know my verified followers?</summary><p>We use <?= $number($rules['assumptions']['verified_follower_ratio'] * 100) ?>% of your total followers as a starting estimate, clearly marked in the result. This is a configurable Xinng assumption, not an X benchmark. Use your real count for a more useful result.</p></details><details><summary>Which X rules are used?</summary><p><?= $number($rules['verified_followers_required']) ?> verified followers and <?= $number($rules['qualified_impressions_required']) ?> verified-user Home Timeline impressions in <?= $rules['window_days'] ?> days. Replies are excluded. Original content and additional account requirements also apply.</p><p><a href="<?= $e($rules['source_url']) ?>" target="_blank" rel="noopener">Read X's Original Content Rewards requirements ↗</a><br>Rules reviewed <?= $e($rules['reviewed_at']) ?>.</p></details></div></section>
 </main><footer class="wrap footer"><span>Xinng tools. A little help with your next step.</span><a href="<?= $e($base) ?>/index.php">Make yourself at home →</a></footer>
 <script type="application/json" id="calculator-rules"><?= json_encode($rules, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?></script>
<?php if ($result): ?><script type="application/json" id="share-initial-inputs"><?= json_encode($values, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?></script><?php endif; ?>
</body>
</html>
