<?php
declare(strict_types=1);
require_once __DIR__ . '/../XCalculator.php';
require_once __DIR__ . '/../GrowthProjection.php';
use Xinng\CreatorCalculator\XCalculator;
use Xinng\CreatorCalculator\GrowthProjection;
$c = new XCalculator(); $checks = 0;
foreach ([0, 14, 280, 1400] as $growth) {
 foreach ([200, 2000, 10000] as $reach) {
  $a = $c->calculateQualification(['followers'=>2000,'verified_followers'=>200,'avg_impressions_per_post'=>$reach,'posts_per_week'=>14,'weekly_follower_growth'=>$growth]);
  $p = GrowthProjection::calculate($a, $c->getRequirements());
  $queue = []; $sum = 0; $first = null;
  for ($day=1; $day<=100000; $day++) {
   $queue[] = (2000 + $growth / 7 * $day) * ($reach / 2000) * 2 * .4;
   $sum += end($queue);
   if (count($queue)>90) $sum -= array_shift($queue);
   if ($sum >= 500000) { $first = $day; break; }
  }
  if ($p['impression_days'] != $first) throw new RuntimeException('Rolling projection differs from daily simulation');
  if ($p['eligibility_days'] !== null && ($p['qualified_impressions'] < 500000 || $p['followers'] * .1 < 500)) throw new RuntimeException('Both targets must pass');
  $checks++;
 }
}
echo "$checks rolling-growth scenarios passed.\n";

