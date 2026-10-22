<?php
declare(strict_types=1);

$utilityEscape = static fn ($value): string => htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
$utilityUserName = trim((string)($_SESSION['user_name'] ?? 'User')) ?: 'User';
$utilityWords = preg_split('/\s+/u', $utilityUserName, -1, PREG_SPLIT_NO_EMPTY) ?: [];
$utilityInitials = strtoupper(implode('', array_map(static fn ($word): string => function_exists('mb_substr') ? mb_substr($word, 0, 1) : substr($word, 0, 1), array_slice($utilityWords, 0, 2))));
$utilityOrganizations = [];
$utilityActiveOrganization = null;
$utilityNotifications = [];
$utilityUnread = 0;
$utilityRole = '';
$utilityUserId = filter_var($_SESSION['user_id'] ?? null, FILTER_VALIDATE_INT);
if ($utilityUserId) {
    try {
        $utilityPdo = isset($pdo) && $pdo instanceof PDO ? $pdo : get_db_connection();
        if ($utilityPdo) {
            $utilityActiveOrganization = xinng_active_organization($utilityPdo, (int)$utilityUserId, $utilityUserName);
            $utilityOrganizations = xinng_user_organizations($utilityPdo, (int)$utilityUserId);
            $utilityRole = (string)($utilityActiveOrganization['role'] ?? '');
            $utilityNotifications = xinng_get_notifications($utilityPdo, (int)$utilityUserId, 6);
            foreach ($utilityNotifications as $utilityNotification) if (empty($utilityNotification['read_at'])) $utilityUnread++;
        }
    } catch (Throwable $utilityError) {
        error_log('Workspace header unavailable: ' . $utilityError->getMessage());
    }
}
?>
<link rel="stylesheet" href="assets/css/workspace-floating.css?v=20261021a">
<div class="workspace-header-utilities" data-workspace-utilities>
  <button class="icon-btn workspace-mobile-nav-trigger" type="button" data-mobile-nav-toggle aria-expanded="false" aria-label="Open navigation"><i class="fa-solid fa-bars" aria-hidden="true"></i></button>
  <button class="icon-btn" type="button" data-search-open aria-label="Search workspace"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i></button>
  <div class="workspace-dropdown-wrap">
    <button class="icon-btn workspace-utility-trigger" type="button" data-utility-toggle="notifications" aria-expanded="false" aria-controls="workspace-notifications" aria-label="Notifications"><i class="fa-solid fa-bell" aria-hidden="true"></i><?php if ($utilityUnread): ?><span class="utility-count"><?= (int)$utilityUnread ?></span><?php endif; ?></button>
    <section class="workspace-dropdown notification-menu" id="workspace-notifications" hidden><header><div><strong>Notifications</strong><span><?= $utilityUnread ? $utilityUnread . ' unread' : 'You are up to date' ?></span></div><button type="button" class="utility-text-button" data-mark-all>Mark all read</button></header><div class="notification-menu-list"><?php if (!$utilityNotifications): ?><p class="utility-empty">No notifications yet.</p><?php else: foreach ($utilityNotifications as $utilityNotification): ?><article class="utility-notification<?= empty($utilityNotification['read_at']) ? ' unread' : '' ?>"><span class="utility-notification-icon"><i class="fa-solid fa-<?= ($utilityNotification['type'] ?? '') === 'security' ? 'shield-halved' : 'circle-info' ?>" aria-hidden="true"></i></span><div><strong><?= $utilityEscape($utilityNotification['title'] ?? 'Update') ?></strong><p><?= $utilityEscape($utilityNotification['message'] ?? '') ?></p></div></article><?php endforeach; endif; ?></div></section>
  </div>
  <button class="icon-btn workspace-utility-trigger" type="button" data-utility-pane="settings" aria-label="Quick settings" aria-expanded="false"><i class="fa-solid fa-gear" aria-hidden="true"></i></button>
  <div class="workspace-dropdown-wrap">
    <button class="workspace-user-trigger" type="button" data-utility-toggle="profile" aria-expanded="false" aria-controls="workspace-profile-menu"><span class="avatar"><?= $utilityEscape($utilityInitials ?: 'U') ?></span><span class="workspace-user-trigger-name"><?= $utilityEscape($utilityUserName) ?></span><i class="fa-solid fa-chevron-down" aria-hidden="true"></i></button>
    <section class="workspace-dropdown profile-menu" id="workspace-profile-menu" hidden><header class="profile-menu-heading"><span class="avatar"><?= $utilityEscape($utilityInitials ?: 'U') ?></span><div><strong><?= $utilityEscape($utilityUserName) ?></strong><span><?= $utilityEscape($utilityActiveOrganization['name'] ?? 'Personal workspace') ?></span></div></header><?php if ($utilityActiveOrganization): ?><form class="header-workspace-switcher" method="post" action="workspaces.php"><input type="hidden" name="csrf_token" value="<?= $utilityEscape(csrf_token()) ?>"><input type="hidden" name="action" value="switch"><input type="hidden" name="return_to" value="<?= $utilityEscape(basename((string)($_SERVER['PHP_SELF'] ?? 'dashboard.php'))) ?>"><label for="header-workspace-select">Active workspace</label><select id="header-workspace-select" name="organization_id" onchange="this.form.submit()"><?php foreach ($utilityOrganizations as $utilityOrganization): ?><option value="<?= (int)$utilityOrganization['id'] ?>"<?= (int)$utilityOrganization['id'] === (int)$utilityActiveOrganization['id'] ? ' selected' : '' ?>><?= $utilityEscape($utilityOrganization['name']) ?></option><?php endforeach; ?></select><?php if (xinng_role_can($utilityRole, 'workspace.members.manage') || xinng_role_can($utilityRole, 'workspace.settings.manage')): ?><a href="workspaces.php">Manage workspaces</a><?php endif; ?></form><?php endif; ?><div class="profile-menu-actions"><button type="button" data-utility-pane="profile"><i class="fa-regular fa-user" aria-hidden="true"></i>Profile</button><a href="logout.php"><i class="fa-solid fa-arrow-right-from-bracket" aria-hidden="true"></i>Log out</a></div></section>
  </div>
</div>
<dialog class="workspace-search-dialog" data-workspace-search aria-labelledby="workspace-search-title"><form method="dialog" class="workspace-search-shell"><header><div><span class="utility-pane-kicker">WORKSPACE SEARCH</span><h2 id="workspace-search-title">Find anything</h2></div><button class="icon-btn" value="cancel" aria-label="Close search"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button></header><label class="workspace-search-field"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i><input data-search-input type="search" autocomplete="off" placeholder="Search pages, short links and QR codes"></label><div class="workspace-search-results" data-search-results aria-live="polite"><p>Start typing to search your workspace.</p></div></form></dialog>
<aside class="workspace-feature-pane" data-feature-pane hidden aria-label="Workspace tools"><header><div><span class="utility-pane-kicker">WORKSPACE TOOLS</span><h2 data-feature-title>Quick settings</h2></div><button class="icon-btn" type="button" data-feature-close aria-label="Close panel"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button></header><div class="workspace-feature-pane-body" data-feature-body><p>Workspace controls are available from the profile menu.</p></div></aside>
<script src="assets/js/workspace-header.js?v=20261021a"></script>
<?php unset($utilityEscape, $utilityUserName, $utilityWords, $utilityInitials, $utilityOrganizations, $utilityActiveOrganization, $utilityNotifications, $utilityUnread, $utilityRole, $utilityUserId, $utilityPdo, $utilityNotification, $utilityOrganization, $utilityError); ?>
