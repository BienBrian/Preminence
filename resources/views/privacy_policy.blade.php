@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center background d-flex align-items-center">
            <div class="col-md-10 mt-5 ">
                <div class="card border bg-white border-0 mb-4">
                    <div class="card-body p-4">
                        <h4 class='big bold'>Privacy Policy</h4>
                        <hr>
                        <p><b>Use of your Information and Privacy</b><br>
                            When you use our website, we have the right to access and store the information you submit for a
                            variety of purposes, including</p>

                        <ol>
                            <li>To track your access for professional purposes.</li>
                            <li>Enhancing the content on our website.</li>
                            <li>Offering services with value-added</li>
                            <li>Engaging in activities related to a legal, governmental, or regulatory necessity for the
                                {{ config('app.name', 'Laravel') }}, in relation to judicial processes, or with regard to the prevention of crime
                                or fraud.
                                Only personal information about a user will be used or disclosed by us. According to how our
                                terms and conditions specify. Personal information about a user includes items like name,
                                contact information, and bank accounts. Some portions of our website may need the user to
                                provide specific information in order to register for features like newsletters.</li>
                        </ol>

                        <p>Your information may be disclosed to one of our affiliated companies or a third party that has a
                            service agreement with {{ config('app.name', 'Laravel') }}. In the event that this information cannot be linked back to
                            the user by a third party, we may also use your personal information to create statistical
                            profiles. By accepting our terms and conditions, you agree that online conversations and
                            transactions are not always secure or error-free.</p>

                        <p><b>Intellectual Property Rights</b><br>
                            Our website offers all of the content on it, including but not limited to logos, images, games,
                            films, music, pictures, and artwork, is protected by copyright. Any material on the website that
                            you desire to utilize must be done so legally. You may only use these items for personal,
                            non-commercial usage by viewing and downloading them.</b>

                        <p>Without our written consent, you are not allowed to sell, edit, reproduce, display, or distribute
                            the materials on our website for non-private, public, or commercial purpose. Your permission to
                            view our material will be canceled and you will be compelled to destroy any copies you may have
                            made if it is determined that you have violated the terms and conditions.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('js')
    <script>
        $(document).ready(function() {
            $('#gender').select2({
                placeholder: 'Select Gender',
                allowClear: true,
                ajax: {
                    url: '{{ url('get/genders') }}',
                    dataType: 'json',
                    delay: 250,
                    processResults: function(data) {
                        return {
                            results: $.map(data, function(item) {
                                return {
                                    text: item.name,
                                    id: item.id
                                }
                            })
                        };
                    },
                    cache: true
                }
            });
        });
    </script>
@endpush
