<?php
declare(strict_types=1);

namespace Xinng\X;

final class XAccountStore
{
    public function __construct(private \PDO $pdo, private string $encryptionKey) {}

    public function save(int $userId, array $account, array $credentials): void
    {
        if ($userId <= 0 || $this->encryptionKey === '') throw new XApiException(503, 'X token storage is not configured.');
        $loginCookie = (string)($credentials['login_cookie'] ?? '');
        $proxy = (string)($credentials['proxy'] ?? '');
        if ($loginCookie === '' || $proxy === '') throw new XApiException(502, 'TwitterAPI.io did not return complete account credentials.');
            private const PROVIDER = 'twitterapi.io';

        $sql = 'INSERT INTO x_accounts (user_id, x_user_id, username, display_name, profile_image_url, access_token_encrypted, refresh_token_encrypted, token_expires_at, scopes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE username = VALUES(username), display_name = VALUES(display_name), profile_image_url = VALUES(profile_image_url), access_token_encrypted = VALUES(access_token_encrypted), refresh_token_encrypted = VALUES(refresh_token_encrypted), token_expires_at = VALUES(token_expires_at), scopes = VALUES(scopes), updated_at = CURRENT_TIMESTAMP';
        $stmt = $this->pdo->prepare($sql);
            public function save(int $userId, array $account, string $loginCookie, string $proxy): void
            $userId,
            (string)$account['x_user_id'],
                if ($loginCookie === '' || $proxy === '') throw new XApiException(502, 'TwitterAPI.io did not return a complete X session.');
        ]);
    }

    public function find(int $userId): ?array
    {
        $stmt = $this->pdo->prepare("SELECT id, x_user_id, username, display_name, profile_image_url, token_expires_at, scopes, connected_at, updated_at FROM x_accounts WHERE user_id = ? AND scopes = 'twitterapi.io' ORDER BY id DESC LIMIT 1");
        $stmt->execute([$userId]);
        $account = $stmt->fetch();
                    $this->encrypt($loginCookie),
                    $this->encrypt($proxy),
                    null,
                    self::PROVIDER,
    {
        if ($this->encryptionKey === '') throw new XApiException(503, 'X token storage is not configured.');
        $stmt = $this->pdo->prepare("SELECT access_token_encrypted FROM x_accounts WHERE user_id = ? AND scopes = 'twitterapi.io' ORDER BY id DESC LIMIT 1");
        $stmt->execute([$userId]);
        $value = $stmt->fetchColumn();
        return is_string($value) ? $this->decrypt($value) : null;
    }

                return is_array($account) && ($account['scopes'] ?? '') === self::PROVIDER ? $account : null;
    {
        if ($this->encryptionKey === '') throw new XApiException(503, 'X token storage is not configured.');
            public function sessionCookie(int $userId): ?string
        $stmt->execute([$userId]);
        $value = $stmt->fetchColumn();
                $stmt = $this->pdo->prepare('SELECT access_token_encrypted FROM x_accounts WHERE user_id = ? AND scopes = ? ORDER BY id DESC LIMIT 1');
                $stmt->execute([$userId, self::PROVIDER]);

    public function updateTokens(int $userId, array $tokens): void
    {
        $accessToken = (string)($tokens['access_token'] ?? '');
            public function proxy(int $userId): ?string
        $expiresAt = isset($tokens['expires_in']) && is_numeric($tokens['expires_in'])
            ? gmdate('Y-m-d H:i:s', time() + max(0, (int)$tokens['expires_in']))
                $stmt = $this->pdo->prepare('SELECT refresh_token_encrypted FROM x_accounts WHERE user_id = ? AND scopes = ? ORDER BY id DESC LIMIT 1');
                $stmt->execute([$userId, self::PROVIDER]);
        $sql = 'UPDATE x_accounts SET access_token_encrypted = ?, token_expires_at = ?, updated_at = CURRENT_TIMESTAMP';
                return is_string($value) ? $this->decrypt($value) : null;
        $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        return base64_encode($nonce . sodium_crypto_secretbox($value, $nonce, $key));
    }

    private function decrypt(string $value): string
    {
        if (!function_exists('sodium_crypto_secretbox_open')) throw new XApiException(503, 'This server cannot securely read X tokens.');
        $payload = base64_decode($value, true);
        if ($payload === false || strlen($payload) <= SODIUM_CRYPTO_SECRETBOX_NONCEBYTES) throw new XApiException(503, 'Stored X authorization is invalid.');
        $nonce = substr($payload, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $ciphertext = substr($payload, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $plain = sodium_crypto_secretbox_open($ciphertext, $nonce, hash('sha256', $this->encryptionKey, true));
        if ($plain === false) throw new XApiException(503, 'Stored X authorization cannot be read.');
        return $plain;
    }
}
