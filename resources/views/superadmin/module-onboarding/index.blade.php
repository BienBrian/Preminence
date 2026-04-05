@extends('superadmin.layouts.app')

@section('title', 'Module Onboarding Review')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4><i class="bi bi-clipboard-check"></i> Module Onboarding Review</h4>
            <p class="text-muted mb-0">Review and approve module activation requests</p>
        </div>
        <div class="btn-group">
            <a href="{{ route('superadmin.module-onboarding.index', ['status' => 'pending']) }}" 
               class="btn {{ $status === 'pending' ? 'btn-primary' : 'btn-outline-primary' }}">
                Pending @if($stats['pending'] > 0)<span class="badge bg-danger ms-1">{{ $stats['pending'] }}</span>@endif
            </a>
            <a href="{{ route('superadmin.module-onboarding.index', ['status' => 'approved']) }}" 
               class="btn {{ $status === 'approved' ? 'btn-primary' : 'btn-outline-primary' }}">
                Approved
            </a>
            <a href="{{ route('superadmin.module-onboarding.index', ['status' => 'rejected']) }}" 
               class="btn {{ $status === 'rejected' ? 'btn-primary' : 'btn-outline-primary' }}">
                Rejected
            </a>
            <a href="{{ route('superadmin.module-onboarding.index', ['status' => 'needs_info']) }}" 
               class="btn {{ $status === 'needs_info' ? 'btn-primary' : 'btn-outline-primary' }}">
                Needs Info
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="mb-0">Pending Review</h6>
                            <h3 class="mb-0">{{ $stats['pending'] }}</h3>
                        </div>
                        <i class="bi bi-hourglass-split fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="mb-0">Approved</h6>
                            <h3 class="mb-0">{{ $stats['approved'] }}</h3>
                        </div>
                        <i class="bi bi-check-circle fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="mb-0">Rejected</h6>
                            <h3 class="mb-0">{{ $stats['rejected'] }}</h3>
                        </div>
                        <i class="bi bi-x-circle fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="mb-0">Needs Info</h6>
                            <h3 class="mb-0">{{ $stats['needs_info'] }}</h3>
                        </div>
                        <i class="bi bi-question-circle fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('superadmin.module-onboarding.index') }}" class="row g-3">
                <input type="hidden" name="status" value="{{ $status }}">
                
                <div class="col-md-4">
                    <label class="form-label">Filter by Module</label>
                    <select name="module" class="form-select">
                        <option value="">All Modules</option>
                        @foreach($modulesWithSubmissions as $key => $name)
                            <option value="{{ $key }}" {{ $moduleFilter == $key ? 'selected' : '' }}>
                                {{ $name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-filter"></i> Filter
                    </button>
                    @if($moduleFilter)
                        <a href="{{ route('superadmin.module-onboarding.index', ['status' => $status]) }}" 
                           class="btn btn-outline-secondary ms-2">Clear</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Bulk Actions (for pending) -->
    @if($status === 'pending' && $submissions->count() > 0)
    <div class="card mb-3">
        <div class="card-body py-2">
            <div class="d-flex align-items-center">
                <div class="form-check me-3">
                    <input class="form-check-input" type="checkbox" id="selectAll">
                    <label class="form-check-label" for="selectAll">Select All</label>
                </div>
                <button type="button" class="btn btn-sm btn-success me-2" onclick="bulkApprove()">
                    <i class="bi bi-check-lg"></i> Approve Selected
                </button>
                <button type="button" class="btn btn-sm btn-danger" onclick="bulkReject()">
                    <i class="bi bi-x-lg"></i> Reject Selected
                </button>
            </div>
        </div>
    </div>
    @endif

    <!-- Submissions Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                {{ ucfirst($status) }} Submissions
                <span class="badge bg-secondary">{{ $submissions->total() }}</span>
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            @if($status === 'pending')
                            <th width="40">
                                <input type="checkbox" class="form-check-input" id="selectAllHeader">
                            </th>
                            @endif
                            <th>Church/Tenant</th>
                            <th>Module</th>
                            <th>Submitted</th>
                            <th>Status</th>
                            <th width="200">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($submissions as $submission)
                        <tr>
                            @if($status === 'pending')
                            <td>
                                <input type="checkbox" class="form-check-input submission-checkbox" 
                                       value="{{ $submission->id }}" name="submission_ids[]">
                            </td>
                            @endif
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-circle bg-primary text-white me-2">
                                        {{ substr($submission->tenant->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <strong>{{ $submission->tenant->name }}</strong>
                                        <br><small class="text-muted">{{ $submission->tenant->slug }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-info">
                                    <i class="bi bi-box-seam"></i> {{ $submission->module?->name ?? $submission->module_key }}
                                </span>
                                @if($submission->network_participation_opt_in)
                                    <br><small class="text-success"><i class="bi bi-globe"></i> Network opt-in</small>
                                @endif
                            </td>
                            <td>
                                {{ $submission->submitted_at?->diffForHumans() ?? 'N/A' }}
                                <br><small class="text-muted">{{ $submission->submitted_at?->format('M d, Y H:i') }}</small>
                            </td>
                            <td>
                                @php
                                    $statusClasses = [
                                        'draft' => 'bg-secondary',
                                        'submitted' => 'bg-warning text-dark',
                                        'under_review' => 'bg-info',
                                        'approved' => 'bg-success',
                                        'rejected' => 'bg-danger',
                                        'needs_info' => 'bg-primary',
                                    ];
                                @endphp
                                <span class="badge {{ $statusClasses[$submission->status] ?? 'bg-secondary' }}">
                                    {{ ucfirst(str_replace('_', ' ', $submission->status)) }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('superadmin.module-onboarding.show', $submission->id) }}" 
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i> Review
                                </a>
                                
                                @if($submission->status === 'needs_info')
                                <button class="btn btn-sm btn-outline-info" 
                                        onclick="showResponseModal({{ $submission->id }})">
                                    <i class="bi bi-reply"></i> View Response
                                </button>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="{{ $status === 'pending' ? 6 : 5 }}" class="text-center py-5">
                                <i class="bi bi-inbox fs-1 text-muted"></i>
                                <p class="text-muted mt-2">No {{ $status }} submissions found.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            {{ $submissions->links() }}
        </div>
    </div>
</div>

<!-- Bulk Reject Modal -->
<div class="modal fade" id="bulkRejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="bulkRejectForm" method="POST" action="{{ route('superadmin.module-onboarding.bulk') }}">
                @csrf
                <input type="hidden" name="action" value="reject">
                <div id="bulkRejectIds"></div>
                
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Reject Selected Submissions</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>You are about to reject <strong id="rejectCount">0</strong> submissions.</p>
                    <div class="mb-3">
                        <label class="form-label">Rejection Reason <span class="text-danger">*</span></label>
                        <textarea name="reason" class="form-control" rows="4" required
                                  placeholder="Explain why these submissions are being rejected..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-x-lg"></i> Reject All
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.avatar-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
}
</style>

@push('scripts')
<script>
// Select All checkbox
$('#selectAll, #selectAllHeader').on('change', function() {
    $('.submission-checkbox').prop('checked', $(this).prop('checked'));
});

// Get selected IDs
function getSelectedIds() {
    var ids = [];
    $('.submission-checkbox:checked').each(function() {
        ids.push($(this).val());
    });
    return ids;
}

// Bulk Approve
function bulkApprove() {
    var ids = getSelectedIds();
    if (ids.length === 0) {
        alert('Please select at least one submission.');
        return;
    }
    
    if (!confirm('Approve ' + ids.length + ' submissions?')) {
        return;
    }
    
    // Submit form via AJAX
    $.ajax({
        url: '{{ route("superadmin.module-onboarding.bulk") }}',
        method: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            action: 'approve',
            ids: ids
        },
        success: function() {
            location.reload();
        },
        error: function() {
            alert('Failed to process bulk approval. Please try again.');
        }
    });
}

// Bulk Reject
function bulkReject() {
    var ids = getSelectedIds();
    if (ids.length === 0) {
        alert('Please select at least one submission.');
        return;
    }
    
    $('#rejectCount').text(ids.length);
    
    // Add IDs to form
    var idsHtml = '';
    ids.forEach(function(id) {
        idsHtml += '<input type="hidden" name="ids[]" value="' + id + '">';
    });
    $('#bulkRejectIds').html(idsHtml);
    
    $('#bulkRejectModal').modal('show');
}
</script>
@endpush
@endsection
