@extends('superadmin.layouts.app')

@section('title', 'Edit Subdomain')
@section('page-title', 'Edit Subdomain: ' . $tenant->name)

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Assign Subdomain</h6>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i>
                    <strong>Current Subdomain:</strong> 
                    @if($tenant->subdomain_url)
                        <a href="https://{{ $tenant->subdomain_url }}" target="_blank">
                            {{ $tenant->subdomain_url }}
                        </a>
                    @else
                        <span class="text-muted">Not assigned</span>
                    @endif
                </div>

                <form method="POST" action="{{ route('superadmin.dns.subdomain.update', $tenant) }}">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-3">
                        <label for="subdomain" class="form-label">Subdomain *</label>
                        <div class="input-group">
                            <input type="text" 
                                   class="form-control @error('subdomain') is-invalid @enderror" 
                                   id="subdomain" 
                                   name="subdomain" 
                                   value="{{ old('subdomain', $tenant->subdomain ?? $tenant->slug) }}"
                                   placeholder="church-name"
                                   required>
                            <span class="input-group-text">.happychurchruiru.org</span>
                        </div>
                        <div class="form-text">
                            Only lowercase letters, numbers, and hyphens. No spaces.
                        </div>
                        @error('subdomain')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="alert alert-warning">
                        <h6><i class="bi bi-exclamation-triangle"></i> Important Note</h6>
                        <p class="mb-0">
                            Changing the subdomain will break existing links and bookmarks. 
                            The old subdomain will no longer work unless you configure 
                            redirects at the server level.
                        </p>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('superadmin.dns.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Back
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg"></i> Update Subdomain
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Future Feature Notice -->
        <div class="card shadow mt-4">
            <div class="card-header py-3 bg-info text-white">
                <h6 class="m-0 font-weight-bold"><i class="bi bi-lightbulb"></i> Phase 3 Feature Preview</h6>
            </div>
            <div class="card-body">
                <p>Coming soon in Phase 3:</p>
                <ul class="mb-0">
                    <li>Automatic wildcard DNS record management</li>
                    <li>Subdomain availability checker</li>
                    <li>Bulk subdomain operations</li>
                    <li>Subdomain redirect rules</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
