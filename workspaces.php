<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

$userId = filter_var($_SESSION['user_id'] ?? null, FILTER_VALIDATE_INT);
if (!$userId) {
    header('Location: signin.php', true, 303);
    exit;
}

$pdo = get_db_connection();
if (!$pdo) {
    http_response_code(503);
    exit('Workspace service is temporarily unavailable.');
}

$userName = trim((string)($_SESSION['user_name'] ?? 'User')) ?: 'User';
xinng_ensure_workspace_tables($pdo);
$activeOrganization = xinng_active_organization($pdo, (int)$userId, $userName);
$errors = [];
$notice = (string)($_SESSION['workspace_notice'] ?? '');
unset($_SESSION['workspace_notice']);

$safeReturn = static function (string $value): string {
    $allowed = ['dashboard.php', 'short_links.php', 'pages.php', 'events.php', 'ai_page.php', 'qr_codes.php', 'credits.php', 'insights.php', 'workspaces.php'];
    $file = basename(parse_url($value, PHP_URL_PATH) ?: 'dashboard.php');
    return in_array($file, $allowed, true) ? $file : 'dashboard.php';
};

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $action = (string)($_POST['action'] ?? '');
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Your session expired. Refresh the page and try again.';
    } else {
        try {
            if ($action === 'switch') {
                $organizationId = filter_var($_POST['organization_id'] ?? null, FILTER_VALIDATE_INT);
                if (!$organizationId || !xinng_organization_membership($pdo, (int)$organizationId, (int)$userId)) {
                    throw new RuntimeException('You do not have access to that workspace.');
                }
                $_SESSION['active_organization_id'] = (int)$organizationId;
                xinng_record_organization_activity($pdo, (int)$organizationId, (int)$userId, 'workspace.switched');
                header('Location: ' . $safeReturn((string)($_POST['return_to'] ?? 'workspaces.php')), true, 303);
                exit;
            }

            if ($action === 'create') {
                $organizationId = xinng_create_organization($pdo, (int)$userId, (string)($_POST['name'] ?? ''));
                $_SESSION['active_organization_id'] = $organizationId;
                xinng_record_organization_activity($pdo, $organizationId, (int)$userId, 'organization.created');
                $_SESSION['workspace_notice'] = 'Workspace created.';
                header('Location: workspaces.php', true, 303);
                exit;
            }

            if (!in_array($action, ['add_member', 'update_role', 'remove_member'], true)) {
                throw new InvalidArgumentException('Unknown workspace action.');
            }
            $organizationId = filter_var($_POST['organization_id'] ?? null, FILTER_VALIDATE_INT);
            if (!$organizationId) throw new RuntimeException('You do not have access to that workspace.');
            $pdo->beginTransaction();
            $actorStmt = $pdo->prepare('SELECT om.role FROM organization_members om JOIN organizations o ON o.id = om.organization_id JOIN users u ON u.id = om.user_id WHERE om.organization_id = ? AND om.user_id = ? AND om.status = "active" AND o.status = "active" AND o.deleted_at IS NULL AND u.status = "active" AND u.deleted_at IS NULL FOR UPDATE');
            $actorStmt->execute([(int)$organizationId, (int)$userId]);
            $actorRole = (string)($actorStmt->fetchColumn() ?: '');
            if (!xinng_role_can($actorRole, 'workspace.members.manage')) {
                throw new RuntimeException('Only workspace owners and administrators can manage members.');
            }
            $targetMembership = static function (int $targetUserId) use ($pdo, $organizationId): ?array {
                $stmt = $pdo->prepare('SELECT om.role, om.status FROM organization_members om JOIN users u ON u.id = om.user_id WHERE om.organization_id = ? AND om.user_id = ? AND u.status = "active" AND u.deleted_at IS NULL FOR UPDATE');
                $stmt->execute([(int)$organizationId, $targetUserId]);
                return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
            };

            if ($action === 'add_member') {
                $email = xinng_normalize_email((string)($_POST['email'] ?? ''));
                $role = (string)($_POST['role'] ?? 'viewer');
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) throw new InvalidArgumentException('Enter a valid member email address.');
                $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? AND status = "active" AND deleted_at IS NULL LIMIT 1');
                $stmt->execute([$email]);
                $memberUserId = (int)$stmt->fetchColumn();
                if (!$memberUserId) throw new RuntimeException('No active Xinng account uses that email yet.');
                $existingMember = $targetMembership($memberUserId);
                if (!xinng_can_manage_organization_member($actorRole, $existingMember['role'] ?? null, $role)) {
                    throw new RuntimeException('You cannot assign that role or change this member.');
                }
                $stmt = $pdo->prepare('INSERT INTO organization_members (organization_id, user_id, role, status, invited_by, joined_at, created_at, updated_at) VALUES (?, ?, ?, "active", ?, NOW(), NOW(), NOW()) ON DUPLICATE KEY UPDATE role = VALUES(role), status = "active", invited_by = VALUES(invited_by), joined_at = NOW(), updated_at = NOW()');
                $stmt->execute([(int)$organizationId, $memberUserId, $role, (int)$userId]);
                xinng_record_organization_activity($pdo, (int)$organizationId, (int)$userId, 'member.added', $memberUserId, ['role' => $role]);
                $pdo->commit();
                $_SESSION['workspace_notice'] = 'Member access saved.';
                header('Location: workspaces.php', true, 303);
                exit;
            }

            if ($action === 'update_role') {
                $memberUserId = filter_var($_POST['member_user_id'] ?? null, FILTER_VALIDATE_INT);
                $role = (string)($_POST['role'] ?? 'viewer');
                $member = $memberUserId ? $targetMembership((int)$memberUserId) : null;
                if (!$member || $member['status'] !== 'active' || !xinng_can_manage_organization_member($actorRole, (string)$member['role'], $role)) {
                    throw new RuntimeException('You cannot assign that role or change this member.');
                }
                $pdo->prepare('UPDATE organization_members SET role = ?, updated_at = NOW() WHERE organization_id = ? AND user_id = ?')->execute([$role, (int)$organizationId, (int)$memberUserId]);
                xinng_record_organization_activity($pdo, (int)$organizationId, (int)$userId, 'member.role_changed', (int)$memberUserId, ['role' => $role]);
                $pdo->commit();
                $_SESSION['workspace_notice'] = 'Member role updated.';
                header('Location: workspaces.php', true, 303);
                exit;
            }

            if ($action === 'remove_member') {
                $memberUserId = filter_var($_POST['member_user_id'] ?? null, FILTER_VALIDATE_INT);
                $member = $memberUserId ? $targetMembership((int)$memberUserId) : null;
                if (!$member || $member['status'] !== 'active' || !xinng_can_manage_organization_member($actorRole, (string)$member['role'])) {
                    throw new RuntimeException('You cannot remove this member.');
                }
                $pdo->prepare('UPDATE organization_members SET status = "removed", updated_at = NOW() WHERE organization_id = ? AND user_id = ?')->execute([(int)$organizationId, (int)$memberUserId]);
                xinng_record_organization_activity($pdo, (int)$organizationId, (int)$userId, 'member.removed', (int)$memberUserId);
                $pdo->commit();
                $_SESSION['workspace_notice'] = 'Member removed.';
                header('Location: workspaces.php', true, 303);
                exit;
            }
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $errors[] = $e instanceof InvalidArgumentException || $e instanceof RuntimeException ? $e->getMessage() : 'Unable to update the workspace right now.';
            if (!($e instanceof InvalidArgumentException) && !($e instanceof RuntimeException)) error_log('Workspace action failed: ' . $e->getMessage());
        }
    }
    $activeOrganization = xinng_active_organization($pdo, (int)$userId, $userName);
}

$organizations = xinng_user_organizations($pdo, (int)$userId);
$members = [];
$canManage = xinng_user_can_manage_organization($pdo, (int)$activeOrganization['id'], (int)$userId);
$canViewMembers = xinng_organization_user_can($pdo, (int)$activeOrganization['id'], (int)$userId, 'workspace.members.view');
if ($canViewMembers) {
    $stmt = $pdo->prepare('SELECT om.user_id, om.role, om.joined_at, u.name, u.email FROM organization_members om JOIN users u ON u.id = om.user_id WHERE om.organization_id = ? AND om.status = "active" ORDER BY FIELD(om.role, "owner", "administrator", "manager", "editor", "analyst", "finance_approver", "contributor", "viewer", "external"), u.name');
    $stmt->execute([(int)$activeOrganization['id']]);
    $members = $stmt->fetchAll();
}
$roles = xinng_assignable_organization_roles((string)$activeOrganization['role']);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Workspaces — <?= e($APP_NAME) ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/brand.css">
  <link rel="stylesheet" href="assets/css/workspaces.css?v=<?= filemtime(__DIR__ . '/assets/css/workspaces.css') ?>">
  <?php require __DIR__ . '/includes/google-analytics.php'; ?>
</head>
<body>
  <header class="workspace-topbar"><a class="xinng-brand" href="dashboard.php" aria-label="Xinng dashboard"><img class="xinng-brand__image" src="<?= e(xinng_brand_logo_url(xinng_public_base_url())) ?>" alt="Xinng" width="1736" height="906"></a><a href="dashboard.php">Back to dashboard</a></header>
  <main class="workspace-page">
    <section class="workspace-hero"><div><span>WORKSPACE SETTINGS</span><h1><?= e($activeOrganization['name']) ?></h1><p>Switch organizations and control who can access this workspace.</p></div><span class="role-badge"><?= e(ucfirst(str_replace('_', ' ', $activeOrganization['role']))) ?></span></section>
    <?php foreach ($errors as $error): ?><div class="message error" role="alert"><?= e($error) ?></div><?php endforeach; ?>
    <?php if ($notice): ?><div class="message notice" role="status"><?= e($notice) ?></div><?php endif; ?>
    <div class="workspace-grid">
      <section class="panel"><h2>Your workspaces</h2><p>Select an organization or create another workspace.</p>
        <div class="organization-list"><?php foreach ($organizations as $organization): ?><form method="post"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="switch"><input type="hidden" name="organization_id" value="<?= (int)$organization['id'] ?>"><button class="organization-card<?= (int)$organization['id'] === (int)$activeOrganization['id'] ? ' active' : '' ?>" type="submit"><span><strong><?= e($organization['name']) ?></strong><small><?= e(ucfirst(str_replace('_', ' ', $organization['role']))) ?></small></span><span aria-hidden="true">→</span></button></form><?php endforeach; ?></div>
        <form method="post" class="stacked-form"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="create"><label for="organization-name">New workspace name</label><input id="organization-name" name="name" maxlength="150" required><button type="submit">Create workspace</button></form>
      </section>
      <section class="panel members-panel"><div class="panel-heading"><div><h2>Members</h2><?php if ($canViewMembers): ?><p><?= count($members) ?> active member<?= count($members) === 1 ? '' : 's' ?></p><?php else: ?><p>Only workspace owners and administrators can view member details.</p><?php endif; ?></div></div>
        <?php if ($canViewMembers): ?>
        <?php if ($canManage): ?><form method="post" class="member-add"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="add_member"><input type="hidden" name="organization_id" value="<?= (int)$activeOrganization['id'] ?>"><label><span>Member email</span><input name="email" type="email" autocomplete="email" required></label><label><span>Role</span><select name="role"><?php foreach ($roles as $role): ?><option value="<?= e($role) ?>"><?= e(ucfirst(str_replace('_', ' ', $role))) ?></option><?php endforeach; ?></select></label><button type="submit">Add member</button></form><?php endif; ?>
        <div class="member-list"><?php foreach ($members as $member): ?><article class="member-row"><span class="member-avatar"><?= e(strtoupper(substr($member['name'], 0, 1))) ?></span><div><strong><?= e($member['name']) ?></strong><small><?= e($member['email']) ?></small></div><?php if ($canManage && xinng_can_manage_organization_member((string)$activeOrganization['role'], (string)$member['role'])): ?><form method="post" class="member-actions"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="organization_id" value="<?= (int)$activeOrganization['id'] ?>"><input type="hidden" name="member_user_id" value="<?= (int)$member['user_id'] ?>"><select name="role" aria-label="Role for <?= e($member['name']) ?>"><?php foreach ($roles as $role): ?><option value="<?= e($role) ?>"<?= $member['role'] === $role ? ' selected' : '' ?>><?= e(ucfirst(str_replace('_', ' ', $role))) ?></option><?php endforeach; ?></select><button name="action" value="update_role" type="submit">Save</button><button class="danger" name="action" value="remove_member" type="submit">Remove</button></form><?php else: ?><span class="role-badge"><?= e(ucfirst(str_replace('_', ' ', $member['role']))) ?></span><?php endif; ?></article><?php endforeach; ?></div>
        <?php endif; ?>
      </section>
    </div>
  </main>
</body>
</html>
