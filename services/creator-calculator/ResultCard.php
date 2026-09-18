<?php
declare(strict_types=1);
namespace Xinng\CreatorCalculator;
require_once __DIR__ . '/GrowthProjection.php';
final class ResultCard
{
    public static function build(array $result, array $rules): array
    {
        $eligible = $result['qualification_status'] === 'thresholds_met';
        $days = $eligible ? null : GrowthProjection::calculate($result, $rules)['eligibility_days'];
        $money = static fn($value): string => '$' . number_format($value, 2);
        return ['headline' => $eligible ? 'My estimated monthly earnings' : 'My estimated time to qualify',
            'value' => $eligible ? $money($result['revenue']['monthly']['low']) . "\u{2013}" . $money($result['revenue']['monthly']['high']) . ' / month'
                : ($days === null ? 'More growth needed' : number_format($days, 0) . ($days == 1 ? ' day' : ' days'))];
    }
}
