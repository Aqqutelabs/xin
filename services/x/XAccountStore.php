<?php
declare(strict_types=1);

namespace Xinng\X;

final class XAccountStore
{
    public function __construct(private \PDO $pdo, private string $encryptionKey) {}

    public function save(int $userId, array $account, array $tokens): void
    {
        if ($userId <= 0) throw new XApiException(422, 'A valid user_id is required.');
        $xUserId = (string)($account['x_user_id'] ?? '');
        $username = (string)($account['username'] ?? '');
        $accessToken = (string)($tokens['access_token'] ?? '');
        if ($xUserId === '' || $username === '' || $accessToken === '') {
            throw new XApiException(502, 'X did not return complete account credentials.');
        }

        $expiresAt = isset($tokens['expires_in']) && is_numeric($tokens['expires_in'])
            ? gmdate('Y-m-d H:i:s', time() + max(0, (int)$tokens['expires_in']))
            : null;
        $scopes = (string)($tokens['scope'] ?? '');
        $sql = 'INSERT INTO x_accounts (user_id, x_user_id, username, display_name, profile_image_url, access_token_encrypted, refresh_token_encrypted, token_expires_at, scopes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE username = VALUES(username), display_name = VALUES(display_name), profile_image_url = VALUES(profile_image_url), access_token_encrypted = VALUES(access_token_encrypted), refresh_token_encrypted = VALUES(refresh_token_encrypted), token_expires_at = VALUES(token_expires_at), scopes = VALUES(scopes), updated_at = CURRENT_TIMESTAMP';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $userId,
            $xUserId,
            $username,
            $account['display_name'] ?? null,
            $account['profile_image_url'] ?? null,
            $this->encrypt($accessToken),
            isset($tokens['refresh_token']) && $tokens['refresh_token'] !== ''
                ? $this->encrypt((string)$tokens['refresh_token'])
                : null,
            $expiresAt,
            $scopes,
        ]);
    }

    public function find(int $userId): ?array
    {
        $stmt = $this->pdo->prepare('SELECT id, x_user_id, username, display_name, profile_image_url, token_expires_at, scopes, connected_at, updated_at FROM x_accounts WHERE user_id = ? ORDER BY id DESC LIMIT 1');
        $stmt->execute([$userId]);
        $account = $stmt->fetch(\PDO::FETCH_ASSOC);
        return is_array($account) ? $account : null;
    }

    public function accessToken(int $userId): ?string
    {
        return $this->storedToken($userId, 'access_token_encrypted');
    }

    public function refreshToken(int $userId): ?string
    {
        return $this->storedToken($userId, 'refresh_token_encrypted');
    }

    public function updateTokens(int $userId, array $tokens): void
    {
        $accessToken = (string)($tokens['access_token'] ?? '');
        if ($userId <= 0 || $accessToken === '') throw new XApiException(502, 'X did not return a valid access token.');
        $expiresAt = isset($tokens['expires_in']) && is_numeric($tokens['expires_in'])
            ? gmdate('Y-m-d H:i:s', time() + max(0, (int)$tokens['expires_in']))
            : null;
        $sql = 'UPDATE x_accounts SET access_token_encrypted = ?, token_expires_at = ?, updated_at = CURRENT_TIMESTAMP';
        $values = [$this->encrypt($accessToken), $expiresAt];
        if (isset($tokens['refresh_token']) && $tokens['refresh_token'] !== '') {
            $sql .= ', refresh_token_encrypted = ?';
            $values[] = $this->encrypt((string)$tokens['refresh_token']);
        }
        $sql .= ' WHERE user_id = ? ORDER BY id DESC LIMIT 1';
        $values[] = $userId;
        $this->pdo->prepare($sql)->execute($values);
    }

    public function disconnect(int $userId): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM x_accounts WHERE user_id = ?');
        $stmt->execute([$userId]);
    }

    private function storedToken(int $userId, string $column): ?string
    {
        if ($userId <= 0) return null;
        if (!in_array($column, ['access_token_encrypted', 'refresh_token_encrypted'], true)) {
            throw new \InvalidArgumentException('Invalid token column.');
        }
        $stmt = $this->pdo->prepare("SELECT {$column} FROM x_accounts WHERE user_id = ? ORDER BY id DESC LIMIT 1");
        $stmt->execute([$userId]);
        $value = $stmt->fetchColumn();
        return is_string($value) && $value !== '' ? $this->decrypt($value) : null;
    }

    private function encrypt(string $value): string
    {
        if ($this->encryptionKey === '' || !function_exists('sodium_crypto_secretbox')) {
            throw new XApiException(503, 'This server cannot securely store X tokens.');
        }
        $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $key = hash('sha256', $this->encryptionKey, true);
        return base64_encode($nonce . sodium_crypto_secretbox($value, $nonce, $key));
    }

    private function decrypt(string $value): string
    {
        if ($this->encryptionKey === '' || !function_exists('sodium_crypto_secretbox_open')) {
            throw new XApiException(503, 'This server cannot securely read X tokens.');
        }
        $payload = base64_decode($value, true);
        if ($payload === false || strlen($payload) <= SODIUM_CRYPTO_SECRETBOX_NONCEBYTES) {
            throw new XApiException(503, 'Stored X authorization is invalid.');
        }
        $nonce = substr($payload, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $ciphertext = substr($payload, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $plain = sodium_crypto_secretbox_open($ciphertext, $nonce, hash('sha256', $this->encryptionKey, true));
        if ($plain === false) throw new XApiException(503, 'Stored X authorization cannot be read.');
        return $plain;
    }
}
