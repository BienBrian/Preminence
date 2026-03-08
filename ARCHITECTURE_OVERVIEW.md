# Pisti SaaS Architecture Overview

> **Version:** 2.0  
> **Last Updated:** 2026-03-08  
> **Primary Domain:** `happychurchruiru.org`

---

## 🎯 Architecture Summary

### Phase 1 (Current): Single Tenant on Primary Domain

**Primary Domain:** `happychurchruiru.org`

| URL | Purpose |
|-----|---------|
| `https://happychurchruiru.org` | Happy Church Ruiru landing page (Tenant #1) |
| `https://happychurchruiru.org/login` | Tenant #1 login |
| `https://superadmin.happychurchruiru.org/login` | Superadmin panel |

### Phase 2 (Future): Multi-Tenant with Separate Marketing Site

**Marketing Domain:** `getpisti.com` (or similar)

| URL | Purpose |
|-----|---------|
| `https://getpisti.com` | Marketing site for new signups |
| `https://{tenant}.getpisti.com` | Tenant subdomains |
| `https://superadmin.getpisti.com` | Superadmin panel |

**Happy Church Ruiru (Tenant #1):**
| URL | Purpose |
|-----|---------|
| `https://happychurch-ruiru.getpisti.com` or keep `happychurchruiru.org` | Tenant #1 site |

---

## 🌐 Domain Resolution Logic

The `IdentifyTenant` middleware resolves tenants in this order:

```
1. Check if localhost/127.0.0.1
   └── Yes → Default to Tenant #1 (or use ?__tenant={slug} override)
   
2. Check if bypassed subdomain (superadmin, www, admin, api)
   └── Yes → Skip tenant resolution
   
3. Check custom_domain column match
   └── Match found → Use that tenant
   
4. Check subdomain slug match
   └── Match found → Use that tenant
   
5. Check if primary platform domain (happychurchruiru.org)
   └── Yes → Default to Tenant #1
   
6. Check if marketing domain (getpisti.com, etc.)
   └── Yes → Redirect to marketing site (Phase 2)
   
7. No match found → 404 Tenant Not Found
```

---

## 📁 DNS Management (Superadmin)

### Automatic Subdomain Assignment

When a new tenant is created:

1. **Slug Generation:** From church name (e.g., "Grace Community" → `grace-community`)
2. **Subdomain Assignment:** `{slug}.happychurchruiru.org`
3. **Wildcard DNS:** The `*.happychurchruiru.org` record handles all subdomains automatically

### Custom Domain Purchase (Future Feature)

Superadmins can configure custom domains:

| Field | Description |
|-------|-------------|
| `custom_domain` | The purchased domain (e.g., `www.gracecommunity.org`) |
| `custom_domain_enabled` | Whether the feature is active |
| `dns_status` | pending / active / error / propagating |
| `ssl_status` | pending / active / error / renewing |

**Phase 3 Automation (Future):**
- Automated DNS verification
- Let's Encrypt SSL provisioning
- Self-service domain setup for tenants

---

## 🧪 Testing Strategy

### Unit Tests Created

| Test File | Coverage |
|-----------|----------|
| `TenantResolutionTest.php` | Domain/subdomain resolution, localhost fallback, 404 handling |
| `ModuleGatingTest.php` | Module middleware, cache management, tenant module toggles |
| `TenantProvisioningTest.php` | Job creation, user setup, funds, modules, storage |

### Manual Testing Checklist

- [ ] Access `http://127.0.0.1:8000` → Should show Happy Church Ruiru
- [ ] Access `http://127.0.0.1:8000/login` → Should show login page
- [ ] Access `http://127.0.0.1:8000/superadmin/login` → Should show superadmin login
- [ ] Login as Jay → Should access dashboard
- [ ] Access superadmin → Should see DNS Management menu
- [ ] Create new tenant → Should auto-assign subdomain

---

## 🔐 Security Considerations

### Tenant Isolation
- ✅ Global Eloquent scopes via `BelongsToTenant` trait
- ✅ Spatie permissions team context per tenant
- ✅ Module-level access control via middleware
- ✅ Database-level foreign key constraints

### Access Control
- ✅ Superadmin bypass for platform management
- ✅ Suspended tenant blocking
- ✅ Trial expiration enforcement
- ✅ Grace period handling

---

## 🚀 Deployment Guide

### DNS Records Required

For `happychurchruiru.org`:

```dns
Type    Host                Value
────────────────────────────────────────
A       @                   SERVER_IP
A       www                 SERVER_IP
A       superadmin          SERVER_IP
A       *                   SERVER_IP
```

### Future: Marketing Domain

For `getpisti.com` (Phase 2):

```dns
Type    Host                Value
────────────────────────────────────────
A       @                   SERVER_IP
A       www                 SERVER_IP
A       superadmin          SERVER_IP
A       *                   SERVER_IP
```

---

## 📋 Module Status

### Implemented Modules (Working)

| Module | Status | Notes |
|--------|--------|-------|
| Finance | ✅ | Full functionality |
| Spiritual | ✅ | Sermons, testimonials |
| Shop | ✅ | Products, purchases |
| Reports | ✅ | Analytics |
| DNS Management | ✅ | Superadmin interface |

### Future Modules (Phase 2-3)

| Module | Status | Notes |
|--------|--------|-------|
| Marketing Site | 📝 | Public landing page on getpisti.com |
| Self-Registration | 📝 | Public signup wizard |
| Automated DNS | 📝 | Let's Encrypt integration |
| API Access | 📝 | REST API for integrations |

**Legend:**
- ✅ Implemented and working
- 📝 Planned for future phase
- ⚠️ Partial implementation with notes

---

## 📝 Graceful Degradation

For features not yet implemented, the application shows informative messages instead of breaking:

### Example: Marketing Site
```
If user accesses getpisti.com (Phase 2 domain):
└── Currently redirects to happychurchruiru.org
└── Future: Will show marketing landing page
```

### Example: Automated DNS
```
Superadmin clicks "Verify DNS":
└── Currently: Manual verification with note
└── Future: Automated DNS record validation
```

---

## 🔄 Data Flow

### New Tenant Creation Flow

```
1. Superadmin creates tenant in panel
   ↓
2. TenantProvisioningJob dispatched
   ↓
3. Job executes:
   a. Create tenant record with auto-generated subdomain
   b. Create admin user with Super Admin role
   c. Create default settings
   d. Create default funds (Tithes, Offering, etc.)
   e. Enable modules based on plan
   f. Create storage directory
   g. Log to platform_audit_log
   ↓
4. Tenant can immediately access:
   https://{subdomain}.happychurchruiru.org
```

---

## 📞 Support & Troubleshooting

### Common Issues

**1. "Church Not Found" Error**
- Check that tenant slug matches subdomain
- Verify wildcard DNS record exists
- Check tenant status is 'active' or 'trial'

**2. Session Not Shared Across Subdomains**
- Verify `SESSION_DOMAIN=.happychurchruiru.org` in .env
- Must have leading dot for subdomain cookies

**3. Custom Domain Not Working**
- Check DNS A records point to server
- Verify custom_domain_enabled is true
- Check ssl_status is 'active'

---

## 📚 Related Documentation

- `DEPLOYMENT_GUIDE.md` - Production deployment steps
- `SAAS_IMPLEMENTATION_STATUS.md` - Implementation checklist
- `LOCAL_TO_PRODUCTION_CHECKLIST.md` - Migration checklist

---

**Questions?** Refer to the deployment guide or contact the development team.
