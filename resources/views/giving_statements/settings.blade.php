@extends('layouts.dashboard')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h5 class="m-0 text-header"><i class='bi bi-gear'></i> Giving Statement Settings</h5>
                </div>
                <div class="col-sm-6 d-none d-sm-block">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('dashboard/home') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ url('dashboard/reports/giving-statements') }}">Giving Statements</a></li>
                        <li class="breadcrumb-item active">Settings</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <form action="{{ url('dashboard/reports/giving-statements/settings') }}" method="POST">
                @csrf
                
                <div class="row">
                    <!-- General Settings -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h3 class="card-title"><i class="bi bi-sliders"></i> General Settings</h3>
                            </div>
                            <div class="card-body">
                                <!-- Church Header -->
                                <div class="form-group">
                                    <label for="church_header_text">Church Header Text</label>
                                    <input type="text" name="church_header_text" id="church_header_text" 
                                           class="form-control @error('church_header_text') is-invalid @enderror"
                                           value="{{ old('church_header_text', $config->church_header_text) }}"
                                           placeholder="{{ auth()->user()->tenant->name }}">
                                    <small class="text-muted">Displayed at the top of every giving statement</small>
                                    @error('church_header_text')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Default Password Type -->
                                <div class="form-group">
                                    <label for="default_password_type">Default Password Protection</label>
                                    <select name="default_password_type" id="default_password_type" class="form-control">
                                        <option value="phone" {{ old('default_password_type', $config->default_password_type) == 'phone' ? 'selected' : '' }}>
                                            Phone Number (last 4 digits)
                                        </option>
                                        <option value="custom_code" {{ old('default_password_type', $config->default_password_type) == 'custom_code' ? 'selected' : '' }}>
                                            Custom Code
                                        </option>
                                        <option value="sms_otp" {{ old('default_password_type', $config->default_password_type) == 'sms_otp' ? 'selected' : '' }}>
                                            SMS OTP (One-Time Password)
                                        </option>
                                        <option value="none" {{ old('default_password_type', $config->default_password_type) == 'none' ? 'selected' : '' }}>
                                            No Password Protection
                                        </option>
                                    </select>
                                    <small class="text-muted">Default password method for new statements</small>
                                </div>

                                <!-- Custom Password Code -->
                                <div class="form-group {{ $config->default_password_type != 'custom_code' ? 'd-none' : '' }}" id="custom_code_group">
                                    <label for="custom_password_code">Default Custom Code</label>
                                    <input type="text" name="custom_password_code" id="custom_password_code" 
                                           class="form-control @error('custom_password_code') is-invalid @enderror"
                                           value="{{ old('custom_password_code', $config->custom_password_code) }}"
                                           placeholder="e.g., 2024">
                                    <small class="text-muted">Default code when using custom code protection</small>
                                    @error('custom_password_code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Info: Bulk generation is always enabled -->
                                <div class="alert alert-light border">
                                    <small class="text-muted">
                                        <i class="bi bi-info-circle"></i> Multi-member statement generation is always enabled.
                                    </small>
                                </div>
                            </div>
                        </div>

                        <!-- Thank You Note -->
                        <div class="card">
                            <div class="card-header bg-success text-white">
                                <h3 class="card-title"><i class="bi bi-heart"></i> Thank You Message</h3>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="include_thank_you_note" 
                                               name="include_thank_you_note" value="1"
                                               {{ old('include_thank_you_note', $config->include_thank_you_note) ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="include_thank_you_note">
                                            Include Thank You Note
                                        </label>
                                    </div>
                                </div>

                                <div class="form-group {{ !$config->include_thank_you_note ? 'd-none' : '' }}" id="thank_you_note_group">
                                    <label for="thank_you_note">Thank You Message</label>
                                    <textarea name="thank_you_note" id="thank_you_note" rows="4" 
                                              class="form-control @error('thank_you_note') is-invalid @enderror"
                                              placeholder="Enter your thank you message...">{{ old('thank_you_note', $config->thank_you_note) }}</textarea>
                                    <small class="text-muted">This message will appear at the end of each statement</small>
                                    @error('thank_you_note')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Email Template -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-info text-white">
                                <h3 class="card-title"><i class="bi bi-envelope"></i> Email Template</h3>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="email_template_subject">Email Subject</label>
                                    <input type="text" name="email_template_subject" id="email_template_subject" 
                                           class="form-control @error('email_template_subject') is-invalid @enderror"
                                           value="{{ old('email_template_subject', $config->email_template_subject) }}"
                                           placeholder="Your Giving Statement - {{ '{' . '{' }}church_name{{ '}' . '}' }}">
                                    <small class="text-muted">
                                        Available variables: <code>{&#123;church_name&#125;}</code>, <code>{&#123;member_name&#125;}</code>, 
                                        <code>{&#123;period_from&#125;}</code>, <code>{&#123;period_to&#125;}</code>
                                    </small>
                                    @error('email_template_subject')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="email_template_body">Email Body</label>
                                    <textarea name="email_template_body" id="email_template_body" rows="12" 
                                              class="form-control @error('email_template_body') is-invalid @enderror"
                                              placeholder="Enter email template...">{{ old('email_template_body', $config->email_template_body) }}</textarea>
                                    <small class="text-muted">
                                        Available variables: <code>{&#123;church_name&#125;}</code>, <code>{&#123;member_name&#125;}</code>, 
                                        <code>{&#123;period_from&#125;}</code>, <code>{&#123;period_to&#125;}</code>, <code>{&#123;total_amount&#125;}</code>, 
                                        <code>{&#123;transaction_count&#125;}</code>, <code>{&#123;password_hint&#125;}</code>, <code>{&#123;custom_message&#125;}</code>
                                    </small>
                                    @error('email_template_body')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Preview Card -->
                        <div class="card">
                            <div class="card-header bg-secondary text-white">
                                <h3 class="card-title"><i class="bi bi-eye"></i> Preview</h3>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-light border">
                                    <h6 class="font-weight-bold" id="preview-subject">Your Giving Statement - Church Name</h6>
                                    <hr>
                                    <div id="preview-body" class="small">
                                        <p>Dear John Doe,</p>
                                        <p>Thank you for your faithful giving. Attached is your giving statement...</p>
                                        <p>Password Hint: Last 4 digits of your phone number</p>
                                    </div>
                                </div>
                                <button type="button" id="btn-refresh-preview" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-arrow-clockwise"></i> Refresh Preview
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Email Settings -->
                <div class="row mt-3">
                    <div class="col-md-12">
                        <div class="card border-warning">
                            <div class="card-header bg-warning text-dark">
                                <h3 class="card-title"><i class="bi bi-envelope-gear"></i> Email Server Settings</h3>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle"></i> 
                                    <strong>Plug & Play Email Configuration:</strong> Configure your own email server settings below. 
                                    Use "System Default" to use the church app's main email settings, or configure your own SMTP or email service.
                                </div>

                                <div class="row">
                                    <!-- Email Driver Selection -->
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="email_driver">Email Driver</label>
                                            <select name="email_driver" id="email_driver" class="form-control">
                                                @foreach($emailDrivers as $value => $label)
                                                    <option value="{{ $value }}" {{ old('email_driver', $config->email_driver ?? 'default') == $value ? 'selected' : '' }}>
                                                        {{ $label }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <small class="text-muted">Select how emails will be sent</small>
                                        </div>

                                        <div class="form-group">
                                            <div class="custom-control custom-switch">
                                                <input type="checkbox" class="custom-control-input" id="enable_email_delivery" 
                                                       name="enable_email_delivery" value="1"
                                                       {{ old('enable_email_delivery', $config->enable_email_delivery) ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="enable_email_delivery">
                                                    Enable Email Delivery
                                                </label>
                                            </div>
                                            <small class="text-muted">Allow sending statements via email</small>
                                        </div>

                                        <!-- SMTP Settings -->
                                        <div id="smtp-settings" class="border rounded p-3 mb-3 {{ old('email_driver', $config->email_driver) == 'smtp' ? '' : 'd-none' }}">
                                            <h6 class="font-weight-bold mb-3"><i class="bi bi-server"></i> SMTP Configuration</h6>
                                            
                                            <div class="form-group">
                                                <label for="smtp_host">SMTP Host</label>
                                                <input type="text" name="smtp_host" id="smtp_host" 
                                                       class="form-control @error('smtp_host') is-invalid @enderror"
                                                       value="{{ old('smtp_host', $config->smtp_host) }}"
                                                       placeholder="smtp.gmail.com">
                                                @error('smtp_host')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="row">
                                                <div class="col-6">
                                                    <div class="form-group">
                                                        <label for="smtp_port">Port</label>
                                                        <input type="number" name="smtp_port" id="smtp_port" 
                                                               class="form-control @error('smtp_port') is-invalid @enderror"
                                                               value="{{ old('smtp_port', $config->smtp_port) }}"
                                                               placeholder="587">
                                                        @error('smtp_port')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="form-group">
                                                        <label for="smtp_encryption">Encryption</label>
                                                        <select name="smtp_encryption" id="smtp_encryption" class="form-control">
                                                            @foreach($encryptionOptions as $value => $label)
                                                                <option value="{{ $value }}" {{ old('smtp_encryption', $config->smtp_encryption ?? 'tls') == $value ? 'selected' : '' }}>
                                                                    {{ $label }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label for="smtp_username">Username</label>
                                                <input type="text" name="smtp_username" id="smtp_username" 
                                                       class="form-control @error('smtp_username') is-invalid @enderror"
                                                       value="{{ old('smtp_username', $config->smtp_username) }}"
                                                       placeholder="your@email.com">
                                                @error('smtp_username')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="form-group">
                                                <label for="smtp_password">Password</label>
                                                <input type="password" name="smtp_password" id="smtp_password" 
                                                       class="form-control @error('smtp_password') is-invalid @enderror"
                                                       placeholder="{{ $config->smtp_password ? '•••••••• (unchanged)' : 'Enter password' }}">
                                                <small class="text-muted">App password or SMTP password</small>
                                                @error('smtp_password')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <!-- Service Settings -->
                                        <div id="service-settings" class="border rounded p-3 mb-3 {{ in_array(old('email_driver', $config->email_driver), ['mailgun', 'postmark', 'ses']) ? '' : 'd-none' }}">
                                            <h6 class="font-weight-bold mb-3"><i class="bi bi-cloud"></i> Email Service Settings</h6>
                                            
                                            <div class="form-group">
                                                <label for="email_service_key">API Key</label>
                                                <input type="text" name="email_service_key" id="email_service_key" 
                                                       class="form-control @error('email_service_key') is-invalid @enderror"
                                                       value="{{ old('email_service_key', $config->email_service_key) }}"
                                                       placeholder="key-xxxxxxxxxx">
                                                @error('email_service_key')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="form-group" id="service-domain-group">
                                                <label for="email_service_domain">Domain</label>
                                                <input type="text" name="email_service_domain" id="email_service_domain" 
                                                       class="form-control @error('email_service_domain') is-invalid @enderror"
                                                       value="{{ old('email_service_domain', $config->email_service_domain) }}"
                                                       placeholder="mg.yourdomain.com">
                                                @error('email_service_domain')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="form-group d-none" id="service-region-group">
                                                <label for="email_service_region">AWS Region</label>
                                                <input type="text" name="email_service_region" id="email_service_region" 
                                                       class="form-control @error('email_service_region') is-invalid @enderror"
                                                       value="{{ old('email_service_region', $config->email_service_region) }}"
                                                       placeholder="us-east-1">
                                                @error('email_service_region')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="form-group d-none" id="service-secret-group">
                                                <label for="email_service_secret">Secret Key</label>
                                                <input type="text" name="email_service_secret" id="email_service_secret" 
                                                       class="form-control @error('email_service_secret') is-invalid @enderror"
                                                       value="{{ old('email_service_secret', $config->email_service_secret) }}"
                                                       placeholder="AWS Secret Access Key">
                                                @error('email_service_secret')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <!-- From Address & Testing -->
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="email_from_address">From Email Address</label>
                                            <input type="email" name="email_from_address" id="email_from_address" 
                                                   class="form-control @error('email_from_address') is-invalid @enderror"
                                                   value="{{ old('email_from_address', $config->email_from_address) }}"
                                                   placeholder="finance@yourchurch.com">
                                            <small class="text-muted">Sender email address (must be verified with your provider)</small>
                                            @error('email_from_address')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="form-group">
                                            <label for="email_from_name">From Name</label>
                                            <input type="text" name="email_from_name" id="email_from_name" 
                                                   class="form-control @error('email_from_name') is-invalid @enderror"
                                                   value="{{ old('email_from_name', $config->email_from_name) }}"
                                                   placeholder="{{ auth()->user()->tenant->name }} Finance">
                                            @error('email_from_name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="form-group">
                                            <label for="email_reply_to">Reply-To Address</label>
                                            <input type="email" name="email_reply_to" id="email_reply_to" 
                                                   class="form-control @error('email_reply_to') is-invalid @enderror"
                                                   value="{{ old('email_reply_to', $config->email_reply_to) }}"
                                                   placeholder="finance@yourchurch.com">
                                            <small class="text-muted">Where replies should be sent</small>
                                            @error('email_reply_to')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <!-- Configuration Status -->
                                        <div class="alert {{ $config->isEmailConfigured() ? 'alert-success' : 'alert-warning' }}">
                                            <i class="bi {{ $config->isEmailConfigured() ? 'bi-check-circle' : 'bi-exclamation-triangle' }}"></i>
                                            <strong>Status:</strong> 
                                            @if($config->isEmailConfigured())
                                                Email is configured
                                                @if($config->email_config_tested_at)
                                                    <br><small>Last tested: {{ $config->email_config_tested_at->diffForHumans() }}</small>
                                                @endif
                                            @else
                                                Email is not fully configured
                                            @endif
                                            @if($config->email_config_test_error)
                                                <br><small class="text-danger">Last error: {{ Str::limit($config->email_config_test_error, 100) }}</small>
                                            @endif
                                        </div>

                                        <!-- Test Email -->
                                        <div class="border rounded p-3 bg-light">
                                            <h6 class="font-weight-bold mb-3"><i class="bi bi-send-check"></i> Test Configuration</h6>
                                            <div class="form-group">
                                                <label for="test_email_address">Test Email Address</label>
                                                <input type="email" name="test_email_address" id="test_email_address" 
                                                       class="form-control"
                                                       value="{{ old('test_email_address', $config->test_email_address) }}"
                                                       placeholder="your@email.com">
                                                <small class="text-muted">Send a test email to verify your settings</small>
                                            </div>
                                            <button type="submit" name="test_email" value="1" class="btn btn-info">
                                                <i class="bi bi-send"></i> Save & Send Test Email
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Save Button -->
                <div class="row mt-3">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-body">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save"></i> Save Settings
                                </button>
                                <a href="{{ url('dashboard/reports/giving-statements') }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-left"></i> Back to Statements
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section>

    <!-- Password Policy Change Confirmation Modal -->
    <div class="modal fade" id="passwordPolicyConfirmModal" tabindex="-1" role="dialog" data-backdrop="static">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title"><i class="bi bi-exclamation-triangle"></i> Confirm Password Policy Change</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>You are about to change the default password protection policy.</p>
                    <div class="alert alert-info">
                        <strong>Current Policy:</strong> <span id="current-policy-display"></span><br>
                        <strong>New Policy:</strong> <span id="new-policy-display"></span>
                    </div>
                    <p class="text-muted">This change will affect all future giving statements. Existing statements will retain their original passwords.</p>
                    <div class="form-group">
                        <label>Type "CONFIRM" to proceed:</label>
                        <input type="text" id="policy-confirm-input" class="form-control" placeholder="Type CONFIRM">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" id="btn-confirm-policy-change" class="btn btn-warning" disabled>
                        <i class="bi bi-check"></i> Confirm Change
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Show/hide custom code field based on password type
        $('#default_password_type').on('change', function() {
            if ($(this).val() === 'custom_code') {
                $('#custom_code_group').removeClass('d-none');
            } else {
                $('#custom_code_group').addClass('d-none');
            }
        });

        // Show/hide thank you note field
        $('#include_thank_you_note').on('change', function() {
            if ($(this).is(':checked')) {
                $('#thank_you_note_group').removeClass('d-none');
            } else {
                $('#thank_you_note_group').addClass('d-none');
            }
        });

        // Refresh preview
        $('#btn-refresh-preview').on('click', function() {
            updatePreview();
        });

        // Update preview on input change
        $('#email_template_subject, #email_template_body').on('input', debounce(updatePreview, 500));

        function updatePreview() {
            const subject = $('#email_template_subject').val() || 'Your Giving Statement - @{{church_name}}';
            const body = $('#email_template_body').val() || 'Dear @{{member_name}},\n\nThank you for your faithful giving.';

            const variables = {
                '@{{church_name}}': '{{ auth()->user()->tenant->name }}',
                '@{{member_name}}': 'John Doe',
                '@{{period_from}}': 'January 01, 2024',
                '@{{period_to}}': 'December 31, 2024',
                '@{{total_amount}}': '50,000.00',
                '@{{transaction_count}}': '52',
                '@{{password_hint}}': 'Last 4 digits of your phone number',
                '@{{custom_message}}': ''
            };

            let previewSubject = subject;
            let previewBody = body;

            for (const [key, value] of Object.entries(variables)) {
                previewSubject = previewSubject.replace(new RegExp(key.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'), 'g'), value);
                previewBody = previewBody.replace(new RegExp(key.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'), 'g'), value);
            }

            $('#preview-subject').text(previewSubject);
            $('#preview-body').html(nl2br(previewBody));
        }

        function nl2br(str) {
            return str.replace(/\n/g, '<br>');
        }

        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }

        // Initial preview
        updatePreview();

        // Email driver change handler
        $('#email_driver').on('change', function() {
            const driver = $(this).val();
            
            // Show/hide SMTP settings
            if (driver === 'smtp') {
                $('#smtp-settings').removeClass('d-none');
                $('#service-settings').addClass('d-none');
            } else if (['mailgun', 'postmark', 'ses', 'sendgrid'].includes(driver)) {
                $('#smtp-settings').addClass('d-none');
                $('#service-settings').removeClass('d-none');
                
                // Show/hide service-specific fields
                if (driver === 'mailgun' || driver === 'postmark') {
                    $('#service-domain-group').removeClass('d-none');
                } else {
                    $('#service-domain-group').addClass('d-none');
                }
                
                if (driver === 'ses') {
                    $('#service-region-group').removeClass('d-none');
                    $('#service-secret-group').removeClass('d-none');
                } else {
                    $('#service-region-group').addClass('d-none');
                    $('#service-secret-group').addClass('d-none');
                }
            } else {
                $('#smtp-settings').addClass('d-none');
                $('#service-settings').addClass('d-none');
            }
        });

        // Trigger change to set initial state
        $('#email_driver').trigger('change');

        // Password policy change confirmation
        const policyLabels = {
            'phone': 'Phone Number (last 4 digits)',
            'custom_code': 'Custom Code',
            'sms_otp': 'SMS OTP (One-Time Password)',
            'none': 'No Password Protection'
        };

        let originalPolicy = $('#default_password_type').val();
        let formSubmitting = false;

        $('form').on('submit', function(e) {
            if (formSubmitting) return true;

            const newPolicy = $('#default_password_type').val();
            
            // Check if policy changed
            if (newPolicy !== originalPolicy) {
                e.preventDefault();
                
                $('#current-policy-display').text(policyLabels[originalPolicy] || originalPolicy);
                $('#new-policy-display').text(policyLabels[newPolicy] || newPolicy);
                $('#policy-confirm-input').val('');
                $('#btn-confirm-policy-change').prop('disabled', true);
                
                $('#passwordPolicyConfirmModal').modal('show');
                return false;
            }
        });

        // Enable confirm button when user types CONFIRM
        $('#policy-confirm-input').on('input', function() {
            $('#btn-confirm-policy-change').prop('disabled', $(this).val() !== 'CONFIRM');
        });

        // Handle confirmation
        $('#btn-confirm-policy-change').on('click', function() {
            if ($('#policy-confirm-input').val() === 'CONFIRM') {
                $('#passwordPolicyConfirmModal').modal('hide');
                formSubmitting = true;
                $('form').submit();
            }
        });
    });
</script>
@endpush
