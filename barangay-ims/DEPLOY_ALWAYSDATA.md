# Deploy Barangay IMS to alwaysdata (Free plan)

This guide assumes:

- alwaysdata account name: `<account>`
- Public URL: `https://<account>.alwaysdata.net`
- GitHub repo: `https://github.com/kielscofield11-gif/capstone.git`
- App directory on the server: `/home/<account>/capstone/barangay-ims`

Replace every `<...>` placeholder before running a command.

> The alwaysdata Free plan gives 1 GB of disk, 256 MB of RAM, PHP 8.2–8.5,
> MariaDB, SSH, cron, and HTTPS. It does not allow a custom domain, so the
> site uses the free `<account>.alwaysdata.net` address.

---

## 0. Push the code you want to deploy

The server deploys whatever is on GitHub, not your local working tree.
Review and push your changes first:

```bash
git status
git add .
git commit -m "Prepare deployment"
git push origin main
```

If the repository is private, have a GitHub username and a fine-grained
personal access token ready (repository contents: read). Git will prompt for
them during `git clone`; do not put the token in the clone URL.

---

## 1. Create the alwaysdata account and database

1. Sign up at <https://www.alwaysdata.com/> and choose the **Free** plan.
2. Note your account name. It is the `<account>` used below.
3. Go to **Environment > PHP** and select **PHP 8.3**.
   - Keep the account-level PHP version at 8.3. The SSH command `php` and
     scheduled tasks use this version.
4. Go to **Databases > MySQL > Add a database**.
   - Name: `barangay_ims`
   - Version: MariaDB 11.4 (or the current default)
   - Grant the database user **ALL PRIVILEGES**.
5. Open **Databases > MySQL** and copy the exact values shown:
   - Host: `mysql-<account>.alwaysdata.net`
   - Port: `3306`
   - Database: usually `<account>_barangay_ims`
   - User: usually `<account>`
   - Password: the one you set

Do not use `127.0.0.1` or `localhost` for `DB_HOST`; alwaysdata databases are
on the `mysql-<account>.alwaysdata.net` host.

---

## 2. Connect over SSH and clone the app

From PowerShell on Windows:

```powershell
ssh <account>@ssh-<account>.alwaysdata.net
```

The SSH host is `ssh-<account>.alwaysdata.net`, port 22, and the default SSH
user is your account name.

Once connected:

```bash
cd "$HOME"
git clone https://github.com/kielscofield11-gif/capstone.git
cd "$HOME/capstone/barangay-ims"
```

Check the runtime and required PHP extensions:

```bash
php -v
composer2 --version
php -m | grep -E 'dom|fileinfo|gd|mbstring|openssl|xml|zip|zlib|pdo_mysql'
```

All of those extensions should be listed. If `composer2` is not found, use
`composer` instead in the commands below.

---

## 3. Install production dependencies

```bash
cd "$HOME/capstone/barangay-ims"

COMPOSER_MEMORY_LIMIT=-1 \
COMPOSER_CACHE_DIR=/dev/null \
composer2 install --no-dev --optimize-autoloader
```

`COMPOSER_CACHE_DIR=/dev/null` avoids filling the 1 GB free-plan disk with
Composer cache files. If Composer runs out of memory, see the troubleshooting
section.

---

## 4. Create the production `.env`

Start from the production template, not the local development template:

```bash
cp .env.production.example .env
nano .env
```

If `nano` is not available, use `vi .env`.

Change these values:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://<account>.alwaysdata.net
BARANGAY_NAME="Barangay Concepcion"

DB_CONNECTION=mysql
DB_HOST=mysql-<account>.alwaysdata.net
DB_PORT=3306
DB_DATABASE=<account>_barangay_ims
DB_USERNAME=<account>
DB_PASSWORD="<database-password>"

# Keep this true because the site is served over HTTPS.
SESSION_SECURE_COOKIE=true

# Use "log" for a capstone demo if you do not have SMTP credentials.
MAIL_MAILER=log
MAIL_FROM_ADDRESS="no-reply@<account>.alwaysdata.net"

# One-time passwords for the initial accounts. Set strong values now.
SEED_ADMIN_PASSWORD="<strong-admin-password>"
SEED_SECRETARY_PASSWORD="<strong-secretary-password>"
SEED_CAPTAIN_PASSWORD="<strong-captain-password>"
SEED_KAGAWAD_PASSWORD="<strong-kagawad-password>"
SEED_STAFF_PASSWORD="<strong-staff-password>"
SEED_MOCK_DATA=false
ALLOW_MOCK_DATA_SEEDING=false
```

Use the exact database name, username, and password shown in
**Databases > MySQL**. If the password contains `$`, `#`, spaces, or other
shell characters, keep it inside double quotes in `.env`.

Generate the application key:

```bash
php artisan key:generate
```

Protect the environment file:

```bash
chmod 600 .env
```

Do not commit `.env`.

---

## 5. Create the database schema and initial accounts

Run the migrations:

```bash
php artisan migrate --force
```

Seed the initial accounts **once**, while the `SEED_*` passwords are still in
`.env`:

```bash
php artisan db:seed --force
```

This creates the accounts:

- `admin@barangay.gov`
- `secretary@barangay.gov`
- `captain@barangay.gov`
- `kagawad@barangay.gov`
- `staff@barangay.gov`

The passwords are the `SEED_*` values from `.env`.

> Do not run `db:seed` again on later deployments. `DatabaseSeeder` uses
> `updateOrCreate` and resets those account passwords every time it runs.

After successful seeding, remove the five `SEED_*_PASSWORD` lines from `.env`
(keep `SEED_MOCK_DATA=false`), then refresh the config cache:

```bash
php artisan optimize:clear
php artisan optimize
```

---

## 6. Storage link and permissions

Create the public storage symlink so uploaded logos are served from
`/storage/...`:

```bash
php artisan storage:link --force
```

Set permissions. alwaysdata runs PHP as your main account user, so do **not**
run `chown www-data` and do not use `sudo`:

```bash
chmod -R u+rwX storage bootstrap/cache
```

Verify:

```bash
ls -l public/storage
php artisan about
```

---

## 7. Create the PHP site in alwaysdata

1. Go to **Web > Sites > Add a site**.
2. Address: `<account>.alwaysdata.net`
3. Type: **PHP**
4. Path: `capstone/barangay-ims/public`
   - This path is relative to `/home/<account>`.
   - It must point at the Laravel `public` directory, not the project root.
5. PHP version: **8.3**
6. Enable **Force HTTPS**.
7. Optional: set **Advanced > Idle time** to `0` so the app is not stopped
   after a long period without traffic.
8. Save.

Test the health endpoint:

```text
https://<account>.alwaysdata.net/up
```

Then open the login page:

```text
https://<account>.alwaysdata.net/login
```

The `*.alwaysdata.net` wildcard certificate is handled by alwaysdata, so HTTPS
works without uploading a certificate.

---

## 8. Configure the Laravel scheduler

The app schedules `reminders:generate` daily at 07:00 Asia/Manila in
`routes/console.php`. Laravel needs `schedule:run` to be invoked every minute.

In alwaysdata:

1. Go to **Advanced > Scheduled tasks > Add**.
2. Type: **Command**
3. Value:

   ```bash
   php /home/<account>/capstone/barangay-ims/artisan schedule:run
   ```

4. Environment: leave the default SSH environment (PHP 8.3 is inherited from
   **Environment > PHP**).
5. Frequency: **Every minute**
6. Error email: your email address
7. Save.

Use the absolute `artisan` path. Do not use `@hourly`-style shortcuts;
alwaysdata does not accept them. Do not create a separate cron entry for
`reminders:generate`; the Laravel scheduler decides when it is due.

Test manually over SSH:

```bash
cd "$HOME/capstone/barangay-ims"
php artisan schedule:list
php artisan schedule:run
php artisan reminders:generate
```

Scheduled-task logs are written to:

```text
/home/<account>/admin/logs/jobs/
```

You can also view them in alwaysdata under **Logs**.

---

## 9. Verify the deployment

```bash
cd "$HOME/capstone/barangay-ims"

php artisan migrate:status
php artisan schedule:list
php artisan about
```

Check:

- `https://<account>.alwaysdata.net/up` returns a successful health response.
- You can log in with `admin@barangay.gov` and the seeded password.
- Creating a resident works.
- Generating a document PDF works.
- An export works.
- Uploading a logo works and the image is served from `/storage/...`.
- The daily scheduler appears in `schedule:list`.

Application logs:

```bash
tail -n 100 storage/logs/laravel.log
```

PHP and HTTP logs:

```bash
tail -n 100 "$HOME/admin/logs/php/php.log"
tail -n 100 "$HOME/admin/logs/http/"*
```

---

## 10. Deploying updates

After pushing changes to GitHub:

```bash
cd "$HOME/capstone"
git pull

cd "$HOME/capstone/barangay-ims"

COMPOSER_MEMORY_LIMIT=-1 \
COMPOSER_CACHE_DIR=/dev/null \
composer2 install --no-dev --optimize-autoloader

php artisan migrate --force
php artisan optimize:clear
php artisan optimize
```

Do **not** run `php artisan db:seed` again unless you intentionally want to
reset the seeded account passwords.

If `public/storage` is ever missing after an update:

```bash
php artisan storage:link --force
```

---

## Troubleshooting

### 500 error

```bash
cd "$HOME/capstone/barangay-ims"
tail -n 100 storage/logs/laravel.log
tail -n 100 "$HOME/admin/logs/php/php.log"
php artisan optimize:clear
```

### 403 / 404 / blank page

- The site path must be `capstone/barangay-ims/public`, not
  `capstone/barangay-ims`.
- `APP_URL` must match `https://<account>.alwaysdata.net`.
- Make sure **Force HTTPS** is enabled.

### Database connection failed

- `DB_HOST` must be `mysql-<account>.alwaysdata.net`, not `localhost`.
- Copy the exact database name and username from **Databases > MySQL**.
- Confirm the database user has **ALL PRIVILEGES** on that database.
- Run `php artisan config:clear` after changing `.env`.

### Composer runs out of memory

```bash
COMPOSER_MEMORY_LIMIT=-1 COMPOSER_CACHE_DIR=/dev/null composer2 install --no-dev --optimize-autoloader
```

If it still fails on the 256 MB free plan, run Composer locally with
`composer install --no-dev --optimize-autoloader`, upload the generated
`vendor/` directory over SFTP, and continue with the artisan commands on the
server.

### Permission denied writing cache, sessions, or logs

```bash
chmod -R u+rwX storage bootstrap/cache
```

Do not use `chown www-data` or `sudo` on alwaysdata.

### Login redirects in a loop or assets use `http://`

- Enable **Force HTTPS** for the site.
- Keep `SESSION_SECURE_COOKIE=true`.
- Clear the cache with `php artisan optimize:clear` and reload.
- If it still happens behind alwaysdata's proxy, add trusted proxies in
  `bootstrap/app.php`:

  ```php
  ->withMiddleware(function (Middleware $middleware) {
      $middleware->trustProxies(at: '*');

      // existing middleware configuration...
  })
  ```

  Then deploy and run `php artisan optimize`.

### Scheduler is not running

- The command must use the absolute path:
  `php /home/<account>/capstone/barangay-ims/artisan schedule:run`
- Confirm **Environment > PHP** is 8.3.
- Check `/home/<account>/admin/logs/jobs/`.
- Run `php artisan schedule:list` and `php artisan schedule:run` manually.
- Make sure the scheduled task is active.

### Disk quota is full

```bash
du -sh "$HOME"/* | sort -h
du -sh "$HOME/capstone/barangay-ims"/*
```

The free plan has 1 GB total, including the database, mail, and logs. Clear old
logs and avoid Composer cache files with `COMPOSER_CACHE_DIR=/dev/null`.
