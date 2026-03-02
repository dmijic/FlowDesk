# FlowDesk Installation and Deployment Guide

This guide covers recommended production deployment on a VPS.

## Overview
Supported deployment modes:
1. Docker production deployment on Ubuntu VPS (recommended)
2. Non-Docker deployment with Nginx + PHP-FPM + systemd

Before deploying, make sure you have:
- A domain or subdomain, for example `flowdesk.example.com`
- A DNS `A` record pointing to your VPS public IP
- SSH access to an Ubuntu 22.04 or 24.04 VPS
- SMTP credentials for production email delivery

## Quick Deploy
### Recommended: Docker on VPS
Copy and run on a fresh Ubuntu VPS:

```bash
sudo apt update
sudo apt install -y git docker.io docker-compose-plugin nginx certbot python3-certbot-nginx
sudo systemctl enable --now docker nginx
sudo usermod -aG docker $USER

git clone https://github.com/<your-org>/flowdesk.git /opt/flowdesk
cd /opt/flowdesk
cp .env.example .env
cp backend/.env.example backend/.env

php -r "echo 'base64:'.base64_encode(random_bytes(32)).PHP_EOL;"
# Paste the generated value into backend/.env as APP_KEY

nano .env
nano backend/.env

./scripts/deploy-prod.sh
```

Then create a host-level nginx reverse proxy and issue TLS:

```bash
sudo nano /etc/nginx/sites-available/flowdesk
sudo ln -s /etc/nginx/sites-available/flowdesk /etc/nginx/sites-enabled/flowdesk
sudo nginx -t
sudo systemctl reload nginx
sudo certbot --nginx -d flowdesk.example.com
```

## Docker Production Deployment
### 1. Install system packages
```bash
sudo apt update
sudo apt install -y git docker.io docker-compose-plugin nginx certbot python3-certbot-nginx
sudo systemctl enable --now docker nginx
```

Optional but recommended:
```bash
sudo usermod -aG docker $USER
newgrp docker
```

### 2. Clone the repository
```bash
git clone https://github.com/<your-org>/flowdesk.git /opt/flowdesk
cd /opt/flowdesk
```

### 3. Prepare environment files
Create runtime env files from the examples:

```bash
cp .env.example .env
cp backend/.env.example backend/.env
```

Root `.env` is used by Docker Compose and image build args.
Backend `backend/.env` is used by Laravel.

### 4. Configure root `.env`
Recommended production values:

```dotenv
COMPOSE_PROJECT_NAME=flowdesk
APP_PORT=8080
MYSQL_ROOT_PASSWORD=change-this-root-password
MYSQL_DATABASE=flowdesk
MYSQL_USER=flowdesk
MYSQL_PASSWORD=change-this-db-password
VITE_API_BASE_URL=
VITE_APP_NAME=FlowDesk
```

Notes:
- `APP_PORT=8080` keeps the app bound to localhost-facing reverse proxy usage.
- Leave `VITE_API_BASE_URL=` empty when frontend and backend are served from the same production domain.

### 5. Configure `backend/.env`
Required production overrides:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://flowdesk.example.com
FRONTEND_URL=https://flowdesk.example.com
APP_VERSION=1.0.0
TRUSTED_PROXIES=*

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=flowdesk
DB_USERNAME=flowdesk
DB_PASSWORD=change-this-db-password

SESSION_DRIVER=database
SESSION_DOMAIN=flowdesk.example.com
SESSION_PATH=/
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=lax

SANCTUM_STATEFUL_DOMAINS=flowdesk.example.com
CORS_ALLOWED_ORIGINS=https://flowdesk.example.com

LOG_CHANNEL=stack
LOG_STACK=single
LOG_LEVEL=info

MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=your-smtp-user
MAIL_PASSWORD=your-smtp-password
MAIL_FROM_ADDRESS="noreply@flowdesk.example.com"
MAIL_FROM_NAME="FlowDesk"
```

Generate an application key if needed:

```bash
php -r "echo 'base64:'.base64_encode(random_bytes(32)).PHP_EOL;"
```

Paste the generated value into `APP_KEY=` in `backend/.env`.

### 6. Build and start the production stack
```bash
./scripts/deploy-prod.sh
```

Equivalent manual commands:

```bash
docker compose -f docker-compose.prod.yml build --pull
docker compose -f docker-compose.prod.yml up -d
```

### 7. Verify the deployment
```bash
docker compose -f docker-compose.prod.yml ps
curl http://127.0.0.1:8080/health
```

Expected health response:

```json
{
  "status": "ok",
  "app": "FlowDesk",
  "version": "1.0.0",
  "environment": "production"
}
```

### 8. Database migrations and optional seeding
The production entrypoint already runs migrations by default.

Optional demo seed on non-production environments only:

```bash
RUN_SEED=true docker compose -f docker-compose.prod.yml up -d
```

For production, leave `RUN_SEED=false`.

### 9. Queue worker and scheduler
The production compose file includes dedicated containers for:
- queue worker
- scheduler

Useful commands:

```bash
docker compose -f docker-compose.prod.yml logs -f queue
docker compose -f docker-compose.prod.yml logs -f scheduler
docker compose -f docker-compose.prod.yml exec php php artisan queue:restart
```

### 10. HTTPS with host nginx reverse proxy
Recommended setup:
- Keep Docker nginx bound to `127.0.0.1:8080`
- Use host nginx to terminate TLS and proxy to Docker nginx

Example host nginx server block:

```nginx
server {
    listen 80;
    server_name flowdesk.example.com;

    location / {
        proxy_pass http://127.0.0.1:8080;
        proxy_http_version 1.1;
        proxy_set_header Host $host;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
    }
}
```

Enable and reload:

```bash
sudo ln -s /etc/nginx/sites-available/flowdesk /etc/nginx/sites-enabled/flowdesk
sudo nginx -t
sudo systemctl reload nginx
```

Issue a Let’s Encrypt certificate:

```bash
sudo certbot --nginx -d flowdesk.example.com
```

### 11. DNS
Create an `A` record:
- Host: `flowdesk` or `@`
- Value: `<your-vps-public-ip>`

Wait for DNS propagation before issuing the certificate.

### 12. Using an external database instead of the bundled MySQL container
If you use a managed or external MySQL instance:
- remove or ignore the `mysql` service in `docker-compose.prod.yml`
- point `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` to the managed database
- make sure the firewall allows access from the VPS

### 13. Backups
Basic backup plan:

Database dump:
```bash
docker compose -f docker-compose.prod.yml exec -T mysql \
  mysqldump -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" "$MYSQL_DATABASE" > /opt/flowdesk-backups/flowdesk-$(date +%F).sql
```

Storage archive:
```bash
tar -czf /opt/flowdesk-backups/flowdesk-storage-$(date +%F).tar.gz /opt/flowdesk/backend/storage
```

Recommended:
- store backups outside the app directory
- encrypt backups at rest
- ship backups to object storage
- test restore procedures regularly

## Non-Docker VPS Deployment
This mode uses host PHP-FPM and host nginx.

### 1. Install required packages
```bash
sudo apt update
sudo apt install -y \
  nginx \
  mysql-client \
  php8.3 php8.3-cli php8.3-fpm php8.3-mysql php8.3-mbstring php8.3-xml php8.3-curl php8.3-gd php8.3-zip php8.3-intl php8.3-bcmath php8.3-pcntl \
  unzip git curl
```

Install Composer:
```bash
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php --install-dir=/usr/local/bin --filename=composer
rm composer-setup.php
```

Install Node.js 20.x or newer using your preferred package source.

### 2. Clone the repository
```bash
git clone https://github.com/<your-org>/flowdesk.git /var/www/flowdesk
cd /var/www/flowdesk
```

### 3. Install backend dependencies
```bash
cp backend/.env.example backend/.env
composer install --working-dir=backend --no-dev --prefer-dist --optimize-autoloader
```

Generate `APP_KEY` and set production env values in `backend/.env`.

### 4. Build frontend assets
```bash
cp frontend/.env.example frontend/.env
npm --prefix frontend ci
VITE_API_BASE_URL= npm --prefix frontend run build
```

This produces `frontend/dist` for nginx to serve.

### 5. Storage and permissions
```bash
cd backend
php artisan storage:link
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R ug+rwx storage bootstrap/cache
```

### 6. Run database migrations
```bash
php artisan migrate --force
```

Optional non-production seed:
```bash
php artisan db:seed --force
```

### 7. Nginx server block
Example Nginx server block for same-origin SPA + API:

```nginx
server {
    listen 80;
    server_name flowdesk.example.com;

    root /var/www/flowdesk/frontend/dist;
    index index.html;
    client_max_body_size 20m;

    location /storage/ {
        alias /var/www/flowdesk/backend/public/storage/;
        access_log off;
        expires 7d;
        add_header Cache-Control "public, max-age=604800";
        try_files $uri =404;
    }

    location ~ ^/(api|auth|sanctum)(/|$) {
        include snippets/fastcgi-php.conf;
        fastcgi_param SCRIPT_FILENAME /var/www/flowdesk/backend/public/index.php;
        fastcgi_param DOCUMENT_ROOT /var/www/flowdesk/backend/public;
        fastcgi_param SCRIPT_NAME /index.php;
        fastcgi_param PATH_INFO "";
        fastcgi_param REQUEST_URI $request_uri;
        fastcgi_param QUERY_STRING $query_string;
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
    }

    location = /me {
        include snippets/fastcgi-php.conf;
        fastcgi_param SCRIPT_FILENAME /var/www/flowdesk/backend/public/index.php;
        fastcgi_param DOCUMENT_ROOT /var/www/flowdesk/backend/public;
        fastcgi_param SCRIPT_NAME /index.php;
        fastcgi_param PATH_INFO "";
        fastcgi_param REQUEST_URI $request_uri;
        fastcgi_param QUERY_STRING $query_string;
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
    }

    location = /health {
        include snippets/fastcgi-php.conf;
        fastcgi_param SCRIPT_FILENAME /var/www/flowdesk/backend/public/index.php;
        fastcgi_param DOCUMENT_ROOT /var/www/flowdesk/backend/public;
        fastcgi_param SCRIPT_NAME /index.php;
        fastcgi_param PATH_INFO "";
        fastcgi_param REQUEST_URI $request_uri;
        fastcgi_param QUERY_STRING $query_string;
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
    }

    location / {
        try_files $uri $uri/ /index.html;
    }
}
```

Enable and reload nginx:

```bash
sudo ln -s /etc/nginx/sites-available/flowdesk /etc/nginx/sites-enabled/flowdesk
sudo nginx -t
sudo systemctl reload nginx
```

### 8. Queue worker with systemd
Create `/etc/systemd/system/flowdesk-queue.service`:

```ini
[Unit]
Description=FlowDesk Queue Worker
After=network.target

[Service]
User=www-data
Group=www-data
Restart=always
WorkingDirectory=/var/www/flowdesk/backend
ExecStart=/usr/bin/php artisan queue:work --sleep=1 --tries=3 --timeout=90

[Install]
WantedBy=multi-user.target
```

Enable it:

```bash
sudo systemctl daemon-reload
sudo systemctl enable --now flowdesk-queue
sudo systemctl status flowdesk-queue
```

### 9. Scheduler with cron
Add cron entry for `www-data`:

```bash
sudo crontab -u www-data -e
```

Add:
```cron
* * * * * cd /var/www/flowdesk/backend && /usr/bin/php artisan schedule:run >> /dev/null 2>&1
```

### 10. HTTPS
Use Let’s Encrypt on the host nginx:

```bash
sudo certbot --nginx -d flowdesk.example.com
```

### 11. Post-deploy checks
```bash
curl -I https://flowdesk.example.com
curl https://flowdesk.example.com/health
sudo systemctl status php8.3-fpm
sudo systemctl status flowdesk-queue
```

## Operational Notes
- Run `php artisan optimize:clear` after changing Laravel config or env values.
- `php artisan storage:link` is required for any public storage URLs.
- Set `SESSION_SECURE_COOKIE=true` when running behind HTTPS.
- Keep `CORS_ALLOWED_ORIGINS` as narrow as possible.
- Keep `APP_DEBUG=false` in production.
- Rotate credentials if a server or backup is exposed.
