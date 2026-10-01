<?php
declare(strict_types=1);

namespace Xinng\X;

final class XAccountStore
{
    public function __construct(private \PDO $pdo, private string $encryptionKey) {}

    public function save(int $userId, array $account, array $tokens): void
    {
        if ($userId <= 0 || $this->encryptionKey === '') throw new XApiException(503, 'X token storage is not configured.');
        $accessToken = (string)($tokens['access_token'] ?? '');
        if ($accessToken === '') throw new XApiException(502, 'X did not return an access token.');
        $refreshToken = isset($tokens['refresh_token']) ? (string)$tokens['refresh_token'] : '';
        $expiresAt = isset($tokens['expires_in']) && is_numeric($tokens['expires_in'])
            ? gmdate('Y-m-d H:i:s', time() + max(0, (int)$tokens['expires_in']))
            : null;
        $scopes = is_string($tokens['scope'] ?? null) ? $tokens['scope'] : '';
        $sql = 'INSERT INTO x_accounts (user_id, x_user_id, username, display_name, profile_image_url, access_token_encrypted, refresh_token_encrypted, token_expires_at, scopes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE username = VALUES(username), display_name = VALUES(display_name), profile_image_url = VALUES(profile_image_url), access_token_encrypted = VALUES(access_token_encrypted), refresh_token_encrypted = VALUES(refresh_token_encrypted), token_expires_at = VALUES(token_expires_at), scopes = VALUES(scopes), updated_at = CURRENT_TIMESTAMP';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $userId,
            (string)$account['x_user_id'],
            (string)$account['username'],
            (string)($account['display_name'] ?? ''),
            $account['profile_image_url'] ?? null,
            $this->encrypt($accessToken),
            $refreshToken !== '' ? $this->encrypt($refreshToken) : null,
            $expiresAt,
            $scopes,
        ]);
    }

    public function find(int $userId): ?array
    {
        $stmt = $this->pdo->prepare('SELECT id, x_user_id, username, display_name, profile_image_url, token_expires_at, scopes, connected_at, updated_at FROM x_accounts WHERE user_id = ? ORDER BY id DESC LIMIT 1');
        $stmt->execute([$userId]);
        $account = $stmt->fetch();
        return is_array($account) ? $account : null;
    }

    public function accessToken(int $userId): ?string
    {
        if ($this->encryptionKey === '') throw new XApiException(503, 'X token storage is not configured.');
        $stmt = $this->pdo->prepare('SELECT access_token_encrypted FROM x_accounts WHERE user_id = ? ORDER BY id DESC LIMIT 1');
        $stmt->execute([$userId]);
        $value = $stmt->fetchColumn();
        return is_string($value) ? $this->decrypt($value) : null;
    }

    public function refreshToken(int $userId): ?string
    {
        if ($this->encryptionKey === '') throw new XApiException(503, 'X token storage is not configured.');
        $stmt = $this->pdo->prepare('SELECT refresh_token_encrypted FROM x_accounts WHERE user_id = ? ORDER BY id DESC LIMIT 1');
        $stmt->execute([$userId]);
        $value = $stmt->fetchColumn();
        return is_string($value) && $value !== '' ? $this->decrypt($value) : null;
    }

    public function updateTokens(int $userId, array $tokens): void
    {
        $accessToken = (string)($tokens['access_token'] ?? '');
        if ($accessToken === '') throw new XApiException(502, 'X did not return a refreshed access token.');
        $expiresAt = isset($tokens['expires_in']) && is_numeric($tokens['expires_in'])
            ? gmdate('Y-m-d H:i:s', time() + max(0, (int)$tokens['expires_in']))
            : null;
        $params = [$this->encrypt($accessToken), $expiresAt];
        $sql = 'UPDATE x_accounts SET access_token_encrypted = ?, token_expires_at = ?, updated_at = CURRENT_TIMESTAMP';
        $newRefreshToken = (string)($tokens['refresh_token'] ?? '');
        if ($newRefreshToken !== '') {
            $sql .= ', refresh_token_encrypted = ?';
            $params[] = $this->encrypt($newRefreshToken);
        }
        $sql .= ' WHERE user_id = ?';
        $params[] = $userId;
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        if ($stmt->rowCount() < 1) throw new XApiException(401, 'X account is no longer connected.');
    }

    public function disconnect(int $userId): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM x_accounts WHERE user_id = ?');
        $stmt->execute([$userId]);
    }

    private function encrypt(string $value): string
    {
        if (!function_exists('sodium_crypto_secretbox')) throw new XApiException(503, 'This server cannot securely store X tokens.');
        $key = hash('sha256', $this->encryptionKey, true);
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
