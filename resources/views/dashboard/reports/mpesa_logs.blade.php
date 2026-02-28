@extends('layouts.dashboard')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h5 class="m-0 text-header"><i class='fas fa-mobile-alt'></i> Mpesa Transaction Logs</h5>
                </div>
                <div class="col-sm-6 d-none d-sm-block">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('dashboard/home') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ url('dashboard/reports') }}">Reports</a></li>
                        <li class="breadcrumb-item active">Mpesa Logs</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <!-- Summary Cards -->
            <div class="row mb-3">
                <div class="col-md-3">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h4>{{ number_format($totalTransactions) }}</h4>
                            <p>Total Transactions</p>
                        </div>
                        <div class="icon"><i class="fas fa-exchange-alt"></i></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h4>{{ number_format($matchedTransactions) }}</h4>
                            <p>Matched to Members</p>
                        </div>
                        <div class="icon"><i class="fas fa-user-check"></i></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h4>{{ number_format($unmatchedTransactions) }}</h4>
                            <p>Unmatched</p>
                        </div>
                        <div class="icon"><i class="fas fa-user-times"></i></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="small-box bg-secondary">
                        <div class="inner">
                            <h4>{{ number_format($totalHashes) }}</h4>
                            <p>Phone Hashes Stored</p>
                        </div>
                        <div class="icon"><i class="fas fa-fingerprint"></i></div>
                    </div>
                </div>
            </div>

            <div class="alert alert-info py-2"><i class="fas fa-info-circle"></i> Showing last 90 days by default. Use the date filters to load older records or change the range.</div>

            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <form class="form-inline" id="filter-form">
                                <div class="form-group mr-2 mb-1">
                                    <label class="mr-1 small">From:</label>
                                    <input type="date" class="form-control form-control-sm" name="date_from">
                                </div>
                                <div class="form-group mr-2 mb-1">
                                    <label class="mr-1 small">To:</label>
                                    <input type="date" class="form-control form-control-sm" name="date_to">
                                </div>
                                <div class="form-group mr-2 mb-1">
                                    <select class="form-control form-control-sm" name="match_status">
                                        <option value="">All Status</option>
                                        <option value="matched">Matched</option>
                                        <option value="unmatched">Unmatched</option>
                                    </select>
                                </div>
                                <button type="button" class="btn btn-sm btn-primary mr-2 mb-1" id="btn-filter"><i class="fas fa-filter"></i> Filter</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary mb-1" id="btn-clear-filter"><i class="fas fa-times"></i> Clear</button>
                            </form>
                        </div>
                        <div class="col-md-4 text-right">
                            <button type="button" class="btn btn-sm btn-success" id="btn-bulk-rehash">
                                <i class="fas fa-sync-alt"></i> Bulk Re-hash & Match
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div id="bulk-result" class="alert alert-info d-none"></div>
                    <table class="table table-striped table-bordered" id="mpesa-table" style="width:100%">
                        <thead>
                            <tr>
                                <th>Trans ID</th>
                                <th>Name</th>
                                <th>Amount</th>
                                <th>Account</th>
                                <th>MSISDN</th>
                                <th>Match Status</th>
                                <th>Date</th>
                                <th style="width:50px"></th>
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
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

    // Default to last 90 days
    var ninetyDaysAgo = new Date();
    ninetyDaysAgo.setDate(ninetyDaysAgo.getDate() - 90);
    var yyyy = ninetyDaysAgo.getFullYear();
    var mm = String(ninetyDaysAgo.getMonth() + 1).padStart(2, '0');
    var dd = String(ninetyDaysAgo.getDate()).padStart(2, '0');
    $('input[name=date_from]').val(yyyy + '-' + mm + '-' + dd);

    var table = $('#mpesa-table').DataTable({
        processing: true,
        serverSide: true,
        scrollX: true,
        ajax: {
            url: "{{ url('dashboard/reports/mpesa-logs/datatable') }}",
            data: function (d) {
                d.date_from = $('input[name=date_from]').val();
                d.date_to = $('input[name=date_to]').val();
                d.match_status = $('select[name=match_status]').val();
            }
        },
        columns: [
            { data: 'TransID', name: 'TransID' },
            { data: 'FirstName', name: 'FirstName' },
            { data: 'amount_fmt', name: 'TransAmount', orderable: true },
            { data: 'BillRefNumber', name: 'BillRefNumber' },
            { data: 'msisdn_display', name: 'MSISDN' },
            { data: 'match_status', name: 'match_status', orderable: false },
            { data: 'date_fmt', name: 'created_at', orderable: true },
            { data: 'action', name: 'action', orderable: false, searchable: false },
        ],
        order: [[6, 'desc']],
        language: { emptyTable: "<i class='fas fa-ban'></i> No transactions found" }
    });

    $('#btn-filter').click(function() { table.draw(); });
    $('#btn-clear-filter').click(function() {
        $('input[name=date_from], input[name=date_to]').val('');
        $('select[name=match_status]').val('');
        table.draw();
    });

    // Single transaction re-hash
    $('#mpesa-table').on('click', '.btn-rehash', function() {
        var btn = $(this);
        var id = btn.data('id');
        btn.html('<i class="fas fa-spinner fa-spin"></i>').attr('disabled', true);
        $.ajax({
            url: "{{ url('dashboard/reports/mpesa-logs/rehash') }}",
            type: 'POST',
            data: { id: id }
        }).done(function(data) {
            if (data.status === 'unidentified') {
                toastr.warning(data.message);
            } else {
                toastr.success(data.message);
            }
            table.draw(false);
        }).fail(function() {
            toastr.error('Failed to re-check transaction.');
        }).always(function() {
            btn.html('<i class="fas fa-sync-alt"></i>').removeAttr('disabled');
        });
    });

    // Bulk re-hash
    $('#btn-bulk-rehash').click(function() {
        var btn = $(this);
        btn.html('<i class="fas fa-spinner fa-spin"></i> Processing...').attr('disabled', true);
        $('#bulk-result').removeClass('d-none alert-success alert-danger').addClass('alert-info')
            .html('<i class="fas fa-spinner fa-spin"></i> Running bulk hash generation and matching...');
        $.ajax({
            url: "{{ url('dashboard/reports/mpesa-logs/bulk-rehash') }}",
            type: 'POST',
            timeout: 120000
        }).done(function(data) {
            $('#bulk-result').removeClass('alert-info').addClass('alert-success')
                .html('<i class="fas fa-check-circle"></i> ' + data.success);
            table.draw(false);
        }).fail(function() {
            $('#bulk-result').removeClass('alert-info').addClass('alert-danger')
                .html('<i class="fas fa-exclamation-circle"></i> Bulk operation failed.');
        }).always(function() {
            btn.html('<i class="fas fa-sync-alt"></i> Bulk Re-hash & Match').removeAttr('disabled');
        });
    });
});
</script>
@endpush
