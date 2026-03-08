@extends('superadmin.layouts.app')

@section('title', 'Custom Domain')
@section('page-title', 'Custom Domain: ' . $tenant->name)

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Custom Domain Settings</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('superadmin.dns.custom_domain.update', $tenant) }}">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-3">
                        <label for="custom_domain" class="form-label">Custom Domain</label>
                        <input type="text" 
                               class="form-control @error('custom_domain') is-invalid @enderror" 
                               id="custom_domain" 
                               name="custom_domain" 
                               value="{{ old('custom_domain', $tenant->custom_domain) }}"
                               placeholder="www.yourchurch.org">
                        <div class="form-text">
                            Enter the full domain (e.g., www.happychurchruiru.org). 
                            Leave empty to disable custom domain.
                        </div>
                        @error('custom_domain')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" 
                               class="form-check-input" 
                               id="custom_domain_enabled" 
                               name="custom_domain_enabled" 
                               value="1"
                               {{ old('custom_domain_enabled', $tenant->custom_domain_enabled) ? 'checked' : '' }}>
                        <label class="form-check-label" for="custom_domain_enabled">
                            Enable Custom Domain
                        </label>
                        <div class="form-text">
                            Requires appropriate plan subscription.
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="dns_status" class="form-label">DNS Status</label>
                                <select class="form-select" id="dns_status" name="dns_status">
                                    <option value="pending" {{ old('dns_status', $tenant->dns_status) == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="propagating" {{ old('dns_status', $tenant->dns_status) == 'propagating' ? 'selected' : '' }}>Propagating</option>
                                    <option value="active" {{ old('dns_status', $tenant->dns_status) == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="error" {{ old('dns_status', $tenant->dns_status) == 'error' ? 'selected' : '' }}>Error</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="ssl_status" class="form-label">SSL Status</label>
                                <select class="form-select" id="ssl_status" name="ssl_status">
                                    <option value="pending" {{ old('ssl_status', $tenant->ssl_status) == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="renewing" {{ old('ssl_status', $tenant->ssl_status) == 'renewing' ? 'selected' : '' }}>Renewing</option>
                                    <option value="active" {{ old('ssl_status', $tenant->ssl_status) == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="error" {{ old('ssl_status', $tenant->ssl_status) == 'error' ? 'selected' : '' }}>Error</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    @if(!empty($requiredDnsRecords))
                        <div class="alert alert-info">
                            <h6><i class="bi bi-dns"></i> Required DNS Records</h6>
                            <p class="small mb-2">The tenant needs to configure these DNS records with their domain registrar:</p>
                            <table class="table table-sm table-bordered mb-0">
                                <thead>
                                    <tr>
                                        <th>Type</th>
                                        <th>Host</th>
                                        <th>Points To</th>
                                        <th>TTL</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($requiredDnsRecords as $record)
                                        <tr>
                                            <td>{{ $record['type'] }}</td>
                                            <td>{{ $record['host'] }}</td>
                                            <td>{{ $record['points_to'] }}</td>
                                            <td>{{ $record['ttl'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                    
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('superadmin.dns.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Back
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg"></i> Save Changes
                        </button>
                    </div>
                </form>
                
                @if($tenant->custom_domain)
                    <hr class="my-4">
                    
                    <h6>Quick Actions</h6>
                    <div class="d-flex gap-2">
                        <form method="POST" action="{{ route('superadmin.dns.verify', $tenant) }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-outline-success">
                                <i class="bi bi-check-circle"></i> Verify DNS
                            </button>
                        </form>
                        <form method="POST" action="{{ route('superadmin.dns.provision-ssl', $tenant) }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-outline-info">
                                <i class="bi bi-shield-check"></i> Provision SSL
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        </div>
        
        <!-- Future Feature Notice -->
        <div class="card shadow mt-4">
            <div class="card-header py-3 bg-info text-white">
                <h6 class="m-0 font-weight-bold"><i class="bi bi-lightbulb"></i> Phase 3 Automation Preview</h6>
            </div>
            <div class="card-body">
                <p>Coming soon in Phase 3:</p>
                <ul class="mb-0">
                    <li><strong>Automated DNS Verification:</strong> Real-time DNS record validation</li>
                    <li><strong>Let's Encrypt Integration:</strong> Automatic SSL certificate provisioning</li>
                    <li><strong>Custom Domain Wizard:</strong> Self-service domain setup for tenants</li>
                    <li><strong>Domain Health Monitoring:</strong> Expiry alerts and renewal automation</li>
                    <li><strong>Multi-region DNS:</strong> Global DNS propagation monitoring</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
