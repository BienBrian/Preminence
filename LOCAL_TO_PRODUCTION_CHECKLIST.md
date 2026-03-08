# Local Development → Production Checklist

> **Quick reference for deploying Pisti from localhost to production**

---

## ✅ Current Local Configuration

Your app is now configured for **local development** with these settings:

```
APP_URL=http://127.0.0.1:8000
SESSION_DOMAIN=
APP_ENV=local
APP_DEBUG=true
```

### Local Access URLs

| Feature | URL |
|---------|-----|
| Main Site | `http://127.0.0.1:8000` |
| Login | `http://127.0.0.1:8000/login` |
| Superadmin | `http://127.0.0.1:8000/superadmin/login` |
| With Tenant Override | `http://127.0.0.1:8000/login?__tenant=happychurch-ruiru` |

### Local Testing Commands

```bash
# Start local server
php artisan serve

# Access at http://127.0.0.1:8000
```

---

## 🚀 Production Deployment Steps

### Step 1: Upload Code to Server

Upload all files to your production server (e.g., `/var/www/pisti/`)

### Step 2: Run Deployment Script

```bash
ssh user@your-server
cd /var/www/pisti
./deploy-to-production.sh
```

This script will:
- Install production dependencies
- Update `.env` for production
- Run migrations
- Cache configurations
- Set up storage directories

### Step 3: Manual Configuration

Edit `.env` on production server:

```bash
# Database (update with your credentials)
DB_DATABASE=happychurchruiru_production
DB_USERNAME=your_db_username
DB_PASSWORD=your_secure_password

# Redis (if using)
REDIS_PASSWORD=your_redis_password

# Mail (update with your SMTP)
MAIL_HOST=smtp.yourprovider.com
MAIL_USERNAME=noreply@happychurchruiru.org
MAIL_PASSWORD=your_mail_password
```

### Step 4: Create DNS Records

In your domain registrar/DNS provider, create these **A records**:

| Type | Host | Value (Server IP) |
|------|------|-------------------|
| A | `@` | Your server IP |
| A | `www` | Your server IP |
| A | `superadmin` | Your server IP |
| A | `*` | Your server IP |

> **Note:** The wildcard (`*`) record automatically handles ALL tenant subdomains.

### Step 5: Set Up SSL Certificate

```bash
# Install Certbot
sudo apt install certbot python3-certbot-nginx

# Obtain wildcard certificate
sudo certbot certonly --manual --preferred-challenges dns \
  -d "*.happychurchruiru.org" \
  -d "happychurchruiru.org"
```

**During this process, Certbot will give you DNS TXT records to create.** Add these in your DNS provider, then continue.

### Step 6: Configure Nginx

Create file: `/etc/nginx/sites-available/happychurchruiru.org`

(See full config in `DEPLOYMENT_GUIDE.md`)

```bash
sudo ln -s /etc/nginx/sites-available/happychurchruiru.org /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### Step 7: Set Permissions

```bash
sudo chown -R www-data:www-data /var/www/pisti
sudo chmod -R 775 /var/www/pisti/storage
sudo chmod -R 775 /var/www/pisti/bootstrap/cache
```

### Step 8: Set Up Queue Worker

Create `/etc/supervisor/conf.d/pisti-worker.conf`:

```ini
[program:pisti-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/pisti/artisan queue:work redis --sleep=3 --tries=3
autostart=true
autorestart=true
user=www-data
numprocs=2
stdout_logfile=/var/www/pisti/storage/logs/worker.log
```

Enable:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start pisti-worker:*
```

### Step 9: Set Up Cron Job

```bash
sudo crontab -e
```

Add:
```
* * * * * cd /var/www/pisti && php artisan schedule:run >> /dev/null 2>&1
```

### Step 10: Security - Change Default Password

**Immediately** after deployment:

1. Visit `https://happychurchruiru.org/superadmin/login`
2. Login with:
   - Email: `admin@pisti.co.ke`
   - Password: `Change-Me-Now-2024!`
3. Go to SuperAdmins section
4. Change the password or create a new admin and delete this one

---

## 🌐 Production URLs

After deployment, your URLs will be:

| Feature | Production URL |
|---------|----------------|
| Main Site | `https://happychurchruiru.org` |
| Login | `https://happychurchruiru.org/login` |
| Superadmin | `https://superadmin.happychurchruiru.org/login` |
| Tenant Subdomain Example | `https://happychurch-ruiru.happychurchruiru.org` |

---

## 📋 Required Subdomains Summary

You need to create **4 DNS records** minimum:

1. **`@`** (root) - Main website
2. **`www`** - WWW redirect  
3. **`superadmin`** - Admin panel access
4. **`*`** (wildcard) - ALL tenant subdomains

The wildcard (`*`) is critical - it automatically handles any church that signs up without you needing to create individual DNS records.

---

## 🔍 Testing Production

After deployment, test these URLs:

```
# Should redirect to HTTPS
http://happychurchruiru.org

# Should show Happy Church Ruiru
https://happychurchruiru.org

# Should show login page
https://happychurchruiru.org/login

# Should show superadmin login
https://superadmin.happychurchruiru.org/login

# Test tenant subdomain (after creating a tenant)
https://test-church.happychurchruiru.org
```

---

## 🆘 Troubleshooting

### Can't access superadmin on subdomain?

Make sure `superadmin.happychurchruiru.org` DNS record exists and points to your server.

### Tenant subdomain shows "Church not found"?

Check that the tenant slug matches the subdomain:
- Subdomain: `mychurch.happychurchruiru.org`
- Tenant slug must be: `mychurch`

### Session not working across subdomains?

Verify in `.env`:
```
SESSION_DOMAIN=.happychurchruiru.org
```
(Note the leading dot!)

### SSL certificate issues?

Ensure your certificate covers:
- `happychurchruiru.org`
- `*.happychurchruiru.org`

---

## 📞 Support Files

- **Full deployment guide:** `DEPLOYMENT_GUIDE.md`
- **Deployment script:** `deploy-to-production.sh`
- **Implementation status:** `SAAS_IMPLEMENTATION_STATUS.md`

---

**Ready to deploy! 🚀**
