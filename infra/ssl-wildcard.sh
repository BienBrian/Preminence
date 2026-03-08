#!/usr/bin/env bash
##
## Pisti SaaS — Wildcard SSL Certificate Setup
## Run once on the production server to obtain *.pisti.co.ke certificate
##
## Prerequisites:
##   - certbot installed: apt install certbot
##   - SSH access to server
##   - DNS access to add TXT record during challenge
##
## Usage: bash infra/ssl-wildcard.sh
##

set -e

DOMAIN="pisti.co.ke"
EMAIL="platform@pisti.co.ke"   # Change to your admin email

echo "======================================="
echo "  Pisti Wildcard SSL Setup"
echo "  Domain: *.${DOMAIN}"
echo "======================================="
echo ""
echo "This will start the Let's Encrypt DNS challenge."
echo "You will need to add a TXT DNS record during the process."
echo ""
read -p "Press ENTER to continue, Ctrl+C to cancel..."

certbot certonly \
  --manual \
  --preferred-challenges dns \
  --agree-tos \
  --email "${EMAIL}" \
  -d "*.${DOMAIN}" \
  -d "${DOMAIN}"

echo ""
echo "======================================="
echo "  Certificate obtained successfully!"
echo ""
echo "  Certificate: /etc/letsencrypt/live/${DOMAIN}/fullchain.pem"
echo "  Private Key: /etc/letsencrypt/live/${DOMAIN}/privkey.pem"
echo ""
echo "  Next steps:"
echo "  1. Copy infra/nginx/pisti.conf to /etc/nginx/sites-available/pisti"
echo "  2. Link: ln -s /etc/nginx/sites-available/pisti /etc/nginx/sites-enabled/pisti"
echo "  3. Test:  nginx -t"
echo "  4. Reload: systemctl reload nginx"
echo ""
echo "  Auto-renewal (add to crontab):"
echo "  0 12 * * * certbot renew --quiet && systemctl reload nginx"
echo "======================================="

# Generate Horizon htpasswd file for dashboard basic auth
if command -v htpasswd &> /dev/null; then
    echo ""
    echo "Generating Horizon dashboard credentials..."
    read -p "Horizon username [pisti-admin]: " HUSER
    HUSER="${HUSER:-pisti-admin}"
    htpasswd -c /etc/nginx/.pisti-horizon-htpasswd "${HUSER}"
    echo "Horizon htpasswd created at /etc/nginx/.pisti-horizon-htpasswd"
else
    echo "Note: htpasswd not found. Install apache2-utils and run:"
    echo "  htpasswd -c /etc/nginx/.pisti-horizon-htpasswd pisti-admin"
fi
