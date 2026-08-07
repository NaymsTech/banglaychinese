# Banglay Chinese — Deployment Guide

## Requirements

- **PHP 8.3+** (with extensions: `bcmath`, `ctype`, `curl`, `fileinfo`, `gd`, `json`, `mbstring`, `openssl`, `pdo`, `tokenizer`, `xml`)
- **Composer 2.x**
- **Node.js 18+** and npm
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
| `DB_CONNECTION` | mysql (or pgsql) |
| `DB_HOST` / `DB_PORT` / `DB_DATABASE` / `DB_USERNAME` / `DB_PASSWORD` | Your database credentials |
| `SESSION_SECURE_COOKIE` | true |
| `LOG_LEVEL` | error (recommended: `error` or `warning`) |

---

## Step 3 — Database Setup

```bash
php artisan migrate
php artisan db:seed
```

This seeds:
- `SettingsSeeder` — default site settings (site name, WhatsApp, email, hero text, stats)
- `AdminUserSeeder` — admin user (check `database/seeders/AdminUserSeeder.php` for credentials)
- `BanglayChineseSeeder` — sample courses, lessons, blog posts

---

## Step 4 — Production Optimization

```bash
# Cache routes & config
php artisan optimize

# Create storage symlink (for uploaded files)
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

## Post-Deployment Checklist

- [ ] `.env` has `APP_DEBUG=false` and `APP_ENV=production`
- [ ] `SESSION_SECURE_COOKIE=true` (only if using HTTPS)
- [ ] Database is migrated and seeded
- [ ] `php artisan optimize` has been run
- [ ] `php artisan storage:link` creates the symlink
- [ ] Nginx config is active and tested
- [ ] SSL certificate is valid and auto-renewal is configured
- [ ] Visit `/` — homepage loads correctly
- [ ] Visit `/contact` — contact form works and redirects to WhatsApp
- [ ] Visit `/courses` — course listings display
- [ ] Visit `/blog` — blog index works
- [ ] Visit `/about` — about page loads
- [ ] Visit `/scholarship` — scholarship page loads
- [ ] 404 page shows Bengali error text for invalid URLs
- [ ] Admin login works at `/login`
- [ ] Admin dashboard at `/admin` is accessible to admin users
- [ ] Rate limiting triggers Bengali message on excessive submissions
- [ ] Queue worker is running (if using queues): `php artisan queue:work --daemon`

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
