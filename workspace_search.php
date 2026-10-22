<?php
declare(strict_types=1);
require_once __DIR__ . '/config.php';
session_start();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
$userId = filter_var($_SESSION['user_id'] ?? null, FILTER_VALIDATE_INT);
$query = trim((string)($_GET['q'] ?? ''));
if (!$userId) { http_response_code(401); echo json_encode(['results' => []]); exit; }
$pdo = get_db_connection();
if (!$pdo) { http_response_code(503); echo json_encode(['results' => []]); exit; }
echo json_encode(['results' => xinng_workspace_search($pdo, (int)$userId, $query)], JSON_UNESCAPED_SLASHES);
