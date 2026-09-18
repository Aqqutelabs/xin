<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once __DIR__ . '/../XHandleLookup.php';
use Xinng\CreatorCalculator\XHandleLookup;
use Xinng\CreatorCalculator\LookupException;
$count = 0;
$check = static function (bool $ok, string $message) use (&$count): void { if (!$ok) throw new RuntimeException($message); $count++; };
$profile = ['username' => 'demo', 'name' => 'Demo Creator', 'public_metrics' => ['followers_count' => 10000], 'verified_followers_count' => 650];
$mock = static fn(array $data, int $status = 200) => new XHandleLookup('test-token', static fn($url) => [$status, json_encode(['data' => $data])]);
$result = $mock($profile)->lookup('@demo');
$check($result['followers'] === 10000 && $result['verified_followers'] === 650, 'Public counts returned');
$check(XHandleLookup::normalize(' @Demo ') === 'Demo', 'Handle normalization');
unset($profile['verified_followers_count']);
$check($mock($profile)->lookup('demo')['verified_followers'] === null, 'Missing verified count is unknown, not zero');
$profile['public_metrics']['followers_count'] = 0;
$profile['verified_followers_count'] = 0;
$check($mock($profile)->lookup('demo')['verified_followers'] === 0, 'True zero is preserved');
$profile['verified_followers_count'] = 100;
$check($mock($profile)->lookup('demo')['verified_followers'] === null, 'Inconsistent count discarded');
foreach (['', '@@demo', 'https://x.com/demo', 'a b', '<script>', str_repeat('x',16)] as $handle) {
    try { XHandleLookup::normalize($handle); throw new RuntimeException('Invalid handle accepted'); }
    catch (LookupException $error) { $check($error->status === 422, 'Invalid handle rejected'); }
}
foreach ([401, 403, 404, 429, 500] as $status) {
    try { $mock($profile, $status)->lookup('demo'); throw new RuntimeException('Upstream failure accepted'); }
    catch (LookupException $error) { $check($error->status === (in_array($status,[404,429]) ? $status : 503), 'Safe upstream failure'); }
}
foreach ([array_replace($profile,['protected'=>true]), array_replace($profile,['username'=>'someone_else']), array_replace($profile,['public_metrics'=>[]])] as $bad) {
    try { $mock($bad)->lookup('demo'); throw new RuntimeException('Invalid profile accepted'); }
    catch (LookupException $error) { $check(true, 'Invalid or protected profile rejected'); }
}
try { (new XHandleLookup(''))->lookup('demo'); throw new RuntimeException('Missing token accepted'); }
catch (LookupException $error) { $check($error->status === 503, 'Missing configuration falls back'); }
try { (new XHandleLookup('test', static fn($url) => [200, 'not json']))->lookup('demo'); throw new RuntimeException('Invalid JSON accepted'); }
catch (LookupException $error) { $check($error->status === 502, 'Malformed response rejected'); }
echo "$count lookup assertions passed.\n";
