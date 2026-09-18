<?php
declare(strict_types=1);
namespace Xinng\CreatorCalculator;

final class LookupException extends \RuntimeException
{
    public int $status;
    public function __construct(int $status, string $message) { parent::__construct($message); $this->status = $status; }
}

final class XHandleLookup
{
    private string $token;
    private $transport;
    public function __construct(string $token, ?callable $transport = null) { $this->token = trim($token); $this->transport = $transport; }
    public static function normalize(string $handle): string
    {
        $handle = trim($handle);
        if (str_starts_with($handle, '@')) $handle = substr($handle, 1);
        if (!preg_match('/\A[A-Za-z0-9_]{1,15}\z/D', $handle)) throw new LookupException(422, 'Use an X handle with 1–15 letters, numbers or underscores.');
        return $handle;
    }
    public function lookup(string $handle): array
    {
        $handle = self::normalize($handle);
        if ($this->token === '' || strpbrk($this->token, "\r\n") !== false) throw new LookupException(503, 'X lookup is not available right now. You can enter your numbers manually.');
        $url = 'https://api.x.com/2/users/by/username/' . rawurlencode($handle) . '?user.fields=public_metrics,verified_followers_count,protected';
        [$status, $body] = $this->transport ? ($this->transport)($url) : $this->request($url);
        if ($status === 404) throw new LookupException(404, 'That X account could not be found. Check the handle or use manual entry.');
        if ($status === 429) throw new LookupException(429, 'X is limiting lookups. Try again later or enter your numbers manually.');
        if ($status !== 200) throw new LookupException(503, 'X could not provide this profile. Please use manual entry for now.');
        try { $decoded = json_decode($body, true, 32, JSON_THROW_ON_ERROR); }
        catch (\JsonException $error) { throw new LookupException(502, 'X returned an unreadable response. Please use manual entry.'); }
        $data = $decoded['data'] ?? null;
        if (!is_array($data) || !is_string($data['username'] ?? null) || strcasecmp($data['username'], $handle) !== 0) throw new LookupException(404, 'No matching profile was returned. Check your handle or use manual entry.');
        if (($data['protected'] ?? false) === true) throw new LookupException(422, 'This profile is protected. Please enter your numbers manually.');
        $count = static fn($value): ?int => is_int($value) && $value >= 0 && $value <= 1000000000 ? $value : null;
        $followers = $count($data['public_metrics']['followers_count'] ?? null);
        $verified = $count($data['verified_followers_count'] ?? null);
        if ($followers === null) throw new LookupException(502, 'Follower data is unavailable for this profile. Please use manual entry.');
        if ($verified !== null && $verified > $followers) $verified = null;
        return [
            'handle' => $data['username'],
            'display_name' => is_string($data['name'] ?? null) ? mb_substr($data['name'], 0, 100) : $data['username'],
            'followers' => $followers,
            'verified_followers' => $verified,
            'source' => 'X API public profile',
            'retrieved_at' => gmdate('Y-m-d\TH:i:s\Z'),
        ];
    }
    private function request(string $url): array
    {
        if (!function_exists('curl_init')) throw new LookupException(503, 'Profile lookup is unavailable. Please use manual entry.');
        $curl = curl_init($url);
        $body = '';
        curl_setopt_array($curl, [
            CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $this->token, 'Accept: application/json'],
            CURLOPT_FOLLOWLOCATION => false, CURLOPT_CONNECTTIMEOUT => 4, CURLOPT_TIMEOUT => 12,
            CURLOPT_PROTOCOLS => CURLPROTO_HTTPS,
            CURLOPT_WRITEFUNCTION => static function ($curl, string $chunk) use (&$body): int {
                if (strlen($body) + strlen($chunk) > 262144) return 0;
                $body .= $chunk; return strlen($chunk);
            },
        ]);
        $ok = curl_exec($curl);
        $status = (int)curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        if ($ok === false) throw new LookupException(503, 'X could not be reached. Please try manual entry.');
        return [$status, $body];
    }
}
