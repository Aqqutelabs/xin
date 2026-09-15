<?php
require 'config.php';

header('Content-Type: text/plain; charset=UTF-8');

if (!extension_loaded('pdo_mysql')) {
    echo "ERROR: PHP extension pdo_mysql is not enabled.\n";
    exit;
}

$missing = [];
foreach (['DB_HOST', 'DB_NAME', 'DB_USER'] as $name) {
    if (constant($name) === '') $missing[] = $name;
}
if ($missing) {
    echo 'ERROR: Missing database settings: ' . implode(', ', $missing) . "\n";
    exit;
}

$pdo = get_db_connection();
if (!$pdo) {
    echo "ERROR: Database connection failed.\n";
    echo 'Host: ' . DB_HOST . "\n";
    echo 'Database: ' . DB_NAME . "\n";
    echo 'User: ' . DB_USER . "\n";
    echo 'Details: ' . ($GLOBALS['xinng_db_error'] ?? 'Check logs/app.log.') . "\n";
    exit;
}
try {
    $stmt = $pdo->query('SELECT COUNT(*) AS c FROM pages');
    $row = $stmt->fetch();
    echo 'pages_count=' . ($row['c'] ?? 0) . "\n";
} catch (Throwable $e) {
    echo 'pages_error=' . $e->getMessage() . "\n";
}
