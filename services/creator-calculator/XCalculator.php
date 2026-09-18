<?php
declare(strict_types=1);

namespace Xinng\CreatorCalculator;

require_once __DIR__ . '/CreatorPlatformCalculator.php';
require_once __DIR__ . '/QualificationProgressEngine.php';
require_once __DIR__ . '/RevenueEstimator.php';

final class InputValidationException extends \InvalidArgumentException
{
    public array $errors;

    public function __construct(array $errors)
    {
        parent::__construct('Please check the highlighted fields.');
        $this->errors = $errors;
    }
}

final class XCalculator implements CreatorPlatformCalculator
{
    private array $rules;

    public function __construct(?array $rules = null)
    {
        $this->rules = $rules ?? require __DIR__ . '/rules/x.php';
        foreach (['verified_followers_required', 'qualified_impressions_required', 'window_days'] as $key) {
            if (!isset($this->rules[$key]) || !is_numeric($this->rules[$key]) || !is_finite((float) $this->rules[$key]) || $this->rules[$key] <= 0) {
                throw new \InvalidArgumentException('Invalid calculator rule: ' . $key);
            }
        }
        foreach (['impressions_per_follower', 'verified_follower_ratio', 'qualified_impression_ratio', 'original_content_ratio'] as $key) {
            $value = $this->rules['assumptions'][$key] ?? null;
            if (!is_numeric($value) || !is_finite((float) $value) || $value < 0 || $value > 1) {
                throw new \InvalidArgumentException('Invalid modelling assumption: ' . $key);
            }
        }
    }

    public function getRequirements(): array
    {
        return $this->rules;
    }

    public function calculateQualification(array $inputs): array
    {
        $errors = [];
        $number = static function (string $key, float $max, ?float $default = null, bool $integer = false) use ($inputs, &$errors): ?float {
            $raw = $inputs[$key] ?? '';
            if ($raw === '' || $raw === null) {
                if ($default !== null) return $default;
                $errors[$key] = 'Enter a number for this field.';
                return null;
            }
            if (!is_scalar($raw) || is_bool($raw) || !is_numeric($raw)) {
                $errors[$key] = 'Enter a valid number.';
                return null;
            }
            $value = (float) $raw;
            if (!is_finite($value) || $value < 0 || $value > $max || ($integer && floor($value) !== $value)) {
                $errors[$key] = 'Enter ' . ($integer ? 'a whole number' : 'a number') . ' from 0 to ' . number_format($max) . '.';
                return null;
            }
            return $value;
        };
        $defaults = $this->rules['assumptions'];
        $followers = $number('followers', 1000000000, null, true);
        $unknown = in_array($inputs['verified_unknown'] ?? false, [true, '1', 1, 'on'], true)
            || !isset($inputs['verified_followers']) || $inputs['verified_followers'] === '';
        $verified = $unknown ? floor(($followers ?? 0) * $defaults['verified_follower_ratio']) : $number('verified_followers', 1000000000, null, true);
        if ($verified !== null && $followers !== null && $verified > $followers) $errors['verified_followers'] = 'Verified followers cannot exceed total followers.';
        $average = $number('avg_impressions_per_post', 1000000000, ($followers ?? 0) * $defaults['impressions_per_follower']);
        $posts = $number('posts_per_week', 100, null, true);
        $qualifiedRatio = $number('qualified_impression_percent', 100, $defaults['qualified_impression_ratio'] * 100);
        $originalRatio = $number('original_content_percent', 100, $defaults['original_content_ratio'] * 100);
        $hasActual = isset($inputs['gross_impressions_90d']) && $inputs['gross_impressions_90d'] !== '';
        $actual = $hasActual ? $number('gross_impressions_90d', 1000000000000, null, true) : null;
        $growthEstimated = !isset($inputs['weekly_follower_growth']) || $inputs['weekly_follower_growth'] === '';
        $growth = $number('weekly_follower_growth', 1000000000, ($followers ?? 0) * 0.02 * 7);
        $country = $inputs['country'] ?? '';
        if (!is_string($country) || strlen($country) > 80) $errors['country'] = 'Use a country name of up to 80 characters.';
        if ($errors) throw new InputValidationException($errors);

        $gross = $hasActual ? $actual : $average * $posts * ($this->rules['window_days'] / 7);
        $qualified = $gross * ($qualifiedRatio / 100);
        $followerTarget = $this->rules['verified_followers_required'];
        $impressionTarget = $this->rules['qualified_impressions_required'];
        $followerPass = $verified >= $followerTarget;
        $impressionPass = $qualified >= $impressionTarget;
        $result = [
            'platform' => 'x',
            'rules_version' => $this->rules['rules_version'],
            'qualification_status' => $followerPass && $impressionPass ? 'thresholds_met' : 'below_thresholds',
            // The limiting requirement determines progress; surplus followers cannot offset low reach.
            'qualification_progress' => min(1, $verified / $followerTarget, $qualified / $impressionTarget) * 100,
            'followers' => $followers,
            'weekly_follower_growth' => $growth,
            'follower_growth_estimated' => $growthEstimated,
            'verified_followers' => $verified,
            'verified_followers_estimated' => $unknown,
            'verified_follower_ratio' => $followers > 0 ? $verified / $followers : 0,
            'verified_followers_gap' => max(0, $followerTarget - $verified),
            'verified_followers_pass' => $followerPass,
            'avg_impressions_per_post' => $average,
            'posts_per_week' => $posts,
            'gross_impressions_90d' => $gross,
            'gross_impressions_source' => $hasActual ? 'provided' : 'estimated',
            'qualified_impression_ratio' => $qualifiedRatio / 100,
            'estimated_qualified_impressions_90d' => $qualified,
            'qualified_impressions_gap' => max(0, $impressionTarget - $qualified),
            'qualified_impressions_pass' => $impressionPass,
            // Original content does not reduce the qualification metric a second time.
            'original_content_ratio' => $originalRatio / 100,
            'country' => trim($country),
        ];
        $result['progress'] = (new QualificationProgressEngine())->calculate($result, $this->rules);
        $result['revenue'] = (new RevenueEstimator())->estimate($result, $this->rules);
        $result['revenue_model_version'] = $result['revenue']['revenue_model_version'];
        return $result;
    }

    public function calculateTimeline(array $inputs): array
    {
        return $this->calculateQualification($inputs)['progress']['timeline'];
    }

    public function estimateRevenue(array $inputs): array
    {
        return $this->calculateQualification($inputs)['revenue'];
    }

    public function calculateScenarios(array $inputs): array
    {
        // Scenario inputs describe a future full window, never rewrite historical analytics.
        unset($inputs['gross_impressions_90d']);
        return $this->calculateQualification($inputs);
    }
}
