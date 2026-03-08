@extends('layouts.dashboard')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h5 class="m-0 bold text-header"><i class="fas fa-hashtag"></i> Mpesa <b>Hash Coverage</b></h5>
            </div>
            <div class="col-sm-6 d-none d-sm-block">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ url('dashboard/home') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ url('dashboard/reports/mpesa-logs') }}">Mpesa Logs</a></li>
                    <li class="breadcrumb-item active">Hash Coverage</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">

        {{-- Stats Row --}}
        <div class="row mb-3">
            <div class="col-md-3">
                <div class="card text-center shadow-sm border-left-primary">
                    <div class="card-body py-3">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Users with Phone</div>
                        <div class="h4 mb-0 font-weight-bold">{{ $totalWithPhone }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center shadow-sm border-left-success">
                    <div class="card-body py-3">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Hashes Generated</div>
                        <div class="h4 mb-0 font-weight-bold text-success">{{ $totalHashed }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center shadow-sm border-left-warning">
                    <div class="card-body py-3">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Missing Hashes</div>
                        <div class="h4 mb-0 font-weight-bold text-warning">{{ $totalWithPhone - $totalHashed }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center shadow-sm border-left-info">
                    <div class="card-body py-3">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Coverage</div>
                        <div class="h4 mb-0 font-weight-bold text-info">
                            {{ $totalWithPhone > 0 ? round($totalHashed / $totalWithPhone * 100, 1) : 0 }}%
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Action Buttons --}}
        <div class="card shadow-sm mb-3">
            <div class="card-body py-2 d-flex align-items-center flex-wrap">
                <button id="btn-bulk-rehash" class="btn btn-success btn-sm mr-2">
                    <i class="fas fa-sync-alt mr-1"></i> Bulk Re-Hash All Users
                </button>
                <span class="text-muted small ml-2" id="bulk-rehash-status"></span>
            </div>
        </div>

        {{-- Users Table --}}
        <div class="card shadow-sm">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h6 class="mb-0"><i class="fas fa-users mr-1"></i> Users — Mpesa Hash Status</h6>
                <div>
                    <input type="text" id="hash-search" class="form-control form-control-sm" placeholder="Search name / phone..." style="width:220px;">
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-sm mb-0" id="hash-coverage-table">
                        <thead class="thead-light">
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Phone</th>
                                <th>Hash (SHA-256)</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $u)
                            @php
                                $hasHash = $hashedPhones->has($u->phone_normalized ?? '');
                                $hash = $hasHash ? $hashedPhones->get($u->phone_normalized)['phone_hash'] : null;
                            @endphp
                            <tr data-user-id="{{ $u->id }}" data-phone="{{ $u->phone }}" data-name="{{ $u->firstname }} {{ $u->lastname }}">
                                <td class="text-muted small">{{ $loop->iteration }}</td>
                                <td>
                                    <a href="{{ url('dashboard/users/view/' . $u->id) }}">{{ $u->firstname }} {{ $u->lastname }}</a>
                                </td>
                                <td>{{ $u->phone }}</td>
                                <td>
                                    @if($hash)
                                        <code class="small text-muted hash-text" title="{{ $hash }}">{{ substr($hash, 0, 16) }}…</code>
                                        <button class="btn btn-link btn-sm p-0 ml-1 btn-copy-hash" data-hash="{{ $hash }}" title="Copy full hash">
                                            <i class="fas fa-copy text-muted"></i>
                                        </button>
                                    @else
                                        <span class="text-muted small">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if($hasHash)
                                        <span class="badge badge-success"><i class="fas fa-check-circle mr-1"></i>Hashed</span>
                                    @elseif($u->phone)
                                        <span class="badge badge-warning text-dark"><i class="fas fa-exclamation-triangle mr-1"></i>No Hash</span>
                                    @else
                                        <span class="badge badge-secondary">No Phone</span>
                                    @endif
                                </td>
                                <td>
                                    @if($u->phone && !$hasHash)
                                    <button class="btn btn-xs btn-outline-primary btn-rehash-user"
                                        data-user-id="{{ $u->id }}" title="Generate hash for this user">
                                        <i class="fas fa-sync-alt"></i> Hash
                                    </button>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                {{ $users->links() }}
            </div>
        </div>
    </div>
</section>
@endsection

@push('js')
<script>
$(function () {
    // Live search filter
    $('#hash-search').on('keyup', function () {
        var q = $(this).val().toLowerCase();
        $('#hash-coverage-table tbody tr').each(function () {
            var text = $(this).text().toLowerCase();
            $(this).toggle(text.indexOf(q) !== -1);
        });
    });

    // Copy hash to clipboard
    $(document).on('click', '.btn-copy-hash', function () {
        var hash = $(this).data('hash');
        navigator.clipboard.writeText(hash).then(function () {
            toastr.success('Hash copied to clipboard');
        });
    });

    // Per-user rehash
    $(document).on('click', '.btn-rehash-user', function () {
        var btn = $(this);
        var userId = btn.data('user-id');
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-pulse"></i>');
        $.ajax({
            url: '{{ url("dashboard/users/rehash") }}',
            type: 'POST',
            data: { _token: '{{ csrf_token() }}', user_id: userId },
        }).done(function (res) {
            if (res.success) {
                toastr.success(res.success);
                // Update the row status
                var row = btn.closest('tr');
                row.find('td:nth-child(4)').html('<code class="small text-muted" title="' + res.hash + '">' + res.hash.substring(0, 16) + '…</code>');
                row.find('td:nth-child(5)').html('<span class="badge badge-success"><i class="fas fa-check-circle mr-1"></i>Hashed</span>');
                btn.closest('td').html('');
            } else {
                toastr.error(res.error || 'Failed');
                btn.prop('disabled', false).html('<i class="fas fa-sync-alt"></i> Hash');
            }
        }).fail(function () {
            toastr.error('Request failed');
            btn.prop('disabled', false).html('<i class="fas fa-sync-alt"></i> Hash');
        });
    });

    // Bulk rehash
    $('#btn-bulk-rehash').on('click', function () {
        var btn = $(this);
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-pulse mr-1"></i> Hashing…');
        $('#bulk-rehash-status').text('');
        $.ajax({
            url: '{{ url("dashboard/reports/mpesa-logs/bulk-rehash") }}',
            type: 'POST',
            data: { _token: '{{ csrf_token() }}' },
        }).done(function (res) {
            var msg = res.hashes_added + ' new hashes added, ' + res.funds_matched + ' funds matched.';
            $('#bulk-rehash-status').text(msg);
            toastr.success(msg);
            btn.prop('disabled', false).html('<i class="fas fa-sync-alt mr-1"></i> Bulk Re-Hash All Users');
            setTimeout(function () { location.reload(); }, 2000);
        }).fail(function () {
            toastr.error('Bulk re-hash failed');
            btn.prop('disabled', false).html('<i class="fas fa-sync-alt mr-1"></i> Bulk Re-Hash All Users');
        });
    });
});
</script>
@endpush
