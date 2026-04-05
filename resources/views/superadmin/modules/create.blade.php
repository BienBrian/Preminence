@extends('superadmin.layouts.app')

@section('title', 'Create Module')
@section('page-title', 'Create New Module')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">New Module</h5>
                    <a href="{{ route('superadmin.modules.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Back
                    </a>
                </div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('superadmin.modules.store') }}">
                        @csrf

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Module Key <span class="text-danger">*</span></label>
                                <input type="text" name="key" class="form-control @error('key') is-invalid @enderror"
                                       value="{{ old('key') }}" required placeholder="e.g. finance, sms, attendance">
                                <small class="text-muted">Unique lowercase identifier (no spaces). Cannot be changed later.</small>
                                @error('key')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Display Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                       value="{{ old('name') }}" required placeholder="e.g. Finance & Giving">
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Category <span class="text-danger">*</span></label>
                                <select name="category" class="form-select @error('category') is-invalid @enderror" required>
                                    <option value="">— Select Category —</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->slug }}" {{ old('category') == $category->slug ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('category')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Icon Class</label>
                                <input type="text" name="icon" class="form-control"
                                       value="{{ old('icon', 'bi-box') }}" placeholder="bi-box">
                                <small class="text-muted">Bootstrap Icons class (e.g. bi-cash, bi-people)</small>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Short Description</label>
                            <input type="text" name="short_description" class="form-control"
                                   value="{{ old('short_description') }}" maxlength="255"
                                   placeholder="One-line description shown in marketplace listings">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Full Description</label>
                            <textarea name="description" class="form-control" rows="4"
                                      placeholder="Detailed description of what this module does">{{ old('description') }}</textarea>
                        </div>

                        <!-- Pricing -->
                        <h6 class="mt-4 mb-3 border-bottom pb-2">Pricing</h6>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <div class="form-check mt-2">
                                    <input type="checkbox" name="is_free" class="form-check-input" id="is_free"
                                           value="1" {{ old('is_free') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_free">Free Module</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Monthly Price (KES)</label>
                                <div class="input-group">
                                    <span class="input-group-text">KES</span>
                                    <input type="number" name="price_monthly" class="form-control" step="0.01" min="0"
                                           value="{{ old('price_monthly', 0) }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Yearly Price (KES)</label>
                                <div class="input-group">
                                    <span class="input-group-text">KES</span>
                                    <input type="number" name="price_yearly" class="form-control" step="0.01" min="0"
                                           value="{{ old('price_yearly', 0) }}">
                                </div>
                            </div>
                        </div>

                        <!-- Visibility -->
                        <h6 class="mt-4 mb-3 border-bottom pb-2">Visibility & Status</h6>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input type="checkbox" name="is_active" class="form-check-input" id="is_active"
                                           value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">Active</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input type="checkbox" name="is_featured" class="form-check-input" id="is_featured"
                                           value="1" {{ old('is_featured') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_featured">Featured in Marketplace</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input type="checkbox" name="requires_onboarding" class="form-check-input" id="requires_onboarding"
                                           value="1" {{ old('requires_onboarding') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="requires_onboarding">Requires Onboarding</label>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-2 mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-plus-lg"></i> Create Module
                            </button>
                            <a href="{{ route('superadmin.modules.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow">
                <div class="card-header"><h6 class="mb-0">Tips</h6></div>
                <div class="card-body small text-muted">
                    <p><strong>Module Key:</strong> Use lowercase letters and underscores only. This is how the module is referenced in code and cannot be changed once created.</p>
                    <p><strong>Category:</strong> Groups related modules together in the marketplace view.</p>
                    <p><strong>Free Module:</strong> If checked, tenants can activate this module at no cost.</p>
                    <p><strong>Requires Onboarding:</strong> If checked, tenants must complete an onboarding form before the module is activated.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
