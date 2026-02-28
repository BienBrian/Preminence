<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Verify Profile - {{ isset($site_settings) && $site_settings ? $site_settings->name : 'Church App' }}</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="{{ asset('fontawesome-free-6.4.0-web/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('dashboard/dist/css/adminlte.min.css') }}">
    <style>
        body { background: #f4f6f9; }
        .verify-card { max-width: 600px; margin: 40px auto; }
    </style>
</head>
<body>
    <div class="container">
        <div class="verify-card">
            <div class="text-center mb-4 mt-4">
                <h3>{{ isset($site_settings) && $site_settings ? $site_settings->name : 'Church App' }}</h3>
                <p class="text-muted">Profile Verification</p>
            </div>

            @if(isset($expired) && $expired)
                <div class="card shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-exclamation-triangle text-warning fa-3x mb-3"></i>
                        <h5>Link Expired</h5>
                        <p class="text-muted">This verification link has expired or is invalid. Please contact the church administration for a new link.</p>
                    </div>
                </div>
            @elseif(isset($completed) && $completed)
                <div class="card shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-check-circle text-success fa-3x mb-3"></i>
                        <h5>Profile Verified</h5>
                        <p class="text-muted">Thank you, {{ $user->firstname }}! Your profile has been updated and verified successfully.</p>
                    </div>
                </div>
            @else
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

                <div class="card shadow-sm">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-user-check mr-1"></i> Please verify and complete your details</h6>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('verify-profile.submit', $token) }}">
                            @csrf
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label font-weight-bold">First Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="firstname" value="{{ old('firstname', $user->firstname) }}" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label font-weight-bold">Last Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="lastname" value="{{ old('lastname', $user->lastname) }}" required>
                                </div>
                                @php $phoneCode = isset($site_settings) && $site_settings && isset($site_settings->phone_code) ? $site_settings->phone_code : '254'; @endphp
                                <div class="col-md-6 mb-3">
                                    <label class="form-label font-weight-bold">Phone <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">+{{ $phoneCode }}</span>
                                        </div>
                                        <input type="text" class="form-control" name="phone" value="{{ old('phone', $user->phone && strlen($user->phone) > strlen($phoneCode) && substr($user->phone, 0, strlen($phoneCode)) === $phoneCode ? substr($user->phone, strlen($phoneCode)) : $user->phone) }}" required>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label font-weight-bold">Email</label>
                                    <input type="email" class="form-control" name="email" value="{{ old('email', $user->email) }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label font-weight-bold">Gender</label>
                                    <select class="form-control" name="gender">
                                        <option value="">-- Select --</option>
                                        <option value="0">Male</option>
                                        <option value="1">Female</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label font-weight-bold">Date of Birth</label>
                                    <input type="date" class="form-control" name="dob" value="{{ old('dob') }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label font-weight-bold">Residential Location</label>
                                    <input type="text" class="form-control" name="resident" value="{{ old('resident', isset($scontact) ? $scontact->resident : '') }}" placeholder="e.g. Ruiru, Membley">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label font-weight-bold">Home Town</label>
                                    <input type="text" class="form-control" name="town" value="{{ old('town', isset($scontact) ? $scontact->town : '') }}">
                                </div>
                            </div>

                            <div class="text-right mt-2">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-check mr-1"></i> Verify & Submit
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>
</body>
</html>
