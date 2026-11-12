# Deploy Barangay IMS to InfinityFree (Free plan)

InfinityFree is free shared hosting. It differs from a normal VPS in ways that
matter for Laravel, so read this before starting:

- **No SSH and no CLI.** You cannot run `php artisan`, `composer`, or `git` on
  the server. Build the deployment package on your own machine and upload it.
- **No Composer on the server.** `vendor/` must be built locally and uploaded.
- **No cron jobs.** The daily reminder job is triggered through a
  token-protected web route that an external cron service (cron-job.org) calls.
- **No symlinks.** `php artisan storage:link` fails. The app ships a
  `PUBLIC_DISK_IN_WEBROOT` disk option that solves this; see step 5.
- **Remote MySQL is generally unavailable.** Migrations are applied through the
  temporary setup script in `deploy/infinityfree-setup.php`, not phpMyAdmin.
- Free plans have **execution-time and entry-process limits** (about 50,000
  hits/day). Laravel is heavier per request than a plain PHP app; it is fine for
  a capstone demonstration, but expect slower responses than local.

This guide assumes:

- GitHub repo: `https://github.com/kielscofield11-gif/capstone.git`
- App directory on the server: `/home/<account>/barangay-ims`
- Public subdomain: `https://<subdomain>.infinityfreeapp.com`

Replace every `<...>` placeholder before running a command.

---

## 0. Push the code you want to deploy

Nothing here runs from your working tree; uploads are built from the local
folder, so commit first so you deploy a known state:

```bash
git status
git add .
git commit -m "Prepare InfinityFree deployment"
git push origin main
```

## 1. Build the deployment package locally

On your machine, in `barangay-ims/`:

```bash
composer install --no-dev --optimize-autoloader
php artisan key:generate
```

Copy the generated `APP_KEY` (the `base64:...` line) — it goes into the server
`.env`. Then **clear every cache** and confirm none remain:

```bash
php artisan view:clear
php artisan config:clear
php artisan route:clear
```

Cached files (`bootstrap/cache/config.php`, `bootstrap/cache/routes-*.php`,
compiled views in `storage/framework/views/`) contain absolute paths from your
machine. Uploading them breaks the app on the server, and you cannot regenerate
them there without a CLI. They must be gone before packaging.

Exclude these from the archive: `.git/`, local `.env`, `node_modules/`, and
`storage/logs/*`. Create two archives (FileZilla-friendly names):

- `barangay-ims.zip` — the whole application directory, including `vendor/`,
  with `public/` still inside it
- `public-content.zip` — the contents of `public/` only

> If your archive is too large for the InfinityFree file manager (roughly 50 MB
> limit per upload on free plans), split it or upload with FileZilla instead.

## 2. Create the site

1. Sign up at <https://www.infinityfree.com> and add a hosting account.
2. In the control panel (vPanel), create a **free subdomain**, e.g.
   `<subdomain>.infinityfreeapp.com`.
3. Open the site's **PHP settings** and choose **PHP 8.3**.
4. Check the enabled extensions. Open a temporary `phpinfo()` page, or use the
   extension list in vPanel, and confirm these are present:

   ```text
   dom, fileinfo, gd, mbstring, openssl, xml, zip, zlib, pdo_mysql
   ```

   `gd` and `zip` are required for document PDFs (dompdf) and Excel exports
   (phpspreadsheet). If one is missing, enable it in vPanel and rebuild.

## 3. Create the MySQL database

1. In vPanel, open **MySQL Databases** and create a database, e.g.
   `barangay_ims`. InfinityFree prefixes names, so note the final values
   exactly as shown, e.g.:

   ```text
   Database:  if0_<account>_barangay_ims
   User:      if0_<account>
   Password:  the one you set
   ```

2. Copy the **MySQL hostname** shown on the same page, e.g.
   `sql###.infinityfree.com`. It is **not** `localhost`.

## 4. Upload and place the files

The web root is `htdocs/`. The rest of the application must sit **outside**
`htdocs/`, otherwise `app/`, `.env`, and `vendor/` would be downloadable over
HTTP.

Connect with FileZilla (FTP credentials are in vPanel → **FTP Accounts**):

1. Upload `barangay-ims.zip` to the account root, then extract it so the
   application lives at `/home/<account>/barangay-ims` (a sibling of `htdocs/`,
   not inside it).
2. Upload `public-content.zip` **into `htdocs/`** and extract it there, so
   `htdocs/` contains `index.php`, `.htaccess`, `images/`, and `favicon.ico`
   directly.
3. If the vPanel file manager cannot see outside `htdocs/`, do both uploads
   over FTP with FileZilla instead; the layout above is what matters.

3. Edit `htdocs/index.php` so it boots the application one level up. Change
   the two paths at the bottom of the file (they normally point at `__DIR__`):

   ```php
   require __DIR__.'/../barangay-ims/vendor/autoload.php';

   $app = require_once __DIR__.'/../barangay-ims/bootstrap/app.php';
   ```

   Keep every other line unchanged.

4. Upload `public/.htaccess` to `htdocs/` **unchanged** — it is the Laravel
   front-controller rewrite file that routes every request to `index.php`
   (without it, only `/` works and all other pages 404). It is a dotfile, so
   enable *Server → Force showing hidden files* in FileZilla and confirm it
   landed. The shipped version already has the `Options` line commented out
   (shared hosts can reject it) and sets `DirectoryIndex index.php`. If the
   host left a `default.html` in `htdocs/`, delete it.

## 5. Create the production `.env`

Copy `.env.production.example` from your local folder to
`/home/<account>/barangay-ims/.env` on the server (next to `artisan`, **not**
inside `htdocs/`) and set these values:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://<subdomain>.infinityfreeapp.com
BARANGAY_NAME="Barangay Concepcion"

DB_CONNECTION=mysql
DB_HOST=sql###.infinityfree.com
DB_PORT=3306
DB_DATABASE=if0_<account>_barangay_ims
DB_USERNAME=if0_<account>
DB_PASSWORD="<database-password>"

# Keep true because the site is served over HTTPS.
SESSION_SECURE_COOKIE=true

# No SMTP on the free plan: mail is written to storage/logs instead.
MAIL_MAILER=log
MAIL_FROM_ADDRESS="no-reply@<subdomain>.infinityfreeapp.com"

# InfinityFree blocks symlinks, so storage:link fails. This makes the public
# disk write to a real public/storage folder inside htdocs instead.
PUBLIC_DISK_IN_WEBROOT=true

# One-time passwords for the initial accounts. Remove them after seeding.
SEED_ADMIN_PASSWORD="<strong-admin-password>"
SEED_SECRETARY_PASSWORD="<strong-secretary-password>"
SEED_CAPTAIN_PASSWORD="<strong-captain-password>"
SEED_KAGAWAD_PASSWORD="<strong-kagawad-password>"
SEED_STAFF_PASSWORD="<strong-staff-password>"
SEED_MOCK_DATA=false
ALLOW_MOCK_DATA_SEEDING=false

# Temporary deployment helper tokens. Generate long random values (for example
# `openssl rand -hex 32` or a password manager). Both endpoints are disabled
# while these are empty.
SETUP_TOKEN="<long-random-setup-token>"
SCHEDULER_TOKEN="<long-random-scheduler-token>"
```

Paste the `APP_KEY` you generated in step 1. Do not commit this file.

## 6. Run migrations and the one-time seeder

There is no CLI, so use the temporary setup runner:

1. Copy `deploy/infinityfree-setup.php` from the repo into the web root:
   `htdocs/setup.php`.
2. Open:

   ```text
   https://<subdomain>.infinityfreeapp.com/setup.php?token=<SETUP_TOKEN>
   ```

   You should see plain-text output ending with `Running migrations (migrate --force)...`
   followed by the list of applied migrations.
3. **First deployment only:** reload with `&seed=1` appended to create the
   initial accounts (`admin@barangay.gov`, `secretary@barangay.gov`,
   `captain@barangay.gov`, `kagawad@barangay.gov`, `staff@barangay.gov`) with
   the `SEED_*` passwords.
4. **Delete `htdocs/setup.php now.** Anyone with the URL can re-run the seeder
   and reset every account password.
5. Remove the five `SEED_*_PASSWORD` lines from `.env` (keep
   `SEED_MOCK_DATA=false`).

> If the request times out (free plans cap execution time), reload the page:
> pending migrations resume and the seeder uses `updateOrCreate`.

Do not run the seeder on later deployments: it resets the seeded passwords
every time.

## 7. Permissions

InfinityFree runs PHP as your own user, so no `chown` is needed. With FileZilla,
set permissions to `755` (recursive) on:

```text
barangay-ims/storage
barangay-ims/bootstrap/cache
```

`public/storage` is created automatically on the first upload, but it needs a
writable parent: keep `htdocs` at `755`. Verify with the logo upload in step 9;
if it fails, set `htdocs/storage` to `755` or `775`.

## 8. HTTPS and the health endpoint

1. In vPanel, open **SSL/TLS** and request the free certificate for the
   subdomain (takes a few minutes while DNS propagates).
2. Enable **Force HTTPS** for the site.
3. Visit:

   ```text
   https://<subdomain>.infinityfreeapp.com/up
   ```

   A successful response (HTTP 200 with `{"status":"ok"}`-style output) means
   the framework booted, `.env` and paths are correct.

Then log in at `/login` with `admin@barangay.gov` and the seeded password.

## 9. Scheduler (reminders) via external cron

InfinityFree free plans have no cron. The app exposes
`GET /scheduler/run?token=<SCHEDULER_TOKEN>` (plain text, runs
`schedule:run`). Point an external cron service at it:

1. Sign up at <https://cron-job.org> (free) and create a job.
2. URL:

   ```text
   https://<subdomain>.infinityfreeapp.com/scheduler/run?token=<SCHEDULER_TOKEN>
   ```

3. Schedule: every 1 minute (the Laravel scheduler decides when
   `reminders:generate` is actually due — 07:00 Asia/Manila).
4. Set the request timeout to at least 30 seconds.

Without this job, reminders are never generated; everything else in the app
works normally. To stop using the endpoint later, clear `SCHEDULER_TOKEN` in
`.env` (the route then returns 404 permanently).

## 10. Verify the deployment

- `GET /up` returns a successful health response.
- Log in as admin; the dashboard renders with charts.
- Create a resident, then a document request; approve it and mark it released.
- Generate a document PDF (`Preview Document` / `Download PDF`) — this proves
  dompdf and the `gd` extension work.
- Run an export (`/exports/residents/excel`) — this proves phpspreadsheet and
  the `zip` extension work.
- Upload a barangay logo in **Document Templates** and reload the page: the
  image loads from `/storage/...`, proving `PUBLIC_DISK_IN_WEBROOT` and the
  writable `public/storage` folder.
- Confirm reminders appear after 07:00 Manila time once the cron job runs
  (or leave this out if you skipped step 9).

Application log (the only one available without SSH):

- FileZilla → `barangay-ims/storage/logs/laravel.log`, or the vPanel **Error
  Log** section.

## 11. Deploying updates

1. Pull and rebuild locally, clear caches again (step 1).
2. Re-zip, upload, extract over the existing files.
3. If the update added migrations, copy `deploy/infinityfree-setup.php` to
   `htdocs/setup.php`, open it with the `SETUP_TOKEN`, and delete it again.
4. Never run the seeder here unless you intend to reset the account passwords.

## Troubleshooting

### 500 error or blank page

- Read `barangay-ims/storage/logs/laravel.log`, or the vPanel **Error Log**.
- The most common cause is a stale cache file uploaded from your machine
  (`bootstrap/cache/*.php`, compiled views). Delete everything in
  `bootstrap/cache/` except `.gitignore`, and everything in
  `storage/framework/views/`.
- Confirm `htdocs/index.php` points at `__DIR__.'/../barangay-ims/...'`.

### 404 on every page except the homepage

- `.htaccess` did not transfer. It is a dotfile: enable *Force showing hidden
  files* in FileZilla and re-upload `public/.htaccess` into `htdocs/`. It is
  required to route requests into `index.php`.
- A `default.html` from the host is shadowing the app: delete it (the shipped
  `.htaccess` sets `DirectoryIndex index.php`, but the stale page still gets
  served on some setups).
- `RewriteBase` is missing because the site was installed in a subdirectory —
  not the case for the standard layout in step 4.

### Branded InfinityFree "403 Forbidden" page

If the error page shows the InfinityFree logo, it is served by their proxy
before Apache reads your files. Nothing in your upload causes it. Usual
causes, in order:

- `htdocs/` has no index document yet (upload not finished) — confirm
  `htdocs/index.php` exists in the file manager.
- The subdomain is not activated yet (new sites take minutes), the account
  email is not verified, or the site is suspended (check the client area).
- SSL was requested but has not been issued yet: browse over `http://` until
  the certificate shows as active, then switch to `https://`.
- Visiting the wrong host (the panel URL or an IP) instead of the assigned
  `<subdomain>.infinityfreeapp.com`.

### 500 error immediately after uploading .htaccess

- The `Options -MultiViews -Indexes` line is active and the host rejects
  `Options` in `.htaccess`. The shipped file has it commented out — keep it
  that way; nothing is lost (directory listing is off by default).

### 403/404 for setup.php or scheduler route

- Wrong token: the endpoints compare tokens byte-for-byte and return 404 on
  any mismatch. Regenerate the token in `.env` and retry.
- `SETUP_TOKEN` or `SCHEDULER_TOKEN` empty in `.env`: the endpoints stay
  disabled by design.

### Database connection failed

- `DB_HOST` must be the InfinityFree hostname (`sql###.infinityfree.com`), not
  `localhost` or `127.0.0.1`.
- Database name and username include the `if0_` prefix — copy them from
  **MySQL Databases** exactly.
- Quote the password in `.env` if it contains `#`, `$`, or spaces.

### PDF or Excel export fails

- `gd` or `zip` is not enabled for the site. Enable both in vPanel → PHP
  extensions, then retry the export.

### Logo or resident photo returns 404 under /storage

- `PUBLIC_DISK_IN_WEBROOT` is not `true` in `.env`, so the app still writes to
  `storage/app/public`, which is outside `htdocs`.
- Or `htdocs/storage` is not writable: set it to `755`/`775` via FileZilla.

### 508 "resource limit reached" errors

- Free plans cap CPU and concurrent PHP processes. Wait a minute and reload.
- Keep `APP_DEBUG=false` (debug pages are slower and leak paths), and avoid
  hammering exports on the free plan during a demo. There is no way to run
  `php artisan optimize` here, since that needs a CLI.

### Login redirects in a loop, or assets load over http://

- Enable **Force HTTPS** in vPanel.
- Keep `SESSION_SECURE_COOKIE=true` and `APP_URL=https://...`.
- If the app sits behind another proxy/CDN in front of InfinityFree, add
  trusted proxies in `bootstrap/app.php`:

  ```php
  ->withMiddleware(function (Middleware $middleware) {
      $middleware->trustProxies(at: '*');
  })
  ```

  Then re-upload `bootstrap/app.php` and clear caches.

### Reminders never appear

- The external cron job is not hitting `/scheduler/run` (check its "Last run"
  in cron-job.org), the token is wrong, or `SCHEDULER_TOKEN` was cleared.
- The site must receive at least that one request per minute — an idle free
  site is stopped between requests, which is normal.

### Disk or file-manager limits

- Free plans cap upload size per file in the file manager. Upload the two
  archives with FileZilla instead of the browser, and delete the archives from
  the server after extracting them.
