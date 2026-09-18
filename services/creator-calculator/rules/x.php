<?php
declare(strict_types=1);

// Official requirements and Xinng modelling assumptions are deliberately separate.
return [
    'platform' => 'x',
    'program' => 'original_content_rewards',
    'rules_version' => 'x_original_content_rewards_2026_09_17',
    'source_url' => 'https://help.x.com/en/using-x/original-content-rewards',
    'reviewed_at' => '2026-09-17',
    'verified_followers_required' => 500,
    'qualified_impressions_required' => 500000,
    'window_days' => 90,
    'assumptions' => [
        'impressions_per_follower' => 0.10,
        'verified_follower_ratio' => 0.40,
        'qualified_impression_ratio' => 0.40,
        'original_content_ratio' => 0.40,
    ],
];
