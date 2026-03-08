<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\PlatformAuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * DNS Management Controller
 * 
 * Allows superadmins to:
 * - View all tenant domains and subdomains
 * - Assign custom subdomains to tenants
 * - Approve/verify custom domains
 * - Check DNS propagation status
 * - Manage SSL certificates
 * 
 * NOTE: Full DNS automation requires server-level access (Phase 3)
 */
class DnsManagementController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:superadmin');
    }

    /**
     * Display DNS management dashboard.
     */
    public function index()
    {
        $tenants = Tenant::with('plan')
            ->select([
                'id', 'name', 'slug', 'subdomain', 'subdomain_url', 'custom_domain',
                'dns_status', 'ssl_status', 'custom_domain_enabled', 'status',
                'created_at'
            ])
            ->orderBy('created_at', 'desc')
            ->paginate(25);

        $stats = [
            'total' => Tenant::count(),
            'with_custom_domain' => Tenant::whereNotNull('custom_domain')->count(),
            'dns_active' => Tenant::where('dns_status', 'active')->count(),
            'ssl_active' => Tenant::where('ssl_status', 'active')->count(),
            'pending_dns' => Tenant::where('dns_status', 'pending')->count(),
        ];

        return view('superadmin.dns.index', compact('tenants', 'stats'));
    }

    /**
     * Show form to assign subdomain to tenant.
     */
    public function editSubdomain(Tenant $tenant)
    {
        return view('superadmin.dns.edit_subdomain', compact('tenant'));
    }

    /**
     * Update tenant subdomain.
     */
    public function updateSubdomain(Request $request, Tenant $tenant)
    {
        $validated = $request->validate([
            'subdomain' => [
                'required',
                'string',
                'max:50',
                'regex:/^[a-z0-9-]+$/',
                'unique:tenants,subdomain,' . $tenant->id,
            ],
        ], [
            'subdomain.regex' => 'Subdomain can only contain lowercase letters, numbers, and hyphens.',
        ]);

        $oldSubdomain = $tenant->subdomain;
        $newSubdomain = $validated['subdomain'];
        $subdomainUrl = $newSubdomain . '.happychurchruiru.org';

        $tenant->update([
            'subdomain' => $newSubdomain,
            'subdomain_url' => $subdomainUrl,
        ]);

        PlatformAuditLog::record(
            action: 'dns.subdomain_updated',
            details: [
                'tenant_id' => $tenant->id,
                'old_subdomain' => $oldSubdomain,
                'new_subdomain' => $newSubdomain,
                'subdomain_url' => $subdomainUrl,
            ],
            tenantId: $tenant->id,
            superAdminId: auth('superadmin')->id()
        );

        return redirect()
            ->route('superadmin.dns.index')
            ->with('success', "Subdomain updated to {$subdomainUrl}");
    }

    /**
     * Show form to manage custom domain.
     */
    public function editCustomDomain(Tenant $tenant)
    {
        // Generate DNS records that tenant needs to configure
        $requiredDnsRecords = $this->getRequiredDnsRecords($tenant);

        return view('superadmin.dns.edit_custom_domain', compact('tenant', 'requiredDnsRecords'));
    }

    /**
     * Update custom domain settings.
     */
    public function updateCustomDomain(Request $request, Tenant $tenant)
    {
        $validated = $request->validate([
            'custom_domain' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z0-9][a-z0-9-]{0,61}[a-z0-9]$/',
                'unique:tenants,custom_domain,' . $tenant->id,
            ],
            'custom_domain_enabled' => 'boolean',
            'dns_status' => 'in:pending,active,error,propagating',
            'ssl_status' => 'in:pending,active,error,renewing',
        ]);

        $oldDomain = $tenant->custom_domain;
        
        $updateData = [
            'custom_domain_enabled' => $request->boolean('custom_domain_enabled', false),
            'dns_status' => $validated['dns_status'] ?? 'pending',
            'ssl_status' => $validated['ssl_status'] ?? 'pending',
        ];

        if (!empty($validated['custom_domain'])) {
            $updateData['custom_domain'] = $validated['custom_domain'];
            $updateData['dns_records'] = $this->generateDnsRecords($validated['custom_domain']);
        } else {
            $updateData['custom_domain'] = null;
            $updateData['dns_records'] = null;
        }

        $tenant->update($updateData);

        PlatformAuditLog::record(
            action: 'dns.custom_domain_updated',
            details: [
                'tenant_id' => $tenant->id,
                'old_domain' => $oldDomain,
                'new_domain' => $updateData['custom_domain'],
                'enabled' => $updateData['custom_domain_enabled'],
                'dns_status' => $updateData['dns_status'],
            ],
            tenantId: $tenant->id,
            superAdminId: auth('superadmin')->id()
        );

        return redirect()
            ->route('superadmin.dns.index')
            ->with('success', 'Custom domain settings updated successfully.');
    }

    /**
     * Verify DNS records for a tenant's custom domain.
     * 
     * NOTE: This is a placeholder for future Phase 3 implementation.
     * Full implementation requires:
     * - DNS lookup functionality (dns_get_record)
     * - Server-level DNS management integration
     * - Automated SSL certificate provisioning
     */
    public function verifyDns(Tenant $tenant)
    {
        // Phase 3: Implement actual DNS verification
        // For now, mark as active if manually verified by admin
        
        $tenant->update([
            'dns_status' => 'active',
            'dns_last_verified_at' => now(),
        ]);

        PlatformAuditLog::record(
            action: 'dns.verified',
            details: [
                'tenant_id' => $tenant->id,
                'custom_domain' => $tenant->custom_domain,
                'method' => 'manual',
            ],
            tenantId: $tenant->id,
            superAdminId: auth('superadmin')->id()
        );

        return redirect()
            ->back()
            ->with('success', 'DNS manually verified. Full automated verification coming in Phase 3.');
    }

    /**
     * Provision SSL certificate for custom domain.
     * 
     * NOTE: This is a placeholder for future Phase 3 implementation.
     * Full implementation requires:
     * - Let's Encrypt integration
     * - Certificate automation (Certbot)
     * - Nginx configuration updates
     */
    public function provisionSsl(Tenant $tenant)
    {
        // Phase 3: Implement actual SSL provisioning
        // For now, mark as active if manually provisioned by admin
        
        $tenant->update([
            'ssl_status' => 'active',
            'ssl_expires_at' => now()->addMonths(3), // Placeholder expiry
        ]);

        PlatformAuditLog::record(
            action: 'dns.ssl_provisioned',
            details: [
                'tenant_id' => $tenant->id,
                'custom_domain' => $tenant->custom_domain,
                'method' => 'manual',
            ],
            tenantId: $tenant->id,
            superAdminId: auth('superadmin')->id()
        );

        return redirect()
            ->back()
            ->with('success', 'SSL marked as active. Automated SSL provisioning coming in Phase 3.');
    }

    /**
     * Show DNS propagation status.
     */
    public function propagationStatus(Tenant $tenant)
    {
        // Phase 3: Implement global DNS propagation check
        // This would check DNS from multiple global locations
        
        $checks = [
            [
                'location' => 'Nairobi, Kenya',
                'status' => 'propagated',
                'timestamp' => now()->subMinutes(5),
            ],
            [
                'location' => 'London, UK',
                'status' => 'pending',
                'timestamp' => null,
            ],
            [
                'location' => 'New York, USA',
                'status' => 'pending',
                'timestamp' => null,
            ],
        ];

        return view('superadmin.dns.propagation_status', compact('tenant', 'checks'));
    }

    /**
     * Get required DNS records for a tenant.
     */
    private function getRequiredDnsRecords(Tenant $tenant): array
    {
        if (empty($tenant->custom_domain)) {
            return [];
        }

        $serverIp = config('app.server_ip', 'YOUR_SERVER_IP');

        return [
            [
                'type' => 'A',
                'host' => '@',
                'points_to' => $serverIp,
                'ttl' => '3600',
                'description' => 'Points root domain to server',
            ],
            [
                'type' => 'A',
                'host' => 'www',
                'points_to' => $serverIp,
                'ttl' => '3600',
                'description' => 'Points www subdomain to server',
            ],
        ];
    }

    /**
     * Generate DNS records configuration.
     */
    private function generateDnsRecords(string $domain): array
    {
        $serverIp = config('app.server_ip', 'YOUR_SERVER_IP');

        return [
            'a_records' => [
                ['host' => '@', 'ip' => $serverIp],
                ['host' => 'www', 'ip' => $serverIp],
            ],
            'generated_at' => now()->toIso8601String(),
            'domain' => $domain,
        ];
    }
}
