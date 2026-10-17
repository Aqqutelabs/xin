<?php
declare(strict_types=1);
require_once __DIR__ . '/../../config.php';
session_start();
$userId = filter_var($_SESSION['user_id'] ?? null, FILTER_VALIDATE_INT);
if ($userId === false || $userId <= 0) {
    header('Location: ../../signin.php');
    exit;
}
header('Location: connect.php?user_id=' . (int)$userId, true, 302);
exit;
