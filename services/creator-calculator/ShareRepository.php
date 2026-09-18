<?php
declare(strict_types=1);
namespace Xinng\CreatorCalculator;

final class ShareRepository
{
    private \PDO $pdo;
    public function __construct(\PDO $pdo) { $this->pdo = $pdo; }
    public function ensureSchema(): void
    {
        if ($this->pdo->getAttribute(\PDO::ATTR_DRIVER_NAME) === 'sqlite') {
            $this->pdo->exec('CREATE TABLE IF NOT EXISTS creator_shared_results (public_token TEXT PRIMARY KEY, public_payload TEXT NOT NULL, created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP)');
            return;
        }
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS creator_shared_results (
            public_token CHAR(48) CHARACTER SET ascii COLLATE ascii_bin PRIMARY KEY,
            public_payload LONGTEXT NOT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }

    public static function connect(): self
    {
        // An explicit development store avoids depending on the absent local app DB.
        // Production uses the application's configured MySQL connection.
        $local = in_array($_SERVER['SERVER_ADDR'] ?? '', ['127.0.0.1', '::1'], true);
        if ($local) {
            $pdo = new \PDO('sqlite:' . __DIR__ . '/storage/shared-results.sqlite', null, null, [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]);
            $pdo->exec('PRAGMA busy_timeout = 5000');
        } else {
            require_once __DIR__ . '/../../config.php';
            $pdo = get_db_connection();
            if (!$pdo) throw new \RuntimeException('Database unavailable');
        }
        return new self($pdo);
    }
    public function save(array $payload): string
    {
        $token = bin2hex(random_bytes(24));
        $stmt = $this->pdo->prepare('INSERT INTO creator_shared_results (public_token, public_payload) VALUES (?, ?)');
        $stmt->execute([$token, json_encode($payload, JSON_THROW_ON_ERROR)]);
        return $token;
    }
    public function find(string $token): ?array
    {
        if (!preg_match('/\A[a-f0-9]{48}\z/D', $token)) return null;
        $stmt = $this->pdo->prepare('SELECT public_payload FROM creator_shared_results WHERE public_token = ?');
        $stmt->execute([$token]);
        $json = $stmt->fetchColumn();
        return $json === false ? null : json_decode($json, true, 32, JSON_THROW_ON_ERROR);
    }
}
