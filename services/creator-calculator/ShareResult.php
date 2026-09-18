<?php
declare(strict_types=1);
namespace Xinng\CreatorCalculator;
require_once __DIR__ . '/ResultCard.php';
require_once __DIR__ . '/XCalculator.php';
final class ShareResult
{
    public static function select(array $result, array $options, string $handle = ''): array
    {
        $handle = preg_replace('/^@/', '', trim($handle));
        if (!preg_match('/\A[A-Za-z0-9_]{1,15}\z/D', $handle)) throw new \InvalidArgumentException('Enter your X handle before creating a share card.');
        return ['schema_version'=>3, 'handle'=>'@' . $handle, 'outcome'=>ResultCard::build($result, (new XCalculator())->getRequirements())];
    }
}
