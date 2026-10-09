<?php
declare(strict_types=1);

namespace Xinng\X;

require_once __DIR__ . '/XApiException.php';

final class TwitterApiIoService
{
    private const BASE_URL = 'https://api.twitterapi.io';

    public function __construct(private string $apiKey) {}

    public function assertConfigured(): void
    {
        if ($this->apiKey === '') throw new XApiException(503, 'TwitterAPI.io is not configured yet.');
    }

    public function login(string $username, string $email, string $password, string $totpSecret, string $proxy): array
    {
        $this->assertConfigured();
        $payload = [
            'user_name' => $username,
            'email' => $email,
            'password' => $password,
            'proxy' => $proxy,
        ];
        if ($totpSecret !== '') $payload['totp_secret'] = $totpSecret;
        $response = $this->request('/twitter/user_login_v2', 'POST', $payload);
        if (($response['status'] ?? '') !== 'success' || !is_string($response['login_cookie'] ?? null) || $response['login_cookie'] === '') {
            throw new XApiException(401, $this->providerMessage($response, 'TwitterAPI.io could not connect this X account.'));
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

    public function recentPosts(string $xUserId): array
    {
        $query = http_build_query(['userId' => $xUserId, 'includeReplies' => 'false'], '', '&', PHP_QUERY_RFC3986);
        $response = $this->request('/twitter/user/last_tweets?' . $query, 'GET');
        $posts = $response['tweets'] ?? null;
        if (($response['status'] ?? '') !== 'success' || !is_array($posts)) {
            throw new XApiException(502, 'TwitterAPI.io returned invalid post data.');
        }
        return array_values(array_map(static function (array $post): array {
            return [
                'id' => (string)($post['id'] ?? ''),
                'text' => (string)($post['text'] ?? ''),
                'created_at' => $post['createdAt'] ?? null,
                'public_metrics' => [
                    'like_count' => $post['likeCount'] ?? 0,
                    'retweet_count' => $post['retweetCount'] ?? 0,
                    'reply_count' => $post['replyCount'] ?? 0,
                    'quote_count' => $post['quoteCount'] ?? 0,
                    'impression_count' => $post['viewCount'] ?? null,
                ],
            ];
        }, array_filter($posts, static fn($post): bool => is_array($post) && is_string($post['id'] ?? null) && $post['id'] !== '')));
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
        $id = $response['tweet_id'] ?? null;
        if (($response['status'] ?? '') !== 'success' || !is_string($id) || $id === '') {
            throw new XApiException(502, $this->providerMessage($response, 'TwitterAPI.io could not publish this post.'));
        }
        return ['id' => $id, 'text' => $text];
    }

    private function request(string $path, string $method, array $payload = []): array
    {
        $this->assertConfigured();
        if (!function_exists('curl_init')) throw new XApiException(503, 'TwitterAPI.io is unavailable on this server.');
        $curl = curl_init(self::BASE_URL . $path);
        $headers = ['Accept: application/json', 'X-API-Key: ' . $this->apiKey];
        if ($method === 'POST') $headers[] = 'Content-Type: application/json';
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => 25,
            CURLOPT_PROTOCOLS => CURLPROTO_HTTPS,
            CURLOPT_MAXREDIRS => 0,
        ]);
        if ($method === 'POST') curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($payload, JSON_THROW_ON_ERROR));
        $body = curl_exec($curl);
        $status = (int)curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $curlErrorNumber = curl_errno($curl);
        curl_close($curl);
        if ($body === false) {
            $endpoint = parse_url($path, PHP_URL_PATH);
            error_log('TwitterAPI.io request failed at ' . $endpoint . ' with cURL error ' . $curlErrorNumber);
            throw new XApiException(
                503,
                'TwitterAPI.io could not be reached (cURL error ' . $curlErrorNumber . '). Check the server DNS, outbound HTTPS, and TLS configuration.'
            );
        }
        if ($status === 401 || $status === 403) {
            throw new XApiException(401, 'TwitterAPI.io rejected the request (HTTP ' . $status . '). Check the configured API key and provider access.');
        }
        if ($status === 429) throw new XApiException(429, 'TwitterAPI.io is rate limiting requests. Try again later.');
        if ($status < 200 || $status >= 300) {
            $endpoint = parse_url($path, PHP_URL_PATH);
            $operation = match ($endpoint) {
                '/twitter/user_login_v2' => 'X sign-in',
                '/twitter/user/info' => 'X profile lookup',
                '/twitter/user/last_tweets' => 'recent-post lookup',
                '/twitter/create_tweet_v2' => 'post publishing',
                default => 'request',
            };
            throw new XApiException(502, 'TwitterAPI.io could not complete the ' . $operation . ' (HTTP ' . $status . ').');
        }
        try { $decoded = json_decode((string)$body, true, 32, JSON_THROW_ON_ERROR); }
        catch (\JsonException) { throw new XApiException(502, 'TwitterAPI.io returned an unreadable response.'); }
        if (!is_array($decoded)) throw new XApiException(502, 'TwitterAPI.io returned an invalid response.');
        return $decoded;
    }

    private function providerMessage(array $response, string $fallback): string
    {
        $message = $response['msg'] ?? $response['message'] ?? null;
        return is_string($message) && $message !== '' ? mb_substr($message, 0, 240) : $fallback;
    }
}