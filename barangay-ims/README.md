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

## Security

Never commit `.env`, generated Laravel cache files, database credentials, or production keys. Configure those values through the deployment environment.
