# Bluebird CRM — Deployment Guide

## Branch
- **Main (stable):** `main`
- **UI Modern Preview:** `ui-modern-preview` ← current work

---

## Railway Deployment

### 1. Environment Variables
Set these in Railway → Service → Variables:
```
APP_NAME=BluebirCRM
APP_ENV=production
APP_KEY=base64:<generate with php artisan key:generate --show>
APP_DEBUG=false
APP_URL=https://your-service.up.railway.app

DB_CONNECTION=pgsql
DB_HOST=postgres.railway.internal
DB_PORT=5432
DB_DATABASE=railway
DB_USERNAME=postgres
DB_PASSWORD=<from Railway PostgreSQL addon>

LOG_CHANNEL=stack
LOG_LEVEL=error
SESSION_DRIVER=database
CACHE_DRIVER=file
QUEUE_CONNECTION=sync
```

### 2. Build Config (`nixpacks.toml`)
Already configured in repo root. Uses PHP 8.4 + pdo_pgsql.

### 3. Deploy
```bash
# CLI deploy
railway up --detach -m "deploy: ui-modern-preview"

# Or connect GitHub repo → auto-deploy on push
```

### 4. Post-deploy (first time only)
```bash
railway run php artisan migrate --force
railway run php artisan db:seed --force
railway run php artisan config:cache
railway run php artisan route:cache
```

### 5. Demo Login
| Role | Email | Password |
|------|-------|----------|
| Director | director@goldenbird.co.id | password123 |
| GM | gm@goldenbird.co.id | password123 |
| Manager | manager@goldenbird.co.id | password123 |
| Sales | sales@goldenbird.co.id | password123 |
| Operational | operational@goldenbird.co.id | password123 |
| Finance | finance@goldenbird.co.id | password123 |

---

## Render.com Deployment

### 1. Create Web Service
- Connect GitHub repo
- Branch: `ui-modern-preview`
- Runtime: `PHP`
- Build Command: `composer install --no-dev --optimize-autoloader && php artisan config:cache && php artisan route:cache`
- Start Command: `php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=$PORT`

### 2. Environment Variables
Same as Railway above. Add a PostgreSQL addon from Render dashboard.

### 3. Database
- Add PostgreSQL from Render → creates `DATABASE_URL`
- Override DB_* vars with values from Render PostgreSQL connection info

---

## Local Development
```bash
git clone https://github.com/adith92/BBCRM-By-Claude
cd BBCRM-By-Claude
git checkout ui-modern-preview

composer install
cp .env.example .env
php artisan key:generate

# Edit .env with your local DB (SQLite or MySQL)
# For SQLite:
# DB_CONNECTION=sqlite
# DB_DATABASE=/absolute/path/to/database/database.sqlite
touch database/database.sqlite

php artisan migrate --seed
php artisan serve
# → http://localhost:8000
```

---

## Troubleshooting

| Error | Fix |
|-------|-----|
| 500 on login | Check APP_KEY is set |
| DB connection failed | Verify DB_* env vars on Railway |
| View error | `railway run php artisan view:clear` |
| Migration error | Check PostgreSQL pdo_pgsql extension loaded |
| White screen | Set APP_DEBUG=true temporarily, check logs |
