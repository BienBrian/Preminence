@extends('layouts.dashboard')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h5 class="m-0 text-header"><i class='bi bi-file-earmark-text'></i> Generate Giving Statements</h5>
                </div>
                <div class="col-sm-6 d-none d-sm-block">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('dashboard/home') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ url('dashboard/reports/giving-statements') }}">Giving Statements</a></li>
                        <li class="breadcrumb-item active">Generate</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <!-- Left Panel: Form -->
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="bi bi-sliders"></i> Report Options</h3>
                        </div>
                        <div class="card-body">
                            <form id="generateForm">
                                @csrf
                                
                                <!-- Date Range -->
                                <div class="form-group">
                                    <label><i class="bi bi-calendar-range"></i> Date Range <span class="text-danger">*</span></label>
                                    <select id="date_preset" class="form-control mb-2">
                                        @foreach($datePresets as $key => $preset)
                                            <option value="{{ $key }}" data-from="{{ $preset['from'] }}" data-to="{{ $preset['to'] }}">
                                                {{ $preset['label'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="row">
                                        <div class="col-6">
                                            <input type="date" name="date_from" id="date_from" class="form-control" required>
                                        </div>
                                        <div class="col-6">
                                            <input type="date" name="date_to" id="date_to" class="form-control" required>
                                        </div>
                                    </div>
                                </div>

                                <!-- Categories -->
                                <div class="form-group">
                                    <label><i class="bi bi-tags"></i> Categories</label>
                                    <select name="categories[]" id="categories" class="form-control select2" multiple>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">Select specific categories or leave empty for all</small>
                                </div>

                                <!-- Filter by Contributors Only -->
                                <div class="form-group">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="filter_contributors" name="filter_contributors" value="1">
                                        <label class="custom-control-label" for="filter_contributors">
                                            <i class="bi bi-funnel"></i> Show only members with contributions
                                        </label>
                                    </div>
                                    <small class="text-muted">Only show members who gave in selected categories during this period</small>
                                </div>

                                <!-- Member Selection -->
                                <div class="form-group">
                                    <label for="user_ids"><i class="bi bi-people"></i> Select Members <span class="text-danger">*</span></label>
                                    <select name="user_ids[]" id="user_ids" class="form-control select2" multiple required>
                                        @foreach($members as $member)
                                            <option value="{{ $member->id }}" 
                                                    data-phone="{{ $member->phone }}" 
                                                    data-email="{{ $member->email }}"
                                                    data-status="{{ $member->status }}"
                                                    data-verified="{{ $member->email_verified_at ? '1' : '0' }}">
                                                {{ $member->firstname }} {{ $member->lastname }} 
                                                @if($member->status !== 'active') [{{ ucfirst($member->status) }}] @endif
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">
                                        Select one or more members. 
                                        <span class="text-success"><i class="bi bi-check-circle"></i> = Email verified</span>
                                        <span class="text-warning ml-2"><i class="bi bi-x-circle"></i> = Not verified</span>
                                    </small>
                                </div>

                                <!-- Selected Members Summary -->
                                <div id="selected-members-summary" class="alert alert-info d-none">
                                    <strong><i class="bi bi-people-fill"></i> <span id="selected-count">0</span> members selected</strong>
                                    <div class="small mt-1">
                                        <span class="text-success"><i class="bi bi-check-circle"></i> <span id="verified-count">0</span> email verified</span>
                                        <span class="text-warning ml-2"><i class="bi bi-x-circle"></i> <span id="unverified-count">0</span> not verified</span>
                                    </div>
                                </div>

                                <!-- Password Protection (Read from Settings) -->
                                <input type="hidden" name="password_type" id="password_type" value="{{ $config->default_password_type }}">
                                <input type="hidden" name="password_value" id="password_value" value="{{ $config->custom_password_code }}">
                                
                                <div class="alert alert-light border">
                                    <small class="text-muted">
                                        <i class="bi bi-lock"></i> <strong>Password Protection:</strong> 
                                        @if($config->default_password_type == 'phone')
                                            Phone (last 4 digits)
                                        @elseif($config->default_password_type == 'custom_code')
                                            Custom Code: {{ $config->custom_password_code }}
                                        @elseif($config->default_password_type == 'sms_otp')
                                            SMS OTP
                                        @else
                                            No Password
                                        @endif
                                        <a href="{{ url('dashboard/reports/giving-statements/settings') }}" class="ml-1">(Change)</a>
                                    </small>
                                </div>

                                <!-- Action Buttons -->
                                <div class="form-group">
                                    <button type="button" id="btn-preview" class="btn btn-info btn-block">
                                        <i class="bi bi-eye"></i> Preview Selected
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Right Panel: Preview -->
                <div class="col-md-8">
                    <div class="card" id="preview-card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h3 class="card-title mb-0"><i class="bi bi-eye"></i> Preview</h3>
                            <div id="preview-actions" class="d-none">
                                <button type="button" id="btn-download-selected" class="btn btn-success btn-sm">
                                    <i class="bi bi-download"></i> Download All
                                </button>
                                <button type="button" id="btn-email-verified" class="btn btn-primary btn-sm">
                                    <i class="bi bi-envelope"></i> Email Verified Only
                                </button>
                            </div>
                        </div>
                        <div class="card-body" id="preview-content">
                            <div class="text-center text-muted py-5">
                                <i class="bi bi-file-earmark-text" style="font-size: 3rem;"></i>
                                <p class="mt-3">Select members and date range, then click "Preview Selected"</p>
                                <small class="text-muted">You can generate statements for multiple members at once</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Email Modal -->
    <div class="modal fade" id="emailModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-envelope"></i> Email Giving Statements</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> 
                        This will send statements to <strong id="email-recipient-count">0</strong> members with verified email addresses.
                    </div>
                    <div class="form-group">
                        <label>Custom Message (Optional)</label>
                        <textarea id="email-message" class="form-control" rows="3" placeholder="Add a personal message to the email..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" id="btn-send-email" class="btn btn-primary">
                        <i class="bi bi-send"></i> Send Emails
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Unverified Email Modal -->
    <div class="modal fade" id="unverifiedEmailModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title"><i class="bi bi-exclamation-triangle"></i> Email Not Verified</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="unverified-email-message" class="mb-3"></div>
                    <div id="sms-invite-section" class="d-none">
                        <hr>
                        <p class="text-muted">Send an SMS invitation to verify their email:</p>
                        <div class="d-grid">
                            <button type="button" id="btn-send-sms-verification" class="btn btn-success">
                                <i class="bi bi-sms"></i> Send Verification SMS
                            </button>
                        </div>
                        <div id="sms-send-status" class="mt-2"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" id="btn-download-instead" class="btn btn-primary">
                        <i class="bi bi-download"></i> Download Instead
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Initialize Select2
        $('.select2').select2({
            theme: 'bootstrap4',
            width: '100%'
        });

        // Date preset handler
        $('#date_preset').on('change', function() {
            const from = $(this).find(':selected').data('from');
            const to = $(this).find(':selected').data('to');
            if (from) $('#date_from').val(from);
            if (to) $('#date_to').val(to);
            
            // Trigger contributor filter if checked
            if ($('#filter_contributors').is(':checked')) {
                loadContributors();
            }
        });

        // Set default dates (current month)
        const currentMonthPreset = $('#date_preset option[value="current_month"]');
        if (currentMonthPreset.length) {
            $('#date_from').val(currentMonthPreset.data('from'));
            $('#date_to').val(currentMonthPreset.data('to'));
        }

        // Filter contributors checkbox
        $('#filter_contributors').on('change', function() {
            if ($(this).is(':checked')) {
                loadContributors();
            } else {
                // Show all members again
                resetMemberSelect();
            }
        });

        // Categories change - reload contributors if filter is checked
        $('#categories').on('change', function() {
            if ($('#filter_contributors').is(':checked')) {
                loadContributors();
            }
        });

        // Load contributors via AJAX
        function loadContributors() {
            const dateFrom = $('#date_from').val();
            const dateTo = $('#date_to').val();
            
            if (!dateFrom || !dateTo) {
                toastr.warning('Please select a date range first');
                $('#filter_contributors').prop('checked', false);
                return;
            }

            const $btn = $('#filter_contributors').prop('disabled', true);
            toastr.info('Loading contributors...');

            $.ajax({
                url: '{{ url("dashboard/reports/giving-statements/contributors") }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    date_from: dateFrom,
                    date_to: dateTo,
                    categories: $('#categories').val()
                },
                success: function(response) {
                    if (response.contributors && response.contributors.length > 0) {
                        updateMemberSelect(response.contributors);
                        toastr.success(`Showing ${response.contributors.length} members with contributions`);
                    } else {
                        toastr.warning('No contributors found for the selected criteria');
                        $('#user_ids').html('').trigger('change');
                    }
                },
                error: function() {
                    toastr.error('Failed to load contributors');
                    $('#filter_contributors').prop('checked', false);
                },
                complete: function() {
                    $btn.prop('disabled', false);
                }
            });
        }

        // Update member select options
        function updateMemberSelect(members) {
            let html = '';
            members.forEach(member => {
                const statusBadge = member.status !== 'active' ? `[${member.status.charAt(0).toUpperCase() + member.status.slice(1)}] ` : '';
                html += `<option value="${member.id}" data-phone="${member.phone || ''}" data-email="${member.email || ''}" data-status="${member.status}">${member.firstname} ${member.lastname} ${statusBadge}</option>`;
            });
            $('#user_ids').html(html).trigger('change');
        }

        // Reset member select to all members
        function resetMemberSelect() {
            @php
            $allMembersHtml = '';
            foreach($members as $member) {
                $statusBadge = $member->status !== 'active' ? '[' . ucfirst($member->status) . '] ' : '';
                $allMembersHtml .= "<option value='{$member->id}' data-phone='{$member->phone}' data-email='{$member->email}' data-status='{$member->status}' data-verified='" . ($member->email_verified_at ? '1' : '0') . "'>{$member->firstname} {$member->lastname} {$statusBadge}</option>";
            }
            echo "const allMembersHtml = `" . $allMembersHtml . "`;";
            @endphp
            
            $('#user_ids').html(allMembersHtml).trigger('change');
        }

        // Update summary when members are selected
        $('#user_ids').on('change', function() {
            const selected = $(this).find(':selected');
            const total = selected.length;
            let verified = 0;
            let unverified = 0;

            selected.each(function() {
                if ($(this).data('verified') === '1' || $(this).data('verified') === 1) {
                    verified++;
                } else {
                    unverified++;
                }
            });

            if (total > 0) {
                $('#selected-members-summary').removeClass('d-none');
                $('#selected-count').text(total);
                $('#verified-count').text(verified);
                $('#unverified-count').text(unverified);
            } else {
                $('#selected-members-summary').addClass('d-none');
            }
        });

        // Preview button handler
        $('#btn-preview').on('click', function() {
            const userIds = $('#user_ids').val();
            if (!userIds || userIds.length === 0) {
                toastr.error('Please select at least one member');
                return;
            }

            const $btn = $(this);
            $btn.prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Loading...');

            // For now, preview first selected member
            // In a full implementation, you'd show a list of all selected
            $.ajax({
                url: '{{ url("dashboard/reports/giving-statements/preview") }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    user_id: userIds[0],
                    date_from: $('#date_from').val(),
                    date_to: $('#date_to').val(),
                    categories: $('#categories').val()
                },
                success: function(response) {
                    renderPreview(response);
                    $('#preview-actions').removeClass('d-none');
                    toastr.success(`Showing preview for ${response.member.name}. ${userIds.length > 1 ? (userIds.length - 1) + ' more member(s) selected.' : ''}`);
                },
                error: function(xhr) {
                    const error = xhr.responseJSON?.error || 'Failed to generate preview';
                    toastr.error(error);
                },
                complete: function() {
                    $btn.prop('disabled', false).html('<i class="bi bi-eye"></i> Preview Selected');
                }
            });
        });

        // Render preview function
        function renderPreview(data) {
            const groupedData = data.data.grouped_data;
            let html = `
                <div class="preview-document">
                    <div class="text-center mb-4 border-bottom pb-3">
                        <h4 class="text-primary">{{ $config->church_header_text ?? auth()->user()->tenant->name }}</h4>
                        <h5 class="mt-2">Giving Statement</h5>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="text-muted">Member</h6>
                            <p class="mb-1"><strong>${data.member.name}</strong></p>
                            <p class="small text-muted">${data.member.email || ''}</p>
                        </div>
                        <div class="col-md-6 text-md-right">
                            <h6 class="text-muted">Period</h6>
                            <p class="mb-1">${formatDate(data.data.period_from)} - ${formatDate(data.data.period_to)}</p>
                            <p class="small text-muted">Generated: ${new Date().toLocaleDateString()}</p>
                        </div>
                    </div>

                    <div class="alert alert-info">
                        <div class="row text-center">
                            <div class="col-6">
                                <h3 class="mb-0">KES ${parseFloat(data.data.total_amount).toLocaleString('en-US', {minimumFractionDigits: 2})}</h3>
                                <small class="text-muted">Total Giving</small>
                            </div>
                            <div class="col-6">
                                <h3 class="mb-0">${data.data.transaction_count}</h3>
                                <small class="text-muted">Transactions</small>
                            </div>
                        </div>
                    </div>
            `;

            // Add grouped data
            groupedData.forEach(group => {
                html += `
                    <div class="mt-4">
                        <h6 class="bg-light p-2 border-left border-primary" style="border-left-width: 4px !important;">
                            ${group.name}
                            <span class="float-right">KES ${parseFloat(group.total).toLocaleString('en-US', {minimumFractionDigits: 2})}</span>
                        </h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-striped">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Description</th>
                                        <th class="text-right">Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                `;

                group.transactions.forEach(tx => {
                    html += `
                        <tr>
                            <td>${formatDate(tx.date)}</td>
                            <td>${tx.description || 'Contribution'}</td>
                            <td class="text-right">KES ${parseFloat(tx.amount).toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                        </tr>
                    `;
                });

                html += `
                                </tbody>
                            </table>
                        </div>
                    </div>
                `;
            });

            html += `
                    <div class="mt-4 pt-3 border-top text-center text-muted small">
                        <p>This is a preview. Download or email to get the full PDF with password protection.</p>
                    </div>
                </div>
            `;

            $('#preview-content').html(html);
        }

        // Format date helper
        function formatDate(dateStr) {
            return new Date(dateStr).toLocaleDateString('en-US', {
                month: 'short',
                day: 'numeric',
                year: 'numeric'
            });
        }

        // Download button handler
        $('#btn-download-selected').on('click', function() {
            const userIds = $('#user_ids').val();
            if (!userIds || userIds.length === 0) {
                toastr.error('Please select at least one member');
                return;
            }

            // Download for each selected user
            let downloaded = 0;
            userIds.forEach((userId, index) => {
                setTimeout(() => {
                    downloadForUser(userId);
                    downloaded++;
                    if (downloaded === userIds.length) {
                        toastr.success(`Downloaded ${downloaded} statement(s)`);
                    }
                }, index * 500); // Stagger downloads
            });
        });

        function downloadForUser(userId) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ url("dashboard/reports/giving-statements/download") }}';
            form.innerHTML = `
                @csrf
                <input type="hidden" name="user_id" value="${userId}">
                <input type="hidden" name="date_from" value="${$('#date_from').val()}">
                <input type="hidden" name="date_to" value="${$('#date_to').val()}">
                ${($('#categories').val() || []).map(cat => `<input type="hidden" name="categories[]" value="${cat}">`).join('')}
                <input type="hidden" name="password_type" value="${$('#password_type').val()}">
                <input type="hidden" name="password_value" value="${$('#password_value').val()}">
            `;
            document.body.appendChild(form);
            form.submit();
            document.body.removeChild(form);
        }

        // Email verified button handler
        $('#btn-email-verified').on('click', function() {
            const userIds = $('#user_ids').val();
            if (!userIds || userIds.length === 0) {
                toastr.error('Please select at least one member');
                return;
            }

            // Count verified emails
            const verifiedUsers = $('#user_ids').find(':selected').filter(function() {
                return $(this).data('verified') === '1' || $(this).data('verified') === 1;
            });

            if (verifiedUsers.length === 0) {
                toastr.warning('None of the selected members have verified email addresses');
                return;
            }

            $('#email-recipient-count').text(verifiedUsers.length);
            $('#emailModal').modal('show');
        });

        // Send email handler
        $('#btn-send-email').on('click', function() {
            const $btn = $(this);
            const userIds = $('#user_ids').val();
            
            $btn.prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Sending...');

            // Send to each verified user
            let sent = 0;
            let failed = 0;
            const verifiedUserIds = $('#user_ids').find(':selected').filter(function() {
                return $(this).data('verified') === '1' || $(this).data('verified') === 1;
            }).map(function() { return $(this).val(); }).get();

            function sendNext(index) {
                if (index >= verifiedUserIds.length) {
                    // All done
                    $('#emailModal').modal('hide');
                    if (sent > 0) {
                        toastr.success(`Successfully sent ${sent} giving statement(s)`);
                    }
                    if (failed > 0) {
                        toastr.warning(`Failed to send ${failed} statement(s)`);
                    }
                    $btn.prop('disabled', false).html('<i class="bi bi-send"></i> Send Emails');
                    return;
                }

                $.ajax({
                    url: '{{ url("dashboard/reports/giving-statements/email") }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        user_id: verifiedUserIds[index],
                        date_from: $('#date_from').val(),
                        date_to: $('#date_to').val(),
                        categories: $('#categories').val(),
                        password_type: $('#password_type').val(),
                        password_value: $('#password_value').val(),
                        custom_message: $('#email-message').val()
                    },
                    success: function(response) {
                        sent++;
                        toastr.success(response.message);
                        sendNext(index + 1);
                    },
                    error: function(xhr) {
                        const response = xhr.responseJSON;
                        if (response?.error === 'Email not verified') {
                            // Shouldn't happen since we filter, but handle anyway
                            failed++;
                        } else {
                            failed++;
                            toastr.error(response?.message || 'Failed to send email');
                        }
                        sendNext(index + 1);
                    }
                });
            }

            sendNext(0);
        });

        // Send SMS verification invitation
        $('#btn-send-sms-verification').on('click', function() {
            const $btn = $(this);
            const userId = $btn.data('user-id');
            
            $btn.prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Sending...');
            $('#sms-send-status').html('');
            
            $.ajax({
                url: '{{ url("dashboard/users/send-verification-request") }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    user_id: userId,
                    method: 'sms'
                },
                success: function(response) {
                    $('#sms-send-status').html('<div class="alert alert-success"><i class="bi bi-check-circle"></i> Verification SMS sent successfully!</div>');
                    $btn.html('<i class="bi bi-check"></i> Sent!');
                    toastr.success('Verification SMS sent to member');
                },
                error: function(xhr) {
                    const error = xhr.responseJSON?.error || 'Failed to send SMS';
                    $('#sms-send-status').html('<div class="alert alert-danger"><i class="bi bi-exclamation-circle"></i> ' + error + '</div>');
                    $btn.prop('disabled', false).html('<i class="bi bi-sms"></i> Send Verification SMS');
                    toastr.error(error);
                }
            });
        });

        // Download instead button
        $('#btn-download-instead').on('click', function() {
            $('#unverifiedEmailModal').modal('hide');
            $('#btn-download-selected').trigger('click');
        });
    });
</script>
@endpush
