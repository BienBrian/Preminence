@extends('layouts.dashboard')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h5 class="m-0 text-header"><i class='fas fa-comment'></i> SMS Messages</h5>
                </div>
                <div class="col-sm-6 d-none d-sm-block">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('dashboard/home') }}">Home</a></li>
                        <li class="breadcrumb-item active">SMS</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <!-- Stats Cards -->
            <div class="row mb-3">
                <div class="col-md-3">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h4>{{ number_format($totalSms) }}</h4>
                            <p>Total Messages</p>
                        </div>
                        <div class="icon"><i class="fas fa-comments"></i></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h4>{{ number_format($thisMonth) }}</h4>
                            <p>This Month</p>
                        </div>
                        <div class="icon"><i class="fas fa-calendar-check"></i></div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h4>{{ number_format($totalRecipients) }}</h4>
                            <p>Total Recipients</p>
                        </div>
                        <div class="icon"><i class="fas fa-users"></i></div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="small-box" style="background:#6f42c1;color:#fff;">
                        <div class="inner">
                            <h4>{{ number_format($invitationCount) }}</h4>
                            <p>Invitations Sent</p>
                        </div>
                        <div class="icon"><i class="fas fa-user-plus"></i></div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="small-box bg-secondary">
                        <div class="inner">
                            <h4>{{ number_format($scheduledCount) }}</h4>
                            <p>Scheduled</p>
                        </div>
                        <div class="icon"><i class="fas fa-clock"></i></div>
                    </div>
                </div>
            </div>

            <!-- Birthday Settings -->
            <div class="card card-outline card-info collapsed-card mb-3">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-birthday-cake"></i> Birthday SMS Settings</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-plus"></i></button>
                    </div>
                </div>
                <div class="card-body">
                    <form id="birthday-settings-form">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Message Template</label>
                                    <textarea class="form-control" name="message" rows="3" id="birthday-message" placeholder="Happy Birthday @{{NAME}}! Wishing you a blessed year ahead. - @{{CHURCH}}"></textarea>
                                    <small class="text-muted">Use <code>@{{NAME}}</code> for member name, <code>@{{CHURCH}}</code> for church name</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Send Time</label>
                                    <input type="text" class="form-control" name="send_time" id="birthday-send-time" placeholder="08:00" value="08:00">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Status</label>
                                    <div class="custom-control custom-switch mt-2">
                                        <input type="checkbox" class="custom-control-input" id="birthday-active" name="active" checked>
                                        <label class="custom-control-label" for="birthday-active">Active</label>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-save"></i> Save Settings</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Mpesa Message Settings -->
            <div class="card card-outline card-success collapsed-card mb-3">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-mobile-alt"></i> Mpesa Message Settings</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-plus"></i></button>
                    </div>
                </div>
                <div class="card-body">
                    <form id="mpesa-settings-form">
                        <div class="row">
                            <div class="col-md-9">
                                <div class="form-group">
                                    <label>Message Template</label>
                                    <textarea class="form-control" name="message" rows="4" id="mpesa-message" placeholder="Thank you @{{NAME}} for your contribution of Ksh. @{{AMOUNT}} through @{{ACCOUNT}}..."></textarea>
                                    <small class="text-muted">Use <code>@{{NAME}}</code> for sender name, <code>@{{AMOUNT}}</code> for amount, <code>@{{ACCOUNT}}</code> for account/bill ref</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Status</label>
                                    <div class="custom-control custom-switch mt-2">
                                        <input type="checkbox" class="custom-control-input" id="mpesa-active" name="active" checked>
                                        <label class="custom-control-label" for="mpesa-active">Active</label>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-success btn-sm"><i class="fas fa-save"></i> Save Settings</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- SMS Table with Category Tabs -->
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col">
                            <form class="row" id="sms-filter-form">
                                <div class="col-sm-3">
                                    <input type="text" class="form-control form-control-sm" name="search" placeholder="Search messages...">
                                </div>
                                <div class="col-sm-3">
                                    <input type="text" class="form-control form-control-sm" name="from_date" id="sms-from-date" placeholder="From Date" readonly>
                                </div>
                                <div class="col-sm-3">
                                    <input type="text" class="form-control form-control-sm" name="to_date" id="sms-to-date" placeholder="To Date" readonly>
                                </div>
                                <div class="col-sm-3 text-right">
                                    <button class='btn btn-primary btn-sm' type="button" data-toggle="modal" data-target="#smsComposeModal">
                                        <i class="fas fa-plus"></i> New SMS
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <ul class="nav nav-tabs mt-2" id="sms-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="all-tab" data-toggle="tab" href="#all-pane" role="tab" data-category="all">All Messages</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="mpesa-tab" data-toggle="tab" href="#mpesa-pane" role="tab" data-category="mpesa"><i class="fas fa-mobile-alt"></i> Mpesa</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="birthday-tab" data-toggle="tab" href="#birthday-pane" role="tab" data-category="birthday"><i class="fas fa-birthday-cake"></i> Birthday</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="pledge-tab" data-toggle="tab" href="#pledge-pane" role="tab" data-category="pledge"><i class="fas fa-hand-holding-usd"></i> Pledges</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="manual-tab" data-toggle="tab" href="#manual-pane" role="tab" data-category="manual"><i class="fas fa-edit"></i> Manual</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="verification-tab" data-toggle="tab" href="#verification-pane" role="tab" data-category="verification"><i class="fas fa-user-check"></i> Verification</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="invitation-tab" data-toggle="tab" href="#invitation-pane" role="tab" data-category="invitation"><i class="fas fa-user-plus"></i> Invitations <span class="badge bg-purple" style="background:#6f42c1;">{{ $invitationCount }}</span></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="scheduled-tab" data-toggle="tab" href="#scheduled-pane" role="tab">Scheduled <span class="badge bg-secondary">{{ $scheduledCount }}</span></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="archive-tab" data-toggle="tab" href="#archive-pane" role="tab"><i class="fas fa-archive"></i> Archive</a>
                        </li>
                    </ul>
                </div>
                <div class='card-body'>
                    <div class="tab-content">
                        <!-- All / Mpesa / Birthday / Pledge / Manual tabs share the same DataTable -->
                        <div class="tab-pane fade show active" id="all-pane" role="tabpanel">
                            <div class="table-responsive">
                                <table class='table w-100' id="sms-table">
                                    <thead>
                                        <tr>
                                            <th>Message</th>
                                            <th>Recipients</th>
                                            <th>Sent</th>
                                            <th class='text-end notexport'>Action</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="mpesa-pane" role="tabpanel"><div class="text-muted p-3"><i class="fas fa-spinner fa-pulse"></i> Loading...</div></div>
                        <div class="tab-pane fade" id="birthday-pane" role="tabpanel"><div class="text-muted p-3"><i class="fas fa-spinner fa-pulse"></i> Loading...</div></div>
                        <div class="tab-pane fade" id="pledge-pane" role="tabpanel"><div class="text-muted p-3"><i class="fas fa-spinner fa-pulse"></i> Loading...</div></div>
                        <div class="tab-pane fade" id="manual-pane" role="tabpanel"><div class="text-muted p-3"><i class="fas fa-spinner fa-pulse"></i> Loading...</div></div>
                        <div class="tab-pane fade" id="verification-pane" role="tabpanel"><div class="text-muted p-3"><i class="fas fa-spinner fa-pulse"></i> Loading...</div></div>
                        <div class="tab-pane fade" id="invitation-pane" role="tabpanel"><div class="text-muted p-3"><i class="fas fa-spinner fa-pulse"></i> Loading...</div></div>

                        <!-- Scheduled Messages -->
                        <div class="tab-pane fade" id="scheduled-pane" role="tabpanel">
                            <div class="table-responsive">
                                <table class='table w-100' id="scheduled-table">
                                    <thead>
                                        <tr>
                                            <th>Message Preview</th>
                                            <th>Recipient</th>
                                            <th>Scheduled For</th>
                                            <th class='text-end'>Action</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>

                        <!-- Archive (Summary by day/week/month) -->
                        <div class="tab-pane fade" id="archive-pane" role="tabpanel">
                            <div class="row mb-3 mt-2">
                                <div class="col-auto">
                                    <div class="btn-group btn-group-sm" role="group" id="archive-period-group">
                                        <button type="button" class="btn btn-outline-primary active" data-period="daily">Daily</button>
                                        <button type="button" class="btn btn-outline-primary" data-period="weekly">Weekly</button>
                                        <button type="button" class="btn btn-outline-primary" data-period="monthly">Monthly</button>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <select class="form-control form-control-sm" id="archive-category-filter">
                                        <option value="all">All Categories</option>
                                        <option value="mpesa">Mpesa</option>
                                        <option value="birthday">Birthday</option>
                                        <option value="pledge">Pledges</option>
                                        <option value="verification">Verification</option>
                                        <option value="invitation">Invitations</option>
                                        <option value="manual">Manual</option>
                                    </select>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class='table w-100' id="archive-table">
                                    <thead>
                                        <tr>
                                            <th>Period</th>
                                            <th>Messages</th>
                                            <th>Recipients</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- SMS Compose Modal -->
    <div class="modal fade" id="smsComposeModal" tabindex="-1" aria-labelledby="smsComposeModalLabel" aria-hidden="true" data-backdrop="static">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-gradient-primary text-white">
                    <h5 class="modal-title" id="smsComposeModalLabel"><i class="fas fa-comment"></i> New Message</h5>
                    <button type="button" class="close text-white" id="smsModalCloseBtn" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ url('dashboard/communication/sms/send') }}" method="POST" id="smsComposeForm">
                    @csrf
                    <input type="hidden" name="time" id="sms-schedule-time">
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Whom do you want to send to?</label>
                            <div class='row'>
                                <div class='col-auto pl-3 pr-3'>
                                    <div class="custom-control custom-radio mb-3">
                                        <input name="choice" class="custom-control-input" id="sms-individual" type="radio" checked="checked" value="0">
                                        <label class="custom-control-label" for="sms-individual">Individual</label>
                                    </div>
                                </div>
                                <div class='col-auto pl-3 pr-3'>
                                    <div class="custom-control custom-radio mb-3">
                                        <input name="choice" class="custom-control-input" id="sms-groups" type="radio" value="1">
                                        <label class="custom-control-label" for="sms-groups">Groups</label>
                                    </div>
                                </div>
                                <div class='col-auto pl-3 pr-3'>
                                    <div class="custom-control custom-radio mb-3">
                                        <input name="choice" class="custom-control-input" id="sms-all" type="radio" value="2">
                                        <label class="custom-control-label" for="sms-all">All</label>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class='col'>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text"><i class="fas fa-search"></i></div>
                                        </div>
                                        <input name="search" type="text" class="form-control" placeholder="Search" id="sms-contact-search">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class='sms-myscroll' style="max-height: 200px; overflow-y: auto;">
                            <div class="sms-results mt-2">
                                <p class='small text-muted'><i class="fas fa-spinner fa-pulse"></i> Please Wait</p>
                            </div>
                            <input type='hidden' name='limit' value='0'>
                            <p class='small font-weight-bold sms-lazy'><i class='fas fa-spinner fa-pulse'></i> Loading...</p>
                        </div>
                        <div class='form-group mt-3'>
                            <textarea class='form-control' name="message" rows="3" style="border: 2px solid #ddd;" placeholder="Enter your message here" id="sms-textarea"></textarea>
                            <small class="text-muted"><span id="sms-char-count">0</span> characters</small>
                        </div>
                        <!-- Schedule datetime picker (hidden until Schedule is clicked) -->
                        <div class="form-group" id="sms-schedule-group" style="display:none;">
                            <label><i class="fas fa-calendar-alt"></i> Schedule Date & Time</label>
                            <input type="text" class="form-control" id="sms-schedule-picker" placeholder="Select date and time...">
                        </div>
                    </div>
                    <div class="modal-footer d-flex justify-content-between">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"></i> Cancel</button>
                        <div>
                            <button type="button" class="btn btn-outline-primary" id="sms-schedule-btn">
                                <i class='fas fa-clock'></i> Schedule
                            </button>
                            <button type="submit" class="btn btn-primary" id="sms-send-btn">
                                <i class='fas fa-paper-plane'></i> Send Now
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- View SMS Modal -->
    <div class="modal fade" id="smsViewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-gradient-info text-white">
                    <h5 class="modal-title"><i class="fas fa-comment"></i> View SMS</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="sms-view-loading" class="text-center p-4">
                        <i class="fas fa-spinner fa-spin fa-2x"></i>
                    </div>
                    <div id="sms-view-content" style="display:none;">
                        <div class="row mb-3">
                            <div class="col-md-8">
                                <span id="sms-view-category-badge"></span>
                                <div class="mt-2 p-3 bg-light rounded" id="sms-view-message" style="white-space: pre-wrap;"></div>
                            </div>
                            <div class="col-md-4">
                                <div class="alert alert-success mb-0">
                                    Sent to <strong id="sms-view-recipient-count"></strong> recipient(s)<br>
                                    <small class="text-muted" id="sms-view-sent"></small>
                                    <div id="sms-view-group" class="mt-1"></div>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <h6><i class="fas fa-users"></i> Recipients</h6>
                        <div id="sms-view-members" class="row" style="max-height: 300px; overflow-y: auto;"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('js')
    <script>
        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            flatpickr("#sms-from-date, #sms-to-date", { dateFormat: "Y-m-d" });

            // Schedule datetime picker
            var schedulePicker = flatpickr("#sms-schedule-picker", {
                enableTime: true,
                dateFormat: "Y-m-d H:i",
                minDate: "today",
                time_24hr: true,
                onChange: function(selectedDates, dateStr) {
                    $('#sms-schedule-time').val(dateStr);
                }
            });

            // Birthday send time picker
            flatpickr("#birthday-send-time", {
                enableTime: true,
                noCalendar: true,
                dateFormat: "H:i",
                time_24hr: true,
            });

            // Send mode toggle
            var sendMode = 'now';
            $('#sms-schedule-btn').click(function() {
                if (sendMode === 'schedule') {
                    sendMode = 'now';
                    $('#sms-schedule-group').hide();
                    $('#sms-schedule-time').val('');
                    $(this).html('<i class="fas fa-clock"></i> Schedule').removeClass('btn-primary').addClass('btn-outline-primary');
                    $('#sms-send-btn').show();
                    $('#sms-schedule-submit-btn').remove();
                } else {
                    sendMode = 'schedule';
                    $('#sms-schedule-group').show();
                    $(this).html('<i class="fas fa-times"></i> Cancel Schedule').removeClass('btn-outline-primary').addClass('btn-primary');
                    $('#sms-send-btn').hide();
                    if ($('#sms-schedule-submit-btn').length === 0) {
                        $(this).after('<button type="button" class="btn btn-success ml-1" id="sms-schedule-submit-btn"><i class="fas fa-clock"></i> Schedule Send</button>');
                    }
                }
            });

            // Current category filter
            var currentCategory = 'all';

            // ===== Sent DataTable =====
            var table = $('#sms-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ url('dashboard/communication/sms/datatable') }}",
                    data: function (d) {
                        d.search = $('#sms-filter-form input[name=search]').val();
                        d.from_date = $('#sms-filter-form input[name=from_date]').val();
                        d.to_date = $('#sms-filter-form input[name=to_date]').val();
                        d.category = currentCategory;
                    }
                },
                dom: "<'top'B>rtip",
                buttons: [
                    {
                        extend: 'excel',
                        text: '<i class="fas fa-file-excel"></i> Excel',
                        className: 'btn btn-success btn-sm text-white',
                        exportOptions: { columns: ':not(.notexport)' }
                    },
                    {
                        extend: 'pdf',
                        text: '<i class="fas fa-file-pdf"></i> PDF',
                        className: 'btn btn-danger btn-sm text-white',
                        exportOptions: { columns: ':not(.notexport)' }
                    },
                    {
                        extend: 'print',
                        text: '<i class="fas fa-print"></i> Print',
                        className: 'btn btn-info btn-sm text-white',
                        exportOptions: { columns: ':not(.notexport)' }
                    }
                ],
                order: [],
                language: { emptyTable: "<i class='fas fa-ban'></i> No SMS messages found" },
                columns: [
                    { data: 'preview', name: 'preview', orderable: false, searchable: false },
                    {
                        data: 'recipients', name: 'recipients', orderable: false, searchable: false,
                        render: function(data) {
                            return '<span class="badge bg-primary">' + data + ' recipient' + (data != 1 ? 's' : '') + '</span>';
                        }
                    },
                    {
                        data: 'time_ago', name: 'time_ago', orderable: false, searchable: false,
                        render: function(data, type, row) {
                            return '<small class="text-muted" title="' + row.sent + '">' + data + '</small>';
                        }
                    },
                    { data: 'action', name: 'action', orderable: false, searchable: false },
                ]
            });

            // ===== Scheduled DataTable =====
            var scheduledTable = $('#scheduled-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ url('dashboard/communication/sms/scheduled/datatable') }}",
                    data: function (d) {
                        d.search = $('#sms-filter-form input[name=search]').val();
                    }
                },
                dom: 'rtip',
                order: [],
                language: { emptyTable: "<i class='fas fa-ban'></i> No scheduled messages" },
                columns: [
                    { data: 'preview', name: 'preview', orderable: false, searchable: false },
                    { data: 'recipient_display', name: 'recipient_display', orderable: false, searchable: false },
                    { data: 'scheduled_for', name: 'scheduled_for', orderable: false, searchable: false },
                    { data: 'action', name: 'action', orderable: false, searchable: false },
                ]
            });

            // ===== Archive DataTable =====
            var archivePeriod = 'daily';
            var archiveCategory = 'all';
            var archiveTable = $('#archive-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ url('dashboard/communication/sms/summary') }}",
                    data: function (d) {
                        d.period = archivePeriod;
                        d.category = archiveCategory;
                    }
                },
                dom: "<'top'B>rtip",
                buttons: [
                    {
                        extend: 'excel',
                        text: '<i class="fas fa-file-excel"></i> Excel',
                        className: 'btn btn-success btn-sm text-white',
                    },
                    {
                        extend: 'pdf',
                        text: '<i class="fas fa-file-pdf"></i> PDF',
                        className: 'btn btn-danger btn-sm text-white',
                    },
                    {
                        extend: 'print',
                        text: '<i class="fas fa-print"></i> Print',
                        className: 'btn btn-info btn-sm text-white',
                    }
                ],
                order: [],
                language: { emptyTable: "<i class='fas fa-ban'></i> No archive data" },
                columns: [
                    { data: 'period', name: 'period', orderable: false, searchable: false },
                    { data: 'messages', name: 'messages', orderable: false, searchable: false },
                    { data: 'recipients', name: 'recipients', orderable: false, searchable: false },
                ]
            });

            // Archive period buttons
            $('#archive-period-group button').click(function() {
                $('#archive-period-group button').removeClass('active');
                $(this).addClass('active');
                archivePeriod = $(this).data('period');
                archiveTable.ajax.reload();
            });

            // Archive category filter
            $('#archive-category-filter').change(function() {
                archiveCategory = $(this).val();
                archiveTable.ajax.reload();
            });

            // Category tab switching
            $('#sms-tabs a[data-category]').on('shown.bs.tab', function (e) {
                currentCategory = $(e.target).data('category');
                // Move the sms-table to the active pane
                var paneId = $(e.target).attr('href');
                var $table = $('#sms-table').closest('.table-responsive');
                $(paneId).html('').append($table);
                table.ajax.reload();
            });

            // Refresh scheduled table when tab is shown
            $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                if ($(e.target).attr('href') === '#scheduled-pane') {
                    scheduledTable.ajax.reload();
                }
                if ($(e.target).attr('href') === '#archive-pane') {
                    archiveTable.ajax.reload();
                }
            });

            var timer = null;
            $('#sms-filter-form input[name=search]').keyup(function () {
                clearTimeout(timer);
                timer = setTimeout(function () { table.draw(); }, 500);
            });
            $('#sms-from-date, #sms-to-date').change(function () { table.draw(); });

            // Character counter
            $(document).on('input', '#sms-textarea', function() {
                $('#sms-char-count').text($(this).val().length);
            });

            // ===== Draft protection =====
            var isDirty = false;
            $('#smsComposeModal').on('input change', 'textarea, input[type=text], input[type=checkbox], input[type=radio]', function() {
                isDirty = true;
            });
            $('#smsComposeModal').on('hide.bs.modal', function(e) {
                if (isDirty) {
                    if (!confirm('You have an unsaved draft. Discard?')) {
                        e.preventDefault();
                        return;
                    }
                }
                isDirty = false;
                $('#smsComposeForm')[0].reset();
                $('#sms-individual').prop('checked', true);
                $('.sms-results').html('<p class="small text-muted"><i class="fas fa-spinner fa-pulse"></i> Please Wait</p>');
                $('#sms-schedule-group').hide();
                $('#sms-schedule-time').val('');
                $('#sms-send-btn').html('<i class="fas fa-paper-plane"></i> Send Now').show();
                $('#sms-schedule-btn').html('<i class="fas fa-clock"></i> Schedule').removeClass('btn-primary').addClass('btn-outline-primary');
                $('#sms-schedule-submit-btn').remove();
                sendMode = 'now';
                $('#sms-char-count').text('0');
            });

            // ===== Cancel scheduled message =====
            $(document).on('click', '.btn-cancel-schedule', function() {
                var btn = $(this);
                var id = btn.data('id');
                if (!confirm('Cancel this scheduled message?')) return;
                $.ajax({
                    url: "{{ url('dashboard/communication/sms/schedule/cancel') }}",
                    method: 'POST',
                    data: { id: id },
                    success: function(data) {
                        toastr.success(data.success);
                        scheduledTable.ajax.reload();
                    },
                    error: function() {
                        toastr.error('Failed to cancel scheduled message');
                    }
                });
            });

            // ===== Compose SMS contacts logic =====
            var groups = 0;
            $('#smsComposeModal input[name="choice"]').change(function() {
                if ($("#sms-groups").prop("checked")) {
                    groups = 1;
                } else {
                    groups = 0;
                }
                defaultUsers();
            });

            $('#smsComposeModal').on('shown.bs.modal', function() {
                defaultUsers();
            });

            function defaultUsers() {
                var search = $('#sms-contact-search').val();
                $.ajax({
                    url: "{{ url('dashboard/communication/sms/phone_numbers')}}?groups=" + groups + "&search=" + search,
                    type: "GET",
                }).done(function(data) {
                    $('.sms-results').html("");
                    displayContacts(data);
                }).fail(function(data) {
                    $('.sms-results').html(
                        "<div class='text-danger'><i class='fas fa-exclamation-circle'></i> <strong>Error</strong> loading contacts</div>"
                        );
                });
            }

            var timer2 = null;
            $('#sms-contact-search').keyup(function() {
                if (timer2 != null)
                    clearTimeout(timer2);
                timer2 = setTimeout(function() {
                    var search = $('#sms-contact-search').val();
                    $('.sms-myscroll input[name="limit"]').val(0);
                    $('.sms-results').html("");
                    $('.sms-lazy').html("<i class='fas fa-spinner fa-pulse'></i> Loading...");
                    $('.sms-lazy').show();
                    allowscroll();
                    $.ajax({
                        url: "{{ url('dashboard/communication/sms/phone_numbers')}}/" + search + "?groups=" + groups,
                        method: "GET",
                    }).done(function(data) {
                        displayContacts(data);
                    }).fail(function() {
                        $('.sms-results').html(
                            "<div class='text-danger'><i class='fas fa-exclamation-circle'></i> <strong>Error</strong> loading contacts</div>"
                            );
                    });
                }, 1000);
            });

            $('#smsComposeForm').submit(function(e) {
                e.preventDefault();
                var form = $(this);
                form.unbind('submit');
                var message = $.trim($('#sms-textarea').val());
                if (message.length === 0) {
                    swal({
                        text: "You cannot send an empty message to members",
                        icon: "error",
                    });
                    form.submit(arguments.callee);
                } else {
                    isDirty = false;
                    form.submit();
                }
            });

            // Schedule submit (separate from Send Now)
            $(document).on('click', '#sms-schedule-submit-btn', function() {
                if (!$('#sms-schedule-time').val()) {
                    swal({ text: "Please select a date and time for scheduling", icon: "error" });
                    return;
                }
                $('#smsComposeForm').unbind('submit');
                var message = $.trim($('#sms-textarea').val());
                if (message.length === 0) {
                    swal({ text: "You cannot send an empty message to members", icon: "error" });
                    return;
                }
                isDirty = false;
                $('#smsComposeForm')[0].submit();
            });

            allowscroll();

            function allowscroll() {
                $('.sms-myscroll').on('scroll', function(e) {
                    if ($(this).scrollTop() + $(this).innerHeight() + 1 >= $(this)[0].scrollHeight) {
                        loadmore();
                    }
                });
            }

            function loadmore() {
                $('.sms-myscroll').unbind();
                $('.sms-lazy').show();
                var search = $('#sms-contact-search').val();
                var limit = parseInt($('.sms-myscroll input[name="limit"]').val()) + 10;
                $.ajax({
                    url: '{{ url("dashboard/communication/sms/phone_numbers")}}?search=' + search + "&limit=" + limit + "&groups=" + groups,
                    type: 'GET',
                }).done(function(data) {
                    displayContacts(data);
                    var objects = JSON.parse(data);
                    if (objects.length < 10) {
                        $('.sms-lazy').html("No more contacts");
                    } else {
                        $('.sms-myscroll input[name="limit"]').val(limit);
                        allowscroll();
                    }
                }).fail(function() {
                    $('.sms-lazy').html(
                        "<span class='text-danger'><i class='fas fa-exclamation-triangle'></i> Something went <strong>wrong</strong></span>"
                        );
                });
            }

            function displayContacts(data) {
                var checked = "";
                if ($('#sms-all').is(':checked')) {
                    checked = "checked";
                }
                var objects = JSON.parse(data);
                if (groups == 1) {
                    for (var i = 0; i < objects.length; i++) {
                        $('.sms-results').append("<div class='bg-white mt-2'>" +
                            "<div class='custom-control custom-control-alternative custom-checkbox mb-3'>" +
                            "<input class='custom-control-input' name='groups[]' id='sms-grp-" + objects[i].id +
                            "' value='" + objects[i].id + "' type='checkbox'>" +
                            "<label class='custom-control-label' for='sms-grp-" + objects[i].id + "'><strong>" +
                            objects[i].name + "</strong><br>" +
                            objects[i].members + " member(s)</label>" +
                            "</div></div>");
                    }
                    if (objects.length < 10) {
                        $('.sms-lazy').html("No more groups");
                    }
                } else {
                    for (var i = 0; i < objects.length; i++) {
                        $('.sms-results').append("<div class='bg-white mt-2'>" +
                            "<div class='custom-control custom-control-alternative custom-checkbox mb-3'>" +
                            "<input class='custom-control-input' name='contacts[]' id='sms-c-" + objects[i].id +
                            "' value='" + objects[i].id + "' type='checkbox' " + checked + ">" +
                            "<label class='custom-control-label' for='sms-c-" + objects[i].id + "'><strong>" +
                            objects[i].firstname + " " + objects[i].lastname + "</strong><br>" +
                            objects[i].phone + "</label>" +
                            "</div></div>");
                    }
                    if (objects.length < 10) {
                        $('.sms-lazy').html("No more contacts");
                    }
                }
            }

            // ===== View SMS Modal =====
            $(document).on('click', '.btn-view-sms', function() {
                var id = $(this).data('id');
                $('#sms-view-loading').show();
                $('#sms-view-content').hide();
                $('#smsViewModal').modal('show');
                $.ajax({
                    url: "{{ url('dashboard/communication/sms/json') }}/" + id,
                    type: 'GET',
                }).done(function(data) {
                    // Category badge
                    var badge = '';
                    switch (data.category) {
                        case 'mpesa': badge = '<span class="badge badge-success">Mpesa</span>'; break;
                        case 'birthday': badge = '<span class="badge badge-info"><i class="fas fa-birthday-cake"></i> Birthday</span>'; break;
                        case 'pledge': badge = '<span class="badge badge-warning">Pledge</span>'; break;
                        case 'verification': badge = '<span class="badge badge-primary"><i class="fas fa-user-check"></i> Verification</span>'; break;
                        case 'invitation': badge = '<span class="badge badge-secondary"><i class="fas fa-envelope-open"></i> Invitation</span>'; break;
                        default: badge = '<span class="badge badge-light">Manual</span>';
                    }
                    $('#sms-view-category-badge').html(badge);
                    $('#sms-view-message').text(data.message);
                    $('#sms-view-recipient-count').text(data.recipient_count);
                    $('#sms-view-sent').html(data.sent + ' <br>(' + data.sent_ago + ')');
                    $('#sms-view-group').html(data.group_name ? '<span class="badge badge-default text-white">Group: ' + data.group_name + '</span>' : '');

                    // Members list
                    var html = '';
                    if (data.members.length > 0) {
                        data.members.forEach(function(m) {
                            html += '<div class="col-sm-6 col-md-4 mb-2">' +
                                '<div class="border rounded p-2 small">' +
                                '<strong>' + (m.firstname || '') + ' ' + (m.lastname || '') + '</strong><br>' +
                                '<span class="text-muted">' + (m.phone || '') + '</span>' +
                                '</div></div>';
                        });
                    } else {
                        html = '<div class="col-12"><p class="text-muted small">No recipient details available</p></div>';
                    }
                    $('#sms-view-members').html(html);
                    $('#sms-view-loading').hide();
                    $('#sms-view-content').show();
                }).fail(function() {
                    $('#sms-view-loading').html('<div class="text-danger"><i class="fas fa-exclamation-circle"></i> Failed to load message</div>');
                });
            });

            // ===== Birthday Settings =====
            $.ajax({
                url: "{{ url('dashboard/communication/sms/birthday/settings') }}",
                type: 'GET',
                success: function(data) {
                    if (data) {
                        $('#birthday-message').val(data.message);
                        $('#birthday-send-time').val(data.send_time);
                        $('#birthday-active').prop('checked', data.active == 1);
                    }
                }
            });

            $('#birthday-settings-form').submit(function(e) {
                e.preventDefault();
                $.ajax({
                    url: "{{ url('dashboard/communication/sms/birthday/settings') }}",
                    method: 'POST',
                    data: {
                        message: $('#birthday-message').val(),
                        send_time: $('#birthday-send-time').val(),
                        active: $('#birthday-active').is(':checked') ? 1 : 0,
                    },
                    success: function(data) {
                        toastr.success(data.success);
                    },
                    error: function(xhr) {
                        toastr.error('Failed to save birthday settings');
                    }
                });
            });

            // ===== Mpesa Message Settings =====
            $.ajax({
                url: "{{ url('dashboard/communication/sms/mpesa/settings') }}",
                type: 'GET',
                success: function(data) {
                    if (data) {
                        $('#mpesa-message').val(data.message);
                        $('#mpesa-active').prop('checked', data.active == 1);
                    }
                }
            });

            $('#mpesa-settings-form').submit(function(e) {
                e.preventDefault();
                $.ajax({
                    url: "{{ url('dashboard/communication/sms/mpesa/settings') }}",
                    method: 'POST',
                    data: {
                        message: $('#mpesa-message').val(),
                        active: $('#mpesa-active').is(':checked') ? 1 : 0,
                    },
                    success: function(data) {
                        toastr.success(data.success);
                    },
                    error: function(xhr) {
                        toastr.error('Failed to save Mpesa message settings');
                    }
                });
            });
        });
    </script>
@endpush
