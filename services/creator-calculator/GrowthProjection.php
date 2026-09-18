<?php
declare(strict_types=1);
namespace Xinng\CreatorCalculator;

final class GrowthProjection
{
    public static function calculate(array $a, array $rules): array
    {
        $growth = $a['weekly_follower_growth'] / 7;
        $ratio = $a['followers'] > 0 ? $a['avg_impressions_per_post'] / $a['followers'] : 0;
        $coefficient = $ratio * $a['posts_per_week'] / 7 * $a['qualified_impression_ratio'];
        $window = (int) $rules['window_days'];
        $target = $rules['qualified_impressions_required'];
        // Sum future days only. Arithmetic-series form is identical to a daily rolling queue.
        $rolling = static function (float $day) use ($a, $growth, $coefficient, $window): float {
            $count = min($window, $day);
            return $coefficient * $count * ($a['followers'] + $growth * ($day - ($count - 1) / 2));
        };
        $impressionDay = null;
        if ($coefficient > 0) {
            for ($day = 1; $day <= $window; $day++) {
                if ($rolling($day) >= $target) { $impressionDay = $day; break; }
            }
            if ($impressionDay === null && $growth > 0) {
                // After a full window the sum is linear; no arbitrary time horizon.
                $impressionDay = max($window + 1, ceil(($target / ($coefficient * $window) - $a['followers']) / $growth + ($window - 1) / 2));
                if ($rolling($impressionDay) < $target) $impressionDay++;
            }
        }
        $verifiedDaily = $growth * $a['verified_follower_ratio'];
        $followerDay = $a['verified_followers_pass'] ? 0 : ($verifiedDaily > 0 ? ceil($a['verified_followers_gap'] / $verifiedDaily) : null);
        $eligibilityDay = $impressionDay !== null && $followerDay !== null ? max($impressionDay, $followerDay) : null;
        return ['follower_days'=>$followerDay, 'impression_days'=>$impressionDay, 'eligibility_days'=>$eligibilityDay,
            'followers'=>$eligibilityDay === null ? null : $a['followers'] + $growth * $eligibilityDay,
            'average'=>$eligibilityDay === null ? null : ($a['followers'] + $growth * $eligibilityDay) * $ratio,
            'qualified_impressions'=>$eligibilityDay === null ? null : $rolling($eligibilityDay)];
    }
}
