@extends('superadmin.layouts.app')

@section('title', 'Plans')
@section('page-title', 'Manage Plans')

@section('content')
<div class="card shadow">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">All Plans</h6>
        <a href="{{ route('superadmin.plans.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg"></i> Add Plan
        </a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Slug</th>
                        <th>Price</th>
                        <th>Billing</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($plans as $plan)
                        <tr>
                            <td>{{ $plan->id }}</td>
                            <td>
                                <strong>{{ $plan->name }}</strong>
                            </td>
                            <td>{{ $plan->slug }}</td>
                            <td>${{ number_format($plan->price, 2) }}</td>
                            <td>{{ ucfirst($plan->billing_period) }}</td>
                            <td>
                                @if($plan->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('superadmin.plans.edit', $plan->id) }}" 
                                   class="btn btn-sm btn-warning text-white" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="bi bi-inbox" style="font-size: 2rem;"></i>
                                <p class="mt-2">No plans found</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
