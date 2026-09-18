<?php
declare(strict_types=1);
namespace Xinng\CreatorCalculator;

require_once __DIR__ . '/GrowthProjection.php';

final class ProgressPresenter
{
    /** Shared copy for native HTML and enhanced JSON responses. */
    public static function describe(array $result, array $rules): array
    {
        $progress = $result['progress'];
        $timeline = $progress['timeline'];
        $n = static fn($value): string => number_format((float) $value, 0);
        $target = $n($rules['qualified_impressions_required']);
        $days = $n($rules['window_days']);
        $paceTitle = $progress['sustainable_pace'] ? 'Your current pace can sustain the impression threshold.' : 'Your current pace is below the qualification threshold.';
        $paceBody = 'At this rate, you are projected to generate ' . $n($progress['projected_qualified_impressions_90d']) . ' qualifying impressions every ' . $days . ' days. The requirement is ' . $target . '.';
        if (!$progress['sustainable_pace']) {
            $paceBody .= $progress['additional_reach_percent'] === null
                ? ' Your projected qualifying activity is zero. Add posting activity, reach and a non-zero qualifying share to model a viable pace.'
                : ' You need approximately ' . $n(ceil($progress['additional_reach_percent'])) . '% more qualifying reach at the same qualifying share.';
            if ($result['qualified_impressions_pass']) $paceBody .= ' Your recent total meets the impression threshold now, but this pace may not maintain it as older impressions expire.';
        }
        $posts = $progress['required_posts_per_week'];
        $postText = $posts === null
            ? 'A posting target cannot be calculated with zero average reach or a zero qualifying share.'
            : 'At your current average reach and qualifying share, about ' . $n(ceil($posts)) . ' posts per week would meet the impression threshold over a full ' . $days . '-day window.';
        if ($posts !== null && $posts > 100) $postText .= ' This exceeds the form’s 100-post weekly range; improving reach or qualifying share is another route.';
        $gross = $progress['required_gross_impressions_per_post'];
        $reachText = $gross === null
            ? 'A reach target needs a non-zero posting frequency and qualifying share.'
            : 'At ' . $n($result['posts_per_week']) . ' posts per week, average about ' . $n(ceil($gross)) . ' gross impressions per post (' . $n(ceil($progress['required_qualified_impressions_per_post'])) . ' qualifying impressions).';
        $timelineTitle = 'More history is needed for a timeline.';
        $timelineBody = 'Enter your actual gross impressions for the last ' . $days . ' days in Fine-tune my estimate. A projection alone is not a record of past impressions.';
        if ($timeline['status'] === 'impressions_met') {
            $timelineTitle = 'The impression threshold is met now.';
            $timelineBody = 'Your supplied numbers already meet the impression requirement. This is not an X approval or a payout date.';
        } elseif ($timeline['status'] === 'below_sustainable_pace') {
            $timelineTitle = 'No qualification date at this pace.';
            $timelineBody = 'Older impressions leave the rolling ' . $days . '-day window. Maintaining this pace alone will not reach the impression threshold; more time is not enough.';
        } elseif ($timeline['status'] === 'modelled') {
            $timelineTitle = 'Modelled impression timeline: ' . $n($timeline['impression_days_low']) . '–' . $n($timeline['impression_days_high']) . ' days';
            $timelineBody = 'About ' . $n($timeline['impression_days_uniform']) . ' days if your existing impressions are spread evenly across the last ' . $days . ' days. The range accounts for unknown expiry dates of those impressions. It assumes steady future posting, reach and qualifying share; it is not a guaranteed date.';
        }
        if (!$result['verified_followers_pass']) $timelineBody .= ' You also need ' . $n($result['verified_followers_gap']) . ' verified followers. Without follower-growth history, an overall qualification date cannot be estimated.';
        elseif ($result['verified_followers_estimated']) $timelineBody .= ' Verify your actual verified-follower count before treating this as progress toward overall qualification.';
        elseif ($timeline['status'] === 'modelled') $timelineBody .= ' Your verified-follower threshold is currently met; this range assumes that count remains sufficient.';
        $projection = GrowthProjection::calculate($result, $rules);
        $growthDaily = $result['weekly_follower_growth'] / 7;
        $format = static fn($value): string => number_format((float) $value, $value > 0 && $value < 1 ? 2 : 0);
        $prefix = $result['follower_growth_estimated']
            ? 'Using estimated growth of ' . $format($growthDaily) . ' followers/day, '
            : 'At your current growth rate, ';
        $followerGrowth = $prefix . ($projection['follower_days'] === null
            ? 'a verified-follower timeline cannot be estimated with the current growth and verified ratio'
            : ($projection['follower_days'] == 0 ? 'you have reached ' . $n($rules['verified_followers_required']) . ' verified followers'
                : 'you could reach ' . $n($rules['verified_followers_required']) . ' verified followers in est. ' . $n($projection['follower_days']) . ' days'));
        $followerGrowth .= $projection['impression_days'] === null
            ? '. Impression eligibility cannot be reached at this growth and posting rate.'
            : ' and you could reach impression eligibility in est. ' . $n($projection['impression_days']) . ' days.';
        if ($projection['eligibility_days'] !== null) {
            if ($projection['eligibility_days'] > 300) $followerGrowth .= "\nThis estimate is over 300 days. More effort to grow your audience or improve reach could shorten the timeline.";
        }
        $shortDays = static fn($value): string => $value === null ? 'No estimate' : ($value == 0 ? 'Reached' : number_format($value, 0) . ' days');
        $growthStats = [
            'growth_rate' => '+' . $format($result['follower_growth_estimated'] ? $growthDaily : $result['weekly_follower_growth']) . ($result['follower_growth_estimated'] ? '/day (est.)' : '/week'),
            'verified_time' => $shortDays($projection['follower_days']),
            'impression_time' => $shortDays($projection['impression_days']),
            'eligibility_time' => $shortDays($projection['eligibility_days']),
        ];
        $summary = $posts !== null && $gross !== null
            ? $n(ceil($posts)) . ' posts/week at current reach, or ' . $n(ceil($gross)) . ' impressions/post at ' . $n($result['posts_per_week']) . ' posts/week.'
            : 'Increase posting, reach or qualifying share to build eligible impressions.';
        if ($projection['eligibility_days'] !== null && $projection['eligibility_days'] > 300) $summary .= ' Over 300 days with follower growth of ' . $format($result['follower_growth_estimated'] ? $growthDaily : $result['weekly_follower_growth']) . ($result['follower_growth_estimated'] ? '/day (est.)' : '/week') . '. Increase effort to shorten the timeline.';
        return ['growth_stats' => $growthStats, 'threshold_summary' => $summary, 'follower_growth' => $followerGrowth, 'pace_title' => $paceTitle, 'pace_body' => $paceBody, 'posts_body' => $postText, 'reach_body' => $reachText, 'timeline_title' => $timelineTitle, 'timeline_body' => $timelineBody];
    }
}
