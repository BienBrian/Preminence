@extends('layouts.dashboard')

@section('content')
<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h5 class="m-0 text-header"><i class='fas fa-university'></i> <b>Bank</b> Payment Settings</h5>
            </div>
            <div class="col-sm-6 text-right">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ url('dashboard/home') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="#">Payment Settings</a></li>
                    <li class="breadcrumb-item active">Bank Accounts</li>
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
                                <h5 class="mb-0">Bank Accounts</h5>
                            </div>
                            @can('Add Bank Settings')
                            <div class="col text-right">
                                <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#bankModal" id="addBankBtn">
                                    <i class='fas fa-plus-circle'></i> Add Account
                                </button>
                            </div>
                            @endcan
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-flush" id="banksTable">
                                <thead class="thead-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Account Holder</th>
                                        <th>Bank Name</th>
                                        <th>Account Number</th>
                                        <th>Branch Code (IFSC)</th>
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

<!-- Bank Modal -->
<div class="modal fade" id="bankModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Bank Account Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="bank_id" value="0">
                <input type="hidden" id="bank_user_id" value="{{ auth()->id() }}">
                <div class="form-group mb-3">
                    <label>Account Holder Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="account_holder_name" placeholder="Full name on account">
                </div>
                <div class="form-group mb-3">
                    <label>Bank Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="bank_name" placeholder="e.g. Equity Bank">
                </div>
                <div class="form-group mb-3">
                    <label>Account Number <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="account_number" placeholder="Bank account number">
                </div>
                <div class="form-group mb-3">
                    <label>Branch Code (IFSC / Sort Code)</label>
                    <input type="text" class="form-control" id="ifsc" placeholder="Branch code">
                </div>
                <div class="form-group mb-3">
                    <label>Status</label>
                    <select class="form-control" id="bank_status">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
                <div class="text-right">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveBankBtn">
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
    var table = $('#banksTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ url('dashboard/payments/settings/datatable/banks') }}",
            data: function (d) {
                d.timezone = Intl.DateTimeFormat().resolvedOptions().timeZone;
            }
        },
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
            {data: 'account_holder_name', name: 'account_holder_name'},
            {data: 'bank_name', name: 'bank_name'},
            {data: 'account_number', name: 'account_number'},
            {data: 'ifsc', name: 'ifsc'},
            {data: 'status', name: 'status'},
            {data: 'user.firstname', name: 'user.firstname', defaultContent: '—'},
            {data: 'created_at', name: 'created_at'},
            {data: 'action', name: 'action', orderable: false, searchable: false}
        ],
        order: [[0, 'asc']]
    });

    // Save bank
    $('#saveBankBtn').click(function () {
        var btn = $(this);
        btn.attr('disabled', true).html('<i class="fas fa-spinner fa-pulse"></i> Saving...');
        $.ajax({
            url: "{{ url('dashboard/payments/settings/banks/add') }}",
            type: 'POST',
            data: {
                _token: "{{ csrf_token() }}",
                id: $('#bank_id').val(),
                user_id: $('#bank_user_id').val(),
                account_holder_name: $('#account_holder_name').val(),
                bank_name: $('#bank_name').val(),
                account_number: $('#account_number').val(),
                ifsc: $('#ifsc').val(),
                status: $('#bank_status').val(),
            },
            success: function (res) {
                $('#bankModal').modal('hide');
                table.ajax.reload();
                toastr.success('Bank account saved successfully.');
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
    $('#addBankBtn').click(function () {
        $('#bank_id').val(0);
        $('#account_holder_name').val('');
        $('#bank_name').val('');
        $('#account_number').val('');
        $('#ifsc').val('');
        $('#bank_status').val(1);
    });
});
</script>
@endpush
