<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once __DIR__ . '/../XCalculator.php';
require_once __DIR__ . '/../ShareResult.php';
require_once __DIR__ . '/../ShareRepository.php';
use Xinng\CreatorCalculator\XCalculator;
use Xinng\CreatorCalculator\ShareResult;
use Xinng\CreatorCalculator\ShareRepository;
use Xinng\CreatorCalculator\ResultCard;
$c = new XCalculator(); $repo = new ShareRepository(new PDO('sqlite::memory:')); $repo->ensureSchema(); $count = 0;
$check = static function($ok) use (&$count) { if (!$ok) throw new RuntimeException('Card assertion failed: ' . ($count+1)); $count++; };
foreach ([0, 1000, 25000] as $reach) {
 $result = $c->calculateQualification(['followers'=>10000,'verified_followers'=>650,'avg_impressions_per_post'=>$reach,'posts_per_week'=>7]);
 $card = ShareResult::select($result, ['earnings'=>true,'timeline'=>true], 'private_handle');
 $check($card['outcome'] === ResultCard::build($result, $c->getRequirements()));
 $check(count($card['outcome']) === 2 && count($card) === 3);
 $check($card['handle'] === '@private_handle');
 $check($card['outcome']['headline'] === ($reach === 25000 ? 'My estimated monthly earnings' : 'My estimated time to qualify'));
 $check($reach === 25000 ? str_contains($card['outcome']['value'], '/ month') : !str_contains($card['outcome']['value'], '$'));
 $token = $repo->save($card); $check($repo->find($token) === $card);
 if ($reach === 0) $check($card['outcome']['value'] === 'More growth needed');
}
$check($repo->find('../secret') === null);
foreach (['', ' ', '@', 'bad handle', '<script>', str_repeat('a', 16)] as $invalid) {
 try { ShareResult::select($result, [], $invalid); throw new RuntimeException('Missing or invalid handle accepted'); }
 catch (InvalidArgumentException $e) { $check(true); }
}
echo "$count sharing assertions passed.\n";
