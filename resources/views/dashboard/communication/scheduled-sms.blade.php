@extends('layouts.dashboard')

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h5 class="m-0 text-header"><i class='fas fa-clock'></i> Scheduled <b>SMS</b></h5>
                </div><!-- /.col -->
                <div class="col-sm-6 text-right">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('dashboard/home') }}">Home</a></li>
                        <li class="breadcrumb-item active">Schedule SMS</li>
                    </ol>
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
                <div class="col-xl-12 mb-5 mb-xl-0">
                    <div class="card shadow">
                        <div class="card-header">
                            <div class="row align-items-center">
                                <!--
                            <div class="col">
                                <h3 class="mb-0"><i class="fas fa-comments"></i> | Scheduled Messages</h3>
                            </div>-->
                                <div class="col text-right">
                                    <button class='btn btn-primary btn-sm newsms'><i class='fas fa-comments'></i> New
                                        SMS</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class='container mt-3'>
                        <div class='row'>
                            @if ($schedules->isEmpty())
                                <div class='col-sm-12'>
                                    <div class="alert alert-warning alert-dismissible fade show -5" role="alert">
                                        <strong><i class='fas fa-ban'></i> </strong> No Scheduled Messages yet.
                                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                </div>
                            @endif

                            @foreach ($schedules as $message)
                                <div class='col-sm-4 d-flex align-items-stretch'>
                                    <a href='{{ url('dashboard/communication/schedule/sms/' . $message->id) }}' class='shadow card p-2 mt-3 text-primary'
                                        style="border: 2px solid rgba(0,0,0,0.1);">
                                        <div class='row d-flex align-items-center'>
                                            <div class="col-auto">
                                                <div class="icon icon-shape text-white rounded-circle shadow d-flex justify-content-center align-items-center"
                                                    style='background-color: #ccc'>
                                                    <i class="far fa-envelope"></i>
                                                </div>
                                            </div>
                                            <div class='col small'>
                                                <span class='font-weight-bold'>sent to:
                                                    {{ $message->firstname == null
                                                        ? ($message->name == null
                                                            ? 'ALL MEMBERS'
                                                            : $message->name)
                                                        : $message->firstname . ' ' . $message->lastname . ' (' . $message->email . ')' }}</span><br>
                                                <small>Schedule:
                                                    {{ \Carbon\Carbon::parse($message->schedule)->format('d M, Y h:i A') }}</small>
                                            </div>
                                            <div class='col-sm-12 p-3'>
                                                {{ \Str::limit($message->message, $limit = 100, $end = '...') }}
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            @endforeach

                            <div class='col-sm-12 mt-2 mb-2'>
                                {{ $schedules->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>



    <!-- Modal -->
    <div class="modal fade" id="assetsModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="exampleModalLabel">Message Modal</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body pt-3 pb-0 mb-0 bg-secondary">
                    <form action='{{ url('dashboard/communication/sms/send') }}' method='post'>
                        @csrf
                        <div class="form-group">
                            <label>Phone numbers (seperate with a comma [,])</label>
                            <div class="input-group input-group-alternative">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                </div>
                                <input type='text' class="form-control form-control-alternative" name='numbers'
                                    placeholder="+254791162496" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Message</label>
                            <div class="input-group input-group-alternative">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-comments"></i></span>
                                </div>
                                <textarea name='message' class="form-control form-control-alternative" placeholder="Your text Message here"
                                    rows="5"></textarea>
                            </div>
                        </div>
                        <div class="form-group text-right">
                            <button type='submit' class="btn btn-primary">Send SMS</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>



    <div class="phonenumbers" style="background-color: rgba(0,0,0,.5)">
        <div class="message-div row">
            <div class='col-sm-12 bg-gradient-primary p-4 text-white' style='max-height: 10vh;'>
                <button class='btn text-white close-message'
                    style='background: transparent; sbox-shadow: none; position: absolute; top: 10px; right: 30px;'><i
                        class='fas fa-times'></i></button>
                New Message
            </div>
            <div class="col-sm-12 p-4">
                <form action="{{ url('dashboard/communication/sms/send') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label>Whom do you want to sent to?</label>
                        <div class='row'>
                            <div class='col-xs-6 pl-3 pr-3'>
                                <div class="custom-control custom-radio mb-3">
                                    <input name="choice" class="custom-control-input" id="individual" type="radio"
                                        value="0">
                                    <label class="custom-control-label" for="individual">Individual</label>
                                </div>
                            </div>
                            <div class='col-xs-6 pl-3 pr-3'>
                                <div class="custom-control custom-radio mb-3">
                                    <input name="choice" class="custom-control-input" id="groups" type="radio"
                                        value="1">
                                    <label class="custom-control-label" for="groups">Groups</label>
                                </div>
                            </div>
                            <div class='col-xs-6 pl-3 pr-3'>
                                <div class="custom-control custom-radio mb-3">
                                    <input name="choice" class="custom-control-input" id="all" type="radio"
                                        checked="checked" value="2">
                                    <label class="custom-control-label" for="all">All</label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class='col'>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text"><i class="fas fa-search"></i></div>
                                    </div>
                                    <input name="search" type="text" class="form-control" placeholder="Search">
                                </div>
                            </div>

                            <div class='col'>
                                <input name="time" type="text" class="form-control mydatepicker"
                                    placeholder="Schedule">
                            </div>
                        </div>
                    </div>

                    <div class='myscroll'>
                        <div class="results mt-2">
                            <p class='small text-muted'><i class="fas fa-spinner fa-pulse"></i> Please Wait</p>
                        </div>
                        <input type='hidden' name='limit' value='0'>
                        <p class='small font-weight-bold lazy'><i class='fas fa-spinner fa-pulse'></i> Loading...</p>
                    </div>
                    <div class='form-group' style="p-2">
                        <div class='input-group'>
                            <textarea class='form-control' name="message" rows="2" style="border: 2px solid #ddd; border-radius: 0px;"
                                placeholder="Enter message here"></textarea>
                            <div class='input-group-append' style="border: 2px solid #ddd; border-radius: 0px;">
                                <button class='input-group-text' style="border: 2px solid #ddd; border-radius: 0px;">
                                    <i class='fas fa-paper-plane'></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@push('js')
    <script>
        $(document).ready(function() {
            flatpickr(".mydatepicker", {
                enableTime: true,
                dateFormat: "Y-m-d H:i",
                //defaultDate: new Date(),
            });
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            var groups = 0;
            $('.phonenumbers input[name="choice"]').change(function() {
                if ($("#groups").prop("checked")) {
                    groups = 1;
                    defaultUsers();
                } else {
                    groups = 0;
                    defaultUsers();
                }
            });

            $('.close-message').click(function() {
                $('.phonenumbers').hide();
            });
            $('.newsms').click(function() {
                defaultUsers();
                $('.phonenumbers').show();
            });

            function defaultUsers() {
                var search = $('.phonenumbers input[name="search"]').val();
                $.ajax({
                    url: "{{ url('dashboard/communication/sms/phone_numbers')}}?groups=" + groups + "&search=" + search,
                    type: "GET",
                }).done(function(data) {
                    $('.phonenumbers .results').html("");
                    displayContacts(data);
                }).fail(function(data) {
                    $('.phonenumbers .results').html(
                        "<div class='text-danger'><i class='fas fa-exclamation-circle'></i> <strong>Error</strong> loading contacts</div>"
                        );
                });
            }

            var timer = null;
            $('.phonenumbers input[name="search"]').keyup(function() {
                if (timer != null)
                    clearTimeout(timer);
                timer = setTimeout(function() {
                    var search = $('.phonenumbers input[name="search"]').val();
                    $('.myscroll input[name="limit"]').val(0);
                    $('.phonenumbers .results').html("");
                    $('.lazy').html("<i class='fas fa-spinner fa-pulse'></i> Loading...");
                    $('.lazy').show();
                    allowscroll();
                    $.ajax({
                        url: "{{ url('dashboard/communication/sms/phone_numbers')}}/" + search + "?groups=" + groups,
                        method: "GET",
                    }).done(function(data) {
                        displayContacts(data);
                    }).fail(function() {
                        $('.phonenumbers .results').html(
                            "<div class='text-danger'><i class='fas fa-exclamation-circle'></i> <strong>Error</strong> loading contacts</div>"
                            );
                    });
                }, 1000);
            });

            $('.phonenumbers form').submit(function(e) {
                e.preventDefault();
                $('.phonenumbers form').unbind();
                var message = $.trim($('.phonenumbers textarea[name="message"]').val())
                if (message.length === 0) {
                    swal({
                        text: "You cannot send an empty message to members",
                        icon: "error",
                    });
                } else {
                    $('.phonenumbers form').submit();
                }
            });

            allowscroll();

            function allowscroll() {
                $('.myscroll').on('scroll', function(e) {
                    if ($(this).scrollTop() + $(this).innerHeight() + 1 >= $(this)[0].scrollHeight) {
                        loadmore();
                    }
                });
            }

            function loadmore() {
                $('.myscroll').unbind();
                $('.lazy').show();
                var search = $('.phonenumbers input[name="search"]').val();
                var limit = parseInt($('.myscroll input[name="limit"]').val()) + 10;
                $.ajax({
                    url: '{{ url("dashboard/communication/sms/phone_numbers")}}?search=' + search + "&limit=" + limit + "&groups=" + groups,
                    type: 'GET',
                }).done(function(data) {
                    displayContacts(data);
                    var objects = JSON.parse(data);
                    if (objects.length < 10) {
                        $('.lazy').html("No more contacts");
                    } else {
                        $('.myscroll input[name="limit"]').val(limit);
                        allowscroll();
                    }
                }).fail(function() {
                    $('.lazy').html(
                        "<span class='text-danger'><i class='fas fa-exclamation-triangle'></i> Something went <strong>wrong</strong></span>"
                        );
                });
            }

            function displayContacts(data) {
                var checked = "";
                if ($('#all').is(':checked')) {
                    checked = "checked";
                }
                var objects = JSON.parse(data);
                if (groups == 1) {
                    for (var i = 0; i < objects.length; i++) {
                        $('.phonenumbers .results').append("<div class='bg-white mt-2'>" +
                            "<div class='custom-control custom-control-alternative custom-checkbox mb-3'>" +
                            "<input class='custom-control-input' name='groups[]' id='" + objects[i].id +
                            "' value='" + objects[i].id + "' type='checkbox'>" +
                            "<label class='custom-control-label' for='" + objects[i].id + "'><strong>" +
                            objects[i].name + "</strong><br>" +
                            objects[i].members + " member(s)</label>" +
                            "</div></div>");
                    }
                    if (objects.length < 10) {
                        $('.lazy').html("No more groups");
                    }
                } else {
                    for (var i = 0; i < objects.length; i++) {
                        $('.phonenumbers .results').append("<div class='bg-white mt-2'>" +
                            "<div class='custom-control custom-control-alternative custom-checkbox mb-3'>" +
                            "<input class='custom-control-input' name='contacts[]' id='" + objects[i].email +
                            "' value='" + objects[i].id + "' type='checkbox' " + checked + ">" +
                            "<label class='custom-control-label' for='" + objects[i].email + "'><strong>" +
                            objects[i].firstname + " " + objects[i].lastname + "</strong><br>" +
                            objects[i].phone + "</label>" +
                            "</div></div>");
                    }
                    if (objects.length < 10) {
                        $('.lazy').html("No more contacts");
                    }
                }
            }

            $('.delete-sms').click(function () {
                var success = 0;
                var failed = 0;
                var count = 0;
                var totals = 0;
                $('.sms').find('input[type="checkbox"]:checked').each(function () {
                    count++;
                });
                $('.user-not').html("<p class='text-white'><i class='fas fa-spinner fa-pulse'></i> Deleting <strong>"+(count-success)+"</strong> of <strong>"+count+"</strong> SMS(s)...</p>");
                $('.user-not').show();
                if(count == 0){
                    $('.user-not').html("<p class='text-white'><i class='fas fa-exclamation-triangle'></i> No SMS(s) selected for deleting!</p>");
                    setTimeout(() => {
                        $('.user-not').hide();
                    }, 2000);
                }
                $('.sms').find('input[type="checkbox"]:checked').each(function () {
                    var i = $(this).val();
                    var myurl = "{{url('dashboard/communication/sms/remove')}}/"+i;
                    $.ajax({
                        url: myurl,
                        type: "GET",
                    }).done(function(data){
                        success++;
                        totals = success + failed;
                        if(count == totals){
                            $('.user-not').html("<p class='text-white'><i class='fas fa-check-circle'></i> <strong>"+success+"</strong> Deleted<br><i class='fas fa-exclamation-circle'></i> <strong>"+failed+"</strong> Failed</p>");
                            setTimeout(() => {
                                location.reload();
                                $('.user-not').hide();
                            }, 2000);
                        }
                    }).fail(function(){
                        failed++;
                        totals = success + failed;
                        if(count == totals){
                            $('.user-not').html("<p class='text-white'><i class='fas fa-check-circle'></i> <strong>"+success+"</strong> Deleted<br><i class='fas fa-exclamation-circle'></i> <strong>"+failed+"</strong> Failed</p>");
                            setTimeout(() => {
                                location.reload();
                                $('.user-not').hide();
                            }, 2000);
                        }
                    });
                });
            });
        });
    </script>
@endpush
