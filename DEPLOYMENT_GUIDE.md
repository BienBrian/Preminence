# Pisti SaaS Deployment Guide

> **Platform:** Pisti Church Management SaaS  
> **Production Domain:** `happychurchruiru.org`  
> **Document Version:** 1.0

---

## 📋 Table of Contents

1. [Local Development Setup](#local-development-setup)
2. [Production Subdomains Required](#production-subdomains-required)
3. [DNS Configuration](#dns-configuration)
4. [Production Deployment Steps](#production-deployment-steps)
5. [SSL Certificate Setup](#ssl-certificate-setup)
6. [Post-Deployment Checklist](#post-deployment-checklist)

---

## 🖥️ Local Development Setup

### Current Configuration (Already Done)

Your `.env` file is configured for localhost development:

```env
APP_URL=http://127.0.0.1:8000
SESSION_DOMAIN=
```

### Testing on Localhost

When running locally (`php artisan serve`), the app will:

1. **Default to Tenant #1** (Happy Church Ruiru) automatically
2. Allow **query parameter override** to test different tenants:
   ```
   http://127.0.0.1:8000/dashboard/home?__tenant=happychurch-ruiru
   ```

3. **Superadmin access** works directly:
   ```
   http://127.0.0.1:8000/superadmin/login
   ```

### Accessing Different Tenants Locally

| Tenant | URL |
|--------|-----|
| Happy Church Ruiru (default) | `http://127.0.0.1:8000/login` |
| Happy Church Ruiru (explicit) | `http://127.0.0.1:8000/login?__tenant=happychurch-ruiru` |
| Superadmin Panel | `http://127.0.0.1:8000/superadmin/login` |

---

## 🌐 Production Subdomains Required

When deploying to production with domain `happychurchruiru.org`, you need to create the following DNS records:

### Required DNS Records (A Records)

| Subdomain | Purpose | Points To |
|-----------|---------|-----------|
| `@` (root) | Main church website / Default tenant | Your server IP |
| `www` | Marketing site / WWW redirect | Your server IP |
| `admin` or `superadmin` | Superadmin panel access | Your server IP |
| `*` (wildcard) | ALL tenant subdomains | Your server IP |

### Example DNS Configuration

```
Type    Host                Value                TTL
─────────────────────────────────────────────────────────
A       @                   192.168.1.100        3600
A       www                 192.168.1.100        3600
A       superadmin          192.168.1.100        3600
A       *                   192.168.1.100        3600
```

> **Note:** Replace `192.168.1.100` with your actual server IP address.

### Tenant Subdomains (Auto-Created)

When churches sign up, their subdomains will be automatically generated:

| Church Name | Slug | Subdomain |
|-------------|------|-----------|
| Happy Church Ruiru | happychurch-ruiru | `happychurch-ruiru.happychurchruiru.org` |
| Grace Community Church | grace-community | `grace-community.happychurchruiru.org` |
| Victory Baptist Church | victory-baptist | `victory-baptist.happychurchruiru.org` |

---

## 🔧 Production Deployment Steps

### Step 1: Update Environment File

Edit `.env` file on production server:

```bash
# Change from local to production
APP_ENV=production
APP_DEBUG=false
APP_URL=https://happychurchruiru.org

# Session domain for subdomain sharing
SESSION_DOMAIN=.happychurchruiru.org
SESSION_SECURE_COOKIE=true

# Database (update with production credentials)
DB_HOST=127.0.0.1
DB_DATABASE=happychurchruiru_production
DB_USERNAME=your_db_user
DB_PASSWORD=your_secure_password

# Redis (if using external Redis)
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=your_redis_password

# Queue
QUEUE_CONNECTION=redis

# Mail (update with production mail settings)
MAIL_MAILER=smtp
MAIL_HOST=smtp.yourhost.com
MAIL_PORT=587
MAIL_USERNAME=noreply@happychurchruiru.org
MAIL_PASSWORD=your_mail_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@happychurchruiru.org"
MAIL_FROM_NAME="${APP_NAME}"
```

### Step 2: Configure Nginx

Create `/etc/nginx/sites-available/happychurchruiru.org`:

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name happychurchruiru.org www.happychurchruiru.org superadmin.happychurchruiru.org *.happychurchruiru.org;
    root /var/www/pisti/public;
    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
    }

    location ~ /\.ht {
        deny all;
    }
}
```

Enable the site:
```bash
sudo ln -s /etc/nginx/sites-available/happychurchruiru.org /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### Step 3: Set Proper Permissions

```bash
# Set ownership
sudo chown -R www-data:www-data /var/www/pisti

# Storage permissions
sudo chmod -R 775 /var/www/pisti/storage
sudo chmod -R 775 /var/www/pisti/bootstrap/cache

# Create tenant storage directory
sudo mkdir -p /var/www/pisti/storage/app/tenants
sudo chmod -R 775 /var/www/pisti/storage/app/tenants
```

### Step 4: Run Migrations and Seeders

```bash
cd /var/www/pisti

# Install dependencies (production optimized)
composer install --optimize-autoloader --no-dev

# Run migrations
php artisan migrate --force

# Seed plans
php artisan db:seed --class=PlansSeeder

# Seed default tenant (if not already done)
php artisan db:seed --class=DefaultTenantSeeder

# Update tenant #1 domain
php artisan tinker --execute="App\Models\Tenant::where('id', 1)->update(['domain' => 'happychurchruiru.org']);"

# Clear all caches
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

### Step 5: Set Up Scheduler (Cron)

```bash
sudo crontab -e
```

Add this line:
```
* * * * * cd /var/www/pisti && php artisan schedule:run >> /dev/null 2>&1
```

### Step 6: Set Up Queue Worker (Supervisor)

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

Enable:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start pisti-worker:*
```

### Step 7: Configure Horizon (Optional but Recommended)

```bash
# Publish Horizon assets
php artisan horizon:publish

# Create supervisor config for Horizon
sudo nano /etc/supervisor/conf.d/pisti-horizon.conf
```

Add:
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

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start pisti-horizon
```

---

## 🔒 SSL Certificate Setup (Let's Encrypt)

### Install Certbot

```bash
sudo apt update
sudo apt install certbot python3-certbot-nginx
```

### Obtain Wildcard Certificate

For wildcard certificates, use DNS challenge:

```bash
sudo certbot certonly --manual --preferred-challenges dns -d "*.happychurchruiru.org" -d "happychurchruiru.org"
```

**During the process, Certbot will ask you to create DNS TXT records.**

### Update Nginx for SSL

Edit your Nginx config:

```nginx
server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name happychurchruiru.org www.happychurchruiru.org superadmin.happychurchruiru.org *.happychurchruiru.org;
    root /var/www/pisti/public;
    index index.php;

    # SSL Certificates
    ssl_certificate /etc/letsencrypt/live/happychurchruiru.org/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/happychurchruiru.org/privkey.pem;

    # SSL Configuration
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    ssl_prefer_server_ciphers on;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
    }

    location ~ /\.ht {
        deny all;
    }
}

# Redirect HTTP to HTTPS
server {
    listen 80;
    listen [::]:80;
    server_name happychurchruiru.org www.happychurchruiru.org superadmin.happychurchruiru.org *.happychurchruiru.org;
    return 301 https://$host$request_uri;
}
```

Test and reload:
```bash
sudo nginx -t
sudo systemctl reload nginx
```

### Auto-Renewal

Test auto-renewal:
```bash
sudo certbot renew --dry-run
```

---

## ✅ Post-Deployment Checklist

### Immediate Tests

- [ ] Visit `https://happychurchruiru.org` - Should show Happy Church Ruiru website
- [ ] Visit `https://happychurchruiru.org/login` - Should show login page
- [ ] Login with Jay's credentials - Should access dashboard
- [ ] Visit `https://happychurchruiru.org/dashboard/home` - Should show dashboard
- [ ] Visit `https://superadmin.happychurchruiru.org/login` - Should show superadmin login
- [ ] Login to superadmin - Should access admin panel

### Subdomain Tests

Create a test tenant via superadmin, then:
- [ ] Visit `https://{tenant-slug}.happychurchruiru.org` - Should show tenant website
- [ ] Visit `https://{tenant-slug}.happychurchruiru.org/login` - Should work

### SSL Tests

- [ ] All URLs redirect HTTP to HTTPS
- [ ] SSL certificate is valid
- [ ] No mixed content warnings

### Functionality Tests

- [ ] User login/logout works
- [ ] Finance module accessible (if enabled)
- [ ] SMS sending works (if configured)
- [ ] File uploads work
- [ ] Reports generate correctly

---

## 🚨 Troubleshooting

### Issue: "Church not found" error

**Cause:** Tenant not found for subdomain

**Solution:**
```bash
# Check tenant exists
php artisan tinker --execute="print_r(App\Models\Tenant::all()->toArray());"

# Check slug matches subdomain
# happychurch-ruiru.happychurchruiru.org requires tenant with slug 'happychurch-ruiru'
```

### Issue: Session not shared across subdomains

**Cause:** SESSION_DOMAIN not set correctly

**Solution:**
```bash
# In .env, must have leading dot
SESSION_DOMAIN=.happychurchruiru.org

# Clear config cache
php artisan config:cache
```

### Issue: SSL certificate not covering subdomains

**Cause:** Certificate doesn't include wildcard

**Solution:**
```bash
# Re-issue with both domains
sudo certbot certonly --manual --preferred-challenges dns \
  -d "*.happychurchruiru.org" \
  -d "happychurchruiru.org"
```

### Issue: Permissions denied on storage

**Solution:**
```bash
sudo chown -R www-data:www-data /var/www/pisti/storage
sudo chmod -R 775 /var/www/pisti/storage
```

---

## 📞 Support

If you encounter issues:

1. Check logs: `tail -f /var/www/pisti/storage/logs/laravel.log`
2. Check Nginx error log: `sudo tail -f /var/log/nginx/error.log`
3. Verify environment: `php artisan about`
4. Test tenant resolution: Add debug to `IdentifyTenant` middleware

---

## 📚 Summary

### Minimum Required Subdomains

You MUST create these DNS A records pointing to your server IP:

1. `@` (root domain)
2. `www`
3. `superadmin` (or `admin`)
4. `*` (wildcard - covers ALL tenant subdomains)

### Quick Reference URLs

| Environment | Main Site | Login | Superadmin |
|-------------|-----------|-------|------------|
| **Local** | `http://127.0.0.1:8000` | `http://127.0.0.1:8000/login` | `http://127.0.0.1:8000/superadmin/login` |
| **Production** | `https://happychurchruiru.org` | `https://happychurchruiru.org/login` | `https://superadmin.happychurchruiru.org/login` |

---

**Document Version:** 1.0  
**Last Updated:** 2026-03-08  
**Next Review:** Before production deployment
