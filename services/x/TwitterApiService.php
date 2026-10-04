<?php
declare(strict_types=1);

namespace Xinng\X;

final class XApiException extends \RuntimeException
{
    public int $status;

    public function __construct(int $status, string $message)
    {
        parent::__construct($message);
        $this->status = $status;
    }
}

final class TwitterApiService
{
    private const BASE_URL = 'https://api.twitterapi.io';

    public function __construct(private string $apiKey) {}

    public function assertConfigured(): void
    {
        if ($this->apiKey === '') throw new XApiException(503, 'TwitterAPI.io is not configured yet.');
    }

    public function login(array $credentials): array
    {
        $this->assertConfigured();
        $payload = [
            'user_name' => $credentials['username'],
            'email' => $credentials['email'],
            'password' => $credentials['password'],
            'proxy' => $credentials['proxy'],
        ];
        if ($credentials['totp_secret'] !== '') $payload['totp_secret'] = $credentials['totp_secret'];
        $response = $this->request('/twitter/user_login_v2', 'POST', $payload);
        if (($response['status'] ?? '') !== 'success' || !is_string($response['login_cookie'] ?? null) || $response['login_cookie'] === '') {
            throw new XApiException(401, 'TwitterAPI.io could not connect this X account. Check the credentials, 2FA secret, and proxy.');
        }
        return ['login_cookie' => $response['login_cookie']];
    }

    public function userInfo(string $username): array
    {
        $response = $this->request('/twitter/user/info?' . http_build_query(['userName' => $username], '', '&', PHP_QUERY_RFC3986), 'GET');
        $user = $response['data'] ?? null;
        if (($response['status'] ?? '') !== 'success' || !is_array($user) || !is_string($user['id'] ?? null) || !is_string($user['userName'] ?? null)) {
            throw new XApiException(502, 'TwitterAPI.io returned an invalid account response.');
        }
        return [
            'x_user_id' => $user['id'],
            'username' => mb_substr($user['userName'], 0, 15),
            'display_name' => is_string($user['name'] ?? null) ? mb_substr($user['name'], 0, 100) : $user['userName'],
            'profile_image_url' => is_string($user['profilePicture'] ?? null) ? $user['profilePicture'] : null,
        ];
    }

    public function recentPosts(string $userId): array
    {
        $response = $this->request('/twitter/user/last_tweets?' . http_build_query(['userId' => $userId], '', '&', PHP_QUERY_RFC3986), 'GET');
        if (($response['status'] ?? '') !== 'success' || !is_array($response['tweets'] ?? null)) {
            throw new XApiException(502, 'TwitterAPI.io returned invalid post data.');
        }
        $posts = [];
        foreach ($response['tweets'] as $tweet) {
            if (!is_array($tweet) || !is_string($tweet['id'] ?? null)) continue;
            $posts[] = [
                'id' => $tweet['id'],
                'text' => is_string($tweet['text'] ?? null) ? $tweet['text'] : '',
                'created_at' => is_string($tweet['createdAt'] ?? null) ? $tweet['createdAt'] : null,
                'public_metrics' => [
                    'like_count' => $tweet['likeCount'] ?? 0,
                    'retweet_count' => $tweet['retweetCount'] ?? 0,
                    'reply_count' => $tweet['replyCount'] ?? 0,
                    'quote_count' => $tweet['quoteCount'] ?? 0,
                    'impression_count' => $tweet['viewCount'] ?? null,
                ],
            ];
        }
        return $posts;
    }

    public function publish(string $loginCookie, string $proxy, string $text): array
    {
        $text = trim($text);
        if ($loginCookie === '' || $proxy === '') throw new XApiException(401, 'Reconnect your X account before publishing.');
        if ($text === '') throw new XApiException(422, 'Write a post before publishing.');
        if (mb_strlen($text, 'UTF-8') > 280) throw new XApiException(422, 'X posts must be 280 characters or fewer.');
        $response = $this->request('/twitter/create_tweet_v2', 'POST', [
            'login_cookies' => $loginCookie,
            'tweet_text' => $text,
            'proxy' => $proxy,
        ]);
        if (($response['status'] ?? '') !== 'success' || !is_string($response['tweet_id'] ?? null)) {
            throw new XApiException(502, 'TwitterAPI.io could not publish this post.');
        }
        return ['id' => $response['tweet_id'], 'text' => $text];
    }

    private function request(string $path, string $method, ?array $payload = null): array
    {
        $this->assertConfigured();
        if (!function_exists('curl_init')) throw new XApiException(503, 'TwitterAPI.io is unavailable on this server.');
        $curl = curl_init(self::BASE_URL . $path);
        $headers = ['Accept: application/json', 'X-API-Key: ' . $this->apiKey];
        if ($payload !== null) $headers[] = 'Content-Type: application/json';
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_PROTOCOLS => CURLPROTO_HTTPS,
            CURLOPT_MAXREDIRS => 0,
        ]);
        if ($payload !== null) curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($payload, JSON_THROW_ON_ERROR));
        $body = curl_exec($curl);
        $status = (int)curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        if ($body === false) throw new XApiException(503, 'TwitterAPI.io could not be reached.');
        try { $decoded = json_decode((string)$body, true, 32, JSON_THROW_ON_ERROR); }
        catch (\JsonException) { throw new XApiException(502, 'TwitterAPI.io returned an unreadable response.'); }
        if ($status === 401 || $status === 403) throw new XApiException(401, 'TwitterAPI.io rejected the API key or X account session.');
        if ($status === 429) throw new XApiException(429, 'TwitterAPI.io is rate limiting requests. Try again later.');
        if ($status < 200 || $status >= 300 || !is_array($decoded)) throw new XApiException(502, 'TwitterAPI.io could not complete the request.');
        return $decoded;
    }
}