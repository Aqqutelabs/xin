<?php
declare(strict_types=1);
require_once __DIR__ . '/../XCalculator.php';
use Xinng\CreatorCalculator\XCalculator;
use Xinng\CreatorCalculator\InputValidationException;
$c = new XCalculator();
$base = ['followers'=>1000, 'verified_followers'=>100, 'posts_per_week'=>70, 'avg_impressions_per_post'=>10000, 'qualified_impression_percent'=>100, 'weekly_follower_growth'=>100];
$checks = 0;
$check = static function ($ok) use (&$checks) { if (!$ok) throw new RuntimeException('Growth assertion failed: ' . ($checks + 1)); $checks++; };
$t = $c->calculateTimeline($base);
$check($t['follower_days'] === 280 && $t['estimated_qualification_days'] === 280);
$t = $c->calculateTimeline(array_replace($base, ['weekly_follower_growth'=>'']));
$check($t['follower_days'] === 200 && str_contains($t['growth_message'], '20 followers/day'));
$check(!str_contains($t['growth_message'], '2%'));
$t = $c->calculateTimeline(array_replace($base, ['weekly_follower_growth'=>0]));
$check($t['estimated_qualification_days'] === null);
$t = $c->calculateTimeline(array_replace($base, ['verified_followers'=>0]));
$check($t['estimated_qualification_days'] === null);
$t = $c->calculateTimeline(array_replace($base, ['avg_impressions_per_post'=>1]));
$check($t['estimated_qualification_days'] === null && str_contains($t['qualification_message'], 'Reach must increase'));
$t = $c->calculateTimeline(array_replace($base, ['verified_followers'=>500]));
$check($t['estimated_qualification_days'] === 5);
$t = $c->calculateTimeline(array_replace($base, ['verified_followers'=>500, 'gross_impressions_90d'=>500000]));
$check($t['estimated_qualification_days'] === 1);
foreach ([-1, 'bad', [], INF] as $bad) {
 try { $c->calculateTimeline(array_replace($base, ['weekly_follower_growth'=>$bad])); $check(false); }
 catch (InputValidationException $e) { $check(isset($e->errors['weekly_follower_growth'])); }
}
echo "$checks growth assertions passed.\n";
