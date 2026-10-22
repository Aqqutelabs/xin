<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/rbac.php';
require_once dirname(__DIR__) . '/includes/workspace-navigation.php';

$items = xinng_workspace_navigation_items('viewer');
foreach (['overview', 'links', 'pages', 'events', 'ai', 'qr', 'insights', 'credits', 'plans'] as $key) {
    if (empty($items[$key]['visible'])) {
        fwrite(STDERR, "FAIL: {$key} should remain available while its records are user-owned.\n");
        exit(1);
    }
}
if (($items['events']['parent'] ?? null) !== 'pages') {
    fwrite(STDERR, "FAIL: Event navigation must remain grouped below Pages.\n");
    exit(1);
}
if (($items['plans']['href'] ?? null) !== 'wallet/plans' || ($items['plans']['parent'] ?? null) !== 'credits') {
    fwrite(STDERR, "FAIL: Plan navigation must remain under the wallet.\n");
    exit(1);
}
fwrite(STDOUT, "Workspace navigation policy checks passed.\n");
