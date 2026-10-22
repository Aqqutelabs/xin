<?php
declare(strict_types=1);

function xinng_workspace_search(PDO $pdo, int $userId, string $query, int $limit = 16): array {
    $query = trim($query);
    if ($userId <= 0 || mb_strlen($query) < 2) return [];
    $limit = max(1, min($limit, 24));
    xinng_ensure_short_link_tables($pdo);
    xinng_ensure_qr_code_tables($pdo);
    xinng_ensure_page_builder_tables($pdo);
    $like = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $query) . '%';
    $results = [];
    $sources = [
        ['sql' => 'SELECT title, slug AS detail, CONCAT("page_builder.php?id=", id) AS href, "Page" AS type, updated_at FROM pages WHERE user_id = ? AND deleted_at IS NULL AND (title LIKE ? ESCAPE "\\\\" OR slug LIKE ? ESCAPE "\\\\") ORDER BY updated_at DESC LIMIT ' . $limit, 'params' => [$userId, $like, $like]],
        ['sql' => 'SELECT title, back_half AS detail, CONCAT("short_links.php?edit=", id) AS href, "Short link" AS type, updated_at FROM short_links WHERE user_id = ? AND deleted_at IS NULL AND (title LIKE ? ESCAPE "\\\\" OR back_half LIKE ? ESCAPE "\\\\" OR destination_url LIKE ? ESCAPE "\\\\") ORDER BY updated_at DESC LIMIT ' . $limit, 'params' => [$userId, $like, $like, $like]],
        ['sql' => 'SELECT COALESCE(NULLIF(title, ""), name, "QR Code") AS title, back_half AS detail, CONCAT("edit_qr_code.php?id=", id) AS href, "QR code" AS type, updated_at FROM qr_codes WHERE user_id = ? AND deleted_at IS NULL AND (title LIKE ? ESCAPE "\\\\" OR name LIKE ? ESCAPE "\\\\" OR back_half LIKE ? ESCAPE "\\\\") ORDER BY updated_at DESC LIMIT ' . $limit, 'params' => [$userId, $like, $like, $like]],
    ];
    foreach ($sources as $source) {
        try { $stmt = $pdo->prepare($source['sql']); $stmt->execute($source['params']); $results = array_merge($results, $stmt->fetchAll()); } catch (PDOException $error) { error_log('Workspace search source unavailable: ' . $error->getMessage()); }
    }
    usort($results, static fn(array $a, array $b): int => strcmp((string)$b['updated_at'], (string)$a['updated_at']));
    return array_slice($results, 0, $limit);
}

function xinng_workspace_recent_activity(PDO $pdo, int $userId, int $limit = 6): array {
    $rows = [];
    foreach ([
        ['pages', 'title', 'Page updated', 'page_builder.php?id='],
        ['short_links', 'title', 'Short link updated', 'short_links.php?edit='],
        ['qr_codes', 'title', 'QR code updated', 'edit_qr_code.php?id='],
    ] as [$table, $column, $label, $href]) {
        try { $stmt = $pdo->prepare("SELECT id, COALESCE(NULLIF({$column}, ''), ?) AS title, updated_at FROM {$table} WHERE user_id = ? AND deleted_at IS NULL ORDER BY updated_at DESC LIMIT {$limit}"); $stmt->execute([$label, $userId]); foreach ($stmt->fetchAll() as $row) $rows[] = ['label' => $label, 'title' => $row['title'], 'at' => $row['updated_at'], 'href' => $href . (int)$row['id']]; } catch (PDOException $error) {}
    }
    usort($rows, static fn(array $a, array $b): int => strcmp((string)$b['at'], (string)$a['at']));
    return array_slice($rows, 0, $limit);
}
