@extends('layouts.dashboard')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h5 class="m-0 text-header"><i class='fas fa-paper-plane'></i> Invitations</h5>
                </div>
                <div class="col-sm-6 d-none d-sm-block">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('dashboard/home') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ url('dashboard/users/all') }}">Users</a></li>
                        <li class="breadcrumb-item active">Invitations</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            @if($unverifiedPhone > 0 || $unverifiedEmail > 0)
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-shield-alt mr-1"></i> Bulk Verification</h6>
                </div>
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <p class="mb-1">
                                <span class="badge bg-warning text-dark">{{ $unverifiedPhone }}</span> users with unverified phone
                                &nbsp;&middot;&nbsp;
                                <span class="badge bg-warning text-dark">{{ $unverifiedEmail }}</span> users with unverified email
                            </p>
                            <p class="text-muted small mb-0">Send verification requests to all unverified users at once.</p>
                        </div>
                        <div class="col-md-6 text-md-right mt-2 mt-md-0">
                            @if($unverifiedPhone > 0)
                            <button class="btn btn-outline-primary btn-sm btn-bulk-verify" data-type="phone">
                                <i class="fas fa-sms mr-1"></i> Verify Phones ({{ $unverifiedPhone }})
                            </button>
                            @endif
                            @if($unverifiedEmail > 0)
                            <button class="btn btn-outline-info btn-sm btn-bulk-verify" data-type="email">
                                <i class="fas fa-envelope mr-1"></i> Verify Emails ({{ $unverifiedEmail }})
                            </button>
                            @endif
                        </div>
                    </div>
                    <div class="alert bulk-verify-feedback border d-none mt-2 mb-0"></div>
                </div>
            </div>
            @else
            <div class="alert alert-success mb-3">
                <i class="fas fa-check-circle mr-1"></i> All users are verified. No bulk verification needed.
            </div>
            @endif

            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted small">Tracking all user invitations</span>
                        <a href="{{ url('dashboard/users/all') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-arrow-left mr-1"></i> Back to Users
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <table class="table table-striped table-bordered" style="width:100%">
                        <thead>
                            <tr>
                                <th>Contact</th>
                                <th>Method</th>
                                <th>Status</th>
                                <th>Step</th>
                                <th>Linked User</th>
                                <th>Invited By</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('js')
<script>
$(document).ready(function () {
    $('.table').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ url('dashboard/users/invitations/datatable') }}",
        columns: [
            { data: 'contact', name: 'contact', orderable: false },
            { data: 'method', name: 'method', orderable: false },
            { data: 'status_badge', name: 'status_badge', orderable: false },
            { data: 'step', name: 'step', orderable: false },
            { data: 'linked_user', name: 'linked_user', orderable: false },
            { data: 'invited_by_name', name: 'invited_by_name', orderable: false },
            { data: 'date', name: 'date', orderable: false },
            { data: 'action', name: 'action', orderable: false, searchable: false },
        ],
        language: { emptyTable: "<i class='fas fa-ban'></i> No invitations sent yet" },
        order: [],
    });

    // Resend invitation handler
    $(document).on('click', '.btn-resend-invitation', function() {
        var btn = $(this);
        var inviteId = btn.data('id');
        
        Swal.fire({
            title: 'Resend Invitation?',
            text: 'This will generate a new link and send it to the recipient. Continue?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, resend',
            confirmButtonColor: '#3085d6',
        }).then((result) => {
            if (result.isConfirmed) {
                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
                
                $.ajax({
                    url: '{{ url("dashboard/users/invitations/resend") }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        invitation_id: inviteId
                    },
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: response.success,
                            timer: 2000,
                            showConfirmButton: false
                        });
                        $('.table').DataTable().ajax.reload();
                    },
                    error: function(xhr) {
                        var error = xhr.responseJSON?.error || 'Failed to resend invitation';
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: error
                        });
                        btn.prop('disabled', false).html('<i class="fas fa-redo"></i>');
                    }
                });
            }
        });
    });

    // Bulk Verify
    $('.btn-bulk-verify').click(function () {
        var btn = $(this);
        var type = btn.data('type');
        Swal.fire({
            title: 'Send Bulk Verification?',
            text: 'This will send verification requests to all users with unverified ' + type + '. Continue?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, send',
        }).then((result) => {
            if (result.isConfirmed) {
                btn.attr('disabled', 'disabled').html('<i class="fas fa-spinner fa-pulse mr-1"></i> Sending...');
                $.ajax({
                    url: '{{ url("dashboard/users/bulk-verify") }}',
                    type: 'POST',
                    data: { _token: '{{ csrf_token() }}', type: type },
                    timeout: 300000
                }).done(function (data) {
                    $('.bulk-verify-feedback').removeClass('d-none alert-danger').addClass('alert-success')
                        .html('<i class="fas fa-check-circle mr-1"></i> ' + data.success);
                    btn.removeAttr('disabled').html(type === 'phone'
                        ? '<i class="fas fa-sms mr-1"></i> Verify Phones'
                        : '<i class="fas fa-envelope mr-1"></i> Verify Emails');
                }).fail(function (r) {
                    $('.bulk-verify-feedback').removeClass('d-none alert-success').addClass('alert-danger')
                        .html('<i class="fas fa-exclamation-circle mr-1"></i> ' + (r.responseJSON?.error || 'Something went wrong'));
                    btn.removeAttr('disabled').html(type === 'phone'
                        ? '<i class="fas fa-sms mr-1"></i> Verify Phones'
                        : '<i class="fas fa-envelope mr-1"></i> Verify Emails');
                });
            }
        });
    });
});
</script>
@endpush
