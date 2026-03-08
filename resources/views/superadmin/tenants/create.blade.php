@extends('superadmin.layouts.app')

@section('title', 'Add Tenant')
@section('page-title', 'Create New Tenant')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Tenant Information</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('superadmin.tenants.store') }}">
                    @csrf
                    
                    <h5 class="mb-3">Basic Information</h5>
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Church/Organization Name *</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                               id="name" name="name" value="{{ old('name') }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="slug" class="form-label">Subdomain Slug *</label>
                        <div class="input-group">
                            <input type="text" class="form-control @error('slug') is-invalid @enderror" 
                                   id="slug" name="slug" value="{{ old('slug') }}" required>
                            <span class="input-group-text">.pisti.co.ke</span>
                        </div>
                        <div class="form-text">This will be the URL for the tenant (e.g., happychurch.pisti.co.ke)</div>
                        @error('slug')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="domain" class="form-label">Custom Domain (Optional)</label>
                        <input type="text" class="form-control @error('domain') is-invalid @enderror" 
                               id="domain" name="domain" value="{{ old('domain') }}">
                        <div class="form-text">e.g., www.happychurch.org (must point to server IP)</div>
                        @error('domain')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="plan_id" class="form-label">Plan</label>
                        <select class="form-select @error('plan_id') is-invalid @enderror" id="plan_id" name="plan_id">
                            <option value="">-- Select Plan --</option>
                            @foreach($plans as $plan)
                                <option value="{{ $plan->id }}" {{ old('plan_id') == $plan->id ? 'selected' : '' }}>
                                    {{ $plan->name }} - ${{ number_format($plan->price, 2) }}/{{ $plan->billing_period }}
                                </option>
                            @endforeach
                        </select>
                        @error('plan_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="status" class="form-label">Status *</label>
                        <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                            <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="trial" {{ old('status') == 'trial' ? 'selected' : '' }}>Trial (14 days)</option>
                            <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="suspended" {{ old('status') == 'suspended' ? 'selected' : '' }}>Suspended</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <hr class="my-4">
                    <h5 class="mb-3">Admin Account</h5>
                    
                    <div class="mb-3">
                        <label for="admin_name" class="form-label">Admin Name *</label>
                        <input type="text" class="form-control @error('admin_name') is-invalid @enderror" 
                               id="admin_name" name="admin_name" value="{{ old('admin_name') }}" required>
                        @error('admin_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="admin_email" class="form-label">Admin Email *</label>
                        <input type="email" class="form-control @error('admin_email') is-invalid @enderror" 
                               id="admin_email" name="admin_email" value="{{ old('admin_email') }}" required>
                        @error('admin_email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="admin_password" class="form-label">Admin Password *</label>
                        <input type="password" class="form-control @error('admin_password') is-invalid @enderror" 
                               id="admin_password" name="admin_password" required>
                        @error('admin_password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('superadmin.tenants.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Back
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg"></i> Create Tenant
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
