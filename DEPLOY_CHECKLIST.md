# Bluebird CRM v7.7 — Deploy Readiness Checklist

> Run this before every deployment to Railway / Render / Vercel / VPS.

---

## 1. Pre-Deploy (Local)

```bash
# Run full test suite — must be 0 failures
php artisan test

# Check for missing routes
php artisan route:list --name | grep -E "revenue|search|widget|analytics"

# Verify no undefined route() in blade files
grep -roh "route('[^']*')" resources/views/ | sort -u

# Lint PHP
composer lint   # (if phpcs/pint configured)

# Build assets
npm run build
```

---

## 2. Environment Variables

| Variable | Required | Notes |
|---|---|---|
| `APP_KEY` | ✅ | `php artisan key:generate` on first deploy |
| `APP_ENV` | ✅ | `production` |
| `APP_DEBUG` | ✅ | `false` in prod |
| `APP_URL` | ✅ | Full HTTPS URL |
| `DB_CONNECTION` | ✅ | `pgsql` (Railway/Render) or `mysql` (VPS) |
| `DATABASE_URL` | Railway/Render | Auto-set by platform — overrides DB_* vars |
| `SESSION_DRIVER` | ✅ | `database` or `redis` (NOT `file` on multi-instance) |
| `CACHE_DRIVER` | ✅ | `redis` or `database` |
| `QUEUE_CONNECTION` | ✅ | `redis` or `database` |
| `LOG_CHANNEL` | ✅ | `stderr` (containers) or `daily` (VPS) |

### Platform-Specific

**Railway:**
```env
DATABASE_URL=${{Postgres.DATABASE_URL}}
SESSION_DRIVER=database
CACHE_DRIVER=redis
```

**Render:**
```env
DB_CONNECTION=pgsql
DB_HOST=$RENDER_DB_HOST
SESSION_DRIVER=database
LOG_CHANNEL=stderr
```

**Vercel (pgsql):**
```env
DB_CONNECTION=pgsql
POSTGRES_URL=${{POSTGRES_URL}}
SESSION_DRIVER=cookie
CACHE_DRIVER=array
```

**VPS (Docker):**
```env
DB_CONNECTION=mysql
SESSION_DRIVER=database
CACHE_DRIVER=redis
LOG_CHANNEL=daily
```

---

## 3. First-Deploy Commands

```bash
php artisan migrate --force
php artisan db:seed --class=RoleSeeder      # if roles are seeded
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link
```

> ⚠️ `route:cache` requires **no Closures in routes/web.php**.
> Check: `php artisan route:cache` locally before deploy.

---

## 4. Opcache (Docker / VPS)

Already configured in `Dockerfile`:
```ini
opcache.memory_consumption=256
opcache.max_accelerated_files=20000
opcache.validate_timestamps=0    ← PRODUCTION ONLY (files don't change at runtime)
opcache.realpath_cache_size=4096K
```

> ⚠️ `validate_timestamps=0` means **changes won't be detected** until container restarts.
> This is correct for immutable containers. Do NOT use on shared hosting.

---

## 5. Database Compat

All raw SQL in the codebase now uses the `dateExpr()` helper in `RevenueController`
that auto-detects driver and uses the correct function:

| Driver | Function Used |
|---|---|
| SQLite (local/test) | `STRFTIME('%Y-%m', ...)` |
| MySQL / MariaDB | `DATE_FORMAT(..., '%Y-%m')` |
| PostgreSQL | `TO_CHAR(..., 'YYYY-MM')` |

---

## 6. Route Closure Check

```bash
# This must succeed (no Closures in routes)
php artisan route:cache

# If it fails, check routes/web.php for:
Route::post('/subscriptions/billing/run', function () { ... })
# → Move to SubscriptionController::processMonthlyBilling()
```

> Currently `subscriptions.billing.run` uses a Closure — if you need `route:cache`,
> move that logic to a controller method.

---

## 7. Known Route Closures (fix before route:cache)

```php
// routes/web.php line ~70
Route::post('/subscriptions/billing/run', function () {
    // ← move to SubscriptionController@runBilling
})->name('subscriptions.billing.run');
```

---

## 8. Post-Deploy Smoke Tests

After each deploy, hit these URLs in the browser:

| URL | Expected | Checks |
|---|---|---|
| `/dashboard` | 200 (auth redirect) | App boots |
| `/login` | 200 | Auth working |
| `/api/search/global?q=test` | JSON | SearchController |
| `/revenue` | 302 → `/analytics` | revenue.index route |
| `/analytics` | 200 (if director) | Analytics page |
| `/finance` | 200 (if finance) | Finance page |

---

## 9. Migration Checklist

```bash
# Verify all migrations ran
php artisan migrate:status

# New in v7.7 — must appear as Ran:
2026_06_07_000001_create_widget_preferences_table
```

---

## 10. Security

- [ ] `APP_DEBUG=false` in production
- [ ] `/master` folder NOT in git (`.gitignore` — NEVER commit)
- [ ] CSRF middleware active (default Laravel — don't remove `VerifyCsrfToken`)
- [ ] All API endpoints inside `auth` middleware group
- [ ] `validate_timestamps=0` only in production Dockerfile

---

*Last updated: v7.7 | June 2026*
