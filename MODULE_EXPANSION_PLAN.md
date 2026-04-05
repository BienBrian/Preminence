# Module Expansion & Onboarding System Plan

## Executive Summary

Expand the existing module marketplace to support granular feature modules with:
1. **New Modules**: Convert existing features and add new capabilities
2. **Module Onboarding**: KYC, documentation collection, and guided setup
3. **Tenant Self-Service Marketplace**: Allow tenants to activate modules on-demand

---

## Part 1: New Module Catalog

### Core Modules (Already Exist)
| Module Key | Name | Category | Description |
|------------|------|----------|-------------|
| `people` | People Management | core | User and member management |
| `attendance` | Attendance Tracking | core | Track church attendance |
| `finance` | Financial Management | core | Tithes, offerings, funds |
| `events` | Events & Calendar | core | Events and notices |
| `website` | Website Builder | core | Church website management |
| `communication` | Communication | core | SMS and email |

### New Feature Modules

#### Spiritual Content
| Module Key | Name | Category | Description | Default Price |
|------------|------|----------|-------------|---------------|
| `sermons` | Sermon Management | spiritual | Upload, organize, and share sermons | Free |
| `articles` | Articles & Blog | content | Write and publish church articles | Free |
| `testimonials` | Testimonials | spiritual | Member testimonies | Free |
| `prayer_requests` | Prayer Requests | spiritual | Prayer wall and requests | Free |

#### Financial Modules
| Module Key | Name | Category | Description | Default Price |
|------------|------|----------|-------------|---------------|
| `donations` | Donations & Fundraising | finance | Online donations, campaigns | 500/month |
| `budgets` | Budget Management | finance | Annual budgeting, tracking | 300/month |
| `assets` | Asset Management | finance | Church asset tracking | 200/month |
| `mpesa_logs` | M-PESA Reconciliation | finance | Advanced M-PESA matching | 400/month |

#### Administration
| Module Key | Name | Category | Description | Default Price |
|------------|------|----------|-------------|---------------|
| `duplication_checker` | Duplicate Finder | admin | Find and merge duplicate members | 200/month |
| `children_checkin` | Children's Check-in | admin | Child check-in/check-out system | 300/month |
| `file_manager` | File Manager | admin | Document and media storage | Free |

#### Advanced Features
| Module Key | Name | Category | Description | Default Price |
|------------|------|----------|-------------|---------------|
| `reports_advanced` | Advanced Analytics | reports | Custom reports and dashboards | 500/month |
| `discipleship` | Discipleship & Mentorship | spiritual | Tracks, mentorship, journals | 400/month |
| `shop` | Church Store | commerce | Sell merchandise and resources | 2% transaction |

---

## Part 2: Module Onboarding System

### Onboarding Types

#### Type A: KYC + Documentation (High-Risk Modules)
**Applies to**: `donations`, `shop` (payment processing modules)

**Required Documents**:
1. Church Registration Certificate
2. Board Resolution (signed by 2+ leaders)
3. Bank Account Details (for remittance)
4. Tax exemption certificate (if applicable)
5. Leadership identification

**Flow**:
```
Tenant Requests Module
    ↓
System shows onboarding form
    ↓
Tenant uploads documents + fills KYC
    ↓
SuperAdmin reviews (approval queue)
    ↓
Approved → Module activated
Rejected → Reason provided, can reapply
```

#### Type B: Guided Setup + Tutorial
**Applies to**: `articles`, `sermons`, `discipleship`

**Components**:
1. Benefits explainer video/text
2. Configuration wizard
3. Best practices guide
4. Optional: Network participation opt-in

**Example - Articles Module**:
```
Step 1: Welcome to Articles
    - Benefits of publishing
    - SEO advantages
    
Step 2: Network Participation
    [ ] Share articles with network churches
    [ ] Receive articles from network
    
Step 3: Setup Categories
    - Configure article categories
    
Step 4: First Article
    - Guided first article creation
```

#### Type C: Self-Service Instant Activation
**Applies to**: `file_manager`, `testimonials`, `prayer_requests`

No onboarding required - immediate activation.

---

## Part 3: Database Schema Additions

### 1. Module Onboarding Config
```sql
CREATE TABLE module_onboarding_configs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    module_key VARCHAR(50) NOT NULL,
    onboarding_type ENUM('kyc', 'guided', 'none') DEFAULT 'none',
    requires_approval BOOLEAN DEFAULT FALSE,
    required_documents JSON, -- ['certificate', 'bank_details', 'board_resolution']
    kyc_form_schema JSON,    -- Dynamic form fields
    tutorial_content JSON,   -- Steps, videos, content
    network_participation_enabled BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY (module_key)
);
```

### 2. Tenant Module Onboarding Submissions
```sql
CREATE TABLE tenant_module_onboarding (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT UNSIGNED NOT NULL,
    module_key VARCHAR(50) NOT NULL,
    status ENUM('draft', 'submitted', 'under_review', 'approved', 'rejected') DEFAULT 'draft',
    form_data JSON,          -- KYC form responses
    documents JSON,          -- Uploaded file paths
    submitted_at TIMESTAMP NULL,
    reviewed_at TIMESTAMP NULL,
    reviewed_by BIGINT UNSIGNED NULL,
    rejection_reason TEXT NULL,
    network_participation_opt_in BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_tenant_module (tenant_id, module_key),
    INDEX idx_status (status)
);
```

### 3. Module Activation Settings (for SuperAdmin)
```sql
CREATE TABLE module_activation_settings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    module_key VARCHAR(50) NOT NULL,
    tenant_can_self_activate BOOLEAN DEFAULT FALSE,
    requires_superadmin_approval BOOLEAN DEFAULT FALSE,
    auto_approve_for_plans JSON, -- ['enterprise', 'premium']
    minimum_plan_tier VARCHAR(50) NULL, -- NULL = any plan
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY (module_key)
);
```

---

## Part 4: UI/UX Components

### 1. Tenant Marketplace (In Profile Dropdown)

**Location**: User profile dropdown → "Module Marketplace"

**Layout**:
```
┌─────────────────────────────────────────────┐
│  Module Marketplace                    [X]  │
├─────────────────────────────────────────────┤
│  Available Modules                          │
│  ┌─────────────────────────────────────┐   │
│  │ [Icon] Articles & Blog        Free  │   │
│  │ Write and publish articles...      │   │
│  │ [Activate Now]                     │   │
│  └─────────────────────────────────────┘   │
│  ┌─────────────────────────────────────┐   │
│  │ [Icon] Donations          KES 500/m │   │
│  │ Collect online donations...        │   │
│  │ [Start Application]                │   │
│  └─────────────────────────────────────┘   │
│                                             │
│  Your Active Modules                        │
│  ✓ Sermons, ✓ Prayer Requests...           │
└─────────────────────────────────────────────┘
```

### 2. Onboarding Wizard UI

**KYC Type (Donations Example)**:
```
┌─────────────────────────────────────────────┐
│  Activate Donations Module             [X]  │
├─────────────────────────────────────────────┤
│  Step 1: Church Information                 │
│  [Progress: 1 of 4]                         │
│                                             │
│  Church Registration Number *               │
│  [____________________]                     │
│                                             │
│  Year Established                           │
│  [____________________]                     │
│                                             │
│  [Save & Continue →]                        │
└─────────────────────────────────────────────┘
```

**Document Upload Section**:
```
Step 3: Required Documents

☐ Church Registration Certificate
   [Upload PDF/Image] registration.pdf ✓

☐ Board Resolution Letter  
   [Upload PDF/Image] board_reso.pdf ✓
   Template: [Download Sample]

☐ Bank Account Details
   Bank: [_______________]
   Account Name: [_______________]
   Account Number: [_______________]
   [Upload Cancelled Cheep/Statement]
```

### 3. SuperAdmin Review Dashboard

```
┌──────────────────────────────────────────────────────┐
│ Module Activation Requests                [Filters]  │
├──────────────────────────────────────────────────────┤
│ Status: Pending (5) | Approved (23) | Rejected (2)   │
├──────────────────────────────────────────────────────┤
│ Church              Module        Submitted  Action  │
│ Grace Church        Donations     2 hrs ago  [Review]│
│ Hope Fellowship     Shop          5 hrs ago  [Review]│
│ ...                                                  │
└──────────────────────────────────────────────────────┘
```

---

## Part 5: API Endpoints

### Tenant Marketplace API
```
GET  /api/modules/marketplace           - List available modules
POST /api/modules/{key}/request         - Request module activation
GET  /api/modules/onboarding/{key}      - Get onboarding form config
POST /api/modules/onboarding/{key}      - Submit onboarding data
POST /api/modules/onboarding/{key}/docs - Upload documents
```

### SuperAdmin Review API
```
GET  /admin/api/module-requests         - List pending requests
GET  /admin/api/module-requests/{id}    - Get request details
POST /admin/api/module-requests/{id}/approve
POST /admin/api/module-requests/{id}/reject
```

---

## Part 6: Permission & Gate Changes

### New Permission Gates
```php
// In controllers
public function __construct() {
    $this->middleware(['module:donations'])->only(['create', 'store']);
}

// In views
@if(module('donations'))
    <a href="/dashboard/donations">Donations</a>
@endif

// Special permission for module management
@can('Manage Modules')
    <a href="/dashboard/marketplace">Marketplace</a>
@endcan
```

### Module-Aware Route Middleware
```php
Route::group(['middleware' => ['auth', 'module:donations']], function () {
    Route::get('dashboard/finances/donations', [...]);
    Route::get('dashboard/finances/donations/campaigns', [...]);
});
```

---

## Part 7: Implementation Phases

### Phase 1: Foundation (Week 1-2)
1. Create database migrations
2. Update Module model with onboarding relationships
3. Create ModuleOnboardingConfig seeder
4. Update ModuleService with new module keys

### Phase 2: Backend APIs (Week 2-3)
1. Tenant marketplace API endpoints
2. Onboarding submission handling
3. Document upload/storage
4. SuperAdmin review workflow

### Phase 3: UI Components (Week 3-4)
1. Tenant marketplace modal (profile dropdown)
2. Onboarding wizard components
3. KYC form builder (dynamic from JSON schema)
4. SuperAdmin review dashboard

### Phase 4: Module Integration (Week 4-5)
1. Convert existing features to module-gated
2. Add navigation checks
3. Create module-specific seeders
4. Migration scripts for existing data

### Phase 5: Testing & Launch (Week 5-6)
1. End-to-end testing
2. Document creation
3. Soft launch with select tenants

---

## Part 8: Configuration Examples

### Module Onboarding Config (Seeder)
```php
// Donations Module - KYC Type
ModuleOnboardingConfig::create([
    'module_key' => 'donations',
    'onboarding_type' => 'kyc',
    'requires_approval' => true,
    'required_documents' => [
        'registration_certificate' => 'Church Registration Certificate',
        'board_resolution' => 'Board Resolution Letter',
        'bank_details' => 'Bank Account Details',
    ],
    'kyc_form_schema' => [
        ['name' => 'church_reg_number', 'type' => 'text', 'required' => true, 'label' => 'Registration Number'],
        ['name' => 'year_established', 'type' => 'number', 'required' => true, 'label' => 'Year Established'],
        ['name' => 'leadership_structure', 'type' => 'textarea', 'required' => true, 'label' => 'Leadership Structure'],
    ],
]);

// Articles Module - Guided Type
ModuleOnboardingConfig::create([
    'module_key' => 'articles',
    'onboarding_type' => 'guided',
    'requires_approval' => false,
    'tutorial_content' => [
        'steps' => [
            ['title' => 'Welcome', 'content' => '...'],
            ['title' => 'Network Participation', 'content' => '...'],
            ['title' => 'Setup Categories', 'content' => '...'],
        ],
    ],
    'network_participation_enabled' => true,
]);
```

---

## Appendix: Network Participation Concept

When churches opt-in to network participation:
- Their articles can be featured in other churches' "Related Content"
- They receive curated articles from other network churches
- SuperAdmin can promote cross-church collaboration
- Analytics show network reach vs. local reach

This creates value-add for activating modules and builds community.
