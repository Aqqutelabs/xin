<?php
// Shared by account pages and standalone tools, without loading the database.
function xinng_brand_logo_url(string $baseUrl = ''): string {
    static $version = null;
    if ($version === null) {
        $version = (string) filemtime(__DIR__ . '/../assets/images/logo.png');
    }
    // Workspace pages can use a relative asset path so localhost installations
    // do not try to load their chrome from the public short-link domain.
    $prefix = $baseUrl !== '' ? rtrim($baseUrl, '/') . '/' : '';
    return $prefix . 'assets/images/logo.png?v=' . $version;
}
