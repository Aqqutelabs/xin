<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once __DIR__ . '/../XCalculator.php';
use Xinng\CreatorCalculator\XCalculator;
use Xinng\CreatorCalculator\InputValidationException;

$calculator = new XCalculator();
$count = 0;
$check = static function (bool $condition, string $message) use (&$count): void {
    if (!$condition) throw new RuntimeException($message);
    $count++;
};
$inputs = ['followers' => 10000, 'verified_followers' => 650, 'avg_impressions_per_post' => 1000, 'posts_per_week' => 7];
$result = $calculator->calculateQualification($inputs);
$check(abs($result['gross_impressions_90d'] - 90000) < .001, 'PRD example gross impressions');
$check(abs($result['estimated_qualified_impressions_90d'] - 36000) < .001, 'PRD example qualified impressions');
$check($result['qualification_status'] === 'below_thresholds' && $result['verified_followers_pass'], 'Both gates must pass');
$check(abs($result['qualification_progress'] - 7.2) < .001, 'Progress is limited by reach');
$check($result['qualified_impressions_gap'] === 464000.0, 'Impression gap');
$actual = array_replace($inputs, ['gross_impressions_90d' => 1250000, 'verified_followers' => 500]);
$result = $calculator->calculateQualification($actual);
$check($result['qualification_status'] === 'thresholds_met', 'Exact boundary qualifies');
$check($result['qualification_progress'] == 100, 'Boundary progress');
$check($result['gross_impressions_source'] === 'provided', 'Actual total overrides projection');
$result = $calculator->calculateQualification(array_replace($actual, ['verified_followers' => 499]));
$check($result['qualification_status'] === 'below_thresholds' && $result['verified_followers_gap'] === 1.0, 'Follower boundary');
$result = $calculator->calculateQualification(array_replace($actual, ['gross_impressions_90d' => 1249999]));
$check($result['qualification_status'] === 'below_thresholds', 'Fractional impression shortfall cannot pass');
$result = $calculator->calculateQualification(array_replace($actual, ['gross_impressions_90d' => 0]));
$check($result['gross_impressions_90d'] === 0.0 && $result['qualification_progress'] === 0.0, 'Actual zero must not fall back');
$zero = $calculator->calculateQualification(['followers' => 0, 'verified_followers' => 0, 'posts_per_week' => 0]);
$check($zero['verified_follower_ratio'] === 0 && $zero['qualification_progress'] === 0.0, 'Zero account handles division safely');
$unknown = $calculator->calculateQualification(array_replace($inputs, ['verified_unknown' => '1', 'verified_followers' => '', 'avg_impressions_per_post' => '']));
$check($unknown['verified_followers'] === 4000.0 && $unknown['verified_followers_estimated'], 'Unknown verified count uses the 40% assumption and is labelled');
$check($unknown['avg_impressions_per_post'] === 1000.0, 'Ten percent average fallback');
$separate = $calculator->calculateQualification(array_replace($actual, ['original_content_percent' => 25]));
$check($separate['estimated_qualified_impressions_90d'] === 500000.0, 'Original ratio is separate from qualification');
$rules = $calculator->getRequirements();
$rules['verified_followers_required'] = 700;
$check((new XCalculator($rules))->calculateQualification($actual)['qualification_status'] === 'below_thresholds', 'Rules can change independently');
foreach ([['followers' => -1], ['verified_followers' => 10001], ['posts_per_week' => 101], ['qualified_impression_percent' => 101], ['original_content_percent' => -1], ['followers' => '1e999'], ['followers' => []], ['followers' => true], ['country' => []], ['verified_followers' => 1.5], ['gross_impressions_90d' => 'oops']] as $invalid) {
    try { $calculator->calculateQualification(array_replace($inputs, $invalid)); throw new RuntimeException('Invalid input accepted'); }
    catch (InputValidationException $exception) { $check(count($exception->errors) > 0, 'Validation errors provided'); }
}
$progress = $calculator->calculateQualification($inputs)['progress'];
$check(!$progress['sustainable_pace'] && $progress['timeline']['status'] === 'below_sustainable_pace', 'Low sustainable pace has no inevitable date');
$check($progress['timeline']['impression_days_low'] === null, 'No date at low pace');
$check(abs($progress['required_posts_per_week'] - 97.2222222222) < .0001, 'Reverse posting rate');
$check(abs($progress['required_gross_impressions_per_post'] - 13888.8888889) < .0001, 'Gross reverse reach');
$check(abs($progress['required_qualified_impressions_per_post'] - 5555.5555556) < .0001, 'Qualified reverse reach is separate');
$growing = array_replace($inputs, ['gross_impressions_90d' => 787500, 'avg_impressions_per_post' => 25000]);
$growth = $calculator->calculateQualification($growing)['progress'];
$check($growth['projected_qualified_impressions_90d'] === 900000.0, 'Future pace remains independent of actual history');
$check($growth['timeline']['impression_days_low'] === 19 && $growth['timeline']['impression_days_high'] === 50, 'Unknown expiry range');
$check($growth['timeline']['impression_days_uniform'] === 29, 'Uniform rolling expiry differs from naive 19 days');
$check($growth['timeline']['qualification_days_low'] === 19, 'Known sufficient followers permit conditional qualification range');
$missingFollowers = $calculator->calculateTimeline(array_replace($growing, ['verified_followers' => 499]));
$check($missingFollowers['qualification_days_low'] === null && $missingFollowers['impression_days_low'] === 19, 'Follower gap blocks overall date but not reach estimate');
$unknownFollowers = $calculator->calculateTimeline(array_replace($growing, ['verified_unknown' => '1']));
$check($unknownFollowers['qualification_days_low'] === null, 'Estimated follower count cannot assert overall date');
$noHistory = $growing;
unset($noHistory['gross_impressions_90d']);
// Without history an adequate projection is already the estimated qualification total.
$check($calculator->calculateTimeline($noHistory)['impression_days_low'] === null, 'A projection is not historical timeline evidence');
$fading = $calculator->calculateQualification(array_replace($inputs, ['gross_impressions_90d' => 1250000]));
$check($fading['qualification_status'] === 'thresholds_met' && !$fading['progress']['sustainable_pace'], 'Current pass can coexist with declining future pace');
$check($fading['progress']['timeline']['status'] === 'impressions_met', 'Already met never shows a future wait');
$noActivity = $calculator->calculateQualification(array_replace($inputs, ['posts_per_week' => 0, 'avg_impressions_per_post' => 0]))['progress'];
$check($noActivity['additional_reach_percent'] === null && $noActivity['required_posts_per_week'] === null && $noActivity['required_gross_impressions_per_post'] === null, 'Zero denominators return null instead of infinity');
$zeroShare = $calculator->calculateQualification(array_replace($inputs, ['qualified_impression_percent' => 0]))['progress'];
$check($zeroShare['required_posts_per_week'] === null && $zeroShare['required_gross_impressions_per_post'] === null, 'Zero qualifying share cannot be solved by more posts');
$zeroHistory = $calculator->calculateTimeline(array_replace($growing, ['gross_impressions_90d' => 0]));
$check($zeroHistory['impression_days_low'] === 50 && $zeroHistory['impression_days_high'] === 50 && $zeroHistory['impression_days_uniform'] === 50, 'Known zero history uses a full accumulation model');
$customRules = $calculator->getRequirements();
$customRules['window_days'] = 70;
$customRules['qualified_impressions_required'] = 700000;
$custom = (new XCalculator($customRules))->calculateQualification(array_replace($growing, ['gross_impressions_90d' => 0]));
$check($custom['progress']['sustainable_pace'] && $custom['progress']['timeline']['impression_days_high'] === 70, 'Exact sustainable boundary and configurable window');
$moreOriginal = $calculator->calculateQualification(array_replace($growing, ['original_content_percent' => 90]));
$check($moreOriginal['progress'] === $growth, 'Original percentage never double-discounts qualification or reverse targets');
require_once __DIR__ . '/../ProgressPresenter.php';
$copy = \Xinng\CreatorCalculator\ProgressPresenter::describe($fading, $calculator->getRequirements());
$check(str_contains($copy['pace_body'], 'may not maintain'), 'Recent pass with low future pace warns about expiry');
$earningInputs = array_replace($inputs, ['avg_impressions_per_post' => 25000, 'gross_impressions_90d' => 1250000]);
$earning = $calculator->estimateRevenue($earningInputs);
$check($earning['basis'] === 'projected_activity', 'Qualified revenue uses future activity');
$check($earning['monthly']['low'] === 78.0 && $earning['monthly']['high'] === 132.0, 'Versioned illustrative revenue range');
$check($earning['annual']['low'] === 936.0 && $earning['annual']['high'] === 1584.0, 'Annual equals monthly times twelve');
$doublePosts = $calculator->estimateRevenue(array_replace($earningInputs, ['posts_per_week' => 14]));
$check($doublePosts['monthly']['low'] === 156.0, 'Posting scenario doubles activity');
$doubleReach = $calculator->estimateRevenue(array_replace($earningInputs, ['avg_impressions_per_post' => 50000]));
$check($doubleReach['monthly'] === $doublePosts['monthly'], 'Reach scenario affects earnings');
$halfOriginal = $calculator->calculateQualification(array_replace($earningInputs, ['original_content_percent' => 20]));
$check($halfOriginal['revenue']['monthly']['low'] === 39.0 && $halfOriginal['qualification_status'] === 'thresholds_met', 'Original share affects revenue once, not qualification');
$zeroOriginal = $calculator->estimateRevenue(array_replace($earningInputs, ['original_content_percent' => 0]));
$check($zeroOriginal['monthly']['high'] === 0.0, 'Zero original activity earns zero in the model');
$zeroFuture = $calculator->estimateRevenue(array_replace($earningInputs, ['posts_per_week' => 0]));
$check($zeroFuture['monthly']['high'] === 0.0, 'Historical eligibility does not imply future revenue with no posts');
$below = $calculator->estimateRevenue($inputs);
$check($below['basis'] === 'below_thresholds' && $below['monthly']['low'] == 0 && $below['monthly']['high'] == 0, 'Below threshold earns zero');
$scenario = $calculator->calculateScenarios(array_replace($earningInputs, ['gross_impressions_90d' => 0]));
$check($scenario['qualification_status'] === 'thresholds_met' && $scenario['gross_impressions_source'] === 'estimated', 'Future scenario does not overwrite supplied actual history');
$check($calculator->calculateQualification(array_replace($earningInputs, ['gross_impressions_90d' => 0]))['qualification_status'] === 'below_thresholds', 'Actual zero remains authoritative outside scenarios');
$check($earning['revenue_model_version'] === 'x_original_rewards_illustrative_v1', 'Model version included');
$model = (new \Xinng\CreatorCalculator\RevenueEstimator())->getModel();
$model['usd_per_thousand']['low'] = -1;
try { new \Xinng\CreatorCalculator\RevenueEstimator($model); throw new RuntimeException('Invalid model accepted'); }
catch (InvalidArgumentException $exception) { $check(true, 'Invalid model rates rejected'); }
echo "$count assertions passed.\n";
