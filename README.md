# FlowDesk

FlowDesk is a monorepo web application for internal service requests such as IT access, procurement, and travel approvals. It includes multi-step workflows, audit logging, notifications, reporting, and a Docker-first local development setup.

## Features
- Laravel 11 REST API with Sanctum session authentication
- Vue 3 SPA with Pinia, Vue Router, and Tailwind CSS
- Multi-step approval workflows with `any` / `all` rules
- Audit trail and timeline per request
- Database notifications and email notifications
- CSV reporting and admin dashboards
- Docker-based local development with MySQL, queue worker, scheduler, and Mailhog

## Repository Layout
```text
/
  backend/               Laravel 11 API
  frontend/              Vue 3 SPA
  docker/                Nginx configs, Dockerfiles, entrypoints
  docs/                  RBAC, workflow JSON, ERD, screenshots placeholder
  scripts/               Setup, test, lint, build, production helper scripts
  docker-compose.yml     Local development stack
  docker-compose.prod.yml Production Docker stack
  INSTALLATION.md        VPS deployment guide
  README.md
  LICENSE
```

## Local Development
### Prerequisites
- Docker Engine + Docker Compose plugin
- GNU Make

### Setup
```bash
make setup
```

This will:
1. Start the local Docker services
2. Install backend dependencies
3. Generate the Laravel app key
4. Run migrations and seed demo data
5. Install frontend dependencies

### Development URLs
- Frontend via Vite: `http://localhost:5173`
- Frontend via nginx: `http://localhost:8080`
- Backend API: `http://localhost:8080/api`
- Health endpoint: `http://localhost:8080/health`
- Mailhog: `http://localhost:8025`

### Common Commands
```bash
make dev-up
make dev-down
make test
make lint
make build
```

## Environment Configuration
Only example env files are tracked in Git:
- `.env.example`
- `backend/.env.example`
- `frontend/.env.example`

Key frontend env:
- `VITE_API_BASE_URL`
  - Dev example: `http://localhost:8080`
  - Production same-origin deployment: empty string
  - Production separate API origin: `https://api.example.com`

Key backend env:
- `APP_URL`
- `FRONTEND_URL`
- `APP_VERSION`
- `TRUSTED_PROXIES`
- `SESSION_SECURE_COOKIE`
- `CORS_ALLOWED_ORIGINS`
- `SANCTUM_STATEFUL_DOMAINS`

## Demo Credentials
All demo users use the password `Password123!`.

- Admin: `admin@flowdesk.local`
- Process Owner: `owner1@flowdesk.local`, `owner2@flowdesk.local`
- Approver: `approver1@flowdesk.local` ... `approver4@flowdesk.local`
- Requester: `requester1@flowdesk.local` ... `requester10@flowdesk.local`

## Login Flow
Sanctum uses cookie-based session auth.

Expected browser flow:
1. `GET /sanctum/csrf-cookie`
2. `POST /auth/login`
3. `GET /me`
4. `POST /auth/logout`

Verify in browser cookies:
- `XSRF-TOKEN`
- `flowdesk-session` (or `<app>_session`)

## Production Deployment
Production deployment artifacts are included in the repo:
- `Dockerfile` for the Laravel PHP-FPM app image
- `docker/nginx/Dockerfile.prod` for the production nginx + SPA image
- `docker-compose.prod.yml`
- `docker/entrypoint.sh`
- `docker/nginx/prod.conf`

Use [INSTALLATION.md](INSTALLATION.md) for the full VPS deployment guide.

## Documentation
- [INSTALLATION.md](INSTALLATION.md)
- [docs/permissions.md](docs/permissions.md)
- [docs/workflow-json.md](docs/workflow-json.md)
- [docs/erd.md](docs/erd.md)

## Screenshots
Screenshots are intentionally not committed yet. Add project screenshots under `docs/screenshots/` before publishing screenshots in GitHub documentation.

## Security
- Do not commit `.env` files, private keys, certificates, database dumps, or backup archives.
- Use GitHub private vulnerability reporting or another private maintainer channel for security disclosures. Do not open public issues for unpatched vulnerabilities.
- Minimum production recommendations:
  - `APP_DEBUG=false`
  - unique `APP_KEY`
  - strong database credentials
  - `SESSION_SECURE_COOKIE=true` when using HTTPS
  - restricted `CORS_ALLOWED_ORIGINS`
  - regular database and storage backups

## License
This repository is licensed under the MIT License. See [LICENSE](LICENSE).
