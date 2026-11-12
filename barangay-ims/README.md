# Barangay Information Management System

A Laravel-based system for managing barangay residents, households, blotter records, document requests, reports, and audit logs.

## Requirements

- PHP 8.2+
- Composer
- MySQL/MariaDB for the application
- SQLite extension for the test suite

## Local setup

```bash
composer install
copy .env.example .env       # Windows
# cp .env.example .env       # macOS/Linux
php artisan key:generate
```

Update the database values in `.env`, create the database, then run:

```bash
php artisan migrate --seed
php artisan storage:link
php artisan serve
```

The seed passwords are read from `SEED_ADMIN_PASSWORD`, `SEED_SECRETARY_PASSWORD`, and `SEED_KAGAWAD_PASSWORD` in `.env`. Change them before seeding any shared or production environment. If they are omitted, Laravel generates random passwords for the seeded users.

## Testing

```bash
php artisan test
```

Tests use an in-memory SQLite database and do not require the application database.

## Production deployment

Host-specific guides with exact commands:

- **InfinityFree** (free shared hosting, no SSH/Composer/cron, symlinks
  blocked): [DEPLOY_INFINITYFREE.md](DEPLOY_INFINITYFREE.md)
- **alwaysdata** (free plan, SSH available): [DEPLOY_ALWAYSDATA.md](DEPLOY_ALWAYSDATA.md)

Generic instructions follow; prefer the host guide when both apply.

### Server requirements

- PHP 8.2+ with the `dom`, `fileinfo`, `gd`, `mbstring`, `openssl`, `xml`, `zip`, and `zlib` extensions
- MySQL 5.7+/8.0+ or MariaDB 10.4+
- Composer 2
- HTTPS certificate
- Web server document root pointed at `public/`
- Write access for the web server user to `storage/` and `bootstrap/cache/`
- Cron (only needed for the daily reminder job)

### First deployment

1. Clone the repository and install production dependencies:

   ```bash
   git clone <repository-url> barangay-ims
   cd barangay-ims
   composer install --no-dev --optimize-autoloader
   ```

2. Create the environment file and application key. Start from
   `.env.production.example` (not `.env.example`, which contains local
   development values). Fill in the real database and mail settings, and set
   `APP_URL` to the public HTTPS URL.

   ```bash
   cp .env.production.example .env
   php artisan key:generate
   ```

   Keep `APP_ENV=production`, `APP_DEBUG=false`, and
   `SESSION_SECURE_COOKIE=true`. Never commit the resulting `.env`.

3. Back up any existing database, then apply migrations. Do not use
   `migrate:fresh` or `migrate:refresh` on a live database.

   ```bash
   php artisan migrate --force
   ```

4. Create the initial accounts once. Set strong `SEED_*` passwords in `.env`,
   run the seeder, then remove the passwords and refresh the config cache.
   `DatabaseSeeder` uses `updateOrCreate` and resets those passwords every time
   it runs, so do not run it on later deployments. Keep `SEED_MOCK_DATA=false`
   in production.

   ```bash
   php artisan db:seed --force
   ```

5. Link storage and build production caches:

   ```bash
   php artisan storage:link
   php artisan optimize
   ```

6. Point the web server document root at `public/`. Apache uses the included
   `public/.htaccess`; for Nginx, route requests to `public/index.php` and deny
   access to everything outside `public/`.

7. Add the scheduler cron entry so reminders are generated daily:

   ```cron
   * * * * * cd /path/to/barangay-ims && php artisan schedule:run >> /dev/null 2>&1
   ```

### Every deployment

```bash
git pull
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan optimize
```

### Post-deployment verification

- `GET https://your-host/up` returns a successful health response.
- `php artisan migrate:status` reports no pending migrations.
- Log in with each role, open the dashboard, create a resident, generate a
  document PDF, and run an export.
- Check that `storage/logs/` is writable and that uploaded logos and resident
  photos are served from `/storage/...`.

### Deployment notes

- If the app runs behind a reverse proxy or CDN, configure trusted proxies in
  `bootstrap/app.php` so generated URLs use HTTPS.
- The UI loads Tailwind CSS, Alpine.js, and Google Fonts from public CDNs. The
  browser needs internet access for styling and interactivity. For offline
  deployments, compile and serve these assets locally.
- Hosts that block symlinks (InfinityFree) cannot run `storage:link`. Set
  `PUBLIC_DISK_IN_WEBROOT=true` in `.env` there so uploads are stored in a real
  `public/storage` folder instead of `storage/app/public`.

## Security

Never commit `.env`, generated Laravel cache files, database credentials, or production keys. Configure those values through the deployment environment.
