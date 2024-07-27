@extends('layouts.dashboard')

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h5 class="m-0 text-header"><i class='fas fa-envelope'></i> <b>Send</b> Email</h5>
                </div><!-- /.col -->
                <div class="col-sm-6 text-right">
                    @can('Add Queue Statuses')
                        <button class="btn btn-primary btn-sm btn-launch-modal" data-toggle="modal" data-target="#email-form"><i
                                class='fas fa-plus'></i> Add Status
                        </button>
                    @else
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ url('home') }}">Home</a></li>
                            <li class="breadcrumb-item active">Send Email</li>
                        </ol>
                    @endcan
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <!-- Small boxes (Stat box) -->
            <div class="row">
                <div class="col-md-12 mb-3">

                    <div class='card'>
                        <!--
                        <div class='card-header'>
                            <h5><b>Send</b> New Mail</h5>
                        </div>-->
                        <div class='card-body'>
                            <form method='POST' action="{{ url('dashboard/emails/send') }}" id='email-form'>
                               @csrf
                               <input type='hidden' name='id' value='0'>
                                <div class='form-group'>
                                    <label>Recipients</label>
                                    <select name='recipients[]' class='form-control' id='recipients' multiple="multiple"></select>
                                </div>
                                <div class='form-group'>
                                    <label>Subject</label>
                                    <input type='text' name='subject' class='form-control' placeholder="Subject"/>
                                </div>
                                <div class='form-group'>
                                    <label>Body</label>
                                    <textarea class='form-control' name='body' id='body' placeholder="Email Body"></textarea>
                                </div>
                                <div class='alert feedback border d-none'>
                                    <i class='fas fa-spinner fa-pulse'></i> Saving... Please wait
                                </div>
                                <div class='form-group text-right'>
                                    <button class='btn btn-primary btnSave'><i class='fas fa-paper-plane'></i> Send</button>
                                </div>
                            </form>
                        </div>
                    </div>

                </div>

                <!-- ./col -->
            </div>
            <!-- /.row -->
        </div><!-- /.container-fluid -->
    </section>
    <!-- /.content -->
@endsection
@push('js')
    <script>
        $(document).ready(function() {
            const timeZone = Intl.DateTimeFormat().resolvedOptions().timeZone;

            $('#recipients').select2({
                width: '100%',
                placeholder: 'Select Recipients',
                //dropdownParent: $('#shareModal'),
                allowClear: true,
                ajax: {
                    url: '{{url("dashboard/search/users")}}',
                    dataType: 'json',
                    delay: 250,
                    processResults: function (data) {
                        return {
                            results: $.map(data, function (item) {
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
            $('#body').summernote({
                placeholder: 'Enter your message here',
                tabsize: 1,
                height: 200,
                toolbar: [
                    //['style', ['style']],
                    ['font', ['bold', 'underline', 'clear']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    //['table', ['table']],
                    //['insert', ['link', 'picture', 'video']],
                    ['view', ['fullscreen', 'codeview', 'help']]
                ]
            });
            getAuctionTime();

            $('#email-form').submit(function(e) {
                e.preventDefault();
                var btn = $('#email-form .btnSave');
                btn.attr('disabled', 'disabled');
                $('#email-form .feedback').removeClass('d-none');
                $('#email-form .feedback').removeClass('alert-danger');
                $('#email-form .feedback').removeClass('alert-success');
                $('#email-form .feedback').html(
                    "<i class='fas fa-spinner fa-pulse'></i> Sending... Please wait");
                var formData = $('#email-form').serialize();
                $.ajax({
                    url: '{{ url('dashboard/emails/send') }}',
                    type: 'POST',
                    data: formData
                }).done(function(data) {
                    $('#email-form .feedback').addClass('alert-success');
                    $('#email-form .feedback').html("<i class='fas fa-exclamation-circle'></i> " +
                        data.success);
                    setTimeout(() => {
                        $('#email-form .feedback').addClass('d-none');
                    }, 3000);
                    location.href = "{{ url('dashboard/emails') }}";
                    //btn.removeAttr('disabled');
                }).fail(function(response) {
                    let data = response.responseJSON;
                    $('#email-form .feedback').addClass('alert-danger');
                    $('#email-form .feedback').html("");
                    if (data.errors) {
                        if (data.errors.recipients) {
                            $('#email-form .feedback').append(
                                "<i class='fas fa-exclamation-circle'></i> " + data.errors
                                .recipients + "<br>");
                        }
                        if (data.errors.id) {
                            $('#email-form .feedback').append(
                                "<i class='fas fa-exclamation-circle'></i> " + data.errors
                                .id + "<br>");
                        }
                        if (data.errors.body) {
                            $('#email-form .feedback').append(
                                "<i class='fas fa-exclamation-circle'></i> " + data.errors
                                .body + "<br>");
                        }
                        if (data.errors.subject) {
                            $('#email-form .feedback').append(
                                "<i class='fas fa-exclamation-circle'></i> " + data.errors
                                .subject + "<br>");
                        }
                    } else if (data.error) {
                        $('#email-form .feedback').html(
                            "<i class='fas fa-exclamation-circle'></i> " + data.error);
                    } else {
                        $('#email-form .feedback').html(
                            "<i class='fas fa-exclamation-circle'></i> <b>Whoops</b> Something went wrong with the server!"
                        );
                    }
                    setTimeout(() => {
                        $('#email-form .feedback').addClass('d-none');
                    }, 3000);
                    btn.removeAttr('disabled');
                });
            });

            $(document).on('click', '.table .btn-edit', function() {
                $('#email-form .modal-title span').text("Edit ");
                var row = $(this).closest('tr');
                var id = row.find('.id').text();
                var name = row.find('.name').text();
                var place = row.find('.place').text();
                var place_id = row.find('.place_id').text();
                var status = row.find('.status').text();

                $('#email-form input[name=id]').val(id);
                $('#email-form input[name=name]').val(name);

                var data = {
                    id: place_id,
                    text: place
                };
                var newOption = new Option(data.text, data.id, false, false);
                $('#place').append(newOption).trigger('change');
                $('#email-form select[name=status]').val(status);
            });

            function getAuctionTime() {
                $.ajax({
                    url: "{{ url('dashboard/auction_time') }}",
                    type: "GET",
                    /*data: {
                        "year": year,
                        "sacco": sacco
                    }*/
                }).done(function(data) {
                    $('.shares').text(data.shares);
                    if (data.auction_time != null) {
                        var date = new Date(Date.parse("{{ \Carbon\Carbon::now()->format('Y-m-d') }} " +
                            data.auction_time.from_time)).toLocaleString('en', {
                            timeZone: timeZone
                        });
                        var closeDate = new Date(Date.parse(
                            "{{ \Carbon\Carbon::now()->format('Y-m-d') }} " +
                            data.auction_time.to_time)).toLocaleString('en', {
                            timeZone: timeZone
                        });
                        var countDownDate = new Date(date).getTime();
                        var closeTimeDate = new Date(closeDate).getTime();
                        var myNow = new Date().getTime();
                        if (countDownDate <= myNow && closeTimeDate >= myNow) {
                            $('.time-card').addClass('d-none');
                            $('.auction-div').removeClass('d-none');
                        } else {
                            $('.time-card').removeClass('d-none');
                            $('.auction-div').addClass('d-none');
                            if (closeTimeDate < myNow) {
                                //alert(data.auction_time.auction_date);
                                if (data.auction_time.week_day == null && data.auction_time.auction_date ==
                                    null) {
                                    countDownDate += (60 * 60 * 24 * 1000);
                                }
                            }
                        }

                        var x = setInterval(function() {

                            // Get today's date and time
                            var now = new Date().getTime();

                            // Find the distance between now and the count down date
                            var distance = countDownDate - now;


                            // Time calculations for days, hours, minutes and seconds
                            var days = Math.floor(distance / (1000 * 60 * 60 * 24));
                            var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 *
                                60));
                            var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                            var seconds = Math.floor((distance % (1000 * 60)) / 1000);

                            // Display the result in the element with id="demo"
                            /*document.getElementById("demo").innerHTML = days + "d " + hours + "h " +
                                minutes + "m " + seconds + "s ";*/
                            $('.days').text((days).toString().padStart(2, "0") + "d");
                            $('.hours').text((hours).toString().padStart(2, "0") + "h");
                            $('.minutes').text((minutes).toString().padStart(2, "0") + "m");
                            $('.seconds').text((seconds).toString().padStart(2, "0") + "s");

                            // If the count down is finished, write some text
                            if (distance < 0) {
                                clearInterval(x);
                                $('.days').text("--d");
                                $('.hours').text("--h");
                                $('.minutes').text("--m");
                                $('.seconds').text("--s");
                            }
                        }, 1000);
                    } /*else {
                        alert("No Auction Time Set!!!");
                    }*/

                })/*.fail(function(data) {
                    alert("fail");
                })*/;
            }
        });
    </script>
@endpush
