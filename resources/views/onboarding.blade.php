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
        body { background: #f4f6f9; }
        .onboarding-card { max-width: 650px; margin: 30px auto; }
        .step-indicator { display: flex; justify-content: center; margin-bottom: 20px; }
        .step-indicator .step { width: 36px; height: 36px; border-radius: 50%; background: #dee2e6;
            display: flex; align-items: center; justify-content: center; font-weight: 700;
            color: #6c757d; font-size: 14px; position: relative; }
        .step-indicator .step.active { background: #007bff; color: #fff; }
        .step-indicator .step.done { background: #28a745; color: #fff; }
        .step-indicator .step-line { width: 60px; height: 2px; background: #dee2e6; align-self: center; margin: 0 6px; }
        .step-indicator .step-line.done { background: #28a745; }
        .child-row { border: 1px solid #e9ecef; border-radius: 6px; padding: 12px; margin-bottom: 10px; background: #fafafa; }
    </style>
</head>
<body>
    <div class="container">
        <div class="onboarding-card">
            <div class="text-center mb-3 mt-4">
                <h3>{{ isset($site_settings) && $site_settings ? $site_settings->name : 'Church App' }}</h3>
                @if(isset($site_settings) && $site_settings && isset($site_settings->slogan) && $site_settings->slogan)
                    <p class="text-muted">{{ $site_settings->slogan }}</p>
                @endif
            </div>

            @if($state === 'expired')
                <div class="card shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-exclamation-triangle text-warning fa-3x mb-3"></i>
                        <h5>Link Expired</h5>
                        <p class="text-muted">This invitation link has expired or is invalid. Please contact the church administration for a new invitation.</p>
                        <a href="{{ url('login') }}" class="btn btn-outline-primary btn-sm mt-2">Already have an account? Log in</a>
                    </div>
                </div>
            @elseif($state === 'completed')
                <div class="card shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-check-circle text-success fa-3x mb-3"></i>
                        <h5>Registration Complete</h5>
                        <p class="text-muted">Your profile has been completed successfully. You can now log in to access the platform.</p>
                        <a href="{{ url('login') }}" class="btn btn-primary btn-sm mt-2"><i class="fas fa-sign-in-alt mr-1"></i> Log In</a>
                    </div>
                </div>
            @else
                @php $step = $step ?? 1; @endphp

                <!-- Step Indicator -->
                <div class="step-indicator">
                    <div class="step {{ $step == 1 ? 'active' : ($step > 1 ? 'done' : '') }}">{{ $step > 1 ? '&#10003;' : '1' }}</div>
                    <div class="step-line {{ $step > 1 ? 'done' : '' }}"></div>
                    <div class="step {{ $step == 2 ? 'active' : ($step > 2 ? 'done' : '') }}">{{ $step > 2 ? '&#10003;' : '2' }}</div>
                    <div class="step-line {{ $step > 2 ? 'done' : '' }}"></div>
                    <div class="step {{ $step == 3 ? 'active' : '' }}">3</div>
                </div>

                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif
                @if($errors->any())
                    <div class="alert alert-danger">
                        @foreach($errors->all() as $error)
                            {{ $error }}<br>
                        @endforeach
                    </div>
                @endif

                @if($step == 1)
                    <!-- STEP 1: Phone + Password -->
                    <div class="card shadow-sm">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="fas fa-mobile-alt mr-1"></i> Step 1 of 3 - Verify Your Phone & Set Password</h6>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('onboarding.step1', $token) }}">
                                @csrf
                                <div class="form-group">
                                    <label class="font-weight-bold">Phone Number <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">+{{ $phoneCode }}</span>
                                        </div>
                                        <input type="text" class="form-control" name="phone"
                                            value="{{ old('phone', isset($invitation) && $invitation->phone ? (strlen($invitation->phone) > strlen($phoneCode) && substr($invitation->phone, 0, strlen($phoneCode)) === $phoneCode ? substr($invitation->phone, strlen($phoneCode)) : $invitation->phone) : '') }}"
                                            placeholder="e.g. 712345678" required>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="font-weight-bold">Password <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control" name="password" placeholder="Minimum 8 characters" required minlength="8">
                                </div>
                                <div class="form-group">
                                    <label class="font-weight-bold">Confirm Password <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control" name="password_confirmation" placeholder="Repeat your password" required>
                                </div>
                                <div class="text-right mt-3">
                                    <button type="submit" class="btn btn-primary">
                                        Continue <i class="fas fa-arrow-right ml-1"></i>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                @elseif($step == 2)
                    <!-- STEP 2: Email (Optional) -->
                    <div class="card shadow-sm">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="fas fa-envelope mr-1"></i> Step 2 of 3 - Add Your Email (Optional)</h6>
                        </div>
                        <div class="card-body">
                            <p class="text-muted mb-3">Add your email to receive weekly devotions and church updates. You can skip this step if you prefer.</p>
                            <form method="POST" action="{{ route('onboarding.step2', $token) }}">
                                @csrf
                                <div class="form-group">
                                    <label class="font-weight-bold">Email Address</label>
                                    <input type="email" class="form-control" name="email"
                                        value="{{ old('email', isset($invitation) && $invitation->email ? $invitation->email : (isset($user) && $user ? $user->email : '')) }}"
                                        placeholder="your.email@example.com">
                                </div>
                                <div class="d-flex justify-content-between mt-3">
                                    <button type="submit" name="skip" class="btn btn-outline-secondary">
                                        Skip <i class="fas fa-forward ml-1"></i>
                                    </button>
                                    <button type="submit" class="btn btn-primary">
                                        Continue <i class="fas fa-arrow-right ml-1"></i>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                @elseif($step == 3)
                    <!-- STEP 3: Personal Details + Children + Spouse -->
                    <div class="card shadow-sm">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="fas fa-user mr-1"></i> Step 3 of 3 - Complete Your Profile</h6>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('onboarding.step3', $token) }}">
                                @csrf
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="font-weight-bold">First Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="firstname" value="{{ old('firstname', isset($user) && $user ? $user->firstname : '') }}" required>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="font-weight-bold">Surname <small class="text-muted">(middle name)</small></label>
                                        <input type="text" class="form-control" name="surname" value="{{ old('surname', isset($user) && $user ? $user->surname : '') }}">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="font-weight-bold">Last Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="lastname" value="{{ old('lastname', isset($user) && $user ? $user->lastname : '') }}" required>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="font-weight-bold">Gender</label>
                                        <select class="form-control" name="gender">
                                            <option value="">-- Select --</option>
                                            <option value="0" {{ old('gender') === '0' ? 'selected' : '' }}>Male</option>
                                            <option value="1" {{ old('gender') === '1' ? 'selected' : '' }}>Female</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="font-weight-bold">Date of Birth</label>
                                        <input type="date" class="form-control" name="dob" value="{{ old('dob') }}">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="font-weight-bold">Marital Status</label>
                                        <select class="form-control" name="marital">
                                            <option value="">-- Select --</option>
                                            <option value="0" {{ old('marital') === '0' ? 'selected' : '' }}>Single</option>
                                            <option value="1" {{ old('marital') === '1' ? 'selected' : '' }}>Married</option>
                                            <option value="2" {{ old('marital') === '2' ? 'selected' : '' }}>Divorced</option>
                                            <option value="3" {{ old('marital') === '3' ? 'selected' : '' }}>Widowed</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="font-weight-bold">Residential Location</label>
                                        <input type="text" class="form-control" name="resident" value="{{ old('resident') }}" placeholder="e.g. Ruiru, Membley">
                                    </div>
                                </div>

                                <!-- Children Section -->
                                <hr>
                                <div class="mb-3">
                                    <h6 class="font-weight-bold"><i class="fas fa-child mr-1"></i> Children <small class="text-muted font-weight-normal">(optional)</small></h6>
                                    <div id="children-container"></div>
                                    <button type="button" class="btn btn-sm btn-outline-primary" id="btn-add-child">
                                        <i class="fas fa-plus mr-1"></i> Add Child
                                    </button>
                                </div>

                                <!-- Spouse Section -->
                                <hr>
                                <div class="mb-3">
                                    <h6 class="font-weight-bold"><i class="fas fa-heart mr-1"></i> Spouse <small class="text-muted font-weight-normal">(optional)</small></h6>
                                    <div class="row">
                                        <div class="col-md-6 mb-2">
                                            <label>Spouse Name</label>
                                            <input type="text" class="form-control" name="spouse_name" value="{{ old('spouse_name') }}">
                                        </div>
                                        <div class="col-md-6 mb-2">
                                            <label>Spouse Phone</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">+{{ $phoneCode }}</span>
                                                </div>
                                                <input type="text" class="form-control" name="spouse_phone" value="{{ old('spouse_phone') }}" placeholder="e.g. 712345678">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="text-right mt-3">
                                    <button type="submit" class="btn btn-success btn-lg">
                                        <i class="fas fa-check mr-1"></i> Complete Registration
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                @endif

                <div class="text-center mt-3 mb-4">
                    <a href="{{ url('login') }}" class="text-muted"><small>Already have an account? Log in</small></a>
                </div>
            @endif
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var childIndex = 0;
        var maxChildren = 10;
        var container = document.getElementById('children-container');
        var addBtn = document.getElementById('btn-add-child');

        if (addBtn) {
            addBtn.addEventListener('click', function() {
                if (childIndex >= maxChildren) {
                    alert('Maximum ' + maxChildren + ' children allowed.');
                    return;
                }
                var row = document.createElement('div');
                row.className = 'child-row';
                row.innerHTML =
                    '<div class="d-flex justify-content-between mb-2">' +
                        '<small class="font-weight-bold text-muted">Child ' + (childIndex + 1) + '</small>' +
                        '<button type="button" class="btn btn-sm btn-outline-danger btn-remove-child" style="padding:0 8px;line-height:1.4;">&times;</button>' +
                    '</div>' +
                    '<div class="row">' +
                        '<div class="col-md-3 mb-2"><input type="text" class="form-control form-control-sm" name="children[' + childIndex + '][firstname]" placeholder="First Name"></div>' +
                        '<div class="col-md-3 mb-2"><input type="text" class="form-control form-control-sm" name="children[' + childIndex + '][lastname]" placeholder="Last Name"></div>' +
                        '<div class="col-md-3 mb-2"><select class="form-control form-control-sm" name="children[' + childIndex + '][gender]"><option value="">Gender</option><option value="Male">Male</option><option value="Female">Female</option></select></div>' +
                        '<div class="col-md-3 mb-2"><input type="date" class="form-control form-control-sm" name="children[' + childIndex + '][dob]"></div>' +
                    '</div>';
                container.appendChild(row);
                childIndex++;

                row.querySelector('.btn-remove-child').addEventListener('click', function() {
                    row.remove();
                });
            });
        }
    });
    </script>
</body>
</html>
