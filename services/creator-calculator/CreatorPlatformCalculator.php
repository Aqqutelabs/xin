<?php
declare(strict_types=1);

namespace Xinng\CreatorCalculator;

// Extend through separate timeline/revenue capabilities as those sprints land.
interface CreatorPlatformCalculator
{
    public function getRequirements(): array;
    public function calculateQualification(array $inputs): array;
    public function calculateTimeline(array $inputs): array;
    public function calculateScenarios(array $inputs): array;
    public function estimateRevenue(array $inputs): array;
}
