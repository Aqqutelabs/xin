<?php
declare(strict_types=1);

namespace Xinng\X;

final class XAnalyticsStore
{
    public function __construct(private \PDO $pdo) {}

    public function savePosts(int $userId, array $posts): void
    {
        $stmt = $this->pdo->prepare('INSERT INTO x_post_metrics (user_id, x_post_id, post_text, posted_at, like_count, repost_count, reply_count, quote_count, impression_count) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
        foreach ($posts as $post) {
            $metrics = is_array($post['public_metrics'] ?? null) ? $post['public_metrics'] : [];
            $postedAt = is_string($post['created_at'] ?? null) ? gmdate('Y-m-d H:i:s', strtotime($post['created_at'])) : null;
            $number = static fn($value): int => is_numeric($value) && (int)$value >= 0 ? (int)$value : 0;
            $impressions = isset($metrics['impression_count']) && is_numeric($metrics['impression_count']) ? max(0, (int)$metrics['impression_count']) : null;
            $stmt->execute([
                $userId,
                (string)$post['id'],
                mb_substr((string)($post['text'] ?? ''), 0, 1000),
                $postedAt,
                $number($metrics['like_count'] ?? 0),
                $number($metrics['retweet_count'] ?? 0),
                $number($metrics['reply_count'] ?? 0),
                $number($metrics['quote_count'] ?? 0),
                $impressions,
            ]);
        }
    }

    public function latest(int $userId, int $limit = 10): array
    {
        $limit = max(1, min(25, $limit));
        $stmt = $this->pdo->prepare('SELECT metrics.x_post_id, metrics.post_text, metrics.posted_at, metrics.like_count, metrics.repost_count, metrics.reply_count, metrics.quote_count, metrics.impression_count, metrics.fetched_at FROM x_post_metrics metrics WHERE metrics.user_id = ? AND NOT EXISTS (SELECT 1 FROM x_post_metrics newer WHERE newer.user_id = metrics.user_id AND newer.x_post_id = metrics.x_post_id AND newer.fetched_at > metrics.fetched_at) ORDER BY metrics.posted_at DESC, metrics.fetched_at DESC LIMIT ' . $limit);
        $stmt->execute([$userId]);
        return $stmt->fetchAll() ?: [];
    }
}