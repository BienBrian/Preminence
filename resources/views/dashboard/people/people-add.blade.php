@extends('layouts.dashboard')

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h5 class="m-0 text-header"><i class='fas fa-users'></i> {!! Request::segment(3) == 'new'
                        ? '<b>New</b> Users, Communities and Departments'
                        : (Request::segment(4) == 1
                            ? 'Edit Community'
                            : 'Edit Department') !!}</h5>
                </div><!-- /.col -->
                <div class="col-sm-6 text-right">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('home') }}">Home</a></li>
                        <li class="breadcrumb-item active">People</li>
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
                                <!--<div class="col-sm-12">
                                            <h3 class="mb-0"><i class="fas fa-users"></i> |
                                                {{ Request::segment(3) == 'new' ? 'New Users, Communities and Departments' : (Request::segment(3) == 1 ? 'Edit Community' : 'Edit Department') }}
                                            </h3>
                                        </div>-->
                            </div>
                        </div>

                        <div class="card-body">
                            <div class='row'>
                                <div class='col'>
                                    <input type="hidden" name='active'
                                        value='{{ Request::segment(3) == 'new' ? '0' : Request::segment(4) }}'>
                                    <div class="custom-control custom-radio mb-3">
                                        <input name="choice" class="custom-control-input" id="user" checked
                                            type="radio" value="user">
                                        <label class="custom-control-label" for="user">New Member</label>
                                    </div>
                                </div>
                                <div class='col'>
                                    <div class="custom-control custom-radio mb-3">
                                        <input name="choice" class="custom-control-input" id="community" type="radio"
                                            value="community">
                                        <label class="custom-control-label" for="community">Community</label>
                                    </div>
                                </div>
                                <div class='col'>
                                    <div class="custom-control custom-radio mb-3">
                                        <input name="choice" class="custom-control-input" id="department" type="radio"
                                            value="department">
                                        <label class="custom-control-label" for="department">Department</label>
                                    </div>
                                </div>
                            </div>
                            <div class='user alert bg-white border' style='border-width: 2px; display:none;'>
                                <form action='{{ url('dashboard/users/add') }}' class='row' method="POST">
                                    @csrf
                                    <div class='col-sm-6'>
                                        <div class="form-group">
                                            <label>First Name</label>
                                            <input type='text' class="form-control" name="fname"
                                                placeholder="First Name" autocomplete="off">
                                        </div>
                                    </div>
                                    <div class='col-sm-6'>
                                        <div class="form-group">
                                            <label>Last Name</label>
                                            <input type='text' class="form-control" name="lname"
                                                placeholder="Last Name" autocomplete="off">
                                        </div>
                                    </div>
                                    <div class='col-sm-6'>
                                        <div class="form-group">
                                            <label>Email Address</label>
                                            <input type='email' class="form-control" name="email"
                                                placeholder="Email Address" autocomplete="off">
                                        </div>
                                    </div>
                                    <div class='col-sm-6'>
                                        <div class="form-group">
                                            <label>Phone Number</label>
                                            <div class='input-group'>
                                                <div class='input-group-prepend'>
                                                    <div class='input-group-text'>
                                                        +254
                                                    </div>
                                                </div>
                                                <input type='text' class="form-control" name="phone"
                                                    placeholder="Phone Number"
                                                    autocomplete="off">
                                            </div>
                                        </div>
                                    </div>
                                    <div class='col-sm-12'>
                                        <div class="text-right">
                                            <button class='btn btn-primary'>Save User</button>
                                        </div>
                                    </div>
                                </form>
                            </div>

                            <div class='alert community bg-white' style='display:none;'>
                                <form method="POST" action="{{ url('dashboard/people/add') }}" enctype="multipart/form-data"
                                    class="row com-form">
                                    @csrf
                                    <div class="col-sm-4">
                                        <img src="{{ $people == null ? asset('website/default.jpg') : ($people->banner == '' ? asset('website/default.jpg') : asset('peoples/' . $people->banner)) }}"
                                            class="img-fluid">
                                        <div class='text-center'>
                                            <a href="#" class="btn btn-outline-primary mt-2 comphoto">Upload
                                                Image</a>
                                        </div>
                                    </div>
                                    <div class='form-group col-sm-8'>
                                        <label class='n-com'>Adding a new category</label>
                                        <hr>
                                        <div class=' alert alert-primary showusers'>
                                            <i class='fas fa-user-plus'></i> Leader: <span
                                                class='leader'>{{ $people == null ? 'Click to select' : $people->firstname . ' ' . $people->lastname }}</span>
                                            </td>
                                        </div>
                                        <input type='hidden' name='people'
                                            value='{{ $people == null ? '1' : $people->user_group }}'>
                                        <input type="hidden" value="{{ $people == null ? '0' : $people->leader }}"
                                            name="user_id">
                                        <input type="text" name="id"
                                            value="{{ $people == null ? '0' : $people->id }}" class="d-none">

                                        <label class='small mt-3'>Name</label>
                                        <input type="file" name="photo" class="d-none">
                                        <input name="name" type="text" placeholder="Name" class="form-control"
                                            style="border: 2px solid #ddd;"
                                            value="{{ $people == null ? '' : $people->name }}">
                                        <label class='mt-3 small'>Description</label>
                                        <textarea name="description" placeholder="Community Description" rows="5" class="form-control summernote"
                                            style="border: 2px solid #ddd;">{{ $people == null ? '' : $people->description }}</textarea>
                                        <div class='text-right'>
                                            <button
                                                class="btn btn-primary mt-2">{{ $people == null ? 'Save' : 'Edit' }}</button>
                                        </div>
                                    </div>
                                </form>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="peopleusers" style="background-color: rgba(0,0,0,.5)">
        <div class="message-div">
            <div class='col-sm-12 bg-gradient-primary p-4 text-white'>
                <button class='btn text-white close'
                    style='background: transparent; box-shadow: none; position: absolute; top: 10px; right: 30px; font-size: 1.3em;'><i
                        class='fas fa-times'></i></button>
                <i class='fas fa-user-plus'></i> Choose Leader
            </div>
            <div class="col-sm-12 p-4">

                <div class="form-group">
                    <label>Whom do you want to head the group?</label>

                    <div class="input-group">
                        <div class="input-group-prepend">
                            <div class="input-group-text"><i class="fas fa-search"></i></div>
                        </div>
                        <input name="search" type="text" class="form-control" placeholder="Search">
                    </div>
                </div>

                <div class='scroll' style="overflow:scroll; overflow-x: hidden; height: 70vh;">
                    <div class="results mt-2">
                        <p class='small text-muted'><i class="fas fa-spinner fa-pulse"></i> Please Wait</p>
                    </div>
                    <input type='hidden' name='limit' value='0'>
                    <p class='small font-weight-bold lazy'><i class='fas fa-spinner fa-pulse'></i> Loading...</p>
                </div>

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


              //communities photo
              $('.comphoto').click(function(e){
                  e.preventDefault();
                  $(".com-form input[type='file']").click();
              });
              $('.com-form input[type="file"]').change(function(){
                    var input = $(this);
                    var reader = new FileReader();

                    reader.onload = function(e) {
                        $('.com-form img').attr('src', e.target.result);
                    }

                    reader.readAsDataURL(input[0].files[0]);
              });
            var showing = parseInt($('input[name="active"]').val());
            if (showing == 1) {
                $('.user').hide();
                $('input[name="active"]').closest('.row').hide();
                $('.n-com').html("Edit Community");
                $('.community').show();
            } else if (showing == 2) {
                $('.user').hide();
                $('input[name="active"]').closest('.row').hide();
                $('.n-com').html("Edit Department");
                $('.community').show();
            } else {
                $('.community').hide();
                $('.user').show();
            }

            $('.peopleusers .close').click(function() {
                $('.peopleusers').hide();
            });
            $('#user, #community, #department').click(function() {
                $('.user, .community, .department').hide();
                if ($(this).val() === 'user') {
                    $('.user').show();
                } else if ($(this).val() === 'community') {
                    $('.community input[name="people"]').val(1);
                    $('.n-com').html("Add New Community");
                    $('.community').show();
                } else {
                    $('.community input[name="people"]').val(2);
                    $('.n-com').html("Add New Department");
                    $('.community').show();
                }
                $('.' + $("input[name='choice']:checked").val()).show();
            });

            var clicked = false;
            $('.showusers').click(function() {
                if (!clicked) {
                    defaultUsers();
                }
                clicked = true;
                $('.peopleusers').show();
            });

            function defaultUsers() {
                $.ajax({
                    url: "{{ url('dashboard/people/users') }}",
                    type: "GET",
                }).done(function(data) {
                    $('.peopleusers .results').html("");
                    displayContacts(data);
                }).fail(function(data) {
                    $('.peopleusers .results').html(
                        "<div class='text-danger'><i class='fas fa-exclamation-circle'></i> <strong>Error</strong> loading contacts</div>"
                        );
                });
            }

            var timer = null;
            $('.peopleusers input[name="search"]').keyup(function() {
                if (timer != null)
                    clearTimeout(timer);
                timer = setTimeout(function() {
                    var search = $('.peopleusers input[name="search"]').val();
                    $('.scroll input[name="limit"]').val(0);
                    $('.peopleusers .results').html("");
                    $('.lazy').html("<i class='fas fa-spinner fa-pulse'></i> Loading...");
                    $('.lazy').show();
                    allowscroll();
                    $.ajax({
                        url: "{{url('dashboard/people/users')}}?search=" + search,
                        method: "GET",
                    }).done(function(data) {
                        displayContacts(data);
                    }).fail(function() {
                        $('.peopleusers .results').html(
                            "<div class='text-danger'><i class='fas fa-exclamation-circle'></i> <strong>Error</strong> loading contacts</div>"
                            );
                    });
                }, 1000);
            });

            allowscroll();

            function allowscroll() {
                $('.peopleusers .scroll').on('scroll', function(e) {
                    if ($(this).scrollTop() + $(this).innerHeight() + 1 >= $(this)[0].scrollHeight) {
                        loadmore();
                    }
                });
            }

            function loadmore() {
                $('.peopleusers .scroll').unbind();
                $('.peopleusers .lazy').show();
                var search = $('input[name="search"]').val();
                var limit = parseInt($('.scroll input[name="limit"]').val()) + 10;
                $.ajax({
                    url: '{{ url("dashboard/people/users") }}?search=' + search + "&limit=" + limit,
                    type: 'GET',
                }).done(function(data) {
                    displayContacts(data);
                    var objects = JSON.parse(data);
                    if (objects.length < 10) {
                        $('.lazy').html("No more contacts");
                    } else {
                        $('.scroll input[name="limit"]').val(limit);
                        allowscroll();
                    }
                }).fail(function() {
                    $('.lazy').html(
                        "<span class='text-danger'><i class='fas fa-exclamation-triangle'></i> Something went <strong>wrong</strong></span>"
                        );
                });
            }

            function displayContacts(data) {
                var objects = JSON.parse(data);
                for (var i = 0; i < objects.length; i++) {
                    $('.peopleusers .results').append("<div class='bg-white mt-2'>" +
                        "<div class='custom-control custom-control-alternative custom-radio mb-3'>" +
                        "<input class='custom-control-input' name='contacts' id='" + objects[i].email +
                        "' value='" + objects[i].id + "' type='radio'>" +
                        "<label class='custom-control-label' for='" + objects[i].email +
                        "'><strong class='name'>" + objects[i].firstname + " " + objects[i].lastname +
                        "</strong><br><span class='small'>" +
                        objects[i].email + "</span></label>" +
                        "</div></div>");
                }
                $("input[name='contacts']:radio").change(function() {
                    $('.leader').text($(this).closest('div').find('.name').text());
                    $('form input[name="user_id"]').val($(this).val());
                });
                if (objects.length < 10) {
                    $('.lazy').html("No more contacts");
                }
            }

            $('.com-form').submit(function(e) {
                e.preventDefault();
                if (parseInt($('.com-form input[name="user_id"]').val()) == 0) {
                    swal({
                        text: "Choose a Leader before proceeding!",
                        icon: "error",
                    });
                } else {
                    $('.com-form').unbind();
                    $('.com-form').submit();
                }
            });

        });
    </script>
@endpush
