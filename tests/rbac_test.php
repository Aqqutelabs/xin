<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/rbac.php';

$failures = [];
$expect = static function (bool $condition, string $message) use (&$failures): void {
    if (!$condition) $failures[] = $message;
};

$expect(xinng_role_can('owner', 'workspace.members.manage'), 'Owner should manage members.');
$expect(xinng_role_can('owner', 'finance.approve'), 'Owner should approve finance actions.');
$expect(!xinng_role_can('owner', 'not.a.permission'), 'Unknown permissions must be denied even to owners.');
$expect(xinng_role_can('administrator', 'workspace.members.manage'), 'Administrator should manage members.');
$expect(xinng_role_can('administrator', 'workspace.members.view'), 'Administrator should view members.');
$expect(!xinng_role_can('viewer', 'workspace.members.view'), 'Viewer must not see member details.');
$expect(!xinng_role_can('administrator', 'finance.approve'), 'Administrator must not approve finance actions.');
$expect(xinng_role_can('finance_approver', 'finance.approve'), 'Finance approver should approve finance actions.');
$expect(!xinng_role_can('finance_approver', 'workspace.members.manage'), 'Finance approver must not manage members.');
$expect(xinng_role_can('analyst', 'analytics.view'), 'Analyst should view analytics.');
$expect(!xinng_role_can('viewer', 'analytics.view'), 'Viewer must not inherit analyst access.');
$expect(!xinng_role_can('checkin_staff', 'events.checkin'), 'Specialist roles must not work as organization-wide roles.');
$expect(!xinng_role_can('unknown', 'workspace.view'), 'Unknown roles must be denied.');
$expect(!xinng_can_manage_organization_member('administrator', 'owner', 'viewer'), 'Administrator cannot demote an owner.');
$expect(!xinng_can_manage_organization_member('owner', 'owner', 'viewer'), 'Owner cannot be demoted through member management.');
$expect(!xinng_can_manage_organization_member('administrator', 'administrator', 'viewer'), 'Administrator cannot manage another administrator.');
$expect(!xinng_can_manage_organization_member('owner', 'unknown', 'viewer'), 'Unknown current roles must be denied.');
$expect(!xinng_can_manage_organization_member('administrator', null, 'administrator'), 'Administrator cannot grant administrator access.');
$expect(xinng_can_manage_organization_member('owner', 'administrator', 'manager'), 'Owner can change an administrator.');
$expect(xinng_can_manage_organization_member('administrator', 'viewer', 'editor'), 'Administrator can change lower roles.');
$expect(!xinng_can_manage_organization_member('viewer', 'editor'), 'Viewer cannot manage members.');
$expect(!in_array('event_manager', xinng_assignable_organization_roles('owner'), true), 'Specialist role should not be assignable yet.');
$expect(!in_array('administrator', xinng_assignable_organization_roles('administrator'), true), 'Administrator role should not be assignable by administrators.');

if ($failures) {
    foreach ($failures as $failure) fwrite(STDERR, "FAIL: {$failure}\n");
    exit(1);
}

fwrite(STDOUT, "RBAC policy checks passed.\n");
