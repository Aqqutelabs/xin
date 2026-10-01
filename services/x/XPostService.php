<?php
declare(strict_types=1);

namespace Xinng\X;

final class XPostService
{
    public function publish(string $accessToken, string $text): array
    {
        $text = trim($text);
        if ($accessToken === '') throw new XApiException(401, 'Connect an X account first.');
        if ($text === '') throw new XApiException(422, 'Write a post before publishing.');
        if (mb_strlen($text, 'UTF-8') > 280) throw new XApiException(422, 'X posts must be 280 characters or fewer.');
        if (!function_exists('curl_init')) throw new XApiException(503, 'X publishing is unavailable on this server.');

        $curl = curl_init('https://api.x.com/2/tweets');
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $accessToken, 'Content-Type: application/json', 'Accept: application/json'],
            CURLOPT_POSTFIELDS => json_encode(['text' => $text], JSON_THROW_ON_ERROR),
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_PROTOCOLS => CURLPROTO_HTTPS,
            CURLOPT_MAXREDIRS => 0,
        ]);
        $body = curl_exec($curl);
        $status = (int)curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        if ($body === false) throw new XApiException(503, 'X could not be reached.');
        try { $decoded = json_decode((string)$body, true, 32, JSON_THROW_ON_ERROR); }
        catch (\JsonException) { throw new XApiException(502, 'X returned an unreadable response.'); }
        if ($status === 401 || $status === 403) throw new XApiException(401, 'X authorization has expired. Reconnect your account.');
        if ($status === 429) throw new XApiException(429, 'X is rate limiting posts. Try again later.');
        if ($status < 200 || $status >= 300 || !is_array($decoded) || !is_array($decoded['data'] ?? null) || !is_string($decoded['data']['id'] ?? null)) {
            throw new XApiException(502, 'X could not publish this post.');
        }
        return ['id' => $decoded['data']['id'], 'text' => $decoded['data']['text'] ?? $text];
    }
}