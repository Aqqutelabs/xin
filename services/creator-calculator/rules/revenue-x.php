<?php
declare(strict_types=1);
return [
    'version' => 'x_original_rewards_illustrative_v1',
    'currency' => 'USD',
    'month_days' => 30,
    // Illustrative planning assumptions, NOT observed or official X payout rates.
    // Replace with calibrated rates and a new version when evidence is available.
    'usd_per_thousand' => ['conservative' => 0.45, 'low' => 0.65, 'high' => 1.10, 'strong' => 1.45],
    'calibration' => 'illustrative_not_calibrated',
];
