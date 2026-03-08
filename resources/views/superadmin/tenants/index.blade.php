@extends('superadmin.layouts.app')

@section('title', 'Tenants')
@section('page-title', 'Manage Tenants')

@section('content')
<div class="card shadow">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">All Tenants</h6>
        <a href="{{ route('superadmin.tenants.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg"></i> Add Tenant
        </a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Slug/Domain</th>
                        <th>Plan</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tenants as $tenant)
                        <tr>
                            <td>{{ $tenant->id }}</td>
                            <td>
                                <strong>{{ $tenant->name }}</strong>
                            </td>
                            <td>
                                {{ $tenant->slug }}
                                @if($tenant->domain)
                                    <br><small class="text-muted">{{ $tenant->domain }}</small>
                                @endif
                            </td>
                            <td>{{ $tenant->plan->name ?? 'No Plan' }}</td>
                            <td>
                                @if($tenant->status === 'active')
                                    <span class="badge bg-success">Active</span>
                                @elseif($tenant->status === 'trial')
                                    <span class="badge bg-info">Trial</span>
                                @elseif($tenant->status === 'suspended')
                                    <span class="badge bg-warning">Suspended</span>
                                @else
                                    <span class="badge bg-secondary">{{ ucfirst($tenant->status) }}</span>
                                @endif
                            </td>
                            <td>{{ $tenant->created_at->format('M d, Y') }}</td>
                            <td>
                                <a href="{{ route('superadmin.tenants.show', $tenant->id) }}" 
                                   class="btn btn-sm btn-info text-white" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('superadmin.tenants.edit', $tenant->id) }}" 
                                   class="btn btn-sm btn-warning text-white" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
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
@endsection
