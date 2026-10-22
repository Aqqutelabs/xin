<?php
declare(strict_types=1);
/**
 * One-off, CLI-only merge of the two already-imported, isolated local databases.
 * Never point this script at a production database.
 */
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

$sourceName = 'xinng_merge_source_20261022';
$targetName = 'xinng_merge_target_20261022';
$dbUser = getenv('DB_USER') ?: 'root';
$dbPass = getenv('DB_PASS') ?: '';
$source = new PDO("mysql:host=127.0.0.1;dbname={$sourceName};charset=utf8mb4", $dbUser, $dbPass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$target = new PDO("mysql:host=127.0.0.1;dbname={$targetName};charset=utf8mb4", $dbUser, $dbPass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

function rows(PDO $db, string $sql): array { return $db->query($sql)->fetchAll(PDO::FETCH_ASSOC); }
function countRows(PDO $db, string $table): int { return (int)$db->query("SELECT COUNT(*) FROM `{$table}`")->fetchColumn(); }
function cols(PDO $db, string $table): array { return array_column(rows($db, "SHOW COLUMNS FROM `{$table}`"), 'Field'); }
function q(string $name): string { return '`' . str_replace('`', '``', $name) . '`'; }
function insertRow(PDO $db, string $table, array $row): void {
    $names = array_keys($row);
    $sql = 'INSERT INTO ' . q($table) . ' (' . implode(',', array_map('q', $names)) . ') VALUES (' . implode(',', array_fill(0, count($names), '?')) . ')';
    $db->prepare($sql)->execute(array_values($row));
}
function updateRow(PDO $db, string $table, string $key, mixed $keyValue, array $row): void {
    unset($row[$key]);
    if (!$row) return;
    $parts = array_map(fn($name) => q($name) . '=?', array_keys($row));
    $db->prepare('UPDATE ' . q($table) . ' SET ' . implode(',', $parts) . ' WHERE ' . q($key) . '=?')->execute([...array_values($row), $keyValue]);
}
function quarantineRow(PDO $db, string $sourceName, string $table, array $row, string $reason, array &$counts): void {
    $quarantineTable = 'migration_quarantine_' . $table;
    if (!isset($counts[$table])) {
        $db->exec('CREATE TABLE ' . q($quarantineTable) . ' AS SELECT * FROM ' . q($sourceName) . '.' . q($table) . ' WHERE 1=0');
        $db->exec('ALTER TABLE ' . q($quarantineTable) . ' ADD COLUMN `quarantine_reason` varchar(160) NOT NULL');
        $counts[$table] = 0;
    }
    $row['quarantine_reason'] = $reason;
    insertRow($db, $quarantineTable, $row);
    $counts[$table]++;
}

// These exact checks make an accidental second run or wrong-database run fail closed.
if (countRows($source, 'users') !== 37 || countRows($source, 'pages') !== 100 ||
    countRows($target, 'users') !== 2 || countRows($target, 'pages') !== 2 ||
    countRows($target, 'qr_codes') !== 2) {
    throw new RuntimeException('Staging database counts differ from the inspected dumps; refusing to merge.');
}

$target->exec('ALTER TABLE `links` ADD COLUMN `user_id` bigint unsigned DEFAULT NULL');
$target->exec('ALTER TABLE `qr_codes` ADD COLUMN `logo_url` text DEFAULT NULL');
$target->exec('ALTER TABLE `notifications` MODIFY COLUMN `title` varchar(180) NOT NULL');

$targetTables = array_column(rows($target, 'SHOW TABLES'), 'Tables_in_' . $targetName);
$sourceTables = array_column(rows($source, 'SHOW TABLES'), 'Tables_in_' . $sourceName);
foreach (array_diff($sourceTables, $targetTables) as $table) {
    $definition = $source->query('SHOW CREATE TABLE ' . q($table))->fetch(PDO::FETCH_ASSOC)['Create Table'];
    $target->exec($definition);
}
$target->exec('CREATE TABLE `migration_quarantine_credit_transactions` LIKE `credit_transactions`');
$target->exec('ALTER TABLE `migration_quarantine_credit_transactions` DROP PRIMARY KEY, MODIFY `id` bigint unsigned NOT NULL DEFAULT 0, ADD COLUMN `quarantine_reason` varchar(160) NOT NULL');
$target->exec('CREATE TABLE `migration_quarantine_short_link_clicks` LIKE `short_link_clicks`');
$target->exec('ALTER TABLE `migration_quarantine_short_link_clicks` DROP PRIMARY KEY, MODIFY `id` bigint unsigned NOT NULL DEFAULT 0, ADD COLUMN `quarantine_reason` varchar(160) NOT NULL');
$target->exec('CREATE TABLE `migration_unresolved_references` (`table_name` varchar(80) NOT NULL, `record_id` bigint unsigned NOT NULL, `column_name` varchar(80) NOT NULL, `original_id` bigint unsigned NOT NULL, `reason` varchar(160) NOT NULL)');
$target->exec('CREATE TABLE `migration_user_map` (`source_user_id` bigint unsigned NOT NULL PRIMARY KEY, `target_user_id` bigint unsigned NOT NULL, `canonical_source_user_id` bigint unsigned DEFAULT NULL, `resolution` varchar(80) NOT NULL)');

// Content-driven canonical selection: user 27 owns more content than user 26;
// user 1 wins the inactive 1/16 tie by earlier ID.
$users = rows($source, 'SELECT * FROM `users` ORDER BY `id`');
$groups = [];
foreach ($users as $user) $groups[mb_strtolower($user['email'])][] = $user;
$contentScore = [];
foreach (['pages', 'short_links', 'qr_codes'] as $table) {
    foreach (rows($source, "SELECT user_id,COUNT(*) AS n FROM `{$table}` GROUP BY user_id") as $item) {
        $contentScore[(int)$item['user_id']] = ($contentScore[(int)$item['user_id']] ?? 0) + (int)$item['n'];
    }
}
$existingUsers = [];
foreach (rows($target, 'SELECT id,email,role FROM `users`') as $user) $existingUsers[mb_strtolower($user['email'])] = $user;
$map = ['users' => [], 'plans' => []];
foreach ($groups as $email => $members) {
    usort($members, fn($a, $b) => ($contentScore[(int)$b['id']] ?? 0) <=> ($contentScore[(int)$a['id']] ?? 0) ?: ((int)$a['id'] <=> (int)$b['id']));
    $canonical = $members[0];
    // Account identity and owned content are merged, but wallet balances are
    // never added: the chosen primary source account remains authoritative.
    if (isset($existingUsers[$email])) {
        $id = (int)$existingUsers[$email]['id'];
        if ($existingUsers[$email]['role'] === 'admin') $canonical['role'] = 'admin';
        updateRow($target, 'users', 'id', $id, $canonical);
    } else {
        unset($canonical['id']);
        insertRow($target, 'users', $canonical);
        $id = (int)$target->lastInsertId();
    }
    foreach ($members as $member) {
        $map['users'][(int)$member['id']] = $id;
        insertRow($target, 'migration_user_map', [
            'source_user_id' => (int)$member['id'],
            'target_user_id' => $id,
            'canonical_source_user_id' => (int)$members[0]['id'],
            'resolution' => (int)$member['id'] === (int)$members[0]['id'] ? 'canonical' : 'merged_duplicate_email',
        ]);
    }
}
// Source user 0 does not exist, but owns seven pages, four short links and two
// QR codes. Keep that content on an inaccessible, suspended placeholder account.
insertRow($target, 'users', [
    'uuid' => '00000000-0000-4000-8000-000000000000',
    'name' => 'Unclaimed legacy content',
    'email' => 'migration-orphan@invalid.example',
    'password_hash' => password_hash(bin2hex(random_bytes(32)), PASSWORD_DEFAULT),
    'credit_balance' => 0,
    'status' => 'suspended',
    'role' => 'user',
]);
$map['users'][0] = (int)$target->lastInsertId();
insertRow($target, 'migration_user_map', ['source_user_id' => 0, 'target_user_id' => $map['users'][0], 'canonical_source_user_id' => null, 'resolution' => 'suspended_orphan_content']);

foreach (rows($target, 'SELECT id,slug FROM `plans`') as $plan) $plansBySlug[$plan['slug']] = (int)$plan['id'];
foreach (rows($source, 'SELECT * FROM `plans` ORDER BY id') as $plan) {
    if (isset($plansBySlug[$plan['slug']])) {
        $map['plans'][(int)$plan['id']] = $plansBySlug[$plan['slug']];
    } else {
        $oldId = (int)$plan['id'];
        unset($plan['id']);
        insertRow($target, 'plans', $plan);
        $map['plans'][$oldId] = (int)$target->lastInsertId();
    }
}

// Assign every source ID before inserting child records, so all references can
// be translated even when the source dump does not declare foreign keys.
$idTables = [];
$zeroNext = [];
foreach ($sourceTables as $table) {
    if (in_array($table, ['users', 'plans'], true)) continue;
    if (!in_array('id', cols($source, $table), true)) continue;
    $idTables[] = $table;
    $max = (int)$target->query('SELECT COALESCE(MAX(id),0) FROM ' . q($table))->fetchColumn();
    $maxSource = (int)$source->query('SELECT COALESCE(MAX(id),0) FROM ' . q($table))->fetchColumn();
    $zeroNext[$table] = $max + $maxSource + 1;
    foreach (rows($source, 'SELECT id FROM ' . q($table) . ' ORDER BY id') as $row) {
        $oldId = (int)$row['id'];
        if ($oldId > 0) $map[$table][$oldId] = $max + $oldId;
    }
}

$foreign = [
    'user_id' => 'users', 'owner_user_id' => 'users', 'member_user_id' => 'users',
    'page_id' => 'pages', 'profile_page_id' => 'pages', 'short_link_id' => 'short_links',
    'qr_code_id' => 'qr_codes', 'link_id' => 'links', 'block_id' => 'page_blocks',
    'plan_id' => 'plans', 'subscription_id' => 'subscriptions',
];
$sourceCounts = [];
$genericQuarantine = [];
$target->exec('SET FOREIGN_KEY_CHECKS=0');
try {
    foreach ($sourceTables as $table) {
        if (in_array($table, ['users', 'plans'], true)) continue;
        $targetColumns = array_flip(cols($target, $table));
        $items = rows($source, 'SELECT * FROM ' . q($table));
        if (in_array($table, ['page_corporate_events', 'page_corporate_profiles'], true)) {
            usort($items, fn($a, $b) => strcmp((string)($b['updated_at'] ?? ''), (string)($a['updated_at'] ?? '')));
        }
        $sourceCounts[$table] = count($items);
        foreach ($items as $row) {
            if ($table === 'credit_transactions' && (int)$row['user_id'] === 0) {
                $row['quarantine_reason'] = 'Source user 0 does not exist; bonus must not enter wallet';
                insertRow($target, 'migration_quarantine_credit_transactions', $row);
                continue;
            }
            if ($table === 'short_link_clicks' && (int)$row['short_link_id'] === 0) {
                $row['quarantine_reason'] = 'Source short link ID 0 identifies multiple links';
                insertRow($target, 'migration_quarantine_short_link_clicks', $row);
                continue;
            }
            $rawRow = $row;
            $oldId = isset($row['id']) ? (int)$row['id'] : null;
            if ($oldId !== null) $row['id'] = $oldId > 0 ? $map[$table][$oldId] : $zeroNext[$table]++;
            $quarantineReason = null;
            foreach ($foreign as $column => $parent) {
                if (!isset($row[$column])) continue;
                $oldParent = (int)$row[$column];
                if ($table === 'qr_codes' && $column === 'short_link_id' && $oldParent === 0) {
                    insertRow($target, 'migration_unresolved_references', [
                        'table_name' => $table, 'record_id' => $row['id'],
                        'column_name' => $column, 'original_id' => 0,
                        'reason' => 'Source short link ID 0 identifies multiple links',
                    ]);
                    $row[$column] = null;
                    continue;
                }
                if (!isset($map[$parent][$oldParent])) {
                    $quarantineReason = "Unmapped {$column}={$oldParent}; parent {$parent} is missing or ambiguous";
                    break;
                }
                $row[$column] = $map[$parent][$oldParent];
            }
            if ($quarantineReason !== null) {
                quarantineRow($target, $sourceName, $table, $rawRow, $quarantineReason, $genericQuarantine);
                continue;
            }
            $row = array_intersect_key($row, $targetColumns);
            try {
                insertRow($target, $table, $row);
            } catch (PDOException $e) {
                if ((string)$e->getCode() !== '23000') throw $e;
                quarantineRow($target, $sourceName, $table, $rawRow, 'Duplicate or invalid target key', $genericQuarantine);
            }
        }
    }
} finally {
    $target->exec('SET FOREIGN_KEY_CHECKS=1');
}

foreach ($sourceCounts as $table => $count) {
    $quarantined = $table === 'credit_transactions' ? countRows($target, 'migration_quarantine_credit_transactions')
        : ($table === 'short_link_clicks' ? countRows($target, 'migration_quarantine_short_link_clicks') : 0);
    $quarantined += $genericQuarantine[$table] ?? 0;
    if (countRows($target, $table) + $quarantined < $count) throw new RuntimeException("Missing imported {$table} rows");
}
$target->exec('CREATE TABLE `migration_wallet_audit` AS SELECT u.id AS user_id, u.credit_balance AS stored_balance, COALESCE(SUM(c.amount),0) AS ledger_sum, u.credit_balance - COALESCE(SUM(c.amount),0) AS difference FROM `users` u LEFT JOIN `credit_transactions` c ON c.user_id=u.id GROUP BY u.id,u.credit_balance');
echo 'Merged source records into isolated target staging database. Tables: ' . count($sourceTables) . ";\n";
echo 'Users: ' . countRows($target, 'users') . '; pages: ' . countRows($target, 'pages') . '; QR codes: ' . countRows($target, 'qr_codes') . ";\n";
echo 'Wallet audit mismatches (including pre-existing source gaps): ' . (int)$target->query('SELECT COUNT(*) FROM `migration_wallet_audit` WHERE difference<>0')->fetchColumn() . ";\n";
