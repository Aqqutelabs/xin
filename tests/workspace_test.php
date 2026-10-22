<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config.php';
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

$pdo = get_db_connection();
if (!$pdo) {
    fwrite(STDERR, "Database unavailable.\n");
    exit(2);
}

xinng_ensure_workspace_tables($pdo);
$failures = [];
$expect = static function (bool $condition, string $message) use (&$failures): void {
    if (!$condition) $failures[] = $message;
};

$suffix = bin2hex(random_bytes(6));
$pdo->beginTransaction();
try {
    $insertUser = $pdo->prepare('INSERT INTO users (uuid, name, email, password_hash, status, created_at, updated_at) VALUES (UUID(), ?, ?, ?, "active", NOW(), NOW())');
    $insertUser->execute(['Workspace Owner', "owner-{$suffix}@example.invalid", password_hash('test-password1', PASSWORD_DEFAULT)]);
    $ownerId = (int)$pdo->lastInsertId();
    $insertUser->execute(['Workspace Member', "member-{$suffix}@example.invalid", password_hash('test-password1', PASSWORD_DEFAULT)]);
    $memberId = (int)$pdo->lastInsertId();

    $organizationId = xinng_create_organization($pdo, $ownerId, 'Test Organization ' . $suffix);
    $expect($organizationId > 0, 'Organization was not created.');
    $expect(xinng_user_can_manage_organization($pdo, $organizationId, $ownerId), 'Owner must manage the organization.');
    $expect(xinng_organization_membership($pdo, $organizationId, $memberId) === null, 'Non-members must not gain organization access.');

    $pdo->prepare('INSERT INTO organization_members (organization_id, user_id, role, status, invited_by, joined_at, created_at, updated_at) VALUES (?, ?, "viewer", "active", ?, NOW(), NOW(), NOW())')->execute([$organizationId, $memberId, $ownerId]);
    $member = xinng_organization_membership($pdo, $organizationId, $memberId);
    $expect(($member['role'] ?? null) === 'viewer', 'Member role was not retained.');
    $expect(!xinng_user_can_manage_organization($pdo, $organizationId, $memberId), 'Viewer must not manage memberships.');
    $expect(xinng_organization_user_can($pdo, $organizationId, $memberId, 'workspace.view'), 'Viewer should read their workspace.');
    $expect(!xinng_organization_user_can($pdo, $organizationId, $memberId, 'finance.approve'), 'Viewer must not approve finance actions.');
    $expect(count(xinng_user_organizations($pdo, $memberId)) === 1, 'Member workspace listing is incorrect.');

    $pdo->prepare('UPDATE organization_members SET role = "administrator" WHERE organization_id = ? AND user_id = ?')->execute([$organizationId, $memberId]);
    $expect(xinng_organization_user_can($pdo, $organizationId, $memberId, 'workspace.members.manage'), 'Role update should change permissions immediately.');
    $expect(!xinng_organization_user_can($pdo, $organizationId, $memberId, 'finance.approve'), 'Administrator must not receive finance approval.');
    $pdo->prepare('UPDATE organization_members SET status = "removed" WHERE organization_id = ? AND user_id = ?')->execute([$organizationId, $memberId]);
    $expect(!xinng_organization_user_can($pdo, $organizationId, $memberId, 'workspace.view'), 'Removed members must lose access immediately.');
    $pdo->prepare('UPDATE organization_members SET role = "viewer", status = "active" WHERE organization_id = ? AND user_id = ?')->execute([$organizationId, $memberId]);
    $pdo->prepare('UPDATE organizations SET status = "suspended" WHERE id = ?')->execute([$organizationId]);
    $expect(!xinng_organization_user_can($pdo, $organizationId, $ownerId, 'workspace.view'), 'Suspended organizations must not grant access.');
    $pdo->prepare('UPDATE organizations SET status = "active" WHERE id = ?')->execute([$organizationId]);

    $_SESSION['active_organization_id'] = $organizationId;
    $active = xinng_active_organization($pdo, $memberId, 'Workspace Member');
    $expect((int)$active['id'] === $organizationId, 'Valid workspace selection was not retained.');
} finally {
    if ($pdo->inTransaction()) $pdo->rollBack();
    unset($_SESSION['active_organization_id']);
}

if ($failures) {
    foreach ($failures as $failure) fwrite(STDERR, "FAIL: {$failure}\n");
    exit(1);
}

fwrite(STDOUT, "Workspace isolation checks passed.\n");
