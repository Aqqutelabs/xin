<?php
declare(strict_types=1);
namespace Xinng\CreatorCalculator;

final class QualificationProgressEngine
{
    /** Operates only on validated qualification results and platform rules. */
    public function calculate(array $account, array $rules): array
    {
        $window = (float) $rules['window_days'];
        $target = (float) $rules['qualified_impressions_required'];
        $perPost = $account['avg_impressions_per_post'] * $account['qualified_impression_ratio'];
        $daily = $perPost * $account['posts_per_week'] / 7;
        $projected = $daily * $window;
        $current = $account['estimated_qualified_impressions_90d'];
        $sustainable = $projected >= $target;
        $requiredPosts = $perPost > 0 ? $target * 7 / ($window * $perPost) : null;
        $requiredQualifiedReach = $account['posts_per_week'] > 0 ? $target * 7 / ($window * $account['posts_per_week']) : null;
        $requiredGrossReach = $requiredQualifiedReach !== null && $account['qualified_impression_ratio'] > 0
            ? $requiredQualifiedReach / $account['qualified_impression_ratio'] : null;
        $timeline = [
            'status' => 'insufficient_history',
            'impression_days_low' => null,
            'impression_days_high' => null,
            'impression_days_uniform' => null,
            'qualification_days_low' => null,
            'qualification_days_high' => null,
            'followers_block_timeline' => !$account['verified_followers_pass'] || $account['verified_followers_estimated'],
            'model_version' => 'rolling_window_bounds_v1',
        ];
        if ($account['qualified_impressions_pass']) {
            $timeline['status'] = 'impressions_met';
        } elseif (!$sustainable) {
            $timeline['status'] = 'below_sustainable_pace';
        } elseif ($account['gross_impressions_source'] === 'provided') {
            // Old impressions may expire at any point in the window. Best case:
            // none expire before the gap is filled. Worst case: all expire now.
            // Both bounds stay within one window when the sustainable pace passes.
            $timeline['status'] = 'modelled';
            $timeline['impression_days_low'] = (int) ceil(($target - $current) / $daily);
            $timeline['impression_days_high'] = (int) ceil($target / $daily);
            // Uniform history is a transparent midpoint model, not inferred history.
            $timeline['impression_days_uniform'] = (int) ceil(($target - $current) / ($daily - $current / $window));
            if (!$timeline['followers_block_timeline']) {
                $timeline['qualification_days_low'] = $timeline['impression_days_low'];
                $timeline['qualification_days_high'] = $timeline['impression_days_high'];
            }
        }
        $growthDaily = $account['weekly_follower_growth'] / 7;
        $verifiedDaily = $growthDaily * $account['verified_follower_ratio'];
        $followerDays = $account['verified_followers_pass'] ? 0 : ($verifiedDaily > 0 ? (int) ceil($account['verified_followers_gap'] / $verifiedDaily) : null);
        // Without supplied history, model a fresh window at the current pace.
        $impressionDays = !$sustainable ? null : ($account['gross_impressions_source'] === 'provided'
            ? ($account['qualified_impressions_pass'] ? 0 : $timeline['impression_days_uniform'])
            : (int) ceil($target / $daily));
        $qualificationDays = $impressionDays !== null && $followerDays !== null ? max(1, $impressionDays, $followerDays) : null;
        $timeline['follower_days'] = $followerDays;
        $timeline['estimated_qualification_days'] = $qualificationDays;
        $timeline['qualification_message'] = !$sustainable
            ? 'Reach must increase to meet the rolling 90-day impression threshold.'
            : ($followerDays === null ? 'Verified-follower growth is needed to estimate a qualification date.'
                : 'Estimated time to qualification: ' . $qualificationDays . ($qualificationDays === 1 ? ' day' : ' days'));
        $timeline['growth_message'] = $account['follower_growth_estimated']
            ? 'Using estimated growth of about ' . number_format($growthDaily, $growthDaily > 0 && $growthDaily < 1 ? 2 : 0) . ' followers/day. Enter actual weekly growth for better accuracy.' : '';
        return [
            'projected_qualified_impressions_90d' => $projected,
            'qualified_impressions_per_day' => $daily,
            'sustainable_pace' => $sustainable,
            'additional_reach_percent' => $projected > 0 ? max(0, ($target / $projected - 1) * 100) : null,
            'required_posts_per_week' => $requiredPosts,
            'required_qualified_impressions_per_post' => $requiredQualifiedReach,
            'required_gross_impressions_per_post' => $requiredGrossReach,
            'timeline' => $timeline,
        ];
    }
}
