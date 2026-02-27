# ERD (textual)

## Core
- `users` belongs to optional `departments`.
- `users` many-to-many `roles` (`role_user`).
- `roles` many-to-many `permissions` (`permission_role`).

## Request lifecycle
- `request_types` has many `requests`.
- `departments` has many `requests`.
- `users` (creator) has many `requests` via `requests.created_by`.
- `requests` has many `request_attachments`.
- `requests` has many `approval_tasks`.
- `requests` has many `audit_logs` (by `entity_type/entity_id` and metadata `request_id`).

## Workflow
- `workflow_definitions` belongs to `request_types`.
- Active workflow per request type is resolved by `request_type_id + is_active=true`.

## Auth/infra
- `personal_access_tokens` polymorphic to `users` (Sanctum).
- `notifications` polymorphic to notifiable model (`users`).
- `jobs`, `failed_jobs`, `job_batches` support database queue.
- `cache` and `sessions` use database stores.
