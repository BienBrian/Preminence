##
## Pisti SaaS — Deployment Readme
## File: infra/README.md
##
## This folder contains all server-side infrastructure configuration for the
## Pisti SaaS platform. Run these steps in order on a fresh server.
##

# Pisti — Server Infrastructure Setup

## Prerequisites
- Ubuntu 20.04+ / Debian 11+
- PHP 8.2+, Nginx, MySQL 8.0+, Redis
- Composer, Node.js 18+
- SSH access as root or sudo user

## Step 1: Wildcard SSL Certificate
```bash
bash infra/ssl-wildcard.sh
```
You will be prompted to add a DNS TXT record (`_acme-challenge.pisti.co.ke`).
After adding the record, press ENTER. Certificate saves to `/etc/letsencrypt/live/pisti.co.ke/`.

## Step 2: Nginx Setup
```bash
sudo cp infra/nginx/pisti.conf /etc/nginx/sites-available/pisti
sudo ln -s /etc/nginx/sites-available/pisti /etc/nginx/sites-enabled/pisti
sudo nginx -t
sudo systemctl reload nginx
```

## Step 3: Horizon (Queue Workers) via Supervisor
```bash
sudo cp infra/supervisor/pisti-horizon.conf /etc/supervisor/conf.d/pisti-horizon.conf
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start "pisti-horizon:*"
```

## Step 4: Laravel Scheduler (Cron)
Add to `crontab -e` (as www-data or deployer user):
```cron
* * * * * php /var/www/pisti/artisan schedule:run >> /dev/null 2>&1
```

## Step 5: DNS Records
In your DNS provider, add:
```
A     pisti.co.ke          → <server-IP>
A     *.pisti.co.ke        → <server-IP>
CNAME www.pisti.co.ke      → pisti.co.ke
```

## Step 6: Environment
```bash
cp .env.example .env
# Edit .env: set DB credentials, REDIS, mail, Mpesa, etc.
php artisan key:generate
php artisan migrate --force
php artisan db:seed --class=PlansSeeder
php artisan db:seed --class=DefaultTenantSeeder
php artisan horizon:publish
```

## Horizon SSL Renewal (Auto)
```bash
# Add to root crontab:
0 12 * * * certbot renew --quiet && systemctl reload nginx
```
