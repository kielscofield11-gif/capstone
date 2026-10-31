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
