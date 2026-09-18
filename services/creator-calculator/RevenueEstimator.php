<?php
declare(strict_types=1);
namespace Xinng\CreatorCalculator;

final class RevenueEstimator
{
    private array $model;
    public function __construct(?array $model = null)
    {
        $this->model = $model ?? require __DIR__ . '/rules/revenue-x.php';
        if (empty($this->model['version']) || !is_numeric($this->model['month_days'] ?? null) || $this->model['month_days'] <= 0 || !is_finite((float)$this->model['month_days'])) throw new \InvalidArgumentException('Invalid revenue model.');
        $previous = 0;
        foreach (['conservative', 'low', 'high', 'strong'] as $key) {
            $rate = $this->model['usd_per_thousand'][$key] ?? null;
            if (!is_numeric($rate) || !is_finite((float)$rate) || $rate < $previous) throw new \InvalidArgumentException('Revenue rates must be finite, non-negative and ordered.');
            $previous = $rate;
        }
    }
    public function getModel(): array { return $this->model; }
    public function estimate(array $account, array $rules): array
    {
        $met = $account['qualification_status'] === 'thresholds_met';
        // Earnings remain zero until both measurable thresholds are met.
        $qualified = $met ? $account['progress']['projected_qualified_impressions_90d'] : 0;
        $monthlyActivity = $qualified / $rules['window_days'] * $this->model['month_days'] * $account['original_content_ratio'];
        $monthly = [];
        $annual = [];
        foreach ($this->model['usd_per_thousand'] as $key => $rate) {
            $monthly[$key] = round($monthlyActivity / 1000 * $rate, 2);
            $annual[$key] = round($monthly[$key] * 12, 2);
        }
        return [
            'revenue_model_version' => $this->model['version'],
            'calibration' => $this->model['calibration'],
            'currency' => $this->model['currency'],
            'basis' => $met ? 'projected_activity' : 'below_thresholds',
            'qualified_impressions_90d_basis' => $qualified,
            'monthly_monetizable_impressions' => $monthlyActivity,
            'monthly' => $monthly,
            'annual' => $annual,
            'title' => $met ? 'What could this pace earn?' : 'Estimated earnings',
            'description' => $met
                ? 'Based on your projected posting pace, qualifying share and original-content share, assuming admission to the program. Your actual recent total is used only for the qualification check.'
                : 'Estimated earnings are zero until both measurable thresholds are met.',
        ];
    }
}
