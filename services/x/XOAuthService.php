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

final class XOAuthService
{
    private const AUTHORIZE_URL = 'https://x.com/i/oauth2/authorize';
    private const TOKEN_URL = 'https://api.x.com/2/oauth2/token';
    private const ME_URL = 'https://api.x.com/2/users/me?user.fields=profile_image_url,public_metrics';

    public function __construct(
        private string $clientId,
        private string $clientSecret,
        private string $redirectUri
    ) {}

    public function assertConfigured(): void
    {
        if ($this->clientId === '' || $this->redirectUri === '') {
            throw new XApiException(503, 'X connection is not configured yet.');
        }
        $scheme = strtolower((string)parse_url($this->redirectUri, PHP_URL_SCHEME));
        $host = strtolower((string)parse_url($this->redirectUri, PHP_URL_HOST));
        $localCallback = in_array($host, ['localhost', '127.0.0.1'], true);
        if (!filter_var($this->redirectUri, FILTER_VALIDATE_URL) || ($scheme !== 'https' && !($scheme === 'http' && $localCallback))) {
            throw new XApiException(503, 'X connection requires an HTTPS callback URL.');
        }
    }

    public function authorizationUrl(string $state, string $codeChallenge): string
    {
        $this->assertConfigured();
        $query = http_build_query([
            'response_type' => 'code',
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUri,
            'scope' => 'users.read tweet.read tweet.write offline.access',
            'state' => $state,
            'code_challenge' => $codeChallenge,
            'code_challenge_method' => 'S256',
        ], '', '&', PHP_QUERY_RFC3986);
        return self::AUTHORIZE_URL . '?' . $query;
    }

    public function exchangeCode(string $code, string $verifier): array
    {
        $this->assertConfigured();
        if ($code === '' || $verifier === '') throw new XApiException(400, 'The X authorization response was incomplete.');
        $fields = [
            'code' => $code,
            'grant_type' => 'authorization_code',
            'redirect_uri' => $this->redirectUri,
            'code_verifier' => $verifier,
            'client_id' => $this->clientId,
        ];
        if ($this->clientSecret !== '') $fields['client_secret'] = $this->clientSecret;
        return $this->requestJson(self::TOKEN_URL, 'POST', $fields, true);
    }

    public function currentUser(string $accessToken): array
    {
        if ($accessToken === '') throw new XApiException(401, 'The X access token is missing.');
        $response = $this->requestJson(self::ME_URL, 'GET', [], false, $accessToken);
        $user = $response['data'] ?? null;
        if (!is_array($user) || !is_string($user['id'] ?? null) || !is_string($user['username'] ?? null)) {
            throw new XApiException(502, 'X returned an invalid account response.');
        }
        return [
            'x_user_id' => $user['id'],
            'username' => $user['username'],
            'display_name' => is_string($user['name'] ?? null) ? mb_substr($user['name'], 0, 100) : $user['username'],
            'profile_image_url' => is_string($user['profile_image_url'] ?? null) ? $user['profile_image_url'] : null,
        ];
    }

    public function refreshAccessToken(string $refreshToken): array
    {
        $this->assertConfigured();
        if ($refreshToken === '') throw new XApiException(401, 'X authorization has expired. Reconnect your account.');
        $fields = [
            'refresh_token' => $refreshToken,
            'grant_type' => 'refresh_token',
            'client_id' => $this->clientId,
        ];
        if ($this->clientSecret !== '') $fields['client_secret'] = $this->clientSecret;
        return $this->requestJson(self::TOKEN_URL, 'POST', $fields, true);
    }
    
    public function recentPosts(string $accessToken, string $xUserId): array
    {
        if ($accessToken === '' || $xUserId === '') throw new XApiException(401, 'Connect an X account first.');
        $url = 'https://api.x.com/2/users/' . rawurlencode($xUserId) . '/tweets?max_results=10&tweet.fields=created_at,public_metrics,text';
        $response = $this->requestJson($url, 'GET', [], false, $accessToken);
        $posts = $response['data'] ?? [];
        if (!is_array($posts)) throw new XApiException(502, 'X returned invalid post data.');
        return array_values(array_filter($posts, static fn($post) => is_array($post) && is_string($post['id'] ?? null)));
    }

    private function requestJson(string $url, string $method, array $fields, bool $form, ?string $accessToken = null): array
    {
        if (!function_exists('curl_init')) throw new XApiException(503, 'X integration is unavailable on this server.');
        $curl = curl_init($url);
        $headers = ['Accept: application/json'];
        if ($accessToken !== null) $headers[] = 'Authorization: Bearer ' . $accessToken;
        if ($form) $headers[] = 'Content-Type: application/x-www-form-urlencoded';
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_PROTOCOLS => CURLPROTO_HTTPS,
            CURLOPT_MAXREDIRS => 0,
        ]);
        if ($method === 'POST') curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($fields, '', '&', PHP_QUERY_RFC3986));
        $body = curl_exec($curl);
        $status = (int)curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error = curl_error($curl);
        curl_close($curl);
        if ($body === false) throw new XApiException(503, 'X could not be reached.');
        try { $decoded = json_decode((string)$body, true, 32, JSON_THROW_ON_ERROR); }
        catch (\JsonException) { throw new XApiException(502, 'X returned an unreadable response.'); }
        if ($status < 200 || $status >= 300) {
            if ($status === 400 || $status === 401) throw new XApiException(401, 'X authorization was not accepted.');
            if ($status === 429) throw new XApiException(429, 'X is rate limiting requests. Try again later.');
            throw new XApiException(502, $error !== '' ? 'X request failed.' : 'X could not complete the request.');
        }
        if (!is_array($decoded)) throw new XApiException(502, 'X returned an invalid response.');
        return $decoded;
    }
}
