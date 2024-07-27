@extends('layouts.dashboard')

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h5 class="m-0 text-header"><i class='fas fa-users'></i> {{ ucfirst(Request::segment(3)) }}</h5>
                </div><!-- /.col -->
                <div class="col-sm-6 text-right">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('home') }}">Home</a></li>
                        <li class="breadcrumb-item"><a
                                href="{{ url('dashboard/people/' . ($people->user_group == 1 ? 'communities' : 'departments')) }}">{{ $people->user_group == 1 ? 'Communities' : 'Departments' }}</a>
                        </li>
                        <li class="breadcrumb-item active">{{ ucfirst(Request::segment(3)) }}</li>
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
                        <div class="card-header border-0">
                            <div class="row align-items-center">
                                <!--
                                        <div class="col">
                                            <h3 class="mb-0"><i class="fas fa-users"></i> | {{ ucfirst(Request::segment(2)) }}</h3>
                                        </div>-->
                                <div class="col text-right">
                                    <button class='btn btn-primary btn-sm showmembers'><i class='fas fa-user-plus'></i> Add
                                        Member(s)</button>
                                </div>
                            </div>
                        </div>

                        <div class='row mt-3 mb-3'>
                            <div class='col-sm-8'>
                                <div class=''>
                                    <div class='card-header border-0 text-center'>
                                        <h3 class='text-center'>
                                            {{ $people->name }}<br>{{ $people->user_group == 1 ? '(Community)' : '(Department)' }}
                                        </h3>
                                        <hr
                                            style='width: 40px; border: 1px solid #aaa; margin-top:1rem !important; margin-bottom: 1rem !important;'>
                                        <p class='small'>{!! $people->description !!}</p>
                                        <!--<button class='btn btn-primary'>More info</button>-->
                                    </div>
                                </div>
                            </div>

                            <div class='col-sm-4'>
                                <div class=''>
                                    <div class='card-header border-0 text-center'>
                                        <h3 class='text-center'>Leader</h3>
                                        <hr
                                            style='width: 40px; border: 1px solid #aaa; margin-top:1rem !important; margin-bottom: 1rem !important;'>
                                        <div class="user-panel d-flex justify-content-center">
                                            <div class='image'>
                                                <img class='img-circle' alt="Image placeholder"
                                                    src="{{ $people->image == '' ? asset('profile_images/default.jpg') : asset('profile_images/' . $people->image) }}">

                                                <span class="mb-0 text-sm  font-weight-bold small">{{ $people->firstname }}
                                                    {{ $people->lastname }} (Leader) <br></span>
                                            </div>
                                        </div>

                                        <span class='text-muted small'><i class='far fa-envelope'></i>
                                            {{ \Str::limit($people->email, 20, '...') }}<br>
                                            <i class='fas fa-phone'></i> {{ $people->phone }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class='row'>
                        @foreach ($members as $member)
                            <div class='col-sm-6 col-lg-4 mt-2'>
                                <div class='card shadow mt-3'>
                                    <div class='card-header'>
                                        <div class="user-panel d-flex justify-content-center">
                                            <span class="avatar avatar-sm rounded-circle">
                                                <img alt="Image placeholder" class='img-circle img-fluid'
                                                    src="{{ $member->name == '' ? asset('profile_images/default.jpg') : asset('profile_images/' . $member->name) }}">
                                            </span>
                                            <div class="media-body ml-3">
                                                <span class="mb-0 text-sm  font-weight-bold small">{{ $member->firstname }}
                                                    {{ $member->lastname }} (Member)</span>
                                            </div>
                                        </div>
                                        <div class='small p-1 mt-1'><i class='far fa-envelope'></i>
                                            {{ \Str::limit($member->email, 20, '...') }}</div>
                                        <div class='small p-1'><i class='fas fa-phone'></i> {{ $member->phone }}</div>
                                        <div class='text-right'>
                                            @if ($member->status == 0)
                                                <span class='badge badge-warning font-weight-bold'>Inactive</span>&nbsp;
                                                <a href='{{ url('dashboard/people/member/activate/' . $member->id) }}'
                                                    class='btn btn-success btn-sm' title='activate' data-placement='bottom'
                                                    data-toggle='tooltip'><i class='fas fa-sync-alt'></i></a>
                                            @else
                                                <span class='badge badge-success font-weight-bold'>Active</span>&nbsp;
                                                <a href='{{ url('dashboard/people/member/deactivate/' . $member->id) }}'
                                                    class='btn btn-outline-danger btn-sm' title='deactivate'
                                                    data-placement='bottom' data-toggle='tooltip'><i
                                                        class='fas fa-ban'></i></a>
                                            @endif
                                            <a href='{{ url('dashboard/people/member/remove/' . $member->id) }}'
                                                class='btn btn-danger btn-sm'><i class='fas fa-trash'></i></a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="col-sm-12 mt-3">
                        {{ $members->links() }}
                    </div>
                </div>
            </div>
        </div>
    </section>


    <div class="membersusers" style="background-color: rgba(0,0,0,.5)">
        <div class="message-div">
            <div class='col-sm-12 bg-gradient-primary p-4 text-white' style='max-height: 10vh;'>
                <button class='btn text-white close'
                    style='background: transparent; sbox-shadow: none; position: absolute; top: 10px; right: 30px; font-size: 1.3em;'><i
                        class='fas fa-times'></i></button>
                <i class='fas fa-user-plus'></i> Choose Members
            </div>
            <div class="col-sm-12 p-4">

                <div class="form-group">
                    <label>Whom do you want to be in this group?</label>

                    <div class="input-group">
                        <div class="input-group-prepend">
                            <div class="input-group-text"><i class="fas fa-search"></i></div>
                        </div>
                        <input name="search" type="text" class="form-control" placeholder="Search">
                    </div>
                </div>
                <form action="{{ url('dashboard/people/members/add') }}" method="POST">
                    @csrf
                    <input type='hidden' name='group_id' value='{{ $people->id }}'>
                    <div class='scroll'>
                        <div class="results mt-2">
                            <p class='small text-muted'><i class="fas fa-spinner fa-pulse"></i> Please Wait</p>
                        </div>
                        <input type='hidden' name='limit' value='0'>
                        <p class='small font-weight-bold lazy'><i class='fas fa-spinner fa-pulse'></i> Loading...
                        </p>
                    </div>
                    <div class='form-group text-center p-2'>
                        <button class='btn btn-outline-primary'>Add Members</button>
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

            $('.membersusers .close').click(function() {
                $('.membersusers').hide();
            });

            var clicked = false;
            $('.showmembers').click(function() {
                if (!clicked) {
                    defaultUsers();
                }
                clicked = true;
                $('.membersusers').show();
            });

            function defaultUsers() {
                $.ajax({
                    url: "{{ url('dashboard/people/users') }}",
                    type: "GET",
                }).done(function(data) {
                    $('.membersusers .results').html("");
                    displayContacts(data);
                }).fail(function(data) {
                    $('.membersusers .results').html(
                        "<div class='text-danger'><i class='fas fa-exclamation-circle'></i> <strong>Error</strong> loading contacts</div>"
                        );
                });
            }

            var timer = null;
            $('.membersusers input[name="search"]').keyup(function() {
                if (timer != null)
                    clearTimeout(timer);
                timer = setTimeout(function() {
                    var search = $('.membersusers input[name="search"]').val();
                    $('.scroll input[name="limit"]').val(0);
                    $('.membersusers .results').html("");
                    $('.lazy').html("<i class='fas fa-spinner fa-pulse'></i> Loading...");
                    $('.lazy').show();
                    allowscroll();
                    $.ajax({
                        url: "{{ url('dashboard/people/users') }}?search=" + search,
                        method: "GET",
                    }).done(function(data) {
                        displayContacts(data);
                    }).fail(function() {
                        $('.membersusers .results').html(
                            "<div class='text-danger'><i class='fas fa-exclamation-circle'></i> <strong>Error</strong> loading contacts</div>"
                            );
                    });
                }, 1000);
            });

            allowscroll();

            function allowscroll() {
                $('.membersusers .scroll').on('scroll', function(e) {
                    if ($(this).scrollTop() + $(this).innerHeight() + 1 >= $(this)[0].scrollHeight) {
                        loadmore();
                    }
                });
            }

            function loadmore() {
                $('.membersusers .scroll').unbind();
                $('.membersusers .lazy').show();
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
                    $('.membersusers .results').append("<div class='bg-white mt-2'>" +
                        "<div class='custom-control custom-control-alternative custom-checkbox mb-3'>" +
                        "<input class='custom-control-input' name='members[]' id='" + objects[i].email +
                        "' value='" + objects[i].id + "' type='checkbox'>" +
                        "<label class='custom-control-label' for='" + objects[i].email +
                        "'><strong class='name'>" + objects[i].firstname + " " + objects[i].lastname +
                        "</strong><br><span class='small'>" +
                        objects[i].email + "</span></label>" +
                        "</div></div>");
                }
                if (objects.length < 10) {
                    $('.lazy').html("No more contacts");
                }
            }

        });
    </script>
@endpush
