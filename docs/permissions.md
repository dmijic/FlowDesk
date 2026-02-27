# FlowDesk Permissions Matrix

| Capability | Admin | ProcessOwner | Approver | Requester |
|---|---:|---:|---:|---:|
| `manage_users` | Yes | No | No | No |
| `manage_workflows` | Yes | Yes | No | No |
| `approve_requests` | Yes | Yes | Yes | No |
| `create_requests` | Yes | No | No | Yes |
| `view_reports` | Yes | Yes | No | No |

## Notes
- Roles are stored in `roles` and linked to users via `role_user`.
- Permissions are linked to roles via `permission_role`.
- API checks are enforced with Laravel Gates/Policies.
- Frontend route guard checks permission slugs and redirects with a toast if denied.
