# Pisti Platform — Architectural Analysis & Strategic Plan

> Generated: 2026-04-05  
> Basis: Full codebase audit covering routing, multi-tenancy, module system, API layer, domain provisioning, payments, and intended future architecture.

---

## Table of Contents

1. [What We Currently Have](#1-what-we-currently-have)
2. [How the Intended Architecture Maps to What Exists](#2-how-the-intended-architecture-maps-to-what-exists)
3. [The Core Decision: Shared Server vs Separate Server](#3-the-core-decision-shared-server-vs-separate-server)
4. [Value-Added Services Architecture](#4-value-added-services-architecture)
5. [Domain Provisioning — What Needs to Be Built](#5-domain-provisioning--what-needs-to-be-built)
6. [Security Implications](#6-security-implications)
7. [Performance Implications](#7-performance-implications)
8. [Build Roadmap](#8-build-roadmap)
9. [Summary Verdict](#9-summary-verdict)

---

## 1. What We Currently Have

### Multi-tenancy model
**Shared database, row-level isolation.** Every tenant table has a `tenant_id` column. The `BelongsToTenant` trait (`app/Traits/BelongsToTenant.php`) attaches a global Eloquent scope that automatically filters every query. This is the **correct approach** — it is exactly how Shopify, HubSpot, and most mature SaaS platforms work at this scale. Separate databases per tenant (like early-stage Basecamp) becomes a DevOps nightmare at 50+ tenants.

### Superadmin routing
Already adaptive. The `PISTI_ENV` / `SUPERADMIN_MODE` env vars control whether superadmin is served at `/superadmin` (dev) or `superadmin.happychurchruiru.org` (prod). Moving it to `admin-super.usepisti.com` is **one config change** — no code rewrite required.

**Relevant files:**
- `routes/web.php` — superadmin route group using `is_path_mode()` / `is_subdomain_mode()` helpers
- `config/pisti.php` — `superadmin_mode` config key
- `app/Http/Middleware/SuperAdminMiddleware.php`

### Module system
Unexpectedly mature. The following tables are fully implemented:

| Table | Purpose |
|---|---|
| `modules` | Platform module catalogue with versioning, pricing, dependencies, KYC config |
| `plan_modules` | Which modules are included/available per subscription plan |
| `tenant_module_subscriptions` | Lifecycle record per tenant per module (trial → active → suspended) |
| `tenant_modules` | Runtime enable/disable flag, with admin override capability |

Additional capabilities already built:
- Async installation via `ModuleInstallationJob`
- Billing lifecycle (trial, monthly/yearly, one-time, complimentary)
- KYC/approval gates (donations module requires document upload + superadmin review)
- `ModuleService` with Redis caching (5-minute TTL per tenant/module pair)
- Trial expiration and billing commands in `app/Console/Commands/Modules/`

### Payment
Per-tenant MPesa credentials stored in the `integrations` table with `.env` fallback. The C2B callback routes already resolve the correct tenant by `BusinessShortCode` — meaning multiple tenants with different MPesa shortcodes works today.

**Relevant files:**
- `app/Services/IntegrationService.php` — `getMpesaConfig()`, `resolveMpesaIntegrationByShortcode()`
- `app/Http/Controllers/APIs/MpesaAPIController.php`

### DNS / domain provisioning
Phase 3 placeholders exist. `app/Http/Controllers/SuperAdmin/DnsManagementController.php` has the database schema and UI routes but **DNS verification, SSL provisioning, and propagation checks are all stubs** returning hardcoded mock data.

**Schema already in place** (`database/migrations/2026_03_08_000004_add_dns_management_to_tenants.php`):

```
tenants
  ├── subdomain           (e.g. happy-church-ruiru)
  ├── subdomain_url       (e.g. happy-church-ruiru.pisticrp.org)
  ├── custom_domain       (e.g. www.happychurchruiru.org)
  ├── dns_status          enum: pending | active | error | propagating
  ├── ssl_status          enum: pending | active | error | renewing
  ├── custom_domain_enabled
  ├── dns_records         JSON
  ├── dns_last_verified_at
  └── ssl_expires_at
```

### API layer
Very thin. `routes/api.php` has 9 endpoints: MPesa callbacks, basic Sanctum auth, and 3 dashboard reads. **There is no superadmin API, no cross-tenant API, and no webhook delivery system.**

---

## 2. How the Intended Architecture Maps to What Exists

| Intended goal | Current state | Gap |
|---|---|---|
| Superadmin on separate domain | Adaptive routing ready — config only | No code gap. DNS config change only. |
| Superadmin on separate server | Assumes shared DB connection | **Important** — if truly separate server, shared Redis required for cache/sessions/queues |
| `tenant.pisticrp.org` subdomains | Schema ready, `IdentifyTenant` middleware works | DNS automation not built; platform domain hardcoded to `happychurchruiru.org` in two places |
| Custom domain purchase & provisioning | Schema ready, controller stubs exist | Registrar API integration (Namecheap / GoDaddy) not built |
| Module activation via API (cross-server) | Currently direct DB via web controller | Works with shared DB; only needs REST API if DBs are truly separate |
| Value-added services (merch, events, donations) | Donations module has KYC skeleton | No platform catalogue, no centralized fulfilment, no inter-tenant content layer |
| Inter-church event / content sharing | `network_participation_enabled` flag designed | Zero implementation beyond the flag |
| Centralized payment infrastructure | MPesa per-tenant | No Pisti-owned merchant account, no disbursement logic, no revenue split |

---

## 3. The Core Decision: Shared Server vs Separate Server

This is the most consequential architectural decision. Three options follow in order of complexity.

---

### Option A — Separate domain, shared server & DB ✅ Recommended for now

`admin-super.usepisti.com` and `tenant.pisticrp.org` both resolve to the **same server and the same database**. The superadmin is just a different route group with a different domain. This is how the code already works.

**Pros:**
- Zero latency between superadmin actions and tenant data
- No API layer to maintain, secure, version, or monitor
- Module activation, tenant suspension, impersonation are instant
- One deployment, one codebase, one migration run
- Battle-tested pattern (Shopify ran this way for years)

**Cons:**
- A single server failure takes down both superadmin and all tenants
- Cannot scale superadmin separately from tenant traffic
- Legally murkier if strict data separation between platform and tenants is required

**Verdict:** Right choice until 200+ active tenants or a legal requirement for separation.

---

### Option B — Separate server, shared DB ✅ Right next step at scale

`admin-super.usepisti.com` runs on Server B. `tenant.pisticrp.org` runs on Server A. They share the **same MySQL cluster** through separate app servers. The DB remains the single source of truth.

> **This does NOT require a REST API layer.** Both servers connect to the same database. Superadmin actions write directly to the shared DB. Tenants read from the same DB. This is how most Laravel multi-tenant SaaS platforms scale horizontally.

**What must be added for this model:**
1. A shared Redis instance reachable by both servers (for `ModuleService` cache, sessions, queues)
2. Queue worker runs on a third dedicated server or either app server
3. DB firewall: only the app server IPs can connect to MySQL

**Code change required:** Zero — purely infrastructure.

**Verdict:** The right step at 50–200 tenants generating meaningful traffic.

---

### Option C — Fully decoupled microservices ❌ Wrong for current stage

Superadmin owns its own database. Tenants own theirs. They communicate via REST API or message queue. This is the "communicate mainly via API" framing.

**Why this is the wrong approach right now:**

1. **Distributed transactions are brutal.** When a superadmin activates a module for a tenant, that writes to `tenant_modules`. If that is a remote API call, you now have network timeouts, partial failures, retry idempotency, and eventual consistency. The `ModuleInstallationJob` becomes a cross-service saga pattern — 6 months of engineering for zero customer-visible benefit.

2. **Scale does not justify it.** Microservices are warranted when you have independent teams (10+ engineers) deploying independent services, or when a service handles millions of requests per second. Neither applies here.

3. **Laravel is not designed for it.** Eloquent relationships, Spatie permissions team context, the global `BelongsToTenant` scope — all assume a shared DB connection. Rebuilding these across service boundaries costs enormous engineering time.

4. **Shopify does not do this either.** Their merchant stores and admin panel share the same DB tier. The "separation" is at the CDN / load balancer / app server level, not the data layer.

**When to revisit this:** If the donations/fundraising platform is spun out as a legally separate company for PSP licensing reasons, that service warrants its own DB, its own API, and webhook integration. That is 3–4 years away at current trajectory.

---

## 4. Value-Added Services Architecture

### Events — cross-church sharing

Current events are tenant-isolated. To enable opt-in cross-church sharing, add two tables:

```sql
platform_events
  ├── id
  ├── tenant_id            (originating church)
  ├── visibility           ENUM('tenant', 'network', 'public')
  ├── title, description
  ├── starts_at, ends_at
  ├── location
  ├── ticket_price
  └── ...

platform_event_adoptions
  ├── id
  ├── platform_event_id    FK → platform_events.id
  ├── adopting_tenant_id   FK → tenants.id
  └── status               ENUM('pending', 'accepted', 'rejected')
```

When a church sets `visibility = 'network'`, other churches see it in a "Network Events" feed and can adopt it (list it on their own site). This is a pure addition to the existing events module — no architectural change required.

---

### Merch & Ecommerce — bibles, books, merchandise

Centrally managed catalogue, distributed per-tenant storefronts. Fulfilment is centralized; the storefront is per-tenant.

```sql
platform_products          (superadmin-managed, no tenant_id)
  ├── id
  ├── name, description
  ├── images               JSON
  ├── price
  ├── category             ENUM('bible', 'book', 'merch')
  ├── commission_rate      DECIMAL — Pisti's revenue share
  └── ...

tenant_product_listings    (tenant subscribes products to their store)
  ├── id
  ├── tenant_id
  ├── platform_product_id  FK → platform_products.id
  ├── custom_price_override  DECIMAL nullable
  └── is_active

platform_orders            (fulfilment tracked centrally)
  ├── id
  ├── tenant_id            (which storefront the sale came from)
  ├── platform_product_id
  ├── buyer_user_id        nullable (could be an unregistered visitor)
  ├── amount
  ├── commission_amount
  └── status               ENUM('pending', 'paid', 'shipped', 'delivered')
```

The existing `Shop` module renders `tenant_product_listings` alongside tenant-managed products. Revenue flows through Pisti's merchant account; net amount is disbursed to the church on a schedule. This is the Shopify Payments / App Store commission model.

---

### Fundraising & Donations — centralized payment infrastructure

The highest-value and highest-risk service. Three requirements:

1. **KYC compliance** — the donations module already has the skeleton. Expand to collect and verify church registration documents, board resolutions, and bank details.

2. **A Pisti-owned payment aggregator account** — M-Pesa PayBill, Pesapal, or Stripe for card. Donor contributions flow through Pisti's merchant account, then are disbursed to the church minus the platform fee. This is the Shopify Payments model.

3. **Regulatory** — In Kenya, operating as a payment aggregator likely requires a PSP license or a licensed PSP partnership (e.g. Pesapal, Flutterwave). **This is the primary reason donations may eventually become a legally separate entity.**

**Architectural implication:** The donations service is the one place where a separate service boundary is genuinely justified — but only when the legal entity separation exists. Until then, it runs in the same Laravel app.

---

### Magazine — curated cross-church content

This is the `articles` module with the existing `network_participation_enabled` flag made functional. A curated "Pisti Magazine" feed aggregates articles that churches opt-in to share. The superadmin editorial team curates the feed. No new architecture — implement the flag that is already designed.

---

## 5. Domain Provisioning — What Needs to Be Built

The schema exists. The implementation is missing. Work required, in order:

### Step 1 — Remove the hardcoded platform domain

`DnsManagementController::updateSubdomain()` hardcodes `.happychurchruiru.org` on line 82. This must read from config:

```php
// config/pisti.php
'platform_domain' => env('PISTI_PLATFORM_DOMAIN', 'pisticrp.org'),
```

All references to `happychurchruiru.org` as the platform domain must be replaced with `config('pisti.platform_domain')`.

### Step 2 — Multi-domain support in IdentifyTenant

`IdentifyTenant` middleware must recognize `pisticrp.org` (and future platform domains) in addition to the current tenant's custom domain. The list of platform domains should live in config:

```php
'platform_domains' => ['pisticrp.org', 'happychurchruiru.org'],
```

### Step 3 — Wildcard DNS (one-time, manual)

At the DNS registrar for `pisticrp.org`, add:
```
*.pisticrp.org  A  <server IP>
```
This covers all subdomains automatically. No automation needed for new subdomain creation.

### Step 4 — Automate SSL

Two options, in order of effort:

**Option 1 — Caddy web server (recommended):**  
Caddy handles Let's Encrypt automatically for any domain that resolves to it. Replacing Nginx with Caddy eliminates the SSL problem entirely — zero-config HTTPS for both wildcard subdomains and custom domains.

**Option 2 — Nginx + acme.sh:**  
Keep Nginx. Add a webhook endpoint that Pisti calls when a new custom domain is verified. The webhook triggers `acme.sh --issue -d {domain} --webroot /var/www/html`. More moving parts but compatible with existing infrastructure.

### Step 5 — Real DNS verification

Replace the stub in `DnsManagementController::verifyDns()` with:

```php
$records = dns_get_record($domain, DNS_A);
$pointsToServer = collect($records)->contains('ip', config('app.server_ip'));
```

### Step 6 — Custom domain registrar API (optional premium feature)

Integrate Namecheap or GoDaddy API. Flow:
1. Tenant searches availability via your UI (proxied to registrar API)
2. Tenant pays via platform billing
3. Platform purchases domain via registrar API
4. Platform auto-configures DNS records
5. Platform triggers SSL provisioning (Step 4)
6. `tenants.custom_domain` updated; `IdentifyTenant` picks it up immediately

---

## 6. Security Implications

### Superadmin on a separate domain — an improvement

When superadmin moves to `admin-super.usepisti.com`:
- Session cookies for superadmin (`superadmin_session`) are scoped to `.usepisti.com`, completely separate from tenant session cookies (`.pisticrp.org`) — no cookie leakage
- CORS policy can strictly limit origins
- Brute-force attacks on the superadmin login page do not affect tenant login infrastructure
- Rate limiting can be independently tuned

**Action required immediately:** Add 2FA to superadmin login. The `SuperAdmin` model currently has no TOTP. Use `pragmarx/google2fa-laravel`.

### Impersonation is currently broken and insecure

`TenantController::impersonate()` finds the tenant admin with `->role('admin')` but the Spatie role is `'Super Admin'`. If no user has exactly `'admin'`, impersonation silently fails or logs in as the wrong user.

Required fixes:
1. Correct role name: `->role('Super Admin')`
2. Add an `impersonation_logs` table (superadmin_id, tenant_id, started_at, ended_at, ip_address)
3. Display a persistent warning banner in the tenant UI during impersonation
4. Automatic session expiry during impersonation (configurable, default 30 minutes)

### Cross-tenant data leakage
With a shared DB, a missing `tenant_id` filter or careless `withoutTenantScope()` is a data breach. Mitigations:
- Integration tests asserting cross-tenant isolation (Tenant A cannot read Tenant B's data)
- Quarterly audit of all `withoutTenantScope()` call sites
- Consider a custom query log listener in non-production environments that warns when a tenant-scoped model is queried without the scope

### API authentication for the cross-service future
When a separate service eventually calls back to the tenant app (e.g. the donations service confirming a payment), use **OAuth 2.0 client credentials flow** (machine-to-machine) — not Sanctum tokens. Laravel Passport supports this natively. Sanctum is for user-facing API auth only.

---

## 7. Performance Implications

### Shared DB is fine — until these specific conditions

With `BelongsToTenant` global scopes, every query adds `WHERE tenant_id = X`. At 500 tenants × 1,000 members each = 500,000 rows in `users`. This is manageable with composite indexes (added in `2026_04_05_000001_add_performance_indexes.php`). The actual bottlenecks will be:

**Multi-table report queries** — These do JOINs and aggregations across large tables. Solution: cache results in Redis with a TTL, or pre-compute into a `report_snapshots` table via a scheduled job.

**The `mpesa_transactions` table** — Grows unboundedly. Add MySQL partitioning by `YEAR(created_at)` when the table exceeds 1 million rows. Partition pruning means queries with date filters only scan the relevant partition.

**Module cache dependency** — `ModuleService::isEnabled()` requires Redis. If Redis is unavailable, every module-gated page load fires an extra DB query per module. Add explicit monitoring/alerting for Redis availability.

### Queue fairness between tenants

Currently all tenants share one queue. A single large church sending a mass SMS campaign (10,000 messages) blocks all other tenants' time-sensitive messages (OTP verification, MPesa receipts).

Solution — three priority channels:

```php
// config/queue.php
'connections' => [
    'redis' => [
        'queues' => ['high', 'default', 'low'],
    ],
],
```

| Channel | Use |
|---|---|
| `high` | OTP verification, MPesa callbacks, payment receipts |
| `default` | Birthday SMS, pledge reminders, general notifications |
| `low` | Bulk SMS campaigns, report generation, data exports |

This is a config change, not an architecture change.

### Session driver
Confirmed in `.env-production`: `SESSION_DRIVER=database`. This is correct for multi-server setups but adds a DB write per request. Move to `SESSION_DRIVER=redis` when Redis is confirmed stable — it is significantly faster.

---

## 8. Build Roadmap

### Phase 1 — Clean up single-tenant assumptions (now → 1 month)

| Task | File(s) | Effort |
|---|---|---|
| Remove hardcoded `happychurchruiru.org` from DNS controller | `DnsManagementController.php:82` | 1 hour |
| Add `PISTI_PLATFORM_DOMAIN` config key | `config/pisti.php` | 30 min |
| Update `IdentifyTenant` to support multiple platform domains | `IdentifyTenant.php` | 2 hours |
| Add superadmin 2FA (TOTP) | New — `pragmarx/google2fa-laravel` | 1 day |
| Fix impersonation role name + add audit log + UI banner | `TenantController.php` | 1 day |
| Add queue priority channels (high/default/low) | `config/queue.php`, job classes | 2 hours |
| Make `SANCTUM_STATEFUL_DOMAINS` dynamic | `config/sanctum.php` | 1 hour |

### Phase 2 — Multi-domain & onboarding automation (1–3 months)

| Task | Notes |
|---|---|
| Real DNS verification (`dns_get_record()`) | Replace stub in `DnsManagementController::verifyDns()` |
| Automated SSL via Caddy or acme.sh | Caddy preferred — eliminates the problem entirely |
| Tenant self-service subdomain selection | Currently only superadmin can assign subdomains |
| Subdomain availability check endpoint | New API endpoint for tenant onboarding UI |
| `TenantProvisioningJob` — auto-assign subdomain | Extend existing job |

### Phase 3 — Cross-church platform features (3–6 months)

| Task | Notes |
|---|---|
| `platform_events` + `platform_event_adoptions` tables | Migration + models + UI |
| Cross-church event discovery feed | New dashboard section |
| `platform_products` catalogue (merch/ecommerce) | Migration + superadmin management UI |
| `tenant_product_listings` + tenant storefront | Extend existing Shop module |
| Magazine feature | Extend articles module with `network_participation` flag already designed |
| Consistent `PlatformAuditLog` across all superadmin actions | Many controllers missing audit log calls |

### Phase 4 — Separate superadmin server (6+ months, when tenant count warrants)

| Task | Notes |
|---|---|
| Provision second server for superadmin | Infrastructure only |
| Configure shared Redis | Both servers point to same Redis instance |
| Configure shared MySQL | Both servers point to same DB cluster |
| Deploy queue workers to dedicated server | Optional — can run on either app server |
| Health check endpoints | `/health` on both servers for load balancer |
| **No API layer needed** | Shared DB eliminates the requirement |

### Phase 5 — Donations as a separate regulated service (12+ months)

Prerequisites: PSP license obtained or licensed PSP partnership signed.

| Task | Notes |
|---|---|
| Register `pisti-payments` as separate Laravel application | Own DB, own deployment |
| Build donor-facing payment page | Hosted on `pay.usepisti.com` |
| Webhook from payments service → tenant app | Marks contribution received, triggers SMS receipt |
| Revenue split and disbursement logic | Cron-based, configurable per tenant |
| KYC document verification integration | Partner with a KYC provider (e.g. Smile Identity) |
| Integrate with existing donations module | Module becomes a thin UI wrapper over the payments API |

---

## 9. Summary Verdict

**The architecture is sound and on the right track.** The module system, tenant isolation model, per-tenant integrations, adaptive superadmin routing, and async job infrastructure are all well-designed decisions that will scale to hundreds of tenants without structural change.

### The "communicate mainly via API" framing is the wrong mental model for now

The correct mental model is: **one codebase, one DB, potentially multiple app servers** — all sharing the same data layer. An API boundary only appears when a service has a separate legal entity, separate data ownership, or a genuinely independent deployment lifecycle. For Pisti, that applies only to the regulated payments service, and only when licensing requires it.

### Gap summary

| Gap | Severity | Effort |
|---|---|---|
| DNS/SSL automation (stubs) | High — blocks new tenant self-onboarding | 2–3 weeks |
| Superadmin 2FA | High — security | 2 days |
| Impersonation audit log + correct role name | High — security/compliance | 1 day |
| Platform domain hardcoded in DNS controller | High — blocks `pisticrp.org` rollout | 1 hour |
| Queue priority channels | Medium — fairness between tenants | 2 hours |
| Cross-church event/content sharing | Medium — product roadmap | 3–4 weeks |
| Custom domain registrar API | Medium — product roadmap | 2–3 weeks |
| Centralized merch/ecommerce catalogue | Low — product roadmap | 4–6 weeks |
| Donations as separate PSP service | Low — legal prerequisite first | 4+ months |

### Key principle to preserve

> Resist the temptation to introduce a REST API between superadmin and tenant until a hard technical or legal boundary requires it. Every premature service boundary adds latency, failure modes, versioning overhead, and operational complexity that slows down the rate of feature delivery. Build the monolith well; extract services only when the cost of NOT extracting them becomes measurable.
