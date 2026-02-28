@extends('layouts.dashboard')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h5 class="m-0 text-header"><i class='fas fa-clone'></i> Duplicate Contacts</h5>
                </div>
                <div class="col-sm-6 d-none d-sm-block">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('dashboard/home') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ url('dashboard/users/all') }}">Users</a></li>
                        <li class="breadcrumb-item active">Duplicates</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col">
                            <p class="mb-0 text-muted">Scan for contacts with similar phone numbers or invalid phone lengths.</p>
                        </div>
                        <div class="col text-right">
                            <button class="btn btn-primary btn-sm" id="btn-scan">
                                <i class="fas fa-search"></i> Scan for Duplicates
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div id="scan-loading" class="text-center d-none py-4">
                        <i class="fas fa-spinner fa-pulse fa-2x text-primary"></i>
                        <p class="mt-2 text-muted">Scanning contacts... This may take a moment.</p>
                    </div>
                    <div id="scan-results" class="d-none">
                        <!-- Invalid Phones Section -->
                        <div id="invalid-section" class="d-none mb-4">
                            <h6><i class="fas fa-exclamation-triangle text-warning"></i> Invalid Phone Numbers (> 12 digits)</h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered" id="invalid-table">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Name</th>
                                            <th>Phone</th>
                                            <th>Length</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Duplicates Section -->
                        <div id="duplicates-section" class="d-none">
                            <div class="d-flex align-items-center mb-2">
                                <h6 class="mb-0"><i class="fas fa-clone text-info"></i> Potential Duplicates</h6>
                                <button class="btn btn-warning btn-sm ml-3 d-none" id="btn-batch-merge">
                                    <i class="fas fa-compress-arrows-alt"></i> Merge All Exact Matches
                                </button>
                            </div>
                            <p class="text-muted small mb-2">For each pair, choose which contact to <strong>keep</strong>. The other will be merged into them (records transferred) and archived.</p>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered" id="duplicates-table">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>User A</th>
                                            <th>Phone A</th>
                                            <th>User B</th>
                                            <th>Phone B</th>
                                            <th>Similarity</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>

                        <div id="no-results" class="d-none text-center py-4">
                            <i class="fas fa-check-circle fa-2x text-success"></i>
                            <p class="mt-2">No duplicates or invalid phone numbers found.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Edit Invalid Phone Modal -->
    <div class="modal fade" id="editPhoneModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-edit mr-1"></i> Edit Contact</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="edit-user-id">
                    <div class="form-group">
                        <label>First Name</label>
                        <input type="text" class="form-control" id="edit-firstname">
                    </div>
                    <div class="form-group">
                        <label>Last Name</label>
                        <input type="text" class="form-control" id="edit-lastname">
                    </div>
                    <div class="form-group">
                        <label>Phone</label>
                        <div class="input-group">
                            <div class="input-group-prepend"><span class="input-group-text">+254</span></div>
                            <input type="text" class="form-control" id="edit-phone">
                        </div>
                        <small class="text-muted" id="edit-phone-digits"></small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary btn-sm" id="btn-save-edit"><i class="fas fa-save mr-1"></i> Save</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Merge Duplicates Modal -->
    <div class="modal fade" id="mergeModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-compress-arrows-alt mr-1"></i> Merge Contacts</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <!-- User A -->
                        <div class="col-md-5">
                            <div class="card" id="merge-card-a">
                                <div class="card-header py-2 d-flex align-items-center">
                                    <strong>User A</strong>
                                    <span class="badge badge-secondary ml-auto" id="merge-id-a"></span>
                                </div>
                                <div class="card-body py-2">
                                    <p class="mb-1"><strong id="merge-name-a"></strong></p>
                                    <p class="mb-1 text-muted small"><i class="fas fa-phone mr-1"></i> <span id="merge-phone-a"></span> <span id="merge-phone-badge-a"></span></p>
                                    <p class="mb-0 text-muted small"><i class="fas fa-envelope mr-1"></i> <span id="merge-email-a">-</span></p>
                                </div>
                                <div class="card-footer py-2 text-center">
                                    <button class="btn btn-sm btn-success btn-select-keep" data-side="a"><i class="fas fa-check mr-1"></i> Keep A</button>
                                </div>
                            </div>
                        </div>
                        <!-- VS -->
                        <div class="col-md-2 d-flex align-items-center justify-content-center">
                            <div class="text-center">
                                <span class="badge badge-pill badge-lg px-3 py-2" id="merge-similarity" style="font-size:1.1rem;"></span>
                                <p class="text-muted small mt-1 mb-0">match</p>
                            </div>
                        </div>
                        <!-- User B -->
                        <div class="col-md-5">
                            <div class="card" id="merge-card-b">
                                <div class="card-header py-2 d-flex align-items-center">
                                    <strong>User B</strong>
                                    <span class="badge badge-secondary ml-auto" id="merge-id-b"></span>
                                </div>
                                <div class="card-body py-2">
                                    <p class="mb-1"><strong id="merge-name-b"></strong></p>
                                    <p class="mb-1 text-muted small"><i class="fas fa-phone mr-1"></i> <span id="merge-phone-b"></span> <span id="merge-phone-badge-b"></span></p>
                                    <p class="mb-0 text-muted small"><i class="fas fa-envelope mr-1"></i> <span id="merge-email-b">-</span></p>
                                </div>
                                <div class="card-footer py-2 text-center">
                                    <button class="btn btn-sm btn-success btn-select-keep" data-side="b"><i class="fas fa-check mr-1"></i> Keep B</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Final details (shown after selecting keep) -->
                    <div id="merge-final-section" class="d-none mt-3">
                        <hr>
                        <h6><i class="fas fa-user-edit mr-1"></i> Final Contact Details <small class="text-muted">(edit before merging)</small></h6>
                        <input type="hidden" id="merge-keep-id">
                        <input type="hidden" id="merge-merge-id">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>First Name</label>
                                    <input type="text" class="form-control" id="merge-final-firstname">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Last Name</label>
                                    <input type="text" class="form-control" id="merge-final-lastname">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Phone</label>
                                    <input type="text" class="form-control" id="merge-final-phone" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-outline-dark btn-sm" id="btn-keep-both" title="Dismiss this pair — both contacts are different people"><i class="fas fa-user-friends mr-1"></i> Keep Both</button>
                    <button type="button" class="btn btn-warning btn-sm d-none" id="btn-confirm-merge"><i class="fas fa-compress-arrows-alt mr-1"></i> Merge Now</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
<script>
$(document).ready(function () {

    function isValidPhone(phone) {
        var digits = (phone || '').replace(/[^0-9]/g, '');
        return digits.length === 12;
    }

    function properCase(str) {
        return (str || '').replace(/\w\S*/g, function(txt) {
            return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();
        });
    }

    function phoneBadge(phone) {
        var len = (phone || '').replace(/[^0-9]/g, '').length;
        return isValidPhone(phone)
            ? '<span class="badge badge-success">' + len + '</span>'
            : '<span class="badge badge-secondary">' + len + '</span>';
    }

    function stripPrefix(phone) {
        var p = (phone || '').replace(/[^0-9]/g, '');
        if (p.length > 3 && p.substring(0, 3) === '254') p = p.substring(3);
        return p;
    }

    function extractError(xhr, fallback) {
        if (!xhr.responseJSON) return fallback || 'Something went wrong';
        if (xhr.responseJSON.error) return xhr.responseJSON.error;
        if (xhr.responseJSON.message) return xhr.responseJSON.message;
        if (xhr.responseJSON.errors) {
            var msgs = [];
            $.each(xhr.responseJSON.errors, function(field, errs) {
                msgs = msgs.concat(errs);
            });
            return msgs.join('<br>');
        }
        return fallback || 'Something went wrong';
    }

    // Store current modal data
    var currentMergeData = {};
    var currentEditRow = null;

    // ============ SCAN ============
    $('#btn-scan').click(function () {
        var btn = $(this);
        btn.attr('disabled', 'disabled');
        $('#scan-loading').removeClass('d-none');
        $('#scan-results').addClass('d-none');

        $.ajax({
            url: '{{ url("dashboard/users/duplicates/scan") }}',
            type: 'GET'
        }).done(function (data) {
            $('#scan-loading').addClass('d-none');
            $('#scan-results').removeClass('d-none');
            var hasResults = false;

            // ---- Invalid Phones ----
            if (data.invalid_phones && data.invalid_phones.length > 0) {
                hasResults = true;
                $('#invalid-section').removeClass('d-none');
                var tbody = $('#invalid-table tbody').empty();
                $.each(data.invalid_phones, function (i, item) {
                    tbody.append(
                        '<tr data-user=\'' + JSON.stringify(item).replace(/'/g, '&#39;') + '\'>' +
                        '<td>' + properCase(item.firstname) + ' ' + properCase(item.lastname) + '</td>' +
                        '<td><code>' + item.phone + '</code></td>' +
                        '<td><span class="badge bg-warning">' + item.length + ' digits</span></td>' +
                        '<td><button class="btn btn-sm btn-info btn-edit-invalid"><i class="fas fa-edit mr-1"></i> Edit</button></td>' +
                        '</tr>'
                    );
                });
            } else {
                $('#invalid-section').addClass('d-none');
            }

            // ---- Duplicates ----
            if (data.duplicates && data.duplicates.length > 0) {
                hasResults = true;
                $('#duplicates-section').removeClass('d-none');
                var tbody = $('#duplicates-table tbody').empty();
                $.each(data.duplicates, function (i, item) {
                    var simColor = item.similarity >= 80 ? 'danger' : (item.similarity >= 60 ? 'warning' : 'info');
                    var aValid = isValidPhone(item.user_a.phone);
                    var bValid = isValidPhone(item.user_b.phone);
                    var isExact = item.similarity === 100;
                    var nameA = properCase(item.user_a.firstname) + ' ' + properCase(item.user_a.lastname);
                    var nameB = properCase(item.user_b.firstname) + ' ' + properCase(item.user_b.lastname);

                    var recommendKeepA = aValid && !bValid;
                    var recommendKeepB = bValid && !aValid;

                    var rowData = JSON.stringify(item).replace(/'/g, '&#39;');

                    var trClass = isExact ? 'table-danger' : '';
                    tbody.append(
                        '<tr class="' + trClass + '" data-dup=\'' + rowData + '\'' +
                            ' data-similarity="' + item.similarity + '"' +
                            (isExact && (recommendKeepA || recommendKeepB) ?
                                ' data-auto-keep="' + (recommendKeepA ? item.user_a.id : item.user_b.id) + '"' +
                                ' data-auto-merge="' + (recommendKeepA ? item.user_b.id : item.user_a.id) + '"'
                                : '') + '>' +
                        '<td><a href="{{ url("dashboard/users/view") }}/' + item.user_a.id + '" target="_blank">' + nameA + '</a></td>' +
                        '<td><code>' + item.user_a.phone + '</code> ' + phoneBadge(item.user_a.phone) + '</td>' +
                        '<td><a href="{{ url("dashboard/users/view") }}/' + item.user_b.id + '" target="_blank">' + nameB + '</a></td>' +
                        '<td><code>' + item.user_b.phone + '</code> ' + phoneBadge(item.user_b.phone) + '</td>' +
                        '<td><span class="badge bg-' + simColor + '">' + item.similarity + '%</span></td>' +
                        '<td><button class="btn btn-sm btn-outline-warning btn-open-merge"><i class="fas fa-compress-arrows-alt mr-1"></i> Resolve</button></td>' +
                        '</tr>'
                    );
                });
                updateBatchButton();
            } else {
                $('#duplicates-section').addClass('d-none');
            }

            if (!hasResults) {
                $('#no-results').removeClass('d-none');
            } else {
                $('#no-results').addClass('d-none');
            }
            btn.removeAttr('disabled');
        }).fail(function (xhr) {
            $('#scan-loading').addClass('d-none');
            Swal.fire('Error', extractError(xhr, 'Failed to scan for duplicates.'), 'error');
            btn.removeAttr('disabled');
        });
    });

    // ============ INVALID PHONE EDIT MODAL ============
    $(document).on('click', '.btn-edit-invalid', function () {
        var row = $(this).closest('tr');
        var user = row.data('user');
        currentEditRow = row;
        $('#edit-user-id').val(user.id);
        $('#edit-firstname').val(properCase(user.firstname));
        $('#edit-lastname').val(properCase(user.lastname));
        var phone = stripPrefix(user.phone);
        $('#edit-phone').val(phone);
        updatePhoneDigits();
        $('#editPhoneModal').modal('show');
    });

    $('#edit-phone').on('input', updatePhoneDigits);
    function updatePhoneDigits() {
        var p = '254' + $('#edit-phone').val().replace(/[^0-9]/g, '').replace(/^0+/, '');
        var len = p.length;
        var cls = len === 12 ? 'text-success' : 'text-danger';
        $('#edit-phone-digits').html('<span class="' + cls + '">' + len + ' digits → ' + p + '</span>');
    }

    $('#btn-save-edit').click(function () {
        var btn = $(this);
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-pulse mr-1"></i> Saving...');
        $.ajax({
            url: '{{ url("dashboard/users/quick-edit") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                id: $('#edit-user-id').val(),
                firstname: $('#edit-firstname').val(),
                lastname: $('#edit-lastname').val(),
                phone: $('#edit-phone').val()
            }
        }).done(function (res) {
            toastr.success(res.success);
            if (currentEditRow) {
                currentEditRow.fadeOut(300, function() { $(this).remove(); });
            }
            $('#editPhoneModal').modal('hide');
        }).fail(function (xhr) {
            if (xhr.status === 409 && xhr.responseJSON && xhr.responseJSON.conflict) {
                // Phone conflict — offer to merge
                var c = xhr.responseJSON.conflict;
                var cur = xhr.responseJSON.current;
                $('#editPhoneModal').modal('hide');

                currentMergeData = {
                    dup: {
                        user_a: cur,
                        user_b: c,
                        similarity: 100
                    },
                    row: currentEditRow
                };

                var nameA = properCase(cur.firstname) + ' ' + properCase(cur.lastname);
                var nameB = properCase(c.firstname) + ' ' + properCase(c.lastname);

                $('#merge-id-a').text('ID: ' + cur.id);
                $('#merge-id-b').text('ID: ' + c.id);
                $('#merge-name-a').text(nameA);
                $('#merge-name-b').text(nameB);
                $('#merge-phone-a').text(cur.phone);
                $('#merge-phone-b').text(c.phone);
                $('#merge-phone-badge-a').html(phoneBadge(cur.phone));
                $('#merge-phone-badge-b').html(phoneBadge(c.phone));
                $('#merge-email-a').text(cur.email || '-');
                $('#merge-email-b').text(c.email || '-');
                $('#merge-similarity').removeClass('badge-danger badge-warning badge-info').addClass('badge-danger').text('100%');

                $('#merge-card-a, #merge-card-b').css('border', '');
                $('#merge-final-section').addClass('d-none');
                $('#btn-confirm-merge').addClass('d-none');

                // Auto-select the one with valid phone
                if (isValidPhone(c.phone)) {
                    selectKeepSide('b');
                } else if (isValidPhone(cur.phone)) {
                    selectKeepSide('a');
                }

                setTimeout(function() { $('#mergeModal').modal('show'); }, 300);
            } else {
                Swal.fire('Error', extractError(xhr, 'Save failed'), 'error');
            }
        }).always(function () {
            btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Save');
        });
    });

    // ============ MERGE MODAL ============
    $(document).on('click', '.btn-open-merge', function () {
        var row = $(this).closest('tr');
        var dup = row.data('dup');
        currentMergeData = { dup: dup, row: row };

        var a = dup.user_a, b = dup.user_b;
        var nameA = properCase(a.firstname) + ' ' + properCase(a.lastname);
        var nameB = properCase(b.firstname) + ' ' + properCase(b.lastname);

        $('#merge-id-a').text('ID: ' + a.id);
        $('#merge-id-b').text('ID: ' + b.id);
        $('#merge-name-a').text(nameA);
        $('#merge-name-b').text(nameB);
        $('#merge-phone-a').text(a.phone);
        $('#merge-phone-b').text(b.phone);
        $('#merge-phone-badge-a').html(phoneBadge(a.phone));
        $('#merge-phone-badge-b').html(phoneBadge(b.phone));
        $('#merge-email-a').text(a.email || '-');
        $('#merge-email-b').text(b.email || '-');

        var simColor = dup.similarity >= 80 ? 'badge-danger' : (dup.similarity >= 60 ? 'badge-warning' : 'badge-info');
        $('#merge-similarity').removeClass('badge-danger badge-warning badge-info').addClass(simColor).text(dup.similarity + '%');

        // Reset selection state
        $('#merge-card-a, #merge-card-b').removeClass('border-success border-2');
        $('#merge-final-section').addClass('d-none');
        $('#btn-confirm-merge').addClass('d-none');

        // Auto-select recommended if one has valid phone
        var aValid = isValidPhone(a.phone);
        var bValid = isValidPhone(b.phone);
        if (aValid && !bValid) {
            selectKeepSide('a');
        } else if (bValid && !aValid) {
            selectKeepSide('b');
        }

        $('#mergeModal').modal('show');
    });

    $(document).on('click', '.btn-select-keep', function () {
        selectKeepSide($(this).data('side'));
    });

    function selectKeepSide(side) {
        var dup = currentMergeData.dup;
        var keep = side === 'a' ? dup.user_a : dup.user_b;
        var merge = side === 'a' ? dup.user_b : dup.user_a;

        // Highlight selected card
        $('#merge-card-a, #merge-card-b').css('border', '');
        $('#merge-card-' + side).css('border', '2px solid #28a745');

        // Fill final details
        $('#merge-keep-id').val(keep.id);
        $('#merge-merge-id').val(merge.id);
        $('#merge-final-firstname').val(properCase(keep.firstname));
        $('#merge-final-lastname').val(properCase(keep.lastname));

        // Use the valid phone
        var keepPhone = isValidPhone(keep.phone) ? keep.phone : (isValidPhone(merge.phone) ? merge.phone : keep.phone);
        $('#merge-final-phone').val(keepPhone);

        $('#merge-final-section').removeClass('d-none');
        $('#btn-confirm-merge').removeClass('d-none');
    }

    // Confirm merge
    $('#btn-confirm-merge').click(function () {
        var btn = $(this);
        var keepId = $('#merge-keep-id').val();
        var mergeId = $('#merge-merge-id').val();
        var firstname = $('#merge-final-firstname').val();
        var lastname = $('#merge-final-lastname').val();
        var phone = $('#merge-final-phone').val();

        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-pulse mr-1"></i> Merging...');
        $.ajax({
            url: '{{ url("dashboard/users/merge") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                keep_id: keepId,
                merge_id: mergeId,
                firstname: firstname,
                lastname: lastname,
                phone: phone
            }
        }).done(function (res) {
            toastr.success(res.success);
            $('#mergeModal').modal('hide');
            if (currentMergeData.row) {
                currentMergeData.row.fadeOut(300, function() { $(this).remove(); updateBatchButton(); });
            }
        }).fail(function (xhr) {
            Swal.fire('Error', extractError(xhr, 'Merge failed'), 'error');
        }).always(function () {
            btn.prop('disabled', false).html('<i class="fas fa-compress-arrows-alt mr-1"></i> Merge Now');
        });
    });

    // Keep Both — dismiss pair
    $('#btn-keep-both').click(function () {
        $('#mergeModal').modal('hide');
        if (currentMergeData.row) {
            currentMergeData.row.addClass('table-light text-muted');
            currentMergeData.row.find('.btn-open-merge').replaceWith('<span class="badge badge-secondary">Kept Both</span>');
            currentMergeData.row.removeAttr('data-auto-keep');
            updateBatchButton();
        }
    });

    // ============ BATCH MERGE ============
    $(document).on('click', '#btn-batch-merge', function () {
        var rows = $('#duplicates-table tbody tr[data-auto-keep]');
        var count = rows.length;
        if (count === 0) return;

        Swal.fire({
            title: 'Batch Merge ' + count + ' Pair' + (count > 1 ? 's' : '') + '?',
            html: 'For each exact-match pair, the contact with a valid 12-digit phone will be kept and the other merged into it. Names will be set to proper case.<br><br><strong>This cannot be undone.</strong>',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e6a817',
            confirmButtonText: 'Yes, merge all'
        }).then(function (result) {
            if (!result.isConfirmed) return;

            var btn = $('#btn-batch-merge');
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-pulse mr-1"></i> Merging...');

            var pairs = [];
            rows.each(function () {
                var dup = $(this).data('dup');
                var keepId = parseInt($(this).attr('data-auto-keep'));
                var mergeId = parseInt($(this).attr('data-auto-merge'));
                var keep = dup.user_a.id === keepId ? dup.user_a : dup.user_b;
                pairs.push({
                    keep_id: keepId,
                    merge_id: mergeId,
                    firstname: keep.firstname,
                    lastname: keep.lastname,
                    row: $(this)
                });
            });

            var done = 0, failed = 0;

            function processNext(index) {
                if (index >= pairs.length) {
                    btn.prop('disabled', false);
                    updateBatchButton();
                    var msg = done + ' merged successfully';
                    if (failed > 0) msg += ', ' + failed + ' failed';
                    Swal.fire('Batch Merge Complete', msg, failed > 0 ? 'warning' : 'success');
                    return;
                }

                var pair = pairs[index];
                btn.html('<i class="fas fa-spinner fa-pulse mr-1"></i> ' + (index + 1) + ' / ' + pairs.length);

                $.ajax({
                    url: '{{ url("dashboard/users/merge") }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        keep_id: pair.keep_id,
                        merge_id: pair.merge_id,
                        firstname: pair.firstname,
                        lastname: pair.lastname
                    }
                }).done(function () {
                    done++;
                    pair.row.fadeOut(200, function() { $(this).remove(); });
                }).fail(function () {
                    failed++;
                    pair.row.addClass('table-secondary');
                    pair.row.removeAttr('data-auto-keep');
                }).always(function () {
                    processNext(index + 1);
                });
            }

            processNext(0);
        });
    });

    function updateBatchButton() {
        var autoRows = $('#duplicates-table tbody tr[data-auto-keep]');
        if (autoRows.length > 0) {
            $('#btn-batch-merge').removeClass('d-none').html(
                '<i class="fas fa-compress-arrows-alt"></i> Merge ' + autoRows.length + ' Exact Match' + (autoRows.length > 1 ? 'es' : '')
            );
        } else {
            $('#btn-batch-merge').addClass('d-none');
        }
    }
});
</script>
@endpush
