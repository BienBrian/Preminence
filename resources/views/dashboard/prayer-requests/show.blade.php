@extends('layouts.dashboard')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h5 class="m-0 text-header"><i class='fas fa-praying-hands'></i> Prayer Request #{{ $prayer->id }}</h5>
                </div>
                <div class="col-sm-6 d-none d-sm-block">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('dashboard/home') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ url('dashboard/prayer-requests') }}">Prayer Requests</a></li>
                        <li class="breadcrumb-item active">#{{ $prayer->id }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <!-- Header Info -->
            <div class="card">
                <div class="card-body">
                    <div class="d-flex flex-wrap align-items-center justify-content-between">
                        <div>
                            <h4 class="mb-1">{{ $prayer->title }}</h4>
                            <div>
                                @php
                                    $statusColors = [
                                        'new' => 'primary', 'in_progress' => 'warning', 'prayed_for' => 'info',
                                        'follow_up' => 'secondary', 'answered' => 'success', 'closed' => 'dark',
                                    ];
                                @endphp
                                <span class="badge badge-{{ $statusColors[$prayer->status] ?? 'secondary' }}">{{ ucwords(str_replace('_', ' ', $prayer->status)) }}</span>
                                @if($prayer->is_urgent)
                                    <span class="badge badge-danger"><i class="fas fa-exclamation-triangle"></i> Urgent</span>
                                @endif
                                @if($prayer->category)
                                    <span class="badge badge-outline-info">{{ ucfirst($prayer->category) }}</span>
                                @endif
                                @if($prayer->request_type === 'praise_report')
                                    <span class="badge badge-warning"><i class="fas fa-star"></i> Praise Report</span>
                                @endif
                                @foreach($prayer->tags as $tag)
                                    <span class="badge badge-{{ $tag->color }}">{{ $tag->name }}</span>
                                @endforeach
                            </div>
                        </div>
                        <div class="text-muted small">
                            Submitted {{ $prayer->created_at->format('M d, Y h:i A') }}
                            by
                            @if($prayer->is_anonymous)
                                <em>Anonymous</em>
                            @elseif($prayer->submitter)
                                {{ $prayer->submitter->firstname }} {{ $prayer->submitter->lastname }}
                                <a href="{{ url('dashboard/users/view/'.$prayer->submitter->id) }}" class="badge badge-success ml-2"><i class="fas fa-check-circle"></i> Member Found</a>
                            @else
                                {{ $prayer->submitted_name ?? 'Guest' }}
                                <span class="badge badge-warning ml-2">Not a Member</span>
                                <button class="btn btn-xs btn-outline-primary ml-1 btn-invite-user" 
                                    data-phone="{{ $prayer->submitted_phone }}" 
                                    data-email="{{ $prayer->submitted_email }}"><i class="fas fa-envelope"></i> Invite</button>
                                <button class="btn btn-xs btn-outline-success ml-1" onclick="toastr.info('Discipleship module coming soon!', 'Feature Pending')"><i class="fas fa-seedling"></i> Enroll</button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Left Column -->
                <div class="col-lg-8">
                    <!-- Description -->
                    <div class="card">
                        <div class="card-header"><h6 class="mb-0"><i class="fas fa-align-left"></i> Description</h6></div>
                        <div class="card-body">
                            {!! nl2br(e($prayer->description)) !!}
                        </div>
                    </div>

                    <!-- Timeline / Notes -->
                    <div class="card">
                        <div class="card-header"><h6 class="mb-0"><i class="fas fa-stream"></i> Timeline</h6></div>
                        <div class="card-body p-0">
                            <div class="timeline timeline-inverse p-3">
                                @forelse($prayer->notes->sortByDesc('created_at') as $note)
                                @php
                                    $icons = [
                                        'note' => 'fas fa-comment bg-info',
                                        'status_change' => 'fas fa-exchange-alt bg-warning',
                                        'assignment' => 'fas fa-user-tag bg-primary',
                                        'follow_up' => 'fas fa-clock bg-secondary',
                                        'sms_sent' => 'fas fa-sms bg-success',
                                    ];
                                @endphp
                                <div class="time-label">
                                    <span class="bg-light text-dark">{{ $note->created_at->format('M d, Y h:i A') }}</span>
                                </div>
                                <div>
                                    <i class="{{ $icons[$note->type] ?? 'fas fa-circle bg-secondary' }}"></i>
                                    <div class="timeline-item">
                                        <h3 class="timeline-header">
                                            <strong>{{ $note->user ? $note->user->firstname . ' ' . $note->user->lastname : 'System' }}</strong>
                                            <small class="text-muted ml-2">{{ ucfirst(str_replace('_', ' ', $note->type)) }}</small>
                                        </h3>
                                        <div class="timeline-body">{{ $note->note }}</div>
                                    </div>
                                </div>
                                @empty
                                <div class="text-center text-muted py-3">No timeline entries yet.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <!-- Add Note -->
                    <div class="card">
                        <div class="card-header"><h6 class="mb-0"><i class="fas fa-plus"></i> Add Note</h6></div>
                        <div class="card-body">
                            <form id="noteForm">
                                <div class="form-group">
                                    <textarea class="form-control" name="note" rows="3" placeholder="Write a note..." required></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary btn-sm" id="btnNote"><i class="fas fa-paper-plane"></i> Add Note</button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="col-lg-4">
                    <!-- Actions -->
                    @can('Edit Prayer Requests')
                    <div class="card">
                        <div class="card-header"><h6 class="mb-0"><i class="fas fa-cogs"></i> Actions</h6></div>
                        <div class="card-body">
                            <div class="form-group">
                                <label>Change Status</label>
                                <select class="form-control form-control-sm" id="statusSelect">
                                    <option value="new" {{ $prayer->status === 'new' ? 'selected' : '' }}>New</option>
                                    <option value="in_progress" {{ $prayer->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                    <option value="prayed_for" {{ $prayer->status === 'prayed_for' ? 'selected' : '' }}>Prayed For</option>
                                    <option value="follow_up" {{ $prayer->status === 'follow_up' ? 'selected' : '' }}>Follow Up</option>
                                    <option value="answered" {{ $prayer->status === 'answered' ? 'selected' : '' }}>Answered</option>
                                    <option value="closed" {{ $prayer->status === 'closed' ? 'selected' : '' }}>Closed</option>
                                </select>
                                <button class="btn btn-warning btn-sm btn-block mt-2" id="btnStatus"><i class="fas fa-sync"></i> Update Status</button>
                            </div>
                            <hr>
                            <div class="form-group">
                                <label>Assign To</label>
                                <select class="form-control form-control-sm select2-assign" id="assignSelect">
                                    <option value="">-- Unassigned --</option>
                                    @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ $prayer->assigned_to == $user->id ? 'selected' : '' }}>{{ $user->firstname }} {{ $user->lastname }}</option>
                                    @endforeach
                                </select>
                                <button class="btn btn-primary btn-sm btn-block mt-2" id="btnAssign"><i class="fas fa-user-tag"></i> Assign</button>
                            </div>

                            @if($prayer->visibility === 'public')
                            <hr>
                            <div class="form-group">
                                <label>Prayer Wall Moderation</label>
                                <div class="d-flex">
                                    @if($prayer->moderation_status === 'pending')
                                        <button class="btn btn-success btn-sm mr-2 flex-fill btn-moderate" data-action="approved"><i class="fas fa-check"></i> Approve</button>
                                        <button class="btn btn-danger btn-sm flex-fill btn-moderate" data-action="rejected"><i class="fas fa-times"></i> Reject</button>
                                    @else
                                        <span class="badge badge-{{ $prayer->moderation_status === 'approved' ? 'success' : 'danger' }} p-2">
                                            {{ ucfirst($prayer->moderation_status) }}
                                            @if($prayer->moderator) by {{ $prayer->moderator->firstname }} @endif
                                        </span>
                                    @endif
                                </div>
                            </div>
                            @endif

                            <hr>
                            <form action="{{ url('dashboard/prayer-requests/'.$prayer->id.'/delete') }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this prayer request?')">
                                @csrf
                                <button type="submit" class="btn btn-outline-danger btn-sm btn-block"><i class="fas fa-trash"></i> Delete</button>
                            </form>
                        </div>
                    </div>
                    @endcan

                    <!-- Details -->
                    <div class="card">
                        <div class="card-header"><h6 class="mb-0"><i class="fas fa-info-circle"></i> Details</h6></div>
                        <div class="card-body p-0">
                            <table class="table table-sm mb-0">
                                <tr><td class="font-weight-bold">Source</td><td>{{ ucfirst($prayer->source) }}</td></tr>
                                <tr><td class="font-weight-bold">Visibility</td><td>{{ ucwords(str_replace('_', ' ', $prayer->visibility)) }}</td></tr>
                                <tr><td class="font-weight-bold">Prayer Count</td><td><i class="fas fa-heart text-danger"></i> {{ $prayer->prayer_count }}</td></tr>
                                @if($prayer->submitted_phone)
                                <tr><td class="font-weight-bold">Phone</td><td>{{ $prayer->submitted_phone }}</td></tr>
                                @endif
                                @if($prayer->submitted_alternate_phone)
                                <tr><td class="font-weight-bold">Alt. Phone</td><td>{{ $prayer->submitted_alternate_phone }}</td></tr>
                                @endif
                                @if($prayer->submitted_email)
                                <tr><td class="font-weight-bold">Email</td><td>{{ $prayer->submitted_email }}</td></tr>
                                @endif
                                @if($prayer->follow_up_at)
                                <tr><td class="font-weight-bold">Follow Up</td><td>{{ $prayer->follow_up_at->format('M d, Y') }}</td></tr>
                                @endif
                                @if($prayer->answered_at)
                                <tr><td class="font-weight-bold">Answered</td><td>{{ $prayer->answered_at->format('M d, Y') }}</td></tr>
                                @endif
                                @if($prayer->closed_at)
                                <tr><td class="font-weight-bold">Closed</td><td>{{ $prayer->closed_at->format('M d, Y') }}</td></tr>
                                @endif
                            </table>
                        </div>
                    </div>

                    <!-- Quick SMS -->
                    @if($prayer->submitted_phone)
                    <div class="card">
                        <div class="card-header"><h6 class="mb-0"><i class="fas fa-sms"></i> Quick SMS Follow-up</h6></div>
                        <div class="card-body">
                            <form id="quickSmsForm">
                                <div class="form-group">
                                    <input type="text" class="form-control form-control-sm" value="{{ $prayer->submitted_phone }}" readonly>
                                </div>
                                <div class="form-group">
                                    <textarea class="form-control form-control-sm" name="sms_message" rows="3" placeholder="Type SMS message...">Hi{{ $prayer->submitted_name ? ' '.$prayer->submitted_name : '' }}, we are praying for your request "{{ $prayer->title }}". God bless you.</textarea>
                                </div>
                                <button type="button" class="btn btn-info btn-sm btn-block" id="btnSendSms" disabled><i class="fas fa-paper-plane"></i> Send SMS (coming soon)</button>
                            </form>
                        </div>
                    </div>
                    @endif

                    <!-- Prayed For -->
                    <div class="card">
                        <div class="card-body text-center">
                            <button class="btn btn-outline-danger btn-lg" id="btnPrayed">
                                <i class="fas fa-heart"></i> I Prayed For This
                            </button>
                            <div class="mt-2"><span id="prayerCount">{{ $prayer->prayer_count }}</span> people have prayed</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Invite Modal -->
    <!-- ===== INVITE USER MODAL ===== -->
    <div class="modal fade" id="inviteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class='fas fa-paper-plane'></i> Invite User</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <form id="invite-form">
                        @csrf
                        <div class="form-group">
                            <label>Invite via</label>
                            <select name="method" class="form-control" id="invite-method">
                                <option value="sms">SMS</option>
                                <option value="email">Email</option>
                            </select>
                        </div>
                        <div class="form-group" id="invite-phone-group">
                            <label>Phone Number</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">+{{ $site_settings->phone_code ?? '254' }}</span>
                                </div>
                                <input type="text" name="phone" class="form-control" placeholder="e.g. 712345678">
                            </div>
                        </div>
                        <div class="form-group d-none" id="invite-email-group">
                            <label>Email Address</label>
                            <input type="email" name="email" class="form-control" placeholder="example@email.com">
                        </div>
                        <div class='alert invite-feedback border d-none'></div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal"><i class='fas fa-times'></i> Close</button>
                    <button type="button" class="btn btn-success btn-sm btnSendInvite"><i class='fas fa-paper-plane'></i> Send Invite</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
<script>
$(document).ready(function () {
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

    // ===== Invite Logic =====
    $('.btn-invite-user').click(function (e) {
        e.preventDefault();
        var phone = $(this).data('phone');
        var email = $(this).data('email');
        
        $('#invite-form')[0].reset();
        $('#inviteModal .invite-feedback').addClass('d-none').removeClass('alert-danger alert-success');

        // Pre-fill
        if (phone && phone != '') {
             $('#invite-method').val('sms').trigger('change');
             $('#invite-form input[name=phone]').val(phone);
        } else if (email && email != '') {
             $('#invite-method').val('email').trigger('change');
             $('#invite-form input[name=email]').val(email);
        } else {
             $('#invite-method').val('sms').trigger('change');
        }

        $('#inviteModal').modal('show');
    });

    $('#invite-method').change(function () {
        if ($(this).val() === 'sms') {
            $('#invite-phone-group').removeClass('d-none');
            $('#invite-email-group').addClass('d-none');
        } else {
            $('#invite-phone-group').addClass('d-none');
            $('#invite-email-group').removeClass('d-none');
        }
    });

    $('.btnSendInvite').click(function () {
        var btn = $(this);
        btn.attr('disabled', 'disabled');
        $('#inviteModal .invite-feedback').removeClass('d-none alert-danger alert-success')
            .html("<i class='fas fa-spinner fa-pulse'></i> Sending... Please wait");
        $.ajax({
            url: '{{ url("dashboard/users/invite") }}',
            type: 'POST',
            data: $('#invite-form').serialize()
        }).done(function (data) {
            $('#inviteModal .invite-feedback').addClass('alert-success')
                .html("<i class='fas fa-check-circle'></i> " + data.success);
            setTimeout(() => { $('#inviteModal .invite-feedback').addClass('d-none'); }, 3000);
            btn.removeAttr('disabled');
        }).fail(function (response) {
            let data = response.responseJSON;
            $('#inviteModal .invite-feedback').addClass('alert-danger').html("");
            if (data && data.errors) {
                $.each(data.errors, function(key, msgs) {
                    $.each(msgs, function(i, msg) {
                        $('#inviteModal .invite-feedback').append("<i class='fas fa-exclamation-circle'></i> " + msg + "<br>");
                    });
                });
            } else {
                $('#inviteModal .invite-feedback').html("<i class='fas fa-exclamation-circle'></i> Something went wrong!");
            }
            setTimeout(() => { $('#inviteModal .invite-feedback').addClass('d-none'); }, 5000);
            btn.removeAttr('disabled');
        });
    });

    $('.select2-assign').select2({ width: '100%', placeholder: 'Select user' });

    // Change status
    $('#btnStatus').click(function () {
        var btn = $(this);
        btn.html('<i class="fas fa-spinner fa-spin"></i>').attr('disabled', true);
        $.post("{{ url('dashboard/prayer-requests/'.$prayer->id.'/status') }}", { status: $('#statusSelect').val() })
            .done(function (d) { toastr.success(d.success); setTimeout(function(){ location.reload(); }, 800); })
            .fail(function () { toastr.error('Failed to update status.'); })
            .always(function () { btn.html('<i class="fas fa-sync"></i> Update Status').removeAttr('disabled'); });
    });

    // Assign
    $('#btnAssign').click(function () {
        var btn = $(this);
        var val = $('#assignSelect').val();
        if (!val) { toastr.warning('Select a user to assign.'); return; }
        btn.html('<i class="fas fa-spinner fa-spin"></i>').attr('disabled', true);
        $.post("{{ url('dashboard/prayer-requests/'.$prayer->id.'/assign') }}", { assigned_to: val })
            .done(function (d) { toastr.success(d.success); setTimeout(function(){ location.reload(); }, 800); })
            .fail(function () { toastr.error('Failed to assign.'); })
            .always(function () { btn.html('<i class="fas fa-user-tag"></i> Assign').removeAttr('disabled'); });
    });

    // Moderate
    $('.btn-moderate').click(function () {
        var btn = $(this);
        var action = btn.data('action');
        btn.html('<i class="fas fa-spinner fa-spin"></i>').attr('disabled', true);
        $.post("{{ url('dashboard/prayer-requests/'.$prayer->id.'/moderate') }}", { action: action })
            .done(function (d) { toastr.success(d.success); setTimeout(function(){ location.reload(); }, 800); })
            .fail(function () { toastr.error('Failed.'); })
            .always(function () { btn.removeAttr('disabled'); });
    });

    // Add Note
    $('#noteForm').on('submit', function (e) {
        e.preventDefault();
        var btn = $('#btnNote');
        btn.html('<i class="fas fa-spinner fa-spin"></i>').attr('disabled', true);
        $.post("{{ url('dashboard/prayer-requests/'.$prayer->id.'/note') }}", { note: $('textarea[name=note]').val() })
            .done(function (d) { toastr.success(d.success); setTimeout(function(){ location.reload(); }, 800); })
            .fail(function () { toastr.error('Failed to add note.'); })
            .always(function () { btn.html('<i class="fas fa-paper-plane"></i> Add Note').removeAttr('disabled'); });
    });

    // Prayed for
    $('#btnPrayed').click(function () {
        var btn = $(this);
        btn.attr('disabled', true);
        $.post("{{ url('dashboard/prayer-requests/'.$prayer->id.'/prayed') }}")
            .done(function (d) {
                $('#prayerCount').text(d.count);
                toastr.success('Thank you for praying!');
            })
            .fail(function () { toastr.error('Failed.'); })
            .always(function () { btn.removeAttr('disabled'); });
    });
});
</script>
@endpush
