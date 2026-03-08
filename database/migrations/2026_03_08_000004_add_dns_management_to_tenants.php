<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add DNS management fields to tenants table.
 * 
 * This migration supports:
 * - Automatic subdomain assignment
 * - Custom domain management
 * - DNS status tracking
 * - SSL certificate status
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            // Rename existing 'domain' to 'custom_domain' for clarity
            // Note: We keep 'domain' for backward compatibility during transition
            
            // Subdomain assigned to this tenant (e.g., 'happychurch-ruiru')
            $table->string('subdomain')->nullable()->after('slug')->unique();
            
            // Full subdomain URL (e.g., 'happychurch-ruiru.happychurchruiru.org')
            $table->string('subdomain_url')->nullable()->after('subdomain');
            
            // Custom domain purchased by tenant (e.g., 'www.happychurchruiru.org')
            $table->string('custom_domain')->nullable()->after('subdomain_url')->unique();
            
            // DNS and SSL status tracking
            $table->enum('dns_status', ['pending', 'active', 'error', 'propagating'])
                ->default('pending')
                ->after('custom_domain');
            
            $table->enum('ssl_status', ['pending', 'active', 'error', 'renewing'])
                ->default('pending')
                ->after('dns_status');
            
            // Whether custom domain feature is enabled (based on plan)
            $table->boolean('custom_domain_enabled')
                ->default(false)
                ->after('ssl_status');
            
            // DNS records for custom domain (JSON)
            $table->json('dns_records')->nullable()->after('custom_domain_enabled');
            
            // When DNS was last verified
            $table->timestamp('dns_last_verified_at')->nullable()->after('dns_records');
            
            // SSL certificate expiry
            $table->timestamp('ssl_expires_at')->nullable()->after('dns_last_verified_at');
            
            // Add index for faster lookups
            $table->index(['subdomain', 'custom_domain'], 'idx_tenant_domains');
        });

        // Migrate existing data: copy 'domain' to 'custom_domain' if it exists
        $this->migrateExistingDomains();
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn([
                'subdomain',
                'subdomain_url',
                'custom_domain',
                'dns_status',
                'ssl_status',
                'custom_domain_enabled',
                'dns_records',
                'dns_last_verified_at',
                'ssl_expires_at',
            ]);
            $table->dropIndex('idx_tenant_domains');
        });
    }

    /**
     * Migrate existing domain data to new structure.
     */
    private function migrateExistingDomains(): void
    {
        $tenants = \DB::table('tenants')->get();
        
        foreach ($tenants as $tenant) {
            $updates = [];
            
            // If there's an existing domain, use it as custom_domain
            if (!empty($tenant->domain)) {
                $updates['custom_domain'] = $tenant->domain;
                $updates['dns_status'] = 'active';
                $updates['ssl_status'] = 'active';
            }
            
            // Set subdomain based on slug
            if (!empty($tenant->slug)) {
                $updates['subdomain'] = $tenant->slug;
                $updates['subdomain_url'] = $tenant->slug . '.happychurchruiru.org';
            }
            
            if (!empty($updates)) {
                \DB::table('tenants')
                    ->where('id', $tenant->id)
                    ->update($updates);
            }
        }
    }
};
