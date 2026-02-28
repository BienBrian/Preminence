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
        ],
        language: { emptyTable: "<i class='fas fa-ban'></i> No invitations sent yet" },
        order: [],
    });
});
</script>
@endpush
