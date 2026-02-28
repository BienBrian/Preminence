@extends('layouts.dashboard')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h5 class="m-0 text-header"><i class='fas fa-envelope'></i> Emails</h5>
                </div>
                <div class="col-sm-6 d-none d-sm-block">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('dashboard/home') }}">Home</a></li>
                        <li class="breadcrumb-item active">Emails</li>
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
                            <h4>{{ number_format($totalEmails) }}</h4>
                            <p>Total Emails</p>
                        </div>
                        <div class="icon"><i class="fas fa-envelope"></i></div>
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
                <div class="col-md-3">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h4>{{ number_format($totalRecipients) }}</h4>
                            <p>Total Recipients</p>
                        </div>
                        <div class="icon"><i class="fas fa-users"></i></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="small-box bg-secondary">
                        <div class="inner">
                            <h4>{{ number_format($scheduledCount) }}</h4>
                            <p>Scheduled</p>
                        </div>
                        <div class="icon"><i class="fas fa-clock"></i></div>
                    </div>
                </div>
            </div>

            <!-- Emails Table with Tabs -->
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col">
                            <form class="row" id="email-filter-form">
                                <div class="col-sm-4">
                                    <input type="text" class="form-control form-control-sm" name="search" placeholder="Search emails...">
                                </div>
                                <div class="col-sm-3">
                                    <input type="text" class="form-control form-control-sm" name="from_date" id="email-from-date" placeholder="From Date" readonly>
                                </div>
                                <div class="col-sm-3">
                                    <input type="text" class="form-control form-control-sm" name="to_date" id="email-to-date" placeholder="To Date" readonly>
                                </div>
                                <div class="col-sm-2 text-right">
                                    <button class='btn btn-primary btn-sm' type="button" data-toggle="modal" data-target="#emailComposeModal">
                                        <i class="fas fa-plus"></i> New Email
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <ul class="nav nav-tabs mt-2" id="email-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="email-all-tab" data-toggle="tab" href="#email-sent-pane" role="tab" data-category="all">All Emails</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="email-manual-tab" data-toggle="tab" href="#email-manual-pane" role="tab" data-category="manual"><i class="fas fa-edit"></i> Manual</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="email-automated-tab" data-toggle="tab" href="#email-automated-pane" role="tab" data-category="automated"><i class="fas fa-robot"></i> Automated</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="email-scheduled-tab" data-toggle="tab" href="#email-scheduled-pane" role="tab">Scheduled <span class="badge bg-secondary">{{ $scheduledCount }}</span></a>
                        </li>
                    </ul>
                </div>
                <div class='card-body'>
                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="email-sent-pane" role="tabpanel">
                            <div class="table-responsive">
                                <table class='table w-100' id="email-table">
                                    <thead>
                                        <tr>
                                            <th>Subject / Preview</th>
                                            <th>Recipients</th>
                                            <th>Sent</th>
                                            <th class='text-end'>Action</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="email-manual-pane" role="tabpanel"><div class="text-muted p-3"><i class="fas fa-spinner fa-pulse"></i> Loading...</div></div>
                        <div class="tab-pane fade" id="email-automated-pane" role="tabpanel"><div class="text-muted p-3"><i class="fas fa-spinner fa-pulse"></i> Loading...</div></div>
                        <div class="tab-pane fade" id="email-scheduled-pane" role="tabpanel">
                            <div class="table-responsive">
                                <table class='table w-100' id="email-scheduled-table">
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
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Email Compose Modal -->
    <div class="modal fade" id="emailComposeModal" tabindex="-1" aria-labelledby="emailComposeModalLabel" aria-hidden="true" data-backdrop="static">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-gradient-primary text-white">
                    <h5 class="modal-title" id="emailComposeModalLabel"><i class="fas fa-envelope"></i> New Email</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ url('dashboard/communication/emails/send') }}" method="POST" id="emailComposeForm">
                    @csrf
                    <input type="hidden" name="time" id="email-schedule-time">
                    <div class="modal-body">
                        <div class="email-contacts-section">
                            <div class="form-group">
                                <label>Whom do you want to send to?</label>
                                <div class='row'>
                                    <div class='col-auto pl-3 pr-3'>
                                        <div class="custom-control custom-radio mb-3">
                                            <input name="choice" class="custom-control-input" id="email-individual" type="radio" checked="checked" value="0">
                                            <label class="custom-control-label" for="email-individual">Individual</label>
                                        </div>
                                    </div>
                                    <div class='col-auto pl-3 pr-3'>
                                        <div class="custom-control custom-radio mb-3">
                                            <input name="choice" class="custom-control-input" id="email-groups" type="radio" value="1">
                                            <label class="custom-control-label" for="email-groups">Groups</label>
                                        </div>
                                    </div>
                                    <div class='col-auto pl-3 pr-3'>
                                        <div class="custom-control custom-radio mb-3">
                                            <input name="choice" class="custom-control-input" id="email-all" type="radio" value="2">
                                            <label class="custom-control-label" for="email-all">All</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class='col'>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <div class="input-group-text"><i class="fas fa-search"></i></div>
                                            </div>
                                            <input name="search" type="text" class="form-control" placeholder="Search" id="email-contact-search">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class='email-myscroll' style="max-height: 150px; overflow-y: auto;">
                                <div class="email-results mt-2">
                                    <p class='small text-muted'><i class="fas fa-spinner fa-pulse"></i> Please Wait</p>
                                </div>
                                <input type='hidden' name='limit' value='0'>
                                <p class='small font-weight-bold email-lazy'><i class='fas fa-spinner fa-pulse'></i> Loading...</p>
                            </div>
                        </div>
                        <div class='text-center pb-2'>
                            <a href='#' class='btn btn-outline-primary btn-sm email-write-message'><strong><i class='fas fa-arrow-right'></i> Write Message</strong></a>
                        </div>
                        <div class='email-message-section' style="display:none;">
                            <div class='form-group'>
                                <input name="subject" class="form-control" placeholder="Subject" style="border: 2px solid #ddd;" autocomplete="off" />
                            </div>
                            <div class='form-group'>
                                <textarea class='form-control summernote' name="message" rows="10" style="border: 2px solid #ddd;" placeholder="Enter message here"></textarea>
                            </div>
                        </div>
                        <!-- Schedule datetime picker (hidden until Schedule is clicked) -->
                        <div class="form-group" id="email-schedule-group" style="display:none;">
                            <label><i class="fas fa-calendar-alt"></i> Schedule Date & Time</label>
                            <input type="text" class="form-control" id="email-schedule-picker" placeholder="Select date and time...">
                        </div>
                    </div>
                    <div class="modal-footer d-flex justify-content-between">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"></i> Cancel</button>
                        <div>
                            <button type="button" class="btn btn-outline-primary" id="email-schedule-btn">
                                <i class='fas fa-clock'></i> Schedule
                            </button>
                            <button type="submit" class="btn btn-primary" id="email-send-btn">
                                <i class='fas fa-paper-plane'></i> Send Now
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- View Email Modal -->
    <div class="modal fade" id="emailViewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-gradient-info text-white">
                    <h5 class="modal-title"><i class="fas fa-envelope-open"></i> View Email</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="email-view-loading" class="text-center p-4">
                        <i class="fas fa-spinner fa-spin fa-2x"></i>
                    </div>
                    <div id="email-view-content" style="display:none;">
                        <div class="row mb-3">
                            <div class="col-md-8">
                                <h5 id="email-view-subject" class="font-weight-bold"></h5>
                                <div class="mt-2 p-3 bg-light rounded" id="email-view-message"></div>
                            </div>
                            <div class="col-md-4">
                                <div class="alert alert-success mb-0">
                                    Sent to <strong id="email-view-recipient-count"></strong> recipient(s)<br>
                                    <small class="text-muted" id="email-view-sent"></small>
                                    <div id="email-view-group" class="mt-1"></div>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <h6><i class="fas fa-users"></i> Recipients</h6>
                        <div id="email-view-members" class="row" style="max-height: 300px; overflow-y: auto;"></div>
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
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
            });

            flatpickr("#email-from-date, #email-to-date", { dateFormat: "Y-m-d" });

            // Schedule datetime picker
            var emailSchedulePicker = flatpickr("#email-schedule-picker", {
                enableTime: true,
                dateFormat: "Y-m-d H:i",
                minDate: "today",
                time_24hr: true,
                onChange: function(selectedDates, dateStr) {
                    $('#email-schedule-time').val(dateStr);
                }
            });

            // Send mode toggle
            var emailSendMode = 'now';
            $('#email-schedule-btn').click(function() {
                if (emailSendMode === 'schedule') {
                    emailSendMode = 'now';
                    $('#email-schedule-group').hide();
                    $('#email-schedule-time').val('');
                    $(this).html('<i class="fas fa-clock"></i> Schedule').removeClass('btn-primary').addClass('btn-outline-primary');
                    $('#email-send-btn').show();
                    $('#email-schedule-submit-btn').remove();
                } else {
                    emailSendMode = 'schedule';
                    $('#email-schedule-group').show();
                    $(this).html('<i class="fas fa-times"></i> Cancel Schedule').removeClass('btn-outline-primary').addClass('btn-primary');
                    $('#email-send-btn').hide();
                    if ($('#email-schedule-submit-btn').length === 0) {
                        $(this).after('<button type="button" class="btn btn-success ml-1" id="email-schedule-submit-btn"><i class="fas fa-clock"></i> Schedule Send</button>');
                    }
                }
            });

            var emailCategory = 'all';

            // ===== Sent DataTable =====
            var table = $('#email-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ url('dashboard/communication/emails/datatable') }}",
                    data: function (d) {
                        d.search = $('#email-filter-form input[name=search]').val();
                        d.from_date = $('#email-filter-form input[name=from_date]').val();
                        d.to_date = $('#email-filter-form input[name=to_date]').val();
                        d.category = emailCategory;
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
                language: { emptyTable: "<i class='fas fa-ban'></i> No emails found" },
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
            var emailScheduledTable = $('#email-scheduled-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ url('dashboard/communication/emails/scheduled/datatable') }}",
                    data: function (d) {
                        d.search = $('#email-filter-form input[name=search]').val();
                    }
                },
                dom: 'rtip',
                order: [],
                language: { emptyTable: "<i class='fas fa-ban'></i> No scheduled emails" },
                columns: [
                    { data: 'preview', name: 'preview', orderable: false, searchable: false },
                    { data: 'recipient_display', name: 'recipient_display', orderable: false, searchable: false },
                    { data: 'scheduled_for', name: 'scheduled_for', orderable: false, searchable: false },
                    { data: 'action', name: 'action', orderable: false, searchable: false },
                ]
            });

            // Category tab switching
            $('#email-tabs a[data-category]').on('shown.bs.tab', function (e) {
                emailCategory = $(e.target).data('category');
                var paneId = $(e.target).attr('href');
                var $table = $('#email-table').closest('.table-responsive');
                $(paneId).html('').append($table);
                table.ajax.reload();
            });

            // Refresh scheduled table when tab is shown
            $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                if ($(e.target).attr('href') === '#email-scheduled-pane') {
                    emailScheduledTable.ajax.reload();
                }
            });

            var timer = null;
            $('#email-filter-form input[name=search]').keyup(function () {
                clearTimeout(timer);
                timer = setTimeout(function () { table.draw(); }, 500);
            });
            $('#email-from-date, #email-to-date').change(function () { table.draw(); });

            // ===== View Email Modal =====
            $(document).on('click', '.btn-view-email', function() {
                var id = $(this).data('id');
                $('#email-view-loading').show();
                $('#email-view-content').hide();
                $('#emailViewModal').modal('show');
                $.ajax({
                    url: "{{ url('dashboard/communication/emails/json') }}/" + id,
                    type: 'GET',
                }).done(function(data) {
                    $('#email-view-subject').text(data.subject);
                    $('#email-view-message').html(data.message);
                    $('#email-view-recipient-count').text(data.recipient_count);
                    $('#email-view-sent').html(data.sent + ' <br>(' + data.sent_ago + ')');
                    $('#email-view-group').html(data.group_name ? '<span class="badge badge-default text-white">Group: ' + data.group_name + '</span>' : '');

                    // Members list
                    var html = '';
                    if (data.members.length > 0) {
                        data.members.forEach(function(m) {
                            html += '<div class="col-sm-6 col-md-4 mb-2">' +
                                '<div class="border rounded p-2 small">' +
                                '<strong>' + (m.firstname || '') + ' ' + (m.lastname || '') + '</strong><br>' +
                                '<span class="text-muted">' + (m.email || '') + '</span>' +
                                '</div></div>';
                        });
                    } else {
                        html = '<div class="col-12"><p class="text-muted small">No recipient details available</p></div>';
                    }
                    $('#email-view-members').html(html);
                    $('#email-view-loading').hide();
                    $('#email-view-content').show();
                }).fail(function() {
                    $('#email-view-loading').html('<div class="text-danger"><i class="fas fa-exclamation-circle"></i> Failed to load email</div>');
                });
            });

            // ===== Draft protection =====
            var emailIsDirty = false;
            $('#emailComposeModal').on('input change', 'textarea, input[type=text], input[type=checkbox], input[type=radio]', function() {
                emailIsDirty = true;
            });
            $('#emailComposeModal').on('hide.bs.modal', function(e) {
                if (emailIsDirty) {
                    if (!confirm('You have an unsaved draft. Discard?')) {
                        e.preventDefault();
                        return;
                    }
                }
                // Reset form on close
                emailIsDirty = false;
                $('#emailComposeForm')[0].reset();
                $('#email-individual').prop('checked', true);
                $('.email-results').html('<p class="small text-muted"><i class="fas fa-spinner fa-pulse"></i> Please Wait</p>');
                $('#email-schedule-group').hide();
                $('#email-schedule-time').val('');
                $('#email-send-btn').html('<i class="fas fa-paper-plane"></i> Send Now').show();
                $('#email-schedule-btn').html('<i class="fas fa-clock"></i> Schedule').removeClass('btn-primary').addClass('btn-outline-primary');
                $('#email-schedule-submit-btn').remove();
                emailSendMode = 'now';
                // Reset contacts/message toggle
                $('.email-contacts-section').show();
                $('.email-message-section').hide();
                emailShowSection = 0;
                $('.email-write-message').html("<i class='fas fa-arrow-right'></i> Write Message");
                // Reset summernote
                $('.summernote').summernote('reset');
            });

            // ===== Cancel scheduled email =====
            $(document).on('click', '.btn-cancel-email-schedule', function() {
                var btn = $(this);
                var id = btn.data('id');
                if (!confirm('Cancel this scheduled email?')) return;
                $.ajax({
                    url: "{{ url('dashboard/communication/emails/schedule/cancel') }}",
                    method: 'POST',
                    data: { id: id },
                    success: function(data) {
                        toastr.success(data.success);
                        emailScheduledTable.ajax.reload();
                    },
                    error: function() {
                        toastr.error('Failed to cancel scheduled email');
                    }
                });
            });

            // ===== Compose Email contacts logic =====
            var emailGroups = 0;
            var emailShowSection = 0;
            $('.email-write-message').click(function(e) {
                e.preventDefault();
                var button = $(this);
                if (emailShowSection == 0) {
                    $('.email-contacts-section').hide();
                    $('.email-message-section').show();
                    button.html("<i class='fas fa-arrow-left'></i> Show Contacts");
                    emailShowSection = 1;
                    // Re-initialize summernote if needed
                    if (!$('.summernote').next('.note-editor').length) {
                        $('.summernote').summernote({
                            height: 150,
                            toolbar: [
                                ['style', ['bold', 'italic', 'underline', 'clear']],
                            ]
                        });
                    }
                } else {
                    $('.email-contacts-section').show();
                    $('.email-message-section').hide();
                    button.html("<i class='fas fa-arrow-right'></i> Write Message");
                    emailShowSection = 0;
                }
            });

            $('#emailComposeModal input[name="choice"]').change(function() {
                if ($("#email-groups").prop("checked")) {
                    emailGroups = 1;
                } else {
                    emailGroups = 0;
                }
                defaultEmailUsers();
            });

            $('#emailComposeModal').on('shown.bs.modal', function() {
                defaultEmailUsers();
            });

            function defaultEmailUsers() {
                var search = $('#email-contact-search').val();
                $.ajax({
                    url: "{{ url('dashboard/communication/emails/users') }}?groups=" + emailGroups + "&search=" + search,
                    type: "GET",
                }).done(function(data) {
                    $('.email-results').html("");
                    displayEmailContacts(data);
                }).fail(function() {
                    $('.email-results').html(
                        "<div class='text-danger'><i class='fas fa-exclamation-circle'></i> <strong>Error</strong> loading contacts</div>");
                });
            }

            var timer2 = null;
            $('#email-contact-search').keyup(function() {
                if (timer2 != null) clearTimeout(timer2);
                timer2 = setTimeout(function() {
                    var search = $('#email-contact-search').val();
                    $('.email-myscroll input[name="limit"]').val(0);
                    $('.email-results').html("");
                    $('.email-lazy').html("<i class='fas fa-spinner fa-pulse'></i> Loading...").show();
                    emailAllowScroll();
                    $.ajax({
                        url: "{{ url('dashboard/communication/emails/users') }}?search=" + search + "&groups=" + emailGroups,
                        method: "GET",
                    }).done(function(data) { displayEmailContacts(data); })
                    .fail(function() {
                        $('.email-results').html(
                            "<div class='text-danger'><i class='fas fa-exclamation-circle'></i> <strong>Error</strong> loading contacts</div>");
                    });
                }, 1000);
            });

            $('#emailComposeForm').submit(function(e) {
                e.preventDefault();
                var form = $(this);
                form.unbind('submit');
                var message = $.trim($('.summernote').summernote('code'));
                var textOnly = message.replace(/<[^>]*>/g, '').trim();
                if (textOnly.length === 0) {
                    swal({ text: "You cannot send an empty message to members", icon: "error" });
                    form.submit(arguments.callee);
                } else {
                    emailIsDirty = false;
                    form.submit();
                }
            });

            // Schedule submit (separate from Send Now)
            $(document).on('click', '#email-schedule-submit-btn', function() {
                if (!$('#email-schedule-time').val()) {
                    swal({ text: "Please select a date and time for scheduling", icon: "error" });
                    return;
                }
                $('#emailComposeForm').unbind('submit');
                var message = $.trim($('.summernote').summernote('code'));
                var textOnly = message.replace(/<[^>]*>/g, '').trim();
                if (textOnly.length === 0) {
                    swal({ text: "You cannot send an empty message to members", icon: "error" });
                    return;
                }
                emailIsDirty = false;
                $('#emailComposeForm')[0].submit();
            });

            emailAllowScroll();

            function emailAllowScroll() {
                $('.email-myscroll').on('scroll', function() {
                    if ($(this).scrollTop() + $(this).innerHeight() + 1 >= $(this)[0].scrollHeight) {
                        emailLoadMore();
                    }
                });
            }

            function emailLoadMore() {
                $('.email-myscroll').unbind();
                $('.email-lazy').show();
                var search = $('#email-contact-search').val();
                var limit = parseInt($('.email-myscroll input[name="limit"]').val()) + 10;
                $.ajax({
                    url: '{{ url("dashboard/communication/emails/users") }}?search=' + search + "&limit=" + limit + "&groups=" + emailGroups,
                    type: 'GET',
                }).done(function(data) {
                    displayEmailContacts(data);
                    var objects = JSON.parse(data);
                    if (objects.length < 10) {
                        $('.email-lazy').html("No more contacts");
                    } else {
                        $('.email-myscroll input[name="limit"]').val(limit);
                        emailAllowScroll();
                    }
                }).fail(function() {
                    $('.email-lazy').html("<span class='text-danger'><i class='fas fa-exclamation-triangle'></i> Something went <strong>wrong</strong></span>");
                });
            }

            function displayEmailContacts(data) {
                var checked = "";
                if ($('#email-all').is(':checked')) { checked = "checked"; }
                var objects = JSON.parse(data);
                if (emailGroups == 1) {
                    for (var i = 0; i < objects.length; i++) {
                        $('.email-results').append("<div class='bg-white mt-2'>" +
                            "<div class='custom-control custom-control-alternative custom-checkbox mb-3'>" +
                            "<input class='custom-control-input' name='groups[]' id='email-grp-" + objects[i].id +
                            "' value='" + objects[i].id + "' type='checkbox'>" +
                            "<label class='custom-control-label' for='email-grp-" + objects[i].id + "'><strong>" +
                            objects[i].name + "</strong><br>" + objects[i].members + " member(s)</label>" +
                            "</div></div>");
                    }
                    if (objects.length < 10) { $('.email-lazy').html("No more groups"); }
                } else {
                    for (var i = 0; i < objects.length; i++) {
                        $('.email-results').append("<div class='bg-white mt-2'>" +
                            "<div class='custom-control custom-control-alternative custom-checkbox mb-3'>" +
                            "<input class='custom-control-input' name='contacts[]' id='email-c-" + objects[i].id +
                            "' value='" + objects[i].id + "' type='checkbox' " + checked + ">" +
                            "<label class='custom-control-label' for='email-c-" + objects[i].id + "'><strong>" +
                            objects[i].firstname + " " + objects[i].lastname + "</strong><br>" +
                            objects[i].email + "</label></div></div>");
                    }
                    if (objects.length < 10) { $('.email-lazy').html("No more contacts"); }
                }
            }
        });
    </script>
@endpush
