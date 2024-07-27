@extends('layouts.dashboard')

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h5 class="m-0 text-header"><i class='fas fa-envelope'></i> Emails</h5>
                </div><!-- /.col -->
                <div class="col-sm-6 text-right">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('dashboard/home') }}">Home</a></li>
                        <li class="breadcrumb-item active">Emails</li>
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
                                        <h3 class="mb-0"><i class="fas fa-envelope"></i> | Emails</h3>
                                    </div>-->
                                <div class="col text-right">
                                    <button class='btn btn-danger delete-emails btn-sm shadow'><span
                                            class="d-block d-lg-none"><i class="fas fa-trash"></i></span><span
                                            class="d-none d-lg-block"><i class="fas fa-trash"></i> Delete</span></button>
                                    <button class='btn btn-primary newemail btn-sm shadow'><span
                                            class="d-block d-lg-none"><i class="fas fa-envelope"></i></span><span
                                            class="d-none d-lg-block"><i class="fas fa-envelope"></i> New
                                            Email</span></button>
                                </div>
                            </div>
                        </div>
                        <div class='card-body emails'>
                            <div class='container'>
                                <div class='row'>
                                    @if ($emails->isEmpty())
                                        <div class='col-sm-12'>
                                            <div class="alert alert-warning alert-dismissible fade show -5" role="alert">
                                                <strong><i class='fas fa-ban'></i> </strong> No emails yet.
                                                <button type="button" class="close" data-dismiss="alert"
                                                    aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                        </div>
                                    @endif

                                    @foreach ($emails as $email)
                                        <div class='col-sm-12'>
                                            <a href="{{ url('dashboard/communication/emails/view/' . $email->id) }}"
                                                style="color: rgba(0,0,0,.8);">
                                                <div class='row border-bottom align-items-center mb-1 pb-1'>
                                                    <div class='col mycheck' style='max-width: 20px;'>
                                                        <input name='emails[]' id='{{ $email->id }}'
                                                            value='{{ $email->id }}' type='checkbox'>
                                                    </div>
                                                    <div class='col' style='max-width: 70px;'>
                                                        <div class="user-panel d-flex">
                                                            <div class="image">
                                                                <img alt="Image placeholder"
                                                                    src="{{ asset('profile_images/default.jpg') }}"
                                                                    class='img-circle'>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class='col' style='max-width:200px'>
                                                        <span class='text-muted'>{{ $email->recipients }}
                                                            Member(s)</span>
                                                    </div>
                                                    <div class='col'>
                                                        {{ \Str::limit(strip_tags(html_entity_decode($email->message)), 60, '...') }}
                                                    </div>
                                                    <div class='col text-right' style="max-width: 100px;">
                                                        <span
                                                            class='font-weight-bold small'>{{ \Carbon\Carbon::parse($email->sent)->diffForHumans() }}</span>
                                                    </div>
                                                </div>
                                            </a><!--
                                                        <div class="p-4 alert shadow bg-secondary" style="border: 2px solid rgba(0,0,0,0.1);">
                                                            <div class="media align-items-center">
                                                                <span class="avatar avatar-md rounded-circle shadow">
                                                                    <img alt="Image placeholder" src="{{ asset('profile_images/default.jpg') }}">
                                                                </span>

                                                                <div class="media-body ml-4">
                                                                    <span class="mb-2 font-weight-bold">
                                                                        <span style='text-muted font-weight-bold;' style='font-size: 1.3em;'>{{ $email->subject }}</span>
                                                                        <span class='small'>
                                                                            <strong class='text-muted'>, Sent to: {{ $email->recipients }} Member(s)</strong>
                                                                        </span><br>
                                                                        <span class='text-muted'>Sent: {{ \Carbon\Carbon::parse($email->sent)->diffForHumans() }}</span>
                                                                    </span>
                                                                </div>
                                                            </div>

                                                            <a href="{{ url('email/' . $email->id) }}" class='text-muted small' style='position: absolute; right: 4px; top: 5px; margin: 10px;'>Read <i class='fas fa-arrow-right'></i></a>
                                                            <p class='mt-3'><strong>{{ $email->subject }}</strong><br>
                                                            {!! html_entity_decode(\Str::words($email->message, 100, '...')) !!}</p>
                                                        </div>-->
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class='col-sm-12 mt-2 mb-2'>
                        {{ $emails->links() }}
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
                    <h4 class="modal-title" id="exampleModalLabel">Send Email</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body pt-3 pb-0 mb-0 bg-secondary">
                    <form action="{{ url('sendemail') }}" method="POST" class="emails-form">
                        @csrf
                        <div class="form-group">
                            <div class="input-group input-group-alternative">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-comment"></i></span>
                                </div>
                                <input class="form-control" name="subject" placeholder="Enter Subject" type="text">
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="input-group input-group-alternative">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                </div>
                                <input class="form-control" name="message" placeholder="Subject" required>
                            </div>
                            <div class="input-group input-group-alternative">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                </div>
                                <textarea class="form-control" name="message" placeholder="Your message here" rows="5"></textarea>
                            </div>
                        </div>
                        <div class="form-group text-right">
                            <button class="btn btn-primary">Send To All</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="emailusers" style="background-color: rgba(0,0,0,.5)">
        <div class="message-div row">
            <div class='col-sm-12 bg-gradient-primary p-4 text-white' style='max-height: 10vh;'>
                <button class='btn text-white close-message'
                    style='background: transparent; sbox-shadow: none; position: absolute; top: 10px; right: 30px;'><i
                        class='fas fa-times'></i></button>
                New Email
            </div>
            <div class="col-sm-12 p-4">
                <form action="{{ url('dashboard/communication/emails/send') }}" method="POST">
                    @csrf
                    <div class="contacts">
                        <div class="form-group">
                            <label>Whom do you want to sent to?</label>
                            <div class='row'>
                                <div class='col-xs-6 pl-3 pr-3'>
                                    <div class="custom-control custom-radio mb-3">
                                        <input name="choice" class="custom-control-input" id="individual"
                                            type="radio" checked="checked" value="0">
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
                                            value="2">
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
                            </div>
                        </div>

                        <div class='myscroll'>
                            <div class="results mt-2">
                                <p class='small text-muted'><i class="fas fa-spinner fa-pulse"></i> Please Wait
                                </p>
                            </div>
                            <input type='hidden' name='limit' value='0'>
                            <p class='small font-weight-bold lazy'><i class='fas fa-spinner fa-pulse'></i>
                                Loading...</p>
                        </div>
                    </div>
                    <div class='text-center pb-2 '>
                        <a href='#' class='btn btn-outline-primary btn-sm write-message'><strong><i
                                    class='fas fa-arrow-right'></i> Write Message</strong></a>
                    </div>
                    <div class='message'>
                        <div class='p-1'>
                            <input name="subject" class="form-control" placeholder="Subject"
                                style="border: 2px solid #ddd; border-radius: 0px;" autocomplete="off" />
                        </div>
                        <div class='p-1'>
                            <div class='form-group'>
                                <textarea class='form-control summernote' name="message" rows="10"
                                    style="border: 2px solid #ddd; border-radius: 0px;" placeholder="Enter message here"></textarea>
                            </div>
                        </div>
                        <div class='p-1 text-center'>
                            <button class='btn btn-primary'>
                                <i class='fas fa-paper-plane'></i> Send Email
                            </button>
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
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            var groups = 0;
            var show = 0;
            $('.write-message').click(function(e) {
                e.preventDefault();
                var button = $(this);
                if (show == 0) {
                    $('.contacts').hide();
                    $('.message').show();
                    button.html("<i class='fas fa-arrow-left'></i> Show Contacts");
                    show = 1;
                } else {
                    $('.contacts').show();
                    $('.message').hide();
                    button.html("Write Message <i class='fas fa-arrow-right'></i>");
                    show = 0;
                }
            });
            $('.emailusers input[name="choice"]').change(function() {
                if ($("#groups").prop("checked")) {
                    groups = 1;
                    defaultUsers();
                } else {
                    groups = 0;
                    defaultUsers();
                }
            });

            $('.close-message').click(function() {
                $('.emailusers').hide();
            });
            $('.newemail').click(function() {
                defaultUsers();
                $('.emailusers').show();
            });

            function defaultUsers() {
                var search = $('.emailusers input[name="search"]').val();
                $.ajax({
                    url: "{{ url('dashboard/communication/emails/users') }}?groups=" + groups +
                        "&search=" + search,
                    type: "GET",
                }).done(function(data) {
                    $('.emailusers .results').html("");
                    displayContacts(data);
                }).fail(function(data) {
                    $('.emailusers .results').html(
                        "<div class='text-danger'><i class='fas fa-exclamation-circle'></i> <strong>Error</strong> loading contacts</div>"
                    );
                });
            }

            var timer = null;
            $('.emailusers input[name="search"]').keyup(function() {
                if (timer != null)
                    clearTimeout(timer);
                timer = setTimeout(function() {
                    var search = $('.emailusers input[name="search"]').val();
                    $('.myscroll input[name="limit"]').val(0);
                    $('.emailusers .results').html("");
                    $('.lazy').html("<i class='fas fa-spinner fa-pulse'></i> Loading...");
                    $('.lazy').show();
                    allowscroll();
                    $.ajax({
                        url: "{{ url('dashboard/communication/emails/users') }}?search=" +
                            search + "&groups=" + groups,
                        method: "GET",
                    }).done(function(data) {
                        displayContacts(data);
                    }).fail(function() {
                        $('.emailusers .results').html(
                            "<div class='text-danger'><i class='fas fa-exclamation-circle'></i> <strong>Error</strong> loading contacts</div>"
                        );
                    });
                }, 1000);
            });

            $('.emailusers form').submit(function(e) {
                e.preventDefault();
                $('.emailusers form').unbind();
                var message = $.trim($('.emailusers textarea[name="message"]').val())
                if (message.length === 0) {
                    swal({
                        text: "You cannot send an empty message to members",
                        icon: "error",
                    });
                } else {
                    $('.emailusers form').submit();
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
                var search = $('.emailusers input[name="search"]').val();
                var limit = parseInt($('.myscroll input[name="limit"]').val()) + 10;
                $.ajax({
                    url: '{{ url('dashboard/communication/emails/users') }}?search=' + search + "&limit=" +
                        limit + "&groups=" + groups,
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
                        $('.emailusers .results').append("<div class='bg-white mt-2'>" +
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
                        $('.emailusers .results').append("<div class='bg-white mt-2'>" +
                            "<div class='custom-control custom-control-alternative custom-checkbox mb-3'>" +
                            "<input class='custom-control-input' name='contacts[]' id='" + objects[i].email +
                            "' value='" + objects[i].id + "' type='checkbox' " + checked + ">" +
                            "<label class='custom-control-label' for='" + objects[i].email + "'><strong>" +
                            objects[i].firstname + " " + objects[i].lastname + "</strong><br>" +
                            objects[i].email + "</label>" +
                            "</div></div>");
                    }
                    if (objects.length < 10) {
                        $('.lazy').html("No more contacts");
                    }
                }
            }


            $('.delete-emails').click(function() {
                var success = 0;
                var failed = 0;
                var count = 0;
                var totals = 0;
                $('.emails').find('input[type="checkbox"]:checked').each(function() {
                    count++;
                });
                $('.user-not').html(
                    "<p class='text-white'><i class='fas fa-spinner fa-pulse'></i> Deleting <strong>" +
                    (count - success) + "</strong> of <strong>" + count + "</strong> Email(s)...</p>");
                $('.user-not').show();
                if (count == 0) {
                    $('.user-not').html(
                        "<p class='text-white'><i class='fas fa-exclamation-triangle'></i> No Email(s) selected for deleting!</p>"
                        );
                    setTimeout(() => {
                        $('.user-not').hide();
                    }, 2000);
                }
                $('.emails').find('input[type="checkbox"]:checked').each(function() {
                    var i = $(this).val();
                    var myurl = "{{ url('dashboard/communication/emails/delete/') }}/" + i;
                    $.ajax({
                        url: myurl,
                        type: "GET",
                    }).done(function(data) {
                        success++;
                        totals = success + failed;
                        if (count == totals) {
                            $('.user-not').html(
                                "<p class='text-white'><i class='fas fa-check-circle'></i> <strong>" +
                                success +
                                "</strong> Deleted<br><i class='fas fa-exclamation-circle'></i> <strong>" +
                                failed + "</strong> Failed</p>");
                            setTimeout(() => {
                                location.reload();
                                $('.user-not').hide();
                            }, 2000);
                        }
                    }).fail(function() {
                        failed++;
                        totals = success + failed;
                        if (count == totals) {
                            $('.user-not').html(
                                "<p class='text-white'><i class='fas fa-check-circle'></i> <strong>" +
                                success +
                                "</strong> Deleted<br><i class='fas fa-exclamation-circle'></i> <strong>" +
                                failed + "</strong> Failed</p>");
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
