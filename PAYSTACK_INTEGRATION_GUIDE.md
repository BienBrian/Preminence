# PayStack Integration Guide

## Overview
This guide covers the PayStack payment gateway integration for the Pisti Module Marketplace.

## Configuration

### 1. Environment Variables
Add these to your `.env` file:

```env
# ─── PayStack Payment Gateway ────────────────────────────────────────────────
PAYSTACK_PUBLIC_KEY=pk_test_your_public_key_here
PAYSTACK_SECRET_KEY=sk_test_your_secret_key_here
PAYSTACK_WEBHOOK_SECRET=whsec_your_webhook_secret
PAYSTACK_IS_LIVE=false
PAYSTACK_CURRENCY=KES
```

### 2. Get Your PayStack API Keys

1. Sign up at [https://paystack.com/](https://paystack.com/)
2. Go to Settings > API Keys & Webhooks
3. Copy your Test keys first for development
4. For production, switch to Live keys and set `PAYSTACK_IS_LIVE=true`

### 3. Configure Webhook

In your PayStack Dashboard:

1. Go to Settings > API Keys & Webhooks
2. Add Webhook URL: `https://your-domain.com/webhooks/paystack`
3. Set Secret Key and copy it to `PAYSTACK_WEBHOOK_SECRET`
4. Enable these events:
   - `charge.success` - Payment successful
   - `charge.failed` - Payment failed
   - `refund.processed` - Refund completed

## Features

### For Tenants
- **Checkout**: Pay for module installations via PayStack
- **Cards**: Accept Visa, Mastercard, Verve
- **Mobile Money**: M-Pesa, MTN Mobile Money (where available)
- **Bank Transfer**: Direct bank transfers
- **USSD**: Pay via USSD codes

### For SuperAdmin
- **Dashboard**: View all payments, revenue stats
- **Refunds**: Process full or partial refunds
- **Manual Payments**: Mark invoices as paid (for cash/bank transfers)
- **Reporting**: Export billing data

## Testing

### Test Cards
Use these test cards in sandbox mode:

```
Success: 4084 0840 8408 4081
        CVV: 408
        Expiry: Any future date

Failure: 4084 0840 8408 4082
        CVV: 408
        Expiry: Any future date
```

### Test M-Pesa (STK Push)
Use test phone numbers provided in PayStack dashboard.

## Payment Flow

### 1. Module Installation
```
Tenant clicks Install → System creates invoice item
    ↓
Redirect to PayStack checkout → Customer completes payment
    ↓
PayStack redirects to callback URL → System verifies transaction
    ↓
Webhook confirms payment → Module activated automatically
```

### 2. Recurring Billing
```
Cron runs daily → Generates invoice items for due subscriptions
    ↓
System attempts PayStack charge (if card saved)
    ↓
Success: Invoice marked paid, subscription renewed
Failure: Invoice marked failed, retry scheduled
```

## API Endpoints

### Webhook (PayStack → Pisti)
```
POST /webhooks/paystack
Content-Type: application/json
X-Paystack-Signature: {signature}
```

### Payment Callback
```
GET /payments/callback?reference={reference}&trxref={reference}
```

## Console Commands

```bash
# Generate invoices for due subscriptions
php artisan modules:generate-invoices

# Process expired trials (convert or suspend)
php artisan modules:process-trials

# Handle overdue payments
php artisan modules:process-overdue --suspend

# Generate billing report
php artisan modules:billing-report --period=30
```

## Troubleshooting

### Webhook Not Working
1. Check webhook URL is accessible from internet
2. Verify `PAYSTACK_WEBHOOK_SECRET` matches PayStack dashboard
3. Check `storage/logs/laravel.log` for webhook errors
4. Ensure CSRF protection is disabled for webhook route

### Payments Not Processing
1. Check `PAYSTACK_SECRET_KEY` is correct
2. Verify you're using Test keys in development
3. Check transaction logs in PayStack dashboard
4. Review error logs in Pisti

### Refunds Failing
1. Ensure transaction was successful
2. Check refund amount is not more than original
3. Verify transaction is within refund window
4. Check PayStack account balance

## Security Considerations

1. **Never** expose Secret Key in frontend code
2. Always verify webhook signatures
3. Use HTTPS for webhook URLs in production
4. Store API keys in environment variables only
5. Enable 2FA on PayStack dashboard

## Switching to Live Mode

1. Get Live API keys from PayStack dashboard
2. Update `.env` with Live keys
3. Set `PAYSTACK_IS_LIVE=true`
4. Update webhook URL to production domain
5. Test with small real transaction
6. Monitor first few transactions closely

## Support

- PayStack Docs: https://paystack.com/docs/
- PayStack Support: support@paystack.com
- Pisti Issues: Check logs in `storage/logs/`
