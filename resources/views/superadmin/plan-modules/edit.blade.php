@extends('superadmin.layouts.app')

@section('title', 'Edit Plan Modules: ' . $plan->name)
@section('page-title', 'Edit Plan Modules')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">{{ $plan->name }}</h4>
            <span class="text-muted">KES {{ number_format($plan->price) }}/month — Assign which modules are included or available as add-ons</span>
        </div>
        <a href="{{ route('superadmin.plan-modules.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to Matrix
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form method="POST" action="{{ route('superadmin.plan-modules.update', $plan) }}">
        @csrf
        @method('PUT')

        <div class="card shadow">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="min-width: 220px;">Module</th>
                                <th class="text-center" style="width: 100px;">Included</th>
                                <th class="text-center" style="width: 100px;">Available</th>
                                <th style="width: 160px;">Monthly Override</th>
                                <th style="width: 160px;">Yearly Override</th>
                                <th style="width: 100px;">Trial Days</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($modules as $i => $module)
                                @php
                                    $assignment = $assignments[$module->key] ?? null;
                                @endphp
                                <tr>
                                    <td>
                                        <input type="hidden" name="modules[{{ $i }}][module_key]" value="{{ $module->key }}">
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="bi {{ $module->icon ?? 'bi-box' }} text-primary"></i>
                                            <div>
                                                <div class="fw-semibold">{{ $module->name }}</div>
                                                <small class="text-muted">{{ $module->key }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="form-check d-flex justify-content-center">
                                            <input type="checkbox" name="modules[{{ $i }}][is_included]"
                                                   class="form-check-input" value="1"
                                                   {{ ($assignment && $assignment->is_included) ? 'checked' : '' }}>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="form-check d-flex justify-content-center">
                                            <input type="checkbox" name="modules[{{ $i }}][is_available]"
                                                   class="form-check-input" value="1"
                                                   {{ ($assignment && $assignment->is_available) ? 'checked' : '' }}>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text">KES</span>
                                            <input type="number" name="modules[{{ $i }}][price_monthly_override]"
                                                   class="form-control form-control-sm" step="0.01" min="0"
                                                   value="{{ $assignment->price_monthly_override ?? '' }}"
                                                   placeholder="{{ $module->price_monthly ?? 'default' }}">
                                        </div>
                                    </td>
                                    <td>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text">KES</span>
                                            <input type="number" name="modules[{{ $i }}][price_yearly_override]"
                                                   class="form-control form-control-sm" step="0.01" min="0"
                                                   value="{{ $assignment->price_yearly_override ?? '' }}"
                                                   placeholder="{{ $module->price_yearly ?? 'default' }}">
                                        </div>
                                    </td>
                                    <td>
                                        <input type="number" name="modules[{{ $i }}][trial_days]"
                                               class="form-control form-control-sm" min="0"
                                               value="{{ $assignment->trial_days ?? 0 }}">
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">No modules available.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Save Changes
                </button>
                <a href="{{ route('superadmin.plan-modules.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </div>
    </form>
</div>
@endsection
