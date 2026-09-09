# Banglay Chinese — Deployment Guide

## Requirements

- **PHP 8.3+** (with extensions: `bcmath`, `ctype`, `curl`, `fileinfo`, `gd`, `json`, `mbstring`, `openssl`, `pdo`, `tokenizer`, `xml`)
- **Composer 2.x**
- **Node.js `^20.19` or `>=22.12`** and npm (required by Vite 8)
- **MySQL 8.0** / PostgreSQL 15 / SQLite (for local dev)
- **Nginx** or Apache (Nginx config provided below)

---

## Step 1 — Clone & Install

```bash
git clone https://github.com/NaymsTech/banglaychinese.git
cd banglaychinese

# Install PHP dependencies (production — no dev packages)
composer install --no-dev --optimize-autoloader

# Install & build frontend assets
npm install
npm run build
```

---

## Step 2 — Environment Setup

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` with your production values:

| Key | Production Value |
|-----|-----------------|
| `APP_NAME` | BanglayChinese |
| `APP_ENV` | production |
| `APP_DEBUG` | false |
| `APP_URL` | https://yourdomain.com |
| `ADMIN_PASSWORD` | Set a strong password; required by `AdminUserSeeder` when seeding in production |
| `DB_CONNECTION` | mysql (or pgsql) |
| `DB_HOST` / `DB_PORT` / `DB_DATABASE` / `DB_USERNAME` / `DB_PASSWORD` | Your database credentials |
| `SESSION_SECURE_COOKIE` | true |
| `LOG_LEVEL` | error (recommended: `error` or `warning`) |
| `MAIL_*` (Brevo SMTP) | Used **only** by `EmailSystemSeeder` to create the initial encrypted "Brevo SMTP" provider row (see `.env.example`) |

---

## Step 3 — Database Setup

```bash
php artisan migrate
php artisan db:seed --force
```

Production seeding is environment-aware and safe (`--force` is required in
production). It seeds ONLY essential production data:

- `SettingsSeeder` — site settings incl. confirmed contact values (bKash/Nagad `01774148708`, WhatsApp `+86 182-2324-9514`, address `Chongqing, China`)
- `AdminUserSeeder` — production admin (`ADMIN_PASSWORD` env required; seeding aborts without it)
- `BanglayChineseSeeder` — the real course & category catalog
- `ServiceSeeder` — Study-in-China service packages
- `AboutPageSeeder` — about-page content
- `EmailSystemSeeder` — email branding, the initial Brevo provider, and the 11 email templates

No demo users, customers, orders, payments, dummy resources or test content
are created in production. Demo/test seeders (e.g. `FreeResourceSeeder`) only
run under `app()->isLocal()`.

---

## Step 4 — Production Optimization

```bash
# Cache routes & config
php artisan optimize

# Create storage symlink (for uploaded files). Must be run ON the server after
# deployment — the symlink is git-ignored and must never be committed.
php artisan storage:link
```

---

## Step 5 — Nginx Configuration

```nginx
server {
    listen 80;
    server_name banglaychinese.com www.banglaychinese.com;
    root /var/www/banglaychinese/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

After saving, test and reload Nginx:

```bash
nginx -t
systemctl reload nginx
```

---

## Step 6 — SSL (Let's Encrypt)

```bash
# Install certbot (Ubuntu/Debian)
apt install certbot python3-certbot-nginx

# Get certificate
certbot --nginx -d banglaychinese.com -d www.banglaychinese.com

# Auto-renewal test
certbot renew --dry-run
```

The certbot Nginx plugin will automatically update your Nginx config to force HTTPS.

---

## Step 7 — File Permissions

```bash
chown -R www-data:www-data /var/www/banglaychinese
chmod -R 755 /var/www/banglaychinese
chmod -R 775 /var/www/banglaychinese/storage
chmod -R 775 /var/www/banglaychinese/bootstrap/cache
```

---

## Step 8 — Queue & Scheduled Tasks (Hostinger)

Hostinger shared hosting has no persistent process daemon, so the database
email queue cannot rely on a long-running `queue:work --daemon`. Instead the
application runs a short-lived queue worker from Laravel's scheduler
(`routes/console.php`) every minute: it processes the `database` queue, stops
when the queue is empty, never overlaps itself, and preserves each job's own
3-attempt retry behaviour.

Add a **single cron job** in Hostinger (Cron Jobs) that runs every minute:

```bash
* * * * * php /path/to/artisan schedule:run >> /dev/null 2>&1
```

Use the absolute path to the `artisan` file on your account (e.g.
`/home/USER/domains/DOMAIN/public_html/artisan`). This one cron keeps both the
scheduled commands (course payment reminders) and the queued email worker
running. Verify with `php artisan schedule:list`, then send a test email from
Filament (Email Templates → Send Test) and confirm the EmailLog moves from
`queued` to `sent`.

---

## Post-Deployment Checklist

- [ ] `.env` has `APP_DEBUG=false` and `APP_ENV=production`
- [ ] `SESSION_SECURE_COOKIE=true` (only if using HTTPS)
- [ ] Database is migrated and seeded
- [ ] `php artisan optimize` has been run
- [ ] `php artisan storage:link` creates the symlink
- [ ] Nginx config is active and tested
- [ ] SSL certificate is valid and auto-renewal is configured
- [ ] Visit `/` — homepage loads correctly
- [ ] Visit `/contact` — contact form stores the message and sends the acknowledgement email
- [ ] Visit `/courses` — course listings display
- [ ] Visit `/blog` — blog index works
- [ ] Visit `/about` — about page loads
- [ ] Visit `/study-in-china` — Study in China page loads
- [ ] 404 page shows the application error page for invalid URLs
- [ ] Admin panel is accessible at `/admin` to admin users
- [ ] Rate limiting triggers Bengali message on excessive submissions
- [ ] Hostinger cron runs `php artisan schedule:run` every minute (see Step 8) so queued email jobs are processed automatically

---

## Updating After Deployment

```bash
git pull origin main
composer install --no-dev --optimize-autoloader
npm install && npm run build
php artisan migrate
php artisan optimize
```

---

## Troubleshooting

| Issue | Fix |
|-------|-----|
| 500 error after deploy | Check `storage/logs/laravel.log`. Run `php artisan optimize:clear` then `php artisan optimize`. |
| CSS/JS not loading | Run `npm run build`. Check that `public/build/` exists. |
| Database errors | Verify `.env` DB credentials. Run `php artisan migrate:status`. |
| Permission denied on storage | `chmod -R 775 storage bootstrap/cache` |
| HTTPS not redirecting | Ensure certbot Nginx plugin updated the config. |
