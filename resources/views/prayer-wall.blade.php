@extends('layouts.app')

@section('content')
    <!-- Hero -->
    <section class="prayer-wall-hero">
        <div class="container text-center">
            <h1><i class="fas fa-praying-hands"></i> Prayer Wall</h1>
            <p class="lead">Share your prayer requests and join us in lifting them up to God.</p>
        </div>
    </section>

    <div class="container py-5">
        <!-- Submit Request — Multi-Step Form -->
        <div class="row justify-content-center mb-5">
            <div class="col-lg-8">
                <div class="card shadow-sm prayer-submit-card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-plus-circle"></i> Submit a Prayer Request</h5>
                    </div>
                    <div class="card-body">
                        @if(session('success'))
                            <div class="alert alert-success"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
                        @endif
                        @if($errors->any())
                            <div class="alert alert-danger">
                                @foreach($errors->all() as $err)
                                    <div><i class="fas fa-exclamation-circle"></i> {{ $err }}</div>
                                @endforeach
                            </div>
                        @endif

                        <!-- Step Indicators -->
                        <div class="d-flex justify-content-center mb-4">
                            <div class="step-indicator active" data-step="1">
                                <span class="step-circle">1</span>
                                <small>Prayer Details</small>
                            </div>
                            <div class="step-line"></div>
                            <div class="step-indicator" data-step="2">
                                <span class="step-circle">2</span>
                                <small>Follow Up</small>
                            </div>
                            <div class="step-line"></div>
                            <div class="step-indicator" data-step="3">
                                <span class="step-circle">3</span>
                                <small>Your Details</small>
                            </div>
                        </div>

                        <form action="{{ url('/prayer-wall/submit') }}" method="POST" id="prayerForm">
                            @csrf

                            <!-- Step 1: Prayer Details -->
                            <div class="form-step" id="step1">
                                <h6 class="mb-3 text-primary"><i class="fas fa-pen"></i> Tell us about your prayer request</h6>
                                <div class="form-group">
                                    <label>Prayer Request Title <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="title" required maxlength="255" value="{{ old('title') }}">
                                </div>
                                <div class="form-group">
                                    <label>Your Prayer Request <span class="text-danger">*</span></label>
                                    <textarea class="form-control" name="description" rows="4" required maxlength="2000">{{ old('description') }}</textarea>
                                    <small class="text-muted">Max 2000 characters</small>
                                </div>
                                <div class="form-group">
                                    <label>Category</label>
                                    <select class="form-control" name="category">
                                        <option value="">-- Select --</option>
                                        <option value="healing" {{ old('category')=='healing'?'selected':'' }}>Healing</option>
                                        <option value="financial" {{ old('category')=='financial'?'selected':'' }}>Financial</option>
                                        <option value="family" {{ old('category')=='family'?'selected':'' }}>Family</option>
                                        <option value="spiritual" {{ old('category')=='spiritual'?'selected':'' }}>Spiritual</option>
                                        <option value="thanksgiving" {{ old('category')=='thanksgiving'?'selected':'' }}>Thanksgiving</option>
                                        <option value="other" {{ old('category')=='other'?'selected':'' }}>Other</option>
                                    </select>
                                </div>
                                <div class="text-right">
                                    <button type="button" class="btn btn-primary btn-next" data-next="2"><i class="fas fa-arrow-right"></i> Next</button>
                                </div>
                            </div>

                            <!-- Step 2: Contact Preference -->
                            <div class="form-step d-none" id="step2">
                                <h6 class="mb-3 text-primary"><i class="fas fa-hand-holding-heart"></i> How would you like us to respond?</h6>
                                <p class="text-muted small mb-3">Let us know how you'd like to be supported after submitting your prayer request.</p>

                                <div class="contact-option mb-3" data-value="follow_up">
                                    <div class="card border p-3" style="cursor:pointer;">
                                        <div class="d-flex align-items-start">
                                            <input type="radio" name="contact_preference" value="follow_up" class="mt-1 mr-3" {{ old('contact_preference','follow_up')=='follow_up'?'checked':'' }}>
                                            <div>
                                                <strong><i class="fas fa-phone-alt text-primary"></i> Follow up with me</strong>
                                                <p class="mb-0 text-muted small">A member of our prayer team will reach out to check on you and pray with you. Your name and contact details will be collected in the next step.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="contact-option mb-3" data-value="pastor_call">
                                    <div class="card border p-3" style="cursor:pointer;">
                                        <div class="d-flex align-items-start">
                                            <input type="radio" name="contact_preference" value="pastor_call" class="mt-1 mr-3" {{ old('contact_preference')=='pastor_call'?'checked':'' }}>
                                            <div>
                                                <strong><i class="fas fa-user-tie text-success"></i> Let the Senior Pastor call me</strong>
                                                <p class="mb-0 text-muted small">The Senior Pastor will personally reach out to you. Your name and phone number will be collected in the next step.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="contact-option mb-3" data-value="anonymous">
                                    <div class="card border p-3" style="cursor:pointer;">
                                        <div class="d-flex align-items-start">
                                            <input type="radio" name="contact_preference" value="anonymous" class="mt-1 mr-3" {{ old('contact_preference')=='anonymous'?'checked':'' }}>
                                            <div>
                                                <strong><i class="fas fa-user-secret text-secondary"></i> I want to remain anonymous</strong>
                                                <p class="mb-0 text-muted small">Your prayer request will be shared without any personal information. No contact details will be collected.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between mt-3">
                                    <button type="button" class="btn btn-outline-secondary btn-prev" data-prev="1"><i class="fas fa-arrow-left"></i> Back</button>
                                    <button type="button" class="btn btn-primary btn-next" data-next="3"><i class="fas fa-arrow-right"></i> Next</button>
                                </div>
                            </div>

                            <!-- Step 3: Contact Details (conditional) -->
                            <div class="form-step d-none" id="step3">
                                <!-- Shown for follow_up / pastor_call -->
                                <div id="contactFields">
                                    <h6 class="mb-3 text-primary"><i class="fas fa-address-card"></i> Your Contact Details</h6>
                                    <p class="text-muted small mb-3">Please provide your details so we can reach out to you. Your information will be kept confidential.</p>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Your Name <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" name="name" maxlength="100" value="{{ old('name') }}" id="inputName">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Phone Number <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text">+{{ $settings->phone_code ?? '254' }}</span>
                                                    </div>
                                                    <input type="text" class="form-control" name="phone" maxlength="20" value="{{ old('phone') }}" id="inputPhone">
                                                </div>
                                            </div>
                                            <div class="form-group mt-2">
                                                <label>Alternate Phone <small class="text-muted">(optional)</small></label>
                                                <input type="text" class="form-control" name="alternate_phone" maxlength="20" value="{{ old('alternate_phone') }}" placeholder="e.g. Spouse or other contact">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label>Email <small class="text-muted">(optional)</small></label>
                                        <input type="email" class="form-control" name="email" maxlength="100" value="{{ old('email') }}" id="inputEmail">
                                    </div>
                                </div>

                                <!-- Shown for anonymous -->
                                <div id="anonymousConfirm" class="d-none text-center py-4">
                                    <i class="fas fa-user-secret fa-3x text-muted mb-3"></i>
                                    <h6 class="text-primary">Submitting Anonymously</h6>
                                    <p class="text-muted">Your prayer request will be submitted without any personal information. Our prayer team will still pray over your request.</p>
                                </div>

                                <div class="d-flex justify-content-between mt-3">
                                    <button type="button" class="btn btn-outline-secondary btn-prev" data-prev="2"><i class="fas fa-arrow-left"></i> Back</button>
                                    <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Submit Prayer Request</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Prayer Wall Grid -->
        @if($prayers->count() > 0)
        <h3 class="mb-4 text-center">Community Prayer Requests</h3>
        <div class="row">
            @foreach($prayers as $prayer)
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card h-100 prayer-card {{ $prayer->is_urgent ? 'prayer-card-urgent' : '' }}">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h5 class="card-title mb-0">{{ $prayer->title }}</h5>
                            @if($prayer->category)
                                <span class="badge badge-info">{{ ucfirst($prayer->category) }}</span>
                            @endif
                        </div>
                        <p class="card-text text-muted">{{ \Illuminate\Support\Str::limit(strip_tags($prayer->description), 150) }}</p>
                        <div class="d-flex justify-content-between align-items-center mt-auto">
                            <small class="text-muted">
                                @if(!$prayer->is_anonymous && $prayer->submitted_name)
                                    {{ $prayer->submitted_name }}
                                @elseif(!$prayer->is_anonymous && $prayer->submitter)
                                    {{ $prayer->submitter->firstname }}
                                @else
                                    Anonymous
                                @endif
                                &middot; {{ $prayer->created_at->diffForHumans() }}
                            </small>
                            <button class="btn btn-sm btn-outline-danger btn-prayed" data-id="{{ $prayer->id }}">
                                <i class="fas fa-heart"></i> <span class="pray-count">{{ $prayer->prayer_count }}</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <div class="d-flex justify-content-center mt-3">
            {{ $prayers->links() }}
        </div>
        @else
        <div class="text-center py-5">
            <i class="fas fa-praying-hands fa-3x text-muted mb-3"></i>
            <p class="text-muted">No prayer requests on the wall yet. Be the first to submit one!</p>
        </div>
        @endif

        <!-- Praise Reports / Answered Prayers -->
        @if($praiseReports->count() > 0)
        <hr class="my-5">
        <h3 class="mb-4 text-center"><i class="fas fa-star text-warning"></i> Praise Reports & Answered Prayers</h3>
        <div class="row">
            @foreach($praiseReports as $praise)
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card h-100 prayer-card prayer-card-praise">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-star text-warning"></i> {{ $praise->title }}</h5>
                        <p class="card-text text-muted">{{ \Illuminate\Support\Str::limit(strip_tags($praise->description), 150) }}</p>
                        <small class="text-muted">{{ $praise->created_at->diffForHumans() }}</small>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
@endsection

@push('css')
<style>
    .step-indicator { text-align: center; flex: 0 0 auto; }
    .step-indicator small { display: block; margin-top: 4px; color: #adb5bd; font-size: .75rem; }
    .step-indicator.active small { color: #5e72e4; font-weight: 600; }
    .step-indicator.completed small { color: #2dce89; }
    .step-circle {
        display: inline-flex; align-items: center; justify-content: center;
        width: 36px; height: 36px; border-radius: 50%;
        background: #e9ecef; color: #6c757d; font-weight: 700; font-size: .9rem;
        transition: all .3s;
    }
    .step-indicator.active .step-circle { background: #5e72e4; color: #fff; }
    .step-indicator.completed .step-circle { background: #2dce89; color: #fff; }
    .step-line { flex: 1; height: 2px; background: #e9ecef; align-self: center; margin: 0 8px; margin-bottom: 18px; }
    .contact-option .card { transition: all .2s; border-color: #dee2e6 !important; }
    .contact-option .card:hover { border-color: #5e72e4 !important; background: #f8f9ff; }
    .contact-option input[type="radio"]:checked ~ div strong { color: #5e72e4; }
    .contact-option .card.selected { border-color: #5e72e4 !important; background: #f0f3ff; box-shadow: 0 0 0 2px rgba(94,114,228,.2); }
</style>
@endpush

@push('js')
<script>
$(document).ready(function () {
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

    // --- Multi-step form logic ---
    var currentStep = 1;

    function showStep(step) {
        $('.form-step').addClass('d-none');
        $('#step' + step).removeClass('d-none');
        $('.step-indicator').each(function () {
            var s = $(this).data('step');
            $(this).removeClass('active completed');
            if (s < step) $(this).addClass('completed');
            if (s === step) $(this).addClass('active');
        });
        currentStep = step;
    }

    // Next button
    $('.btn-next').click(function () {
        var next = $(this).data('next');

        // Validate step 1
        if (currentStep === 1) {
            var title = $('input[name="title"]').val().trim();
            var desc = $('textarea[name="description"]').val().trim();
            if (!title || !desc) {
                alert('Please fill in the prayer request title and description.');
                return;
            }
        }

        // Step 2 → 3: check selection
        if (currentStep === 2) {
            if (!$('input[name="contact_preference"]:checked').length) {
                alert('Please select how you would like us to respond.');
                return;
            }
            var pref = $('input[name="contact_preference"]:checked').val();
            if (pref === 'anonymous') {
                $('#contactFields').addClass('d-none');
                $('#anonymousConfirm').removeClass('d-none');
                $('#inputName').removeAttr('required');
                $('#inputPhone').removeAttr('required');
            } else {
                $('#contactFields').removeClass('d-none');
                $('#anonymousConfirm').addClass('d-none');
                $('#inputName').attr('required', 'required');
                $('#inputPhone').attr('required', 'required');
            }
        }

        showStep(next);
    });

    // Back button
    $('.btn-prev').click(function () {
        showStep($(this).data('prev'));
    });

    // Radio card click
    $('.contact-option .card').click(function () {
        $(this).find('input[type="radio"]').prop('checked', true);
        $('.contact-option .card').removeClass('selected');
        $(this).addClass('selected');
    });

    // Highlight pre-selected
    $('input[name="contact_preference"]:checked').closest('.card').addClass('selected');

    // Form submit validation
    $('#prayerForm').submit(function (e) {
        var pref = $('input[name="contact_preference"]:checked').val();
        if (pref !== 'anonymous') {
            var name = $('#inputName').val().trim();
            var phone = $('#inputPhone').val().trim();
            if (!name || !phone) {
                e.preventDefault();
                alert('Please provide your name and phone number.');
                return false;
            }
        }
    });

    // --- Prayer count ---
    $('.btn-prayed').click(function () {
        var btn = $(this);
        var id = btn.data('id');
        btn.attr('disabled', true);
        $.post('/prayer-wall/' + id + '/prayed')
            .done(function (d) {
                btn.find('.pray-count').text(d.count);
                btn.addClass('btn-danger text-white').removeClass('btn-outline-danger');
            })
            .fail(function (xhr) {
                if (xhr.status === 429) {
                    alert('Please wait a moment before praying again.');
                }
            })
            .always(function () { btn.removeAttr('disabled'); });
    });
});
</script>
@endpush
