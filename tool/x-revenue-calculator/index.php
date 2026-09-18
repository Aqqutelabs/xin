<?php
// Keep the originally requested tool location as an entry point.
$script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
$base = preg_replace('~/tool/x-revenue-calculator/(?:index\.php)?$~', '', $script);
header('Location: ' . $base . '/tools/creator-calculator/x/', true, 302);
exit;
