# XNG-003: workspace RBAC

Organization permissions are checked server-side against a current, active membership in an active organization. They are not inferred from the selected workspace ID or the platform administrator flag. Unknown roles and permissions are denied. Only owners and administrators can view the member directory. The `owner` role cannot be granted, demoted, or removed through member management. Administrators can manage lower roles but cannot grant or change another administrator. Membership changes retain the existing organization activity audit records.

| Role | Workspace members | Presence | Analytics | Events | Finance approval |
| --- | --- | --- | --- | --- | --- |
| Owner | Manage | Edit/publish | View | Manage/check in | Yes |
| Administrator | Manage lower roles | Edit/publish | View | Manage/check in | No |
| Manager | No | Edit/publish | View | Manage/check in | No |
| Editor | No | Edit/publish | No | No | No |
| Analyst | No | Read | View | No | No |
| Finance approver | No | No | No | No | Yes |
| Contributor | No | Edit, not publish | No | No | No |
| Viewer | No | Read | No | No | No |
| External | No | No | No | No | No |

This policy defines future organization-owned resource permissions; it does not change ownership of today's user-owned Pages, short links, QR codes, or wallet records. Their existing per-user server checks remain authoritative until those resources are explicitly migrated. Platform admin access is separate from workspace roles.

Event manager, check-in staff, publisher, sponsor, vendor, and institutional funder permissions are defined in `includes/rbac.php` as resource-scoped roles. They are deliberately not assignable as organization membership roles until their resource binding and enforcement are implemented in the corresponding product trains.
