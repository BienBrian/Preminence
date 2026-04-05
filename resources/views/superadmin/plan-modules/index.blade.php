@extends('superadmin.layouts.app')

@section('title', 'Plan-Module Matrix')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>Plan-Module Matrix</h4>
        <div>
            <button class="btn btn-sm btn-outline-primary" onclick="copyPlanModal()">
                <i class="bi bi-copy"></i> Copy from Plan
            </button>
            <a href="{{ route('superadmin.plans.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Plans
            </a>
        </div>
    </div>

    <!-- Legend -->
    <div class="card mb-4">
        <div class="card-body py-2">
            <small class="text-muted">
                <span class="badge bg-success me-2">✓</span> Included in plan
                <span class="badge bg-info ms-3 me-2">+</span> Available as add-on
                <span class="badge bg-secondary ms-3 me-2">-</span> Not available
            </small>
        </div>
    </div>

    <!-- Matrix Table -->
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="min-width: 200px;">Module</th>
                            @foreach($plans as $plan)
                                <th class="text-center" style="min-width: 120px;">
                                    <div>{{ $plan->name }}</div>
                                    <small class="text-muted">KES {{ number_format($plan->price) }}/mo</small>
                                    <div class="mt-1">
                                        <a href="{{ route('superadmin.plan-modules.edit', $plan) }}" class="btn btn-xs btn-outline-primary">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    </div>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @php $currentCategory = null; @endphp
                        @foreach($matrix as $row)
                            @if($currentCategory !== $row['module']->category)
                                @php $currentCategory = $row['module']->category; @endphp
                                <tr class="table-secondary">
                                    <td colspan="{{ count($plans) + 1 }}" class="fw-bold text-uppercase small">
                                        {{ $currentCategory }}
                                    </td>
                                </tr>
                            @endif
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <i class="bi {{ $row['module']->icon ?? 'bi-box' }} me-2"></i>
                                        <div>
                                            <div class="fw-medium">{{ $row['module']->name }}</div>
                                            @if(!$row['module']->is_free)
                                                <small class="text-muted">
                                                    KES {{ number_format($row['module']->price_monthly ?? 0) }}/mo
                                                </small>
                                            @else
                                                <small class="text-success">Free</small>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                @foreach($plans as $plan)
                                    @php $cell = $row['plans'][$plan->id]; @endphp
                                    <td class="text-center align-middle">
                                        <div class="dropdown">
                                            <button class="btn btn-sm {{ $cell['is_included'] ? 'btn-success' : ($cell['is_available'] ? 'btn-info' : 'btn-outline-secondary') }} dropdown-toggle w-100" 
                                                    type="button" data-bs-toggle="dropdown">
                                                @if($cell['is_included'])
                                                    <i class="bi bi-check-lg"></i> Included
                                                @elseif($cell['is_available'])
                                                    <i class="bi bi-plus-lg"></i> Add-on
                                                @else
                                                    <i class="bi bi-dash"></i> -
                                                @endif
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li>
                                                    <form method="POST" action="{{ route('superadmin.plan-modules.toggle') }}" class="dropdown-item p-0">
                                                        @csrf
                                                        <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                                                        <input type="hidden" name="module_key" value="{{ $row['module']->key }}">
                                                        <input type="hidden" name="field" value="is_included">
                                                        <button type="submit" class="btn btn-link text-decoration-none text-dark w-100 text-start px-3 py-2">
                                                            <i class="bi bi-check-lg text-success"></i> 
                                                            {{ $cell['is_included'] ? 'Remove Inclusion' : 'Include in Plan' }}
                                                        </button>
                                                    </form>
                                                </li>
                                                <li>
                                                    <form method="POST" action="{{ route('superadmin.plan-modules.toggle') }}" class="dropdown-item p-0">
                                                        @csrf
                                                        <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                                                        <input type="hidden" name="module_key" value="{{ $row['module']->key }}">
                                                        <input type="hidden" name="field" value="is_available">
                                                        <button type="submit" class="btn btn-link text-decoration-none text-dark w-100 text-start px-3 py-2">
                                                            <i class="bi bi-plus-lg text-info"></i>
                                                            {{ $cell['is_available'] ? 'Remove Add-on' : 'Make Available' }}
                                                        </button>
                                                    </form>
                                                </li>
                                                @if($cell['plan_module'])
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <a class="dropdown-item" href="#" data-bs-toggle="modal" 
                                                       data-bs-target="#editPricingModal" 
                                                       data-plan="{{ $plan->id }}"
                                                       data-module="{{ $row['module']->key }}"
                                                       data-monthly="{{ $cell['price_override'] ?? $row['module']->price_monthly }}">
                                                        <i class="bi bi-currency-dollar"></i> Edit Pricing
                                                    </a>
                                                </li>
                                                @endif
                                            </ul>
                                        </div>
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Copy Plan Modal -->
<div class="modal fade" id="copyPlanModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('superadmin.plan-modules.copy') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Copy Plan Modules</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Source Plan</label>
                        <select name="source_plan_id" class="form-select" required>
                            @foreach($plans as $plan)
                                <option value="{{ $plan->id }}">{{ $plan->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Target Plan</label>
                        <select name="target_plan_id" class="form-select" required>
                            @foreach($plans as $plan)
                                <option value="{{ $plan->id }}">{{ $plan->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle"></i>
                        This will overwrite all module assignments for the target plan.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Copy Modules</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function copyPlanModal() {
    new bootstrap.Modal(document.getElementById('copyPlanModal')).show();
}
</script>
@endsection
