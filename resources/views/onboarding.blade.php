<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Join - {{ isset($site_settings) && $site_settings ? $site_settings->name : 'Church App' }}</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="{{ asset('fontawesome-free-6.4.0-web/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('dashboard/dist/css/adminlte.min.css') }}">
    <style>
        body { background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%); min-height: 100vh; }
        .onboarding-card { max-width: 580px; margin: 30px auto; }
        .brand-card { background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); backdrop-filter: blur(10px); border-radius: 16px; padding: 24px; margin-bottom: 20px; text-align: center; color: #fff; }
        .brand-card h3 { font-weight: 700; margin: 0; font-size: 1.6rem; }
        .brand-card p { color: rgba(255,255,255,0.7); margin: 4px 0 0; }
        .step-card { background: #fff; border-radius: 16px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); overflow: hidden; }
        .step-header { background: linear-gradient(135deg, #007bff, #6610f2); color: #fff; padding: 20px 24px; }
        .step-header h6 { margin: 0; font-size: 1rem; font-weight: 600; }
        .step-header p { margin: 4px 0 0; font-size: 0.85rem; opacity: 0.85; }
        .step-body { padding: 28px 24px; }
        .step-indicator { display: flex; justify-content: center; align-items: center; margin-bottom: 20px; }
        .step-dot { width: 38px; height: 38px; border-radius: 50%; background: rgba(255,255,255,0.15); border: 2px solid rgba(255,255,255,0.3);
            display: flex; align-items: center; justify-content: center; font-weight: 700; color: rgba(255,255,255,0.6); font-size: 14px; }
        .step-dot.active { background: #007bff; border-color: #007bff; color: #fff; box-shadow: 0 0 20px rgba(0,123,255,0.5); }
        .step-dot.done { background: #28a745; border-color: #28a745; color: #fff; }
        .step-line { width: 50px; height: 2px; background: rgba(255,255,255,0.2); margin: 0 8px; }
        .step-line.done { background: #28a745; }
        .otp-input { font-size: 1.5rem; font-weight: 700; letter-spacing: 6px; text-align: center; border: 2px solid #dee2e6; border-radius: 8px; }
        .otp-input:focus { border-color: #007bff; box-shadow: 0 0 0 3px rgba(0,123,255,0.15); }
        .incentive-note { background: #fff8e1; border-left: 4px solid #ffc107; border-radius: 0 8px 8px 0; padding: 10px 14px; margin-top: 6px; font-size: 0.82rem; color: #856404; }
        .edit-contact { font-size: 0.82rem; color: #6c757d; margin-top: 8px; }
        .btn-link-edit { background: none; border: none; padding: 0; color: #007bff; font-size: 0.82rem; cursor: pointer; text-decoration: underline; }
        .edit-form { background: #f8f9fa; border-radius: 8px; padding: 14px; margin-top: 10px; display: none; }
    </style>
</head>
<body>
    <div class="container py-4">
        @php $step = $step ?? 1; @endphp
        @if(session('force_step')) @php $step = session('force_step'); @endphp @endif

        <div class="onboarding-card">
            <div class="brand-card">
                <h3>{{ isset($site_settings) && $site_settings ? $site_settings->name : 'Church App' }}</h3>
                @if(isset($site_settings) && $site_settings && isset($site_settings->slogan) && $site_settings->slogan)
                    <p>{{ $site_settings->slogan }}</p>
                @endif
            </div>

            @if($state === 'expired')
                <div class="step-card">
                    <div class="step-body text-center py-5">
                        <i class="fas fa-exclamation-triangle text-warning fa-3x mb-3"></i>
                        <h5>Link Expired</h5>
                        <p class="text-muted">This invitation link has expired or is invalid. Please contact the church administration for a new invitation.</p>
                        <a href="{{ url('login') }}" class="btn btn-outline-primary btn-sm mt-2">Already have an account? Log in</a>
                    </div>
                </div>
            @elseif($state === 'completed')
                <div class="step-card">
                    <div class="step-body text-center py-5">
                        <i class="fas fa-check-circle text-success fa-3x mb-3"></i>
                        <h5>Registration Complete!</h5>
                        <p class="text-muted">Your account has been set up successfully. You can now log in.</p>
                        <a href="{{ url('login') }}" class="btn btn-primary mt-2"><i class="fas fa-sign-in-alt mr-1"></i> Log In</a>
                    </div>
                </div>
            @else
                {{-- Step Indicator --}}
                <div class="step-indicator">
                    <div class="step-dot {{ $step == 1 ? 'active' : ($step > 1 ? 'done' : '') }}">
                        {{ $step > 1 ? '✓' : '1' }}
                    </div>
                    <div class="step-line {{ $step > 1 ? 'done' : '' }}"></div>
                    <div class="step-dot {{ $step == 2 ? 'active' : ($step > 2 ? 'done' : '') }}">
                        {{ $step > 2 ? '✓' : '2' }}
                    </div>
                    <div class="step-line {{ $step > 2 ? 'done' : '' }}"></div>
                    <div class="step-dot {{ $step == 3 ? 'active' : '' }}">3</div>
                </div>

                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if($errors->any())
                    <div class="alert alert-danger">
                        @foreach($errors->all() as $error) {{ $error }}<br> @endforeach
                    </div>
                @endif

                @if($step == 1)
                {{-- ═══════════════ STEP 1: Name, Phone, Email, Password ═══════════════ --}}
                <div class="step-card">
                    <div class="step-header">
                        <h6><i class="fas fa-user-plus mr-1"></i> Step 1 of 3 — Create Your Account</h6>
                        <p>Enter your name, phone, email, and choose a password.</p>
                    </div>
                    <div class="step-body">
                        <form method="POST" action="{{ route('onboarding.step1', $token) }}">
                            @csrf
                            {{-- Name Fields --}}
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label class="font-weight-bold">First Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="firstname" 
                                        value="{{ old('firstname') }}" placeholder="e.g. John" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="font-weight-bold">Surname <small class="text-muted">(Middle)</small></label>
                                    <input type="text" class="form-control" name="surname" 
                                        value="{{ old('surname') }}" placeholder="e.g. Mwangi">
                                </div>
                                <div class="col-md-4">
                                    <label class="font-weight-bold">Last Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="lastname" 
                                        value="{{ old('lastname') }}" placeholder="e.g. Doe" required>
                                </div>
                            </div>
                            <div class="form-group mb-3">
                                <label class="font-weight-bold">Phone Number <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-phone mr-1"></i>+{{ $phoneCode }}</span>
                                    </div>
                                    <input type="text" class="form-control" name="phone"
                                        value="{{ old('phone', isset($invitation) && $invitation->phone ? (substr($invitation->phone, 0, strlen($phoneCode)) === $phoneCode ? substr($invitation->phone, strlen($phoneCode)) : $invitation->phone) : '') }}"
                                        placeholder="e.g. 712345678" required>
                                </div>
                            </div>
                            <div class="form-group mb-3">
                                <label class="font-weight-bold">Email Address</label>
                                <input type="email" class="form-control" name="email"
                                    value="{{ old('email', isset($invitation) && $invitation->email ? $invitation->email : '') }}"
                                    placeholder="your.email@example.com">
                                <div class="incentive-note">
                                    <i class="fas fa-envelope mr-1"></i> <strong>Tip:</strong> Adding your email lets us send you personalized weekly devotions and church updates.
                                </div>
                            </div>
                            <div class="form-group mb-3">
                                <label class="font-weight-bold">Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" name="password" placeholder="Minimum 8 characters" required minlength="8">
                            </div>
                            <div class="form-group mb-3">
                                <label class="font-weight-bold">Confirm Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" name="password_confirmation" placeholder="Repeat your password" required>
                            </div>
                            <div class="text-right mt-4">
                                <button type="submit" class="btn btn-primary btn-lg px-5">
                                    Continue <i class="fas fa-arrow-right ml-1"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                @elseif($step == 2)
                {{-- ═══════════════ STEP 2: Phone OTP Verification ═══════════════ --}}
                <div class="step-card">
                    <div class="step-header">
                        <h6><i class="fas fa-shield-alt mr-1"></i> Step 2 of 3 — Verify Your Phone</h6>
                        <p>We sent a 6-digit code to your phone.</p>
                    </div>
                    <div class="step-body">
                        <form method="POST" action="{{ route('onboarding.step2', $token) }}" id="otpForm">
                            @csrf
                            <div class="form-group mb-4">
                                <label class="font-weight-bold">
                                    <i class="fas fa-sms text-primary mr-1"></i>
                                    Phone Code
                                    <small class="text-muted font-weight-normal">
                                        (sent to {{ isset($invitation) && $invitation->phone ? '+' . substr($invitation->phone, 0, 3) . ' *** *** ' . substr($invitation->phone, -3) : 'your phone' }})
                                    </small>
                                </label>
                                <input type="text" class="form-control otp-input" name="phone_otp"
                                    maxlength="6" pattern="[0-9]{6}" inputmode="numeric"
                                    placeholder="— — — — — —" required>
                                <div class="edit-contact">
                                    Wrong number? <button type="button" class="btn-link-edit" onclick="toggleEdit('phone-edit')">Edit phone number</button>
                                </div>
                                <div class="edit-form" id="phone-edit">
                                    <label class="small font-weight-bold mb-1">Correct Phone Number</label>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend"><span class="input-group-text">+{{ $phoneCode }}</span></div>
                                        <input type="text" class="form-control" name="new_phone" placeholder="e.g. 712345678">
                                    </div>
                                </div>
                            </div>

                            <div class="alert alert-info py-2">
                                <i class="fas fa-info-circle mr-1"></i>
                                <strong>Email verification:</strong> You'll be able to verify your email later from your dashboard to receive personalized devotions and church updates.
                            </div>

                            <div class="d-flex justify-content-between align-items-center mt-4">
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="btn-resend-otp">
                                    <i class="fas fa-redo mr-1"></i> Resend Code
                                </button>
                                <button type="submit" class="btn btn-primary btn-lg px-5">
                                    Verify <i class="fas fa-check ml-1"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                @elseif($step == 3)
                {{-- ═══════════════ STEP 3: Alternate Phone Numbers ═══════════════ --}}
                <div class="step-card">
                    <div class="step-header">
                        <h6><i class="fas fa-phone-alt mr-1"></i> Step 3 of 3 — Alternate Phone (Optional)</h6>
                        <p>Add other phones you use so we can reach you. You can skip this step.</p>
                    </div>
                    <div class="step-body">
                        <form method="POST" action="{{ route('onboarding.step3', $token) }}">
                            @csrf
                            <div class="form-group mb-3">
                                <label class="font-weight-bold">Alternate Phone 1</label>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text">+{{ $phoneCode }}</span></div>
                                    <input type="text" class="form-control" name="alt_phone_1" placeholder="e.g. 722345678">
                                </div>
                            </div>
                            <div class="form-group mb-3">
                                <label class="font-weight-bold">Alternate Phone 2 <small class="text-muted">(optional)</small></label>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text">+{{ $phoneCode }}</span></div>
                                    <input type="text" class="form-control" name="alt_phone_2" placeholder="e.g. 733456789">
                                </div>
                            </div>
                            <div class="alert alert-info py-2 mt-3">
                                <i class="fas fa-info-circle mr-1"></i>
                                You can fill in further details like your name, family information, education etc. after logging in.
                            </div>
                            <div class="d-flex justify-content-between mt-4">
                                <button type="submit" name="skip" class="btn btn-outline-secondary">
                                    Skip <i class="fas fa-forward ml-1"></i>
                                </button>
                                <button type="submit" class="btn btn-success btn-lg px-5">
                                    <i class="fas fa-check mr-1"></i> Finish & Log In
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                @endif

                <div class="text-center mt-3 mb-4">
                    <a href="{{ url('login') }}" class="text-white-50"><small>Already have an account? Log in</small></a>
                </div>
            @endif
        </div>{{-- /.onboarding-card --}}
    </div>{{-- /.container --}}

    <script>
    function toggleEdit(id) {
        var el = document.getElementById(id);
        el.style.display = el.style.display === 'block' ? 'none' : 'block';
    }

    document.addEventListener('DOMContentLoaded', function() {
        // OTP input auto-advance on max length
        document.querySelectorAll('.otp-input').forEach(function(input) {
            input.addEventListener('input', function() {
                this.value = this.value.replace(/[^0-9]/g, '');
            });
        });

        // Resend OTP button
        var resendBtn = document.getElementById('btn-resend-otp');
        if (resendBtn) {
            resendBtn.addEventListener('click', function() {
                resendBtn.disabled = true;
                resendBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Sending...';
                fetch("{{ isset($token) ? url('onboarding/' . $token . '/resend-otp') : '#' }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json',
                    }
                })
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    if (data.success) {
                        resendBtn.innerHTML = '<i class="fas fa-check mr-1"></i> Codes Resent!';
                        setTimeout(function() {
                            resendBtn.innerHTML = '<i class="fas fa-redo mr-1"></i> Resend Codes';
                            resendBtn.disabled = false;
                        }, 30000);
                    } else {
                        resendBtn.innerHTML = '<i class="fas fa-redo mr-1"></i> Resend Codes';
                        alert(data.error || 'Failed to resend. Please try again.');
                        resendBtn.disabled = false;
                    }
                })
                .catch(function() {
                    resendBtn.innerHTML = '<i class="fas fa-redo mr-1"></i> Resend Codes';
                    resendBtn.disabled = false;
                });
            });
        }
    });
    </script>
</body>
</html>
