#!/bin/bash

# =============================================================================
# Pisti SaaS Production Deployment Script
# =============================================================================
# This script prepares the application for production deployment.
# Run this on your production server after uploading the code.
# =============================================================================

echo "=============================================="
echo "Pisti SaaS Production Deployment"
echo "=============================================="
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Check if running as root (should NOT be root)
if [ "$EUID" -eq 0 ]; then 
   echo -e "${RED}ERROR: Do not run this script as root${NC}"
   echo "Run as the web server user (usually www-data) or your deploy user"
   exit 1
fi

# Function to print status
print_status() {
    echo -e "${GREEN}[✓]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[!]${NC} $1"
}

print_error() {
    echo -e "${RED}[✗]${NC} $1"
}

# =============================================================================
# Step 1: Check prerequisites
# =============================================================================
echo "Step 1: Checking prerequisites..."

# Check if composer is installed
if ! command -v composer &> /dev/null; then
    print_error "Composer is not installed. Please install Composer first."
    exit 1
fi

# Check if PHP is installed
if ! command -v php &> /dev/null; then
    print_error "PHP is not installed. Please install PHP 8.1+ first."
    exit 1
fi

print_status "Prerequisites check passed"

# =============================================================================
# Step 2: Install dependencies
# =============================================================================
echo ""
echo "Step 2: Installing PHP dependencies (production optimized)..."
composer install --optimize-autoloader --no-dev --no-interaction

if [ $? -ne 0 ]; then
    print_error "Composer install failed"
    exit 1
fi

print_status "Dependencies installed"

# =============================================================================
# Step 3: Update environment file
# =============================================================================
echo ""
echo "Step 3: Updating environment configuration..."

# Backup current .env
cp .env .env.backup.$(date +%Y%m%d_%H%M%S)

# Update key settings for production
sed -i 's/APP_ENV=local/APP_ENV=production/' .env
sed -i 's/APP_DEBUG=true/APP_DEBUG=false/' .env
sed -i 's|APP_URL=http://127.0.0.1:8000|APP_URL=https://happychurchruiru.org|' .env
sed -i 's/SESSION_DOMAIN=/SESSION_DOMAIN=.happychurchruiru.org/' .env
sed -i 's/SESSION_SECURE_COOKIE=false/SESSION_SECURE_COOKIE=true/' .env

print_status "Environment file updated"
print_warning "Review .env file and update database credentials, mail settings, etc."

# =============================================================================
# Step 4: Generate application key (if not set)
# =============================================================================
echo ""
echo "Step 4: Checking application key..."

if grep -q "APP_KEY=$" .env || grep -q "APP_KEY=SomeRandomString" .env; then
    echo "Generating new application key..."
    php artisan key:generate --force
    print_status "New application key generated"
else
    print_status "Application key already set"
fi

# =============================================================================
# Step 5: Run database migrations
# =============================================================================
echo ""
echo "Step 5: Running database migrations..."
echo "WARNING: This will modify your database. Press Ctrl+C to cancel."
echo "Waiting 5 seconds..."
sleep 5

php artisan migrate --force --no-interaction

if [ $? -ne 0 ]; then
    print_error "Migration failed"
    exit 1
fi

print_status "Migrations completed"

# =============================================================================
# Step 6: Seed essential data
# =============================================================================
echo ""
echo "Step 6: Seeding essential data..."

# Seed plans if not exists
php artisan db:seed --class=PlansSeeder --force

# Update tenant #1 domain
php artisan tinker --execute="App\Models\Tenant::where('id', 1)->update(['domain' => 'happychurchruiru.org']);"

print_status "Data seeding completed"

# =============================================================================
# Step 7: Clear and cache configurations
# =============================================================================
echo ""
echo "Step 7: Caching configurations..."

php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

print_status "Configurations cached"

# =============================================================================
# Step 8: Set up storage directories
# =============================================================================
echo ""
echo "Step 8: Setting up storage directories..."

mkdir -p storage/app/tenants
mkdir -p storage/app/public
mkdir -p storage/framework/cache
mkdir -p storage/framework/sessions
mkdir -p storage/framework/testing
mkdir -p storage/framework/views
mkdir -p storage/logs

# Create symlink for public storage
php artisan storage:link

print_status "Storage directories ready"

# =============================================================================
# Step 9: Set permissions (if running as web user)
# =============================================================================
echo ""
echo "Step 9: Setting permissions..."

# Note: These commands may fail if user doesn't have permission
# They should be run as root or with sudo if needed
touch storage/logs/laravel.log 2>/dev/null || print_warning "Could not create log file"

print_status "Permissions set (if possible)"

# =============================================================================
# Step 10: Restart queue workers
# =============================================================================
echo ""
echo "Step 10: Restarting queue workers..."

php artisan queue:restart 2>/dev/null || print_warning "Queue workers may need manual restart via Supervisor"

print_status "Queue workers signaled to restart"

# =============================================================================
# Deployment Complete
# =============================================================================
echo ""
echo "=============================================="
echo -e "${GREEN}Deployment Preparation Complete!${NC}"
echo "=============================================="
echo ""
echo "NEXT STEPS:"
echo "-----------"
echo ""
echo "1. Update database credentials in .env:"
echo "   DB_DATABASE=your_production_db"
echo "   DB_USERNAME=your_db_user"
echo "   DB_PASSWORD=your_secure_password"
echo ""
echo "2. Update mail settings in .env:"
echo "   MAIL_HOST=your_smtp_host"
echo "   MAIL_USERNAME=your_email"
echo "   MAIL_PASSWORD=your_password"
echo ""
echo "3. Ensure these DNS A records point to your server IP:"
echo "   - @ (root domain)"
echo "   - www"
echo "   - superadmin"
echo "   - * (wildcard for all tenant subdomains)"
echo ""
echo "4. Set up SSL certificate:"
echo "   sudo certbot certonly --manual -d '*.happychurchruiru.org' -d 'happychurchruiru.org'"
echo ""
echo "5. Configure Nginx (see DEPLOYMENT_GUIDE.md)"
echo ""
echo "6. Set up Supervisor for queue workers:"
echo "   sudo supervisorctl reread && sudo supervisorctl update"
echo ""
echo "7. Set up cron job:"
echo "   * * * * * cd $(pwd) && php artisan schedule:run >> /dev/null 2>&1"
echo ""
echo "8. Change default superadmin password immediately!"
echo "   Login: https://happychurchruiru.org/superadmin/login"
echo "   Email: admin@pisti.co.ke"
echo "   Password: Change-Me-Now-2024!"
echo ""
echo "=============================================="
echo ""

# Show current configuration
echo "Current Configuration:"
echo "----------------------"
grep "^APP_ENV=" .env
grep "^APP_URL=" .env
grep "^SESSION_DOMAIN=" .env
grep "^DB_DATABASE=" .env

echo ""
echo "For full details, see DEPLOYMENT_GUIDE.md"
