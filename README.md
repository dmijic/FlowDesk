# FlowDesk

FlowDesk je monorepo aplikacija za interne zahtjeve (`IT pristup`, `nabava`, `putni nalog`) s višerazinskim odobravanjima, audit trailom, notifikacijama i reportingom.

## Stack
- Backend: Laravel 11 API, PHP 8.3, Sanctum
- Frontend: Vue 3 + Vite + Pinia + Vue Router + Tailwind
- DB: MySQL 8
- Queue: database + worker container
- Mail: Mailhog
- Reverse proxy: Nginx

## Monorepo struktura

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

## Brzi start

```bash
make setup
```

Alternativa:

```bash
./scripts/setup.sh
```

Setup radi:
1. Podizanje Docker servisa (`nginx`, `php`, `mysql`, `node`, `mailhog`, `queue`, `scheduler`)
2. `composer install`
3. `php artisan key:generate`
4. `php artisan migrate:fresh --seed`
5. `npm install` za frontend

## URL-ovi
- Frontend preko nginx: `http://localhost`
- Frontend direktno Vite: `http://localhost:5173`
- Backend API: `http://localhost/api`
- Mailhog UI: `http://localhost:8025`

## Demo korisnici (seed)
Svi imaju lozinku: `Password123!`

- Admin: `admin@flowdesk.local`
- Process Owner: `owner1@flowdesk.local`, `owner2@flowdesk.local`
- Approver: `approver1@flowdesk.local` ... `approver4@flowdesk.local`
- Requester: `requester1@flowdesk.local` ... `requester10@flowdesk.local`

## Što se seeda
- 1 admin
- 2 process owner
- 4 approver
- 10 requester
- 3 departmenta
- 5 request type-a
- 3 workflow definicije (`any/all`, `parallel/non-parallel`)
- 25 demo requestova + approval taskovi + audit logovi + in-app notifikacije

## API ruta mapa
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

## Testovi
Pokretanje feature testova:

```bash
make test
```

Pokriti scenariji:
- submit request -> kreira first-step taskove
- `any` rule -> jedan approve kompletira step i skipa ostale
- `all` rule -> treba sve approve odluke
- reject odmah zatvara request i skipa preostale
- permission denial (requester ne vidi tuđi request, approver ne može `manage_users`)

## Sanctum SPA login checklist
1. Otvori frontend na `http://localhost:5173`.
2. Login flow mora imati redoslijed:
   - `GET /sanctum/csrf-cookie` (200)
   - `POST /auth/login` (200)
3. U browseru provjeri cookies za `http://localhost`:
   - `XSRF-TOKEN`
   - `flowdesk-session` (ili `{app}_session`)
4. Nakon login-a `GET /me` mora vratiti user JSON.
5. Logout (`POST /auth/logout`) mora završiti bez greške i sesija više ne smije biti validna.

## Debug / cache clear
Ako mijenjaš CORS/session/Sanctum config, očisti cache:

```bash
docker compose exec php php artisan optimize:clear
```

Ako vidiš auth greške u browseru:
- `404 /auth/login` ili `404 /me`: provjeri da nginx prosljeđuje `/auth`, `/me`, `/sanctum` na Laravel (`docker/nginx/default.conf`) i restartaj nginx.
- `502 /login`: frontend Vite servis nije spreman; provjeri `docker compose ps` i `docker compose logs node`.

## Workflow engine (sažetak)
- `WorkflowEngine`:
  - submit request
  - kreira taskove za prvi/idući korak
  - zaključuje request kao approved na zadnjem koraku
- `ApprovalService`:
  - approve/reject logika
  - `any/all` pravila
  - skipanje taskova i zatvaranje requesta
- `AuditLogger`:
  - zapisuje actor/action/entity + before/after + metadata

## Kako dodati novi workflow
1. Login kao `admin` ili `process owner`.
2. Otvori `Admin -> Workflows`.
3. Odaberi request type.
4. Zalijepi/uredi `definition_json`.
5. Označi `Active` ako treba postati trenutno aktivna verzija.
6. Spremi.

Za JSON format vidi [docs/workflow-json.md](docs/workflow-json.md).

## Dodatna dokumentacija
- [docs/permissions.md](docs/permissions.md)
- [docs/workflow-json.md](docs/workflow-json.md)
- [docs/erd.md](docs/erd.md)
