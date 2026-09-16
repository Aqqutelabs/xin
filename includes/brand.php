<?php
// Shared by account pages and standalone tools, without loading the database.
function xinng_brand_logo_url(string $baseUrl): string {
    static $version = null;
    if ($version === null) {
        $version = (string) filemtime(__DIR__ . '/../assets/images/logo.svg');
    }
    return rtrim($baseUrl, '/') . '/assets/images/logo.svg?v=' . $version;
}
