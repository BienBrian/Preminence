@extends('layouts.dashboard')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h5 class="m-0 text-header"><i class='fas fa-praying-hands'></i> Prayer Requests</h5>
                </div>
                <div class="col-sm-6 d-none d-sm-block">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('dashboard/home') }}">Home</a></li>
                        <li class="breadcrumb-item active">Prayer Requests</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <!-- Summary Cards -->
            <div class="row mb-3">
                <div class="col-md col-6">
                    <div class="small-box bg-primary">
                        <div class="inner">
                            <h4>{{ $newCount }}</h4>
                            <p>New</p>
                        </div>
                        <div class="icon"><i class="fas fa-plus-circle"></i></div>
                    </div>
                </div>
                <div class="col-md col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h4>{{ $inProgressCount }}</h4>
                            <p>In Progress</p>
                        </div>
                        <div class="icon"><i class="fas fa-spinner"></i></div>
                    </div>
                </div>
                <div class="col-md col-6">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h4>{{ $urgentCount }}</h4>
                            <p>Urgent</p>
                        </div>
                        <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
                    </div>
                </div>
                <div class="col-md col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h4>{{ $answeredCount }}</h4>
                            <p>Answered</p>
                        </div>
                        <div class="icon"><i class="fas fa-check-circle"></i></div>
                    </div>
                </div>
                <div class="col-md col-6">
                    <div class="small-box bg-secondary">
                        <div class="inner">
                            <h4>{{ $totalCount }}</h4>
                            <p>Total</p>
                        </div>
                        <div class="icon"><i class="fas fa-praying-hands"></i></div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col-md-9">
                            <form class="form-inline" id="filter-form">
                                <div class="form-group mr-2 mb-1">
                                    <select class="form-control form-control-sm" name="status">
                                        <option value="">All Status</option>
                                        <option value="new">New</option>
                                        <option value="in_progress">In Progress</option>
                                        <option value="prayed_for">Prayed For</option>
                                        <option value="follow_up">Follow Up</option>
                                        <option value="answered">Answered</option>
                                        <option value="closed">Closed</option>
                                    </select>
                                </div>
                                <div class="form-group mr-2 mb-1">
                                    <select class="form-control form-control-sm" name="category">
                                        <option value="">All Categories</option>
                                        <option value="healing">Healing</option>
                                        <option value="financial">Financial</option>
                                        <option value="family">Family</option>
                                        <option value="spiritual">Spiritual</option>
                                        <option value="thanksgiving">Thanksgiving</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                                <div class="form-group mr-2 mb-1">
                                    <select class="form-control form-control-sm" name="is_urgent">
                                        <option value="">All Priority</option>
                                        <option value="1">Urgent Only</option>
                                    </select>
                                </div>
                                <div class="form-group mr-2 mb-1">
                                    <label class="mr-1 small">From:</label>
                                    <input type="date" class="form-control form-control-sm" name="date_from">
                                </div>
                                <div class="form-group mr-2 mb-1">
                                    <label class="mr-1 small">To:</label>
                                    <input type="date" class="form-control form-control-sm" name="date_to">
                                </div>
                                <button type="button" class="btn btn-sm btn-primary mr-2 mb-1" id="btn-filter"><i class="fas fa-filter"></i> Filter</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary mb-1" id="btn-clear-filter"><i class="fas fa-times"></i> Clear</button>
                            </form>
                        </div>
                        <div class="col-md-3 text-right">
                            @can('Add Prayer Requests')
                            <button class="btn btn-sm btn-success" data-toggle="modal" data-target="#addModal">
                                <i class="fas fa-plus"></i> Add Request
                            </button>
                            @endcan
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <table class="table table-striped table-bordered" id="prayer-table" style="width:100%">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Title</th>
                                <th>Category</th>
                                <th>Submitted By</th>
                                <th>Status</th>
                                <th>Urgency</th>
                                <th>Assigned To</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </section>

    <!-- Add Prayer Request Modal -->
    @can('Add Prayer Requests')
    <div class="modal fade" id="addModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-plus"></i> New Prayer Request</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <form id="addForm">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label>Title <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="title" required maxlength="255">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Type</label>
                                    <select class="form-control" name="request_type">
                                        <option value="prayer">Prayer Request</option>
                                        <option value="praise_report">Praise Report</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Description <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="description" rows="4" required></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Category</label>
                                    <select class="form-control" name="category">
                                        <option value="">-- Select --</option>
                                        <option value="healing">Healing</option>
                                        <option value="financial">Financial</option>
                                        <option value="family">Family</option>
                                        <option value="spiritual">Spiritual</option>
                                        <option value="thanksgiving">Thanksgiving</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Visibility</label>
                                    <select class="form-control" name="visibility">
                                        <option value="staff_only">Staff Only</option>
                                        <option value="public">Public (Prayer Wall)</option>
                                        <option value="confidential">Confidential</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Tags</label>
                                    <select class="form-control select2-tags" name="tags[]" multiple>
                                        @foreach($tags as $tag)
                                        <option value="{{ $tag->id }}">{{ $tag->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="chkUrgent" name="is_urgent" value="1">
                                    <label class="custom-control-label" for="chkUrgent">Mark as Urgent (sends SMS to pastors)</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="chkAnon" name="is_anonymous" value="1">
                                    <label class="custom-control-label" for="chkAnon">Submit Anonymously</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success" id="btnAdd"><i class="fas fa-save"></i> Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endcan
@endsection

@push('js')
<script>
$(document).ready(function () {
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

    $('.select2-tags').select2({ width: '100%', placeholder: 'Select tags' });

    // Check URL params for moderation filter
    var urlParams = new URLSearchParams(window.location.search);
    var initialFilter = urlParams.get('filter') || '';

    var table = $('#prayer-table').DataTable({
        processing: true,
        serverSide: true,
        scrollX: true,
        ajax: {
            url: "{{ url('dashboard/prayer-requests/datatable') }}",
            data: function (d) {
                d.status = $('select[name=status]').val();
                d.category = $('select[name=category]').val();
                d.is_urgent = $('select[name=is_urgent]').val();
                d.date_from = $('input[name=date_from]').val();
                d.date_to = $('input[name=date_to]').val();
                if (initialFilter) d.filter = initialFilter;
            }
        },
        columns: [
            { data: 'id', name: 'id' },
            { data: 'title', name: 'title' },
            { data: 'category', name: 'category', render: function(data) { return data ? data.charAt(0).toUpperCase() + data.slice(1) : '-'; } },
            { data: 'submitted_by_name', name: 'submitted_by_name', orderable: false },
            { data: 'status_badge', name: 'status' },
            { data: 'urgency', name: 'is_urgent' },
            { data: 'assigned_to_name', name: 'assigned_to_name', orderable: false },
            { data: 'date_fmt', name: 'created_at' },
        ],
        order: [[0, 'desc']],
        language: { emptyTable: "<i class='fas fa-praying-hands'></i> No prayer requests found" }
    });

    // Row click to view detail
    $('#prayer-table tbody').on('click', 'tr', function () {
        var data = table.row(this).data();
        if (data) window.location.href = "{{ url('dashboard/prayer-requests') }}/" + data.id;
    });
    $('#prayer-table tbody').css('cursor', 'pointer');

    $('#btn-filter').click(function() { initialFilter = ''; table.draw(); });
    $('#btn-clear-filter').click(function() {
        initialFilter = '';
        $('select[name=status], select[name=category], select[name=is_urgent]').val('');
        $('input[name=date_from], input[name=date_to]').val('');
        table.draw();
    });

    // Add form
    $('#addForm').on('submit', function (e) {
        e.preventDefault();
        var btn = $('#btnAdd');
        btn.html('<i class="fas fa-spinner fa-spin"></i> Saving...').attr('disabled', true);

        var formData = {
            title: $('input[name=title]').val(),
            description: $('textarea[name=description]').val(),
            category: $('#addForm select[name=category]').val(),
            visibility: $('select[name=visibility]').val(),
            request_type: $('select[name=request_type]').val(),
            tags: $('.select2-tags').val(),
            is_urgent: $('#chkUrgent').is(':checked') ? 1 : 0,
            is_anonymous: $('#chkAnon').is(':checked') ? 1 : 0,
        };

        $.ajax({
            url: "{{ url('dashboard/prayer-requests') }}",
            type: 'POST',
            data: formData,
        }).done(function (data) {
            toastr.success(data.success);
            $('#addModal').modal('hide');
            $('#addForm')[0].reset();
            $('.select2-tags').val(null).trigger('change');
            table.draw();
        }).fail(function (xhr) {
            var msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Error creating request.';
            toastr.error(msg);
        }).always(function () {
            btn.html('<i class="fas fa-save"></i> Submit').removeAttr('disabled');
        });
    });
});
</script>
@endpush
