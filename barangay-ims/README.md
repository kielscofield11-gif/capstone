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
   development values). Fill in the real database, mail, and PayMongo
   credentials, and set `APP_URL` to the public HTTPS URL.

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

8. Register the PayMongo webhook at `https://your-host/webhooks/paymongo` with
   the events listed in the payments section below.

### Every deployment

```bash
git pull
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan optimize
```

### Post-deployment verification

- `GET https://your-host/up` returns a successful health response.
- `php artisan project:preflight` reports no pending migrations or duplicate
  control numbers.
- Log in with each role, open the dashboard, create a resident, generate a
  document PDF, run an export, and complete a payment in PayMongo test mode.
- Check that `storage/logs/` is writable and that uploaded logos and resident
  photos are served from `/storage/...`.

### Deployment notes

- If the app runs behind a reverse proxy or CDN, configure trusted proxies in
  `bootstrap/app.php` so generated URLs and PayMongo redirects use HTTPS.
- The UI loads Tailwind CSS, Alpine.js, and Google Fonts from public CDNs. The
  browser needs internet access for styling and interactivity. For offline
  deployments, compile and serve these assets locally.
- `PAYMENT_GATEWAY=fake` is disabled when `APP_ENV=production`; live online
  checkout requires PayMongo credentials. Cash payments work without them.

## Online payments (PayMongo)

Document requests with a fee can be paid online through PayMongo hosted checkout.
Staff create a payment link from a document request and share it with the resident.
PayMongo confirms the payment through a signed webhook, and a document cannot be
marked released until its fee has been paid (zero-fee documents are exempt).
Walk-in cash payments can also be recorded from the same page.

Add these values to `.env`:

```dotenv
PAYMENT_GATEWAY=paymongo
PAYMONGO_PUBLIC_KEY=pk_test_...
PAYMONGO_SECRET_KEY=sk_test_...
PAYMONGO_WEBHOOK_SECRET=whsec_...
PAYMONGO_PAYMENT_METHODS="gcash,paymaya,grab_pay,card,qrph"
```

Then run `php artisan migrate` and register the webhook in
PayMongo Dashboard → Developers → Webhooks:

- URL: `https://your-host/webhooks/paymongo`
- Events: `checkout_session.payment.paid`, `payment.paid`, `payment.failed`

For local development, expose the app with a public HTTPS tunnel (for example
ngrok) and set `APP_URL` to the tunnel URL so PayMongo can reach the webhook.

### Offline demo mode

If PayMongo credentials are not available, set `PAYMENT_GATEWAY=fake`. The
"online" checkout then opens a local demo page that simulates a successful or
failed payment without charging money. Demo mode is disabled when `APP_ENV=production`.

## Security

Never commit `.env`, generated Laravel cache files, database credentials, or production keys. Configure those values through the deployment environment.
