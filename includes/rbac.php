<?php
declare(strict_types=1);

// Organization permissions apply only to organization-owned operations. Existing
// Pages, links, QR codes and wallet records remain user-owned until migrated.
function xinng_organization_role_permissions(): array {
    return [
        'owner' => ['*'],
        'administrator' => ['workspace.view', 'workspace.members.view', 'workspace.members.manage', 'workspace.settings.manage', 'presence.view', 'presence.edit', 'presence.publish', 'analytics.view', 'events.view', 'events.manage', 'events.checkin', 'wallet.view'],
        'manager' => ['workspace.view', 'presence.view', 'presence.edit', 'presence.publish', 'analytics.view', 'events.view', 'events.manage', 'events.checkin'],
        'editor' => ['workspace.view', 'presence.view', 'presence.edit', 'presence.publish'],
        'analyst' => ['workspace.view', 'presence.view', 'analytics.view'],
        'finance_approver' => ['workspace.view', 'wallet.view', 'finance.approve'],
        'contributor' => ['workspace.view', 'presence.view', 'presence.edit'],
        'viewer' => ['workspace.view', 'presence.view'],
        'external' => ['workspace.view'],
    ];
}

function xinng_organization_permissions(): array {
    $known = [];
    foreach (xinng_organization_role_permissions() as $permissions) {
        foreach ($permissions as $permission) {
            if ($permission !== '*') $known[$permission] = true;
        }
    }
    return array_keys($known);
}

// Specialist roles are resource-scoped definitions, not organization membership
// roles. They cannot be assigned until the corresponding resource bindings exist.
function xinng_specialist_role_permissions(): array {
    return [
        'event_manager' => ['scope' => 'event', 'permissions' => ['events.view', 'events.manage', 'events.checkin']],
        'checkin_staff' => ['scope' => 'event', 'permissions' => ['events.view', 'events.checkin']],
        'publisher' => ['scope' => 'publisher', 'permissions' => ['publisher.view', 'publisher.submit']],
        'sponsor' => ['scope' => 'event', 'permissions' => ['events.view', 'sponsor.view']],
        'vendor' => ['scope' => 'event', 'permissions' => ['events.view', 'vendor.view']],
        'institutional_funder' => ['scope' => 'funding', 'permissions' => ['funding.view', 'funding.review']],
    ];
}

function xinng_role_can(string $role, string $permission): bool {
    if (!in_array($permission, xinng_organization_permissions(), true)) return false;
    $permissions = xinng_organization_role_permissions()[$role] ?? [];
    return in_array('*', $permissions, true) || in_array($permission, $permissions, true);
}

function xinng_can_manage_organization_member(string $actorRole, ?string $currentRole, ?string $newRole = null): bool {
    if (!xinng_role_can($actorRole, 'workspace.members.manage')) return false;
    if ($currentRole !== null && !array_key_exists($currentRole, xinng_organization_role_permissions())) return false;
    if ($currentRole === 'owner' || ($actorRole !== 'owner' && $currentRole === 'administrator')) return false;
    if ($newRole !== null) {
        if ($newRole === 'owner' || !array_key_exists($newRole, xinng_organization_role_permissions())) return false;
        if ($actorRole !== 'owner' && $newRole === 'administrator') return false;
    }
    return true;
}

function xinng_assignable_organization_roles(string $actorRole): array {
    return array_values(array_filter(array_keys(xinng_organization_role_permissions()),
        static fn (string $role): bool => xinng_can_manage_organization_member($actorRole, null, $role)));
}
