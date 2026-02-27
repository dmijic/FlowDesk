# FlowDesk

FlowDesk is a monorepo application for internal service requests (`IT access`, `procurement`, `travel orders`) with multi-level approvals, audit trail, notifications, and reporting.

## Stack
- Backend: Laravel 11 API, PHP 8.3, Sanctum
- Frontend: Vue 3 + Vite + Pinia + Vue Router + Tailwind
- DB: MySQL 8
- Queue: database + worker container
- Mail: Mailhog
- Reverse proxy: Nginx

## Monorepo Structure

```text
/
  backend/
  frontend/
  docker/
  docs/
  scripts/
  docker-compose.yml
  Makefile
  .env.example
```

## Quick Start

```bash
make setup
```

Alternative:

```bash
./scripts/setup.sh
```

Setup performs:
1. Starts Docker services (`nginx`, `php`, `mysql`, `node`, `mailhog`, `queue`, `scheduler`)
2. Runs `composer install`
3. Runs `php artisan key:generate`
4. Runs `php artisan migrate:fresh --seed`
5. Runs frontend `npm install`

## URLs
- Frontend via nginx: `http://localhost`
- Frontend via Vite dev server: `http://localhost:5173`
- Backend API: `http://localhost/api`
- Mailhog UI: `http://localhost:8025`

## Demo Users (Seeded)
All users share the same password: `Password123!`

- Admin: `admin@flowdesk.local`
- Process Owner: `owner1@flowdesk.local`, `owner2@flowdesk.local`
- Approver: `approver1@flowdesk.local` ... `approver4@flowdesk.local`
- Requester: `requester1@flowdesk.local` ... `requester10@flowdesk.local`

## Seeded Data
- 1 admin
- 2 process owners
- 4 approvers
- 10 requesters
- 3 departments
- 5 request types
- 3 workflow definitions (`any/all`, `parallel/non-parallel`)
- 25 demo requests + approval tasks + audit logs + in-app notifications

## API Route Map
- `GET /sanctum/csrf-cookie`
- `POST /auth/login`
- `POST /auth/logout`
- `POST /auth/forgot-password`
- `GET /me`
- `CRUD /api/users`
- `CRUD /api/departments`
- `CRUD /api/request-types`
- `CRUD /api/workflows`
- `GET/POST /api/requests`
- `GET /api/requests/{id}`
- `POST /api/requests/{id}/submit`
- `POST /api/requests/{id}/attachments`
- `GET /api/attachments/{id}/download`
- `GET /api/approvals/inbox`
- `POST /api/approvals/tasks/{id}/approve`
- `POST /api/approvals/tasks/{id}/reject`
- `GET /api/reports/summary`
- `GET /api/reports/requests.csv`
- `GET /api/audit-logs`

## Tests
Run feature tests with:

```bash
make test
```

Covered scenarios:
- submit request -> creates first-step approval tasks
- `any` rule -> one approval completes the step and skips remaining tasks
- `all` rule -> all approvals are required
- reject immediately closes request and skips remaining tasks
- permission denial (requester cannot view other users' requests, approver cannot `manage_users`)

## Sanctum SPA Login Checklist
1. Open frontend on `http://localhost:5173`.
2. Login flow must be:
   - `GET /sanctum/csrf-cookie` (200/204)
   - `POST /auth/login` (200)
3. In browser cookies for `http://localhost`, verify:
   - `XSRF-TOKEN`
   - `flowdesk-session` (or `{app}_session`)
4. After login, `GET /me` must return user JSON.
5. Logout (`POST /auth/logout`) must succeed and session must no longer be valid.

## Debug / Cache Clear
If you change CORS/session/Sanctum config, clear caches:

```bash
docker compose exec php php artisan optimize:clear
```

If you see auth errors in the browser:
- `404 /auth/login` or `404 /me`: verify nginx forwards `/auth`, `/me`, `/sanctum` to Laravel (`docker/nginx/default.conf`) and restart nginx.
- `502 /login`: Vite service is not ready; check `docker compose ps` and `docker compose logs node`.

## Workflow Engine (Summary)
- `WorkflowEngine`:
  - submits requests
  - creates tasks for first/next workflow step
  - marks requests as approved on final step
- `ApprovalService`:
  - approve/reject logic
  - `any/all` rule handling
  - task skipping and request closing
- `AuditLogger`:
  - stores actor/action/entity + before/after + metadata

## Adding a New Workflow
1. Login as `admin` or `process owner`.
2. Open `Admin -> Workflows`.
3. Choose request type.
4. Paste/edit `definition_json`.
5. Mark as `Active` if it should be current version.
6. Save.

For JSON format, see [docs/workflow-json.md](docs/workflow-json.md).

## Additional Documentation
- [docs/permissions.md](docs/permissions.md)
- [docs/workflow-json.md](docs/workflow-json.md)
- [docs/erd.md](docs/erd.md)
