@extends('superadmin.layouts.app')

@section('title', 'DNS Management')
@section('page-title', 'Domain & DNS Management')

@section('content')
<!-- Stats Cards -->
<div class="row mb-4">
    <div class="col-xl-2 col-md-4 mb-3">
        <div class="card stat-card primary shadow h-100 py-2">
            <div class="card-body">
                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Tenants</div>
                <div class="h5 mb-0 font-weight-bold">{{ $stats['total'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-md-4 mb-3">
        <div class="card stat-card info shadow h-100 py-2">
            <div class="card-body">
                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Custom Domains</div>
                <div class="h5 mb-0 font-weight-bold">{{ $stats['with_custom_domain'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-md-4 mb-3">
        <div class="card stat-card success shadow h-100 py-2">
            <div class="card-body">
                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">DNS Active</div>
                <div class="h5 mb-0 font-weight-bold">{{ $stats['dns_active'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-md-4 mb-3">
        <div class="card stat-card warning shadow h-100 py-2">
            <div class="card-body">
                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">SSL Active</div>
                <div class="h5 mb-0 font-weight-bold">{{ $stats['ssl_active'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-md-4 mb-3">
        <div class="card stat-card danger shadow h-100 py-2">
            <div class="card-body">
                <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Pending DNS</div>
                <div class="h5 mb-0 font-weight-bold">{{ $stats['pending_dns'] }}</div>
            </div>
        </div>
    </div>
</div>

<!-- Future Feature Notice -->
<div class="alert alert-info alert-dismissible fade show mb-4" role="alert">
    <h5 class="alert-heading"><i class="bi bi-info-circle"></i> Phase 3 Roadmap</h5>
    <p class="mb-0">
        <strong>Coming Soon:</strong> Automated DNS verification, Let's Encrypt SSL provisioning, 
        and global propagation monitoring. Currently all DNS changes require manual server configuration.
    </p>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>

<!-- Tenants Table -->
<div class="card shadow">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">Tenant Domains</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Tenant</th>
                        <th>Subdomain</th>
                        <th>Custom Domain</th>
                        <th>DNS Status</th>
                        <th>SSL Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tenants as $tenant)
                        <tr>
                            <td>
                                <strong>{{ $tenant->name }}</strong>
                                <br>
                                <small class="text-muted">{{ $tenant->slug }}</small>
                            </td>
                            <td>
                                @if($tenant->subdomain_url)
                                    <a href="https://{{ $tenant->subdomain_url }}" target="_blank">
                                        {{ $tenant->subdomain_url }}
                                    </a>
                                @else
                                    <span class="text-muted">Not assigned</span>
                                @endif
                            </td>
                            <td>
                                @if($tenant->custom_domain)
                                    <a href="https://{{ $tenant->custom_domain }}" target="_blank">
                                        {{ $tenant->custom_domain }}
                                    </a>
                                    @if($tenant->custom_domain_enabled)
                                        <span class="badge bg-success">Enabled</span>
                                    @else
                                        <span class="badge bg-secondary">Disabled</span>
                                    @endif
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($tenant->dns_status === 'active')
                                    <span class="badge bg-success">Active</span>
                                @elseif($tenant->dns_status === 'pending')
                                    <span class="badge bg-warning">Pending</span>
                                @elseif($tenant->dns_status === 'error')
                                    <span class="badge bg-danger">Error</span>
                                @else
                                    <span class="badge bg-info">{{ ucfirst($tenant->dns_status) }}</span>
                                @endif
                            </td>
                            <td>
                                @if($tenant->ssl_status === 'active')
                                    <span class="badge bg-success">Active</span>
                                @elseif($tenant->ssl_status === 'pending')
                                    <span class="badge bg-warning">Pending</span>
                                @elseif($tenant->ssl_status === 'error')
                                    <span class="badge bg-danger">Error</span>
                                @else
                                    <span class="badge bg-info">{{ ucfirst($tenant->ssl_status) }}</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route('superadmin.dns.subdomain.edit', $tenant) }}" 
                                       class="btn btn-sm btn-outline-primary" title="Edit Subdomain">
                                        <i class="bi bi-globe"></i>
                                    </a>
                                    <a href="{{ route('superadmin.dns.custom_domain.edit', $tenant) }}" 
                                       class="btn btn-sm btn-outline-info" title="Custom Domain">
                                        <i class="bi bi-shield-lock"></i>
                                    </a>
                                    <a href="{{ route('superadmin.dns.propagation', $tenant) }}" 
                                       class="btn btn-sm btn-outline-secondary" title="Propagation Status">
                                        <i class="bi bi-activity"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="bi bi-inbox" style="font-size: 2rem;"></i>
                                <p class="mt-2">No tenants found</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="d-flex justify-content-end">
            {{ $tenants->links() }}
        </div>
    </div>
</div>

<!-- DNS Configuration Guide -->
<div class="card shadow mt-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">DNS Configuration Guide</h6>
    </div>
    <div class="card-body">
        <div class="alert alert-warning">
            <h6><i class="bi bi-exclamation-triangle"></i> Manual Configuration Required</h6>
            <p class="mb-0">
                Until Phase 3 automation is complete, DNS changes require manual server access:
            </p>
        </div>
        
        <h6>Required DNS Records for New Tenants:</h6>
        <pre class="bg-light p-3 rounded"><code>; For subdomain tenants
tenant-slug  IN  A  YOUR_SERVER_IP

; For custom domains (when purchased)
@            IN  A  YOUR_SERVER_IP
www          IN  A  YOUR_SERVER_IP</code></pre>
        
        <h6>SSL Certificate (Let's Encrypt):</h6>
        <pre class="bg-light p-3 rounded"><code>sudo certbot certonly --manual -d "*.happychurchruiru.org" -d "happychurchruiru.org"</code></pre>
    </div>
</div>
@endsection
