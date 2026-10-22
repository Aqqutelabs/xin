<?php
declare(strict_types=1);

/**
 * The dashboard has one navigation model for every breakpoint.  Links whose
 * records are still user-owned deliberately remain available to that user;
 * organization permissions apply only to organization-owned features.
 */
function xinng_workspace_navigation_items(?string $organizationRole = null): array {
    $allows = static function (?string $permission = null, bool $userOwned = false) use ($organizationRole): bool {
        if ($userOwned || $permission === null || $organizationRole === null) return true;
        return xinng_role_can($organizationRole, $permission);
    };

    return [
        'overview' => ['section' => 'Dashboard', 'href' => 'dashboard.php', 'icon' => 'fa-solid fa-house', 'label' => 'Den', 'visible' => true],
        'links' => ['section' => 'Channels', 'href' => 'short_links.php', 'icon' => 'fa-solid fa-link', 'label' => 'Short Links', 'visible' => $allows(null, true)],
        'pages' => ['section' => 'Channels', 'href' => 'pages.php', 'icon' => 'fa-regular fa-file-lines', 'label' => 'Pages', 'visible' => $allows('presence.view', true)],
        'events' => ['section' => 'Pages', 'href' => 'events.php', 'icon' => 'fa-solid fa-calendar-days', 'label' => 'Events', 'visible' => $allows('events.view', true), 'parent' => 'pages'],
        'ai' => ['section' => 'Pages', 'href' => 'ai_page.php', 'icon' => 'fa-solid fa-wand-magic-sparkles', 'label' => 'AI Page Builder', 'visible' => $allows('presence.edit', true), 'parent' => 'pages'],
        'qr' => ['section' => 'Channels', 'href' => 'qr_codes.php', 'icon' => 'fa-solid fa-qrcode', 'label' => 'QR Codes', 'visible' => $allows(null, true)],
        'insights' => ['section' => 'Tools', 'href' => 'insights.php', 'icon' => 'fa-solid fa-chart-line', 'label' => 'Insights', 'visible' => $allows('analytics.view', true)],
        'credits' => ['section' => 'Finance', 'href' => 'credits.php', 'icon' => 'fa-solid fa-wallet', 'label' => 'Wallet', 'visible' => $allows('wallet.view', true)],
        'plans' => ['section' => 'Finance', 'href' => 'wallet/plans', 'icon' => 'fa-solid fa-layer-group', 'label' => 'Plans', 'visible' => $allows('wallet.view', true), 'parent' => 'credits'],
    ];
}

function xinng_workspace_navigation_role(?PDO $pdo, ?int $userId, string $userName = 'My'): ?string {
    if (!$pdo || !$userId) return null;
    try {
        $organization = xinng_active_organization($pdo, $userId, $userName);
        return isset($organization['role']) ? (string)$organization['role'] : null;
    } catch (Throwable $error) {
        error_log('Workspace navigation role unavailable: ' . $error->getMessage());
        return null;
    }
}
