# Pisti SaaS Deployment Checklist

> Use this checklist when deploying Pisti from local development to production.
> Domain: `happychurchruiru.org` | Server path: `/var/www/pisti`

---

## 1. Pre-Deployment

- [ ] Confirm production server meets requirements (PHP 8.1+, Composer, Nginx, MySQL/PostgreSQL, Redis optional)
- [ ] Ensure SSH access to production server
- [ ] Verify domain registrar / DNS provider access
- [ ] Confirm SSL certificate strategy (Let's Encrypt wildcard recommended)
- [ ] Backup any existing production database and `.env`

---

## 2. Upload Application

- [ ] Upload all project files to `/var/www/pisti` on the production server
- [ ] Ensure `.env` is present (copy from `.env.example` if needed)
- [ ] Exclude development-only files if uploading manually (`node_modules`, `.git`, local logs, etc.)

---

## 3. Run Deployment Script

```bash
ssh user@your-server
cd /var/www/pisti
chmod +x deploy-to-production.sh
./deploy-to-production.sh
```

- [ ] Execute `deploy-to-production.sh`
- [ ] Verify script completes without errors (dependencies, migrations, cache, storage)
- [ ] Review script output for any warnings

---

## 4. Configure Environment Variables

Edit `/var/www/pisti/.env` manually and confirm the following:

- [ ] `APP_ENV=production`
- [ ] `APP_DEBUG=false`
- [ ] `APP_URL=https://happychurchruiru.org`
- [ ] `APP_KEY` is set (run `php artisan key:generate --force` if blank)
- [ ] `SESSION_DOMAIN=.happychurchruiru.org` (leading dot is required)
- [ ] `SESSION_SECURE_COOKIE=true`
- [ ] Database credentials updated:
  - `DB_DATABASE=...`
  - `DB_USERNAME=...`
  - `DB_PASSWORD=...`
- [ ] Redis credentials updated (if using Redis):
  - `REDIS_HOST=...`
  - `REDIS_PASSWORD=...`
- [ ] Mail credentials updated:
  - `MAIL_HOST=...`
  - `MAIL_USERNAME=noreply@happychurchruiru.org`
  - `MAIL_PASSWORD=...`
  - `MAIL_ENCRYPTION=tls`
  - `MAIL_FROM_ADDRESS="noreply@happychurchruiru.org"`
- [ ] `QUEUE_CONNECTION=redis` (or desired driver)

---

## 5. Set File Permissions

```bash
sudo chown -R www-data:www-data /var/www/pisti
sudo chmod -R 775 /var/www/pisti/storage
sudo chmod -R 775 /var/www/pisti/bootstrap/cache
sudo mkdir -p /var/www/pisti/storage/app/tenants
sudo chmod -R 775 /var/www/pisti/storage/app/tenants
```

- [ ] Ownership set to `www-data:www-data`
- [ ] `storage` directory is writable
- [ ] `bootstrap/cache` directory is writable
- [ ] `storage/app/tenants` directory exists and is writable

---

## 6. DNS Configuration

Create the following **A records** pointing to your server IP:

| Type | Host         | Value         |
|------|--------------|---------------|
| A    | `@`          | Server IP     |
| A    | `www`        | Server IP     |
| A    | `superadmin` | Server IP     |
| A    | `*`          | Server IP     |

- [ ] Root domain (`@`) record created
- [ ] `www` record created
- [ ] `superadmin` record created
- [ ] Wildcard (`*`) record created (critical for tenant subdomains)
- [ ] DNS propagated (verify with `dig` or online DNS checker)

---

## 7. SSL Certificate

```bash
sudo apt update
sudo apt install certbot python3-certbot-nginx
sudo certbot certonly --manual --preferred-challenges dns \
  -d "*.happychurchruiru.org" \
  -d "happychurchruiru.org"
```

- [ ] Certbot installed
- [ ] Wildcard certificate obtained
- [ ] DNS TXT records added during Certbot challenge and verified
- [ ] Auto-renewal tested: `sudo certbot renew --dry-run`

---

## 8. Web Server (Nginx)

- [ ] Create site config at `/etc/nginx/sites-available/happychurchruiru.org`
- [ ] Enable site: `sudo ln -s /etc/nginx/sites-available/happychurchruiru.org /etc/nginx/sites-enabled/`
- [ ] Test config: `sudo nginx -t`
- [ ] Reload Nginx: `sudo systemctl reload nginx`
- [ ] Confirm HTTP → HTTPS redirect is active
- [ ] Confirm wildcard server_name covers `*.happychurchruiru.org`

---

## 9. Database & Application Cache

```bash
cd /var/www/pisti
php artisan migrate --force
php artisan db:seed --class=PlansSeeder --force
php artisan db:seed --class=DefaultTenantSeeder --force
php artisan tinker --execute="App\Models\Tenant::where('id', 1)->update(['domain' => 'happychurchruiru.org']);"
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

- [ ] Migrations executed successfully
- [ ] Essential seeders run (Plans, DefaultTenant)
- [ ] Tenant #1 domain updated to `happychurchruiru.org`
- [ ] Config, route, view, and event caches cleared and rebuilt

---

## 10. Background Workers

### Queue Workers (Supervisor)

Create `/etc/supervisor/conf.d/pisti-worker.conf`:

```ini
[program:pisti-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/pisti/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/pisti/storage/logs/worker.log
stopwaitsecs=3600
```

- [ ] Supervisor config created
- [ ] `sudo supervisorctl reread && sudo supervisorctl update`
- [ ] `sudo supervisorctl start pisti-worker:*`
- [ ] Worker log file exists and shows no immediate errors

### Laravel Horizon (Optional but Recommended)

Create `/etc/supervisor/conf.d/pisti-horizon.conf`:

```ini
[program:pisti-horizon]
process_name=%(program_name)s
command=php /var/www/pisti/artisan horizon
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/var/www/pisti/storage/logs/horizon.log
stopwaitsecs=3600
```

- [ ] Horizon config created (if used)
- [ ] `sudo supervisorctl start pisti-horizon`

### Scheduler (Cron)

```bash
sudo crontab -e
```

Add:
```
* * * * * cd /var/www/pisti && php artisan schedule:run >> /dev/null 2>&1
```

- [ ] Cron job added and active

---

## 11. Post-Deployment Verification

### Core URLs

- [ ] `https://happychurchruiru.org` loads the default tenant site
- [ ] `https://happychurchruiru.org/login` displays the login page
- [ ] Login with tenant admin credentials succeeds
- [ ] `https://happychurchruiru.org/dashboard/home` loads the dashboard
- [ ] `https://superadmin.happychurchruiru.org/login` displays superadmin login
- [ ] Superadmin login succeeds

### Subdomain & SSL

- [ ] `http://happychurchruiru.org` redirects to `https://`
- [ ] SSL certificate is valid for both root domain and wildcard subdomains
- [ ] No mixed-content warnings in browser dev tools
- [ ] Create a test tenant and verify `https://{slug}.happychurchruiru.org` resolves

### Functionality

- [ ] User login / logout works across subdomains
- [ ] Session persists across subdomains
- [ ] File uploads succeed (profile photos, documents)
- [ ] Reports generate correctly
- [ ] SMS sending works (if configured)
- [ ] Finance module loads (if enabled for tenant)
- [ ] Email queue processes (check `storage/logs/worker.log`)

---

## 12. Security Hardening

- [ ] Change default superadmin password immediately
  - Login: `https://superadmin.happychurchruiru.org/login`
  - Default email: `admin@pisti.co.ke`
  - Default password: `Change-Me-Now-2024!`
- [ ] Delete or disable default superadmin account after creating a new one
- [ ] Ensure `.env`, `.git`, and log files are not web-accessible
- [ ] Review Nginx config for `deny all` on hidden files (`~ /\.ht`)
- [ ] Confirm `APP_DEBUG=false` in production `.env`
- [ ] Enable firewall rules (allow 80, 443; block unused ports)
- [ ] Enable automatic security updates on the server (unattended-upgrades)

---

## 13. Rollback Plan

- [ ] Database dump saved before migration step
- [ ] Previous release directory or backup available
- [ ] `.env.backup.*` file exists (created by deploy script)
- [ ] Team knows how to restore database and revert Nginx config quickly

---

## Sign-Off

| Role | Name | Date | Signature |
|------|------|------|-----------|
| Deployed By |      |      |           |
| Verified By |      |      |           |

---

**References**
- Full guide: `DEPLOYMENT_GUIDE.md`
- Quick checklist: `LOCAL_TO_PRODUCTION_CHECKLIST.md`
- Deploy script: `deploy-to-production.sh`
