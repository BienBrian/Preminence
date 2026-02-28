@extends('layouts.dashboard')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6 p-2">
                    <h5 class="m-0 text-header"><i class='fas fa-cog'></i> <b>General</b> Settings</h5>
                </div>
                <div class="col-sm-6 d-none d-sm-block p-2">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('dashboard/home') }}">Home</a></li>
                        <li class="breadcrumb-item active">General Settings</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <form id='general-settings-form'>
                @csrf

                <!-- Organization Details -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-church mr-1"></i> Organization Details</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class='form-group col-sm-6 col-lg-4'>
                                <label>Organization Name</label>
                                <input name='name' class='form-control' placeholder='e.g. Happy Church Ruiru'
                                    value="{{ $settings->name ?? '' }}" autocomplete="off">
                            </div>
                            <div class='form-group col-sm-6 col-lg-4'>
                                <label>Slogan / Tag Line</label>
                                <input name='slogan' class='form-control' placeholder='Slogan/Tag line'
                                    value="{{ $settings->slogan ?? '' }}" autocomplete="off">
                            </div>
                            <div class='form-group col-sm-6 col-lg-4'>
                                <label>Theme</label>
                                <select name='theme' class='form-control'>
                                    <option value='blue.css' {{ ($settings->theme ?? '') == 'blue.css' ? 'selected' : '' }}>Blue Theme</option>
                                    <option value='red.css' {{ ($settings->theme ?? '') == 'red.css' ? 'selected' : '' }}>Red Theme</option>
                                    <option value='black.css' {{ ($settings->theme ?? '') == 'black.css' ? 'selected' : '' }}>Black Theme</option>
                                    <option value='dark.css' {{ ($settings->theme ?? '') == 'dark.css' ? 'selected' : '' }}>Dark Blue Theme</option>
                                    <option value='green.css' {{ ($settings->theme ?? '') == 'green.css' ? 'selected' : '' }}>Green Theme</option>
                                    <option value='light.css' {{ ($settings->theme ?? '') == 'light.css' ? 'selected' : '' }}>Light Blue Theme</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Location Details -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-map-marker-alt mr-1"></i> Location Details</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class='form-group col-sm-6 col-lg-4'>
                                <label>Country</label>
                                <select name='country_code' class='form-control' id='country-select'>
                                    <option value="">-- Select Country --</option>
                                </select>
                                <input type="hidden" name="country_name" id="country-name-input" value="{{ $settings->country_name ?? '' }}">
                                <input type="hidden" name="phone_code" id="phone-code-input" value="{{ $settings->phone_code ?? '' }}">
                            </div>
                            <div class='form-group col-sm-6 col-lg-4'>
                                <label>Phone Code</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">+</span>
                                    </div>
                                    <input type="text" class="form-control" id="phone-code-display" value="{{ $settings->phone_code ?? '' }}" readonly style="background:#e9ecef;">
                                </div>
                                <small class="text-muted">Auto-set from country selection</small>
                            </div>
                            <div class='form-group col-sm-6 col-lg-4'>
                                <label>County / State / Province</label>
                                <input name='county' class='form-control' placeholder='e.g. Kiambu'
                                    value="{{ $settings->county ?? '' }}" autocomplete="off">
                            </div>
                            <div class='form-group col-sm-6 col-lg-4'>
                                <label>City / Town</label>
                                <input name='city' class='form-control' placeholder='e.g. Ruiru'
                                    value="{{ $settings->city ?? '' }}" autocomplete="off">
                            </div>
                            <div class='form-group col-sm-6 col-lg-4'>
                                <label>Specific Location</label>
                                <input name='specific_location' class='form-control' placeholder='e.g. Kimbo, Near Equity Bank'
                                    value="{{ $settings->specific_location ?? '' }}" autocomplete="off">
                            </div>
                            <div class='form-group col-sm-6 col-lg-4'>
                                <label>Google Map Address / Embed</label>
                                <input name='address' class='form-control' placeholder='Google Map Address'
                                    value="{{ $settings->address ?? '' }}" autocomplete="off">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Social Media Links -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-share-alt mr-1"></i> Social Media Links</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class='form-group col-sm-6 col-lg-4'>
                                <label><i class="fab fa-facebook text-primary mr-1"></i> Facebook</label>
                                <input name='facebook' class='form-control' placeholder='Facebook URL'
                                    value="{{ $settings->facebook ?? '' }}" autocomplete="off">
                            </div>
                            <div class='form-group col-sm-6 col-lg-4'>
                                <label><i class="fab fa-twitter text-info mr-1"></i> Twitter</label>
                                <input name='twitter' class='form-control' placeholder='Twitter URL'
                                    value="{{ $settings->twitter ?? '' }}" autocomplete="off">
                            </div>
                            <div class='form-group col-sm-6 col-lg-4'>
                                <label><i class="fab fa-instagram text-danger mr-1"></i> Instagram</label>
                                <input name='instagram' class='form-control' placeholder='Instagram URL'
                                    value="{{ $settings->instagram ?? '' }}" autocomplete="off">
                            </div>
                            <div class='form-group col-sm-6 col-lg-4'>
                                <label><i class="fab fa-youtube text-danger mr-1"></i> YouTube</label>
                                <input name='youtube' class='form-control' placeholder='YouTube URL'
                                    value="{{ $settings->youtube ?? '' }}" autocomplete="off">
                            </div>
                            <div class='form-group col-sm-6 col-lg-4'>
                                <label><i class="fab fa-whatsapp text-success mr-1"></i> WhatsApp</label>
                                <input name='whatsapp' class='form-control' placeholder='WhatsApp Number'
                                    value="{{ $settings->whatsapp ?? '' }}" autocomplete="off">
                            </div>
                            <div class='form-group col-sm-6 col-lg-4'>
                                <label><i class="fab fa-linkedin text-primary mr-1"></i> LinkedIn</label>
                                <input name='linkedin' class='form-control' placeholder='LinkedIn URL'
                                    value="{{ $settings->linkedin ?? '' }}" autocomplete="off">
                            </div>
                            <div class='form-group col-sm-6 col-lg-4'>
                                <label><i class="fab fa-pinterest text-danger mr-1"></i> Pinterest</label>
                                <input name='pinterest' class='form-control' placeholder='Pinterest URL'
                                    value="{{ $settings->pinterest ?? '' }}" autocomplete="off">
                            </div>
                            <div class='form-group col-sm-6 col-lg-4'>
                                <label><i class="fab fa-google text-warning mr-1"></i> Google</label>
                                <input name='google' class='form-control' placeholder='Google URL'
                                    value="{{ $settings->google ?? '' }}" autocomplete="off">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- About & Contact -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-info-circle mr-1"></i> About & Contact</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="form-group col-sm-6">
                                <label>About Us</label>
                                <textarea name="aboutus" class="form-control summernote" rows="4" placeholder="About Us">{{ $settings->aboutus ?? '' }}</textarea>
                            </div>
                            <div class="form-group col-sm-6">
                                <label>Contact Us</label>
                                <textarea name="contactus" class="form-control summernote" rows="4" placeholder="Contact Us">{{ $settings->contactus ?? '' }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Save Button -->
                <div class="card mb-4">
                    <div class="card-body text-right">
                        <button type="submit" class='btn btn-primary btn-sm btnSave'><i class='fas fa-paper-plane'></i> Save Settings</button>
                    </div>
                </div>
            </form>
        </div>
    </section>
@endsection

@push('js')
<script>
$(document).ready(function() {
    // Country data with phone codes
    var countries = [
        { code: 'KE', name: 'Kenya', phone: '254' },
        { code: 'UG', name: 'Uganda', phone: '256' },
        { code: 'TZ', name: 'Tanzania', phone: '255' },
        { code: 'RW', name: 'Rwanda', phone: '250' },
        { code: 'BI', name: 'Burundi', phone: '257' },
        { code: 'ET', name: 'Ethiopia', phone: '251' },
        { code: 'SS', name: 'South Sudan', phone: '211' },
        { code: 'SO', name: 'Somalia', phone: '252' },
        { code: 'CD', name: 'DR Congo', phone: '243' },
        { code: 'NG', name: 'Nigeria', phone: '234' },
        { code: 'GH', name: 'Ghana', phone: '233' },
        { code: 'ZA', name: 'South Africa', phone: '27' },
        { code: 'ZM', name: 'Zambia', phone: '260' },
        { code: 'ZW', name: 'Zimbabwe', phone: '263' },
        { code: 'MW', name: 'Malawi', phone: '265' },
        { code: 'MZ', name: 'Mozambique', phone: '258' },
        { code: 'BW', name: 'Botswana', phone: '267' },
        { code: 'CM', name: 'Cameroon', phone: '237' },
        { code: 'SN', name: 'Senegal', phone: '221' },
        { code: 'CI', name: "Cote d'Ivoire", phone: '225' },
        { code: 'US', name: 'United States', phone: '1' },
        { code: 'GB', name: 'United Kingdom', phone: '44' },
        { code: 'CA', name: 'Canada', phone: '1' },
        { code: 'AU', name: 'Australia', phone: '61' },
        { code: 'IN', name: 'India', phone: '91' },
    ];

    // Populate country dropdown
    var $select = $('#country-select');
    var currentCode = '{{ $settings->country_code ?? '' }}';
    $.each(countries, function(i, c) {
        $select.append('<option value="' + c.code + '" data-phone="' + c.phone + '" data-name="' + c.name + '"' +
            (c.code === currentCode ? ' selected' : '') + '>' + c.name + ' (+' + c.phone + ')</option>');
    });

    // On country change, auto-fill phone code
    $('#country-select').change(function() {
        var $option = $(this).find(':selected');
        var phone = $option.data('phone') || '';
        var name = $option.data('name') || '';
        $('#phone-code-display').val(phone);
        $('#phone-code-input').val(phone);
        $('#country-name-input').val(name);
    });

    // Form submit via AJAX
    $('#general-settings-form').submit(function(e) {
        e.preventDefault();
        var btn = $(this).find('.btnSave');
        btn.attr('disabled', 'disabled').html("<i class='fas fa-spinner fa-pulse'></i> Saving...");
        $.ajax({
            url: '{{ url("dashboard/settings/general/add") }}',
            type: 'POST',
            data: $(this).serialize()
        }).done(function(data) {
            toastr.success(data.success);
            btn.html("<i class='fas fa-paper-plane'></i> Save Settings").removeAttr('disabled');
        }).fail(function(response) {
            var data = response.responseJSON;
            if (data && data.errors) {
                $.each(data.errors, function(key, msgs) {
                    $.each(msgs, function(i, msg) { toastr.error(msg); });
                });
            } else if (data && data.error) {
                toastr.error(data.error);
            } else {
                toastr.error("Something went wrong!");
            }
            btn.html("<i class='fas fa-paper-plane'></i> Save Settings").removeAttr('disabled');
        });
    });
});
</script>
@endpush
