@extends('layouts.dashboard')

@section('content')
<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h5 class="m-0 text-header"><i class='fas fa-qrcode'></i> <b>UPI</b> Payment Settings</h5>
            </div>
            <div class="col-sm-6 text-right">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ url('dashboard/home') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="#">Payment Settings</a></li>
                    <li class="breadcrumb-item active">UPI Settings</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12 mb-4">
                <div class="card shadow">
                    <div class="card-header border-0">
                        <div class="row align-items-center">
                            <div class="col">
                                <h5 class="mb-0">UPI IDs</h5>
                            </div>
                            @can('Add UPI Settings')
                            <div class="col text-right">
                                <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#upiModal" id="addUpiBtn">
                                    <i class='fas fa-plus-circle'></i> Add UPI ID
                                </button>
                            </div>
                            @endcan
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-flush" id="upisTable">
                                <thead class="thead-light">
                                    <tr>
                                        <th>#</th>
                                        <th>UPI ID</th>
                                        <th>Name</th>
                                        <th>Status</th>
                                        <th>Added By</th>
                                        <th>Date</th>
                                        <th class="text-right">Action</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- UPI Modal -->
<div class="modal fade" id="upiModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">UPI ID Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="upi_id" value="0">
                <input type="hidden" id="upi_user_id" value="{{ auth()->id() }}">
                <div class="form-group mb-3">
                    <label>UPI ID <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="upi_address" placeholder="e.g. name@paytm">
                </div>
                <div class="form-group mb-3">
                    <label>Display Name</label>
                    <input type="text" class="form-control" id="upi_name" placeholder="Name displayed to payers">
                </div>
                <div class="form-group mb-3">
                    <label>Status</label>
                    <select class="form-control" id="upi_status">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
                <div class="text-right">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveUpiBtn">
                        <i class="fas fa-save"></i> Save
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
$(document).ready(function () {
    var table = $('#upisTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ url('dashboard/payments/settings/datatable/upis') }}",
            data: function (d) {
                d.timezone = Intl.DateTimeFormat().resolvedOptions().timeZone;
            }
        },
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
            {data: 'upi_id', name: 'upi_id', defaultContent: '—'},
            {data: 'name', name: 'name', defaultContent: '—'},
            {data: 'status', name: 'status'},
            {data: 'user.firstname', name: 'user.firstname', defaultContent: '—'},
            {data: 'created_at', name: 'created_at'},
            {data: 'action', name: 'action', orderable: false, searchable: false}
        ],
        order: [[0, 'asc']]
    });

    // Save UPI
    $('#saveUpiBtn').click(function () {
        var btn = $(this);
        btn.attr('disabled', true).html('<i class="fas fa-spinner fa-pulse"></i> Saving...');
        $.ajax({
            url: "{{ url('dashboard/payments/settings/upis/add') }}",
            type: 'POST',
            data: {
                _token: "{{ csrf_token() }}",
                id: $('#upi_id').val(),
                user_id: $('#upi_user_id').val(),
                upi_id: $('#upi_address').val(),
                name: $('#upi_name').val(),
                status: $('#upi_status').val(),
            },
            success: function (res) {
                $('#upiModal').modal('hide');
                table.ajax.reload();
                toastr.success('UPI ID saved successfully.');
            },
            error: function (xhr) {
                var msg = xhr.responseJSON && xhr.responseJSON.error ? xhr.responseJSON.error : 'Error saving. Try again.';
                toastr.error(msg);
            },
            complete: function () {
                btn.attr('disabled', false).html('<i class="fas fa-save"></i> Save');
            }
        });
    });

    // Reset modal on open for new entry
    $('#addUpiBtn').click(function () {
        $('#upi_id').val(0);
        $('#upi_address').val('');
        $('#upi_name').val('');
        $('#upi_status').val(1);
    });
});
</script>
@endpush
