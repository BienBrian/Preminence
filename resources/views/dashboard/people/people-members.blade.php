@extends('layouts.dashboard')

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h5 class="m-0 text-header"><i class='fas fa-users'></i> {{ $people->name }}</h5>
                </div>
                <div class="col-sm-6 text-right">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('dashboard/home') }}">Home</a></li>
                        <li class="breadcrumb-item"><a
                                href="{{ url('dashboard/people/' . ($people->user_group == 1 ? 'communities' : 'departments')) }}">{{ $people->user_group == 1 ? 'Communities' : 'Departments' }}</a>
                        </li>
                        <li class="breadcrumb-item active">{{ $people->name }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <!-- Group Info Card -->
            <div class="card shadow mb-3">
                <div class="row no-gutters">
                    <div class="col-md-4">
                        <img src="{{ $people->banner == '' ? asset('website/default.jpg') : asset('peoples/' . $people->banner) }}"
                            class="img-fluid" style="height: 100%; object-fit: cover; width: 100%;">
                    </div>
                    <div class="col-md-8">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h3 class="mb-1">{{ $people->name }}</h3>
                                    <span class="badge badge-{{ $people->user_group == 1 ? 'primary' : 'info' }} mb-2">
                                        {{ $people->user_group == 1 ? 'Community' : 'Department' }}
                                    </span>
                                </div>
                                <a href="{{ url('dashboard/people/edit/' . $people->user_group . '/' . $people->id) }}" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                            </div>
                            <div class="row mt-3">
                                <div class="col-sm-6">
                                    <div class="d-flex align-items-center mb-2">
                                        <img class='rounded-circle mr-2'
                                            src="{{ $people->image == '' ? asset('profile_images/default.jpg') : asset('profile_images/' . $people->image) }}"
                                            style="width: 40px; height: 40px; object-fit: cover;">
                                        <div>
                                            <span class="font-weight-bold">{{ $people->firstname }} {{ $people->lastname }}</span>
                                            <br><small class="text-muted">Leader</small>
                                        </div>
                                    </div>
                                    <p class="small text-muted mb-0"><i class='far fa-envelope'></i> {{ $people->email }}</p>
                                    <p class="small text-muted"><i class='fas fa-phone'></i> {{ $people->phone }}</p>
                                </div>
                                <div class="col-sm-6">
                                    <p class="small text-muted">{!! \Str::words(strip_tags($people->description), 40, '...') !!}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Members DataTable -->
            <div class="card shadow">
                <div class="card-header border-0">
                    <div class="row align-items-center">
                        <div class="col-sm-6">
                            <form class='search-form'>
                                <input type='search' class='form-control' placeholder="Search members..." name='search' />
                            </form>
                        </div>
                        <div class="col-sm-6 text-right mt-2 mt-sm-0">
                            <button class='btn btn-primary btn-sm showmembers'><i class='fas fa-user-plus'></i> Add Member(s)</button>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table align-items-center table-flush members-table">
                        <thead class="thead-light">
                            <tr>
                                <th scope="col">Name</th>
                                <th scope="col">Email</th>
                                <th scope="col">Phone</th>
                                <th scope="col">Status</th>
                                <th scope="col" class="text-right notexport">Actions</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>


    <div class="membersusers" style="background-color: rgba(0,0,0,.5)">
        <div class="message-div">
            <div class='col-sm-12 bg-gradient-primary p-4 text-white' style='max-height: 10vh;'>
                <button class='btn text-white close'
                    style='background: transparent; box-shadow: none; position: absolute; top: 10px; right: 30px; font-size: 1.3em;'><i
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
                        <input name="search_members" type="text" class="form-control" placeholder="Search">
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
                        <p class='small font-weight-bold lazy'><i class='fas fa-spinner fa-pulse'></i> Loading...</p>
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
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
            });

            // Members DataTable
            var table = $('.members-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ url('dashboard/people/members/datatable/' . $people->id) }}",
                    data: function(d) {
                        d.search = $('input[name=search]').val();
                    }
                },
                buttons: [
                    {
                        extend: 'excel',
                        text: '<i class="fas fa-file-excel"></i> Excel',
                        className: 'btn border btn-sm',
                        exportOptions: { columns: ':not(.notexport)' }
                    },
                    {
                        extend: 'pdf',
                        text: '<i class="fas fa-file-pdf"></i> PDF',
                        className: 'btn border btn-sm',
                        exportOptions: { columns: ':not(.notexport)' }
                    }
                ],
                dom: "<'top'B>rt<'bottom'lip><'clear'>",
                "lengthMenu": [[15, 50, 100], [15, 50, 100]],
                columns: [
                    { data: 'member_name', name: 'member_name', orderable: false, searchable: false },
                    { data: 'email', name: 'email' },
                    { data: 'phone', name: 'phone' },
                    { data: 'status_badge', name: 'status_badge', orderable: false, searchable: false },
                    { data: 'action', name: 'action', orderable: false, searchable: false, className: 'notexport' }
                ]
            });

            $('.search-form').submit(function(e) { e.preventDefault(); });
            var timer = null;
            $('input[name=search]').keyup(function() {
                clearTimeout(timer);
                timer = setTimeout(function() { table.draw(); }, 500);
            });

            // Add members slide panel
            $('.membersusers .close').click(function() { $('.membersusers').hide(); });

            var clicked = false;
            $('.showmembers').click(function() {
                if (!clicked) { defaultUsers(); }
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
                }).fail(function() {
                    $('.membersusers .results').html("<div class='text-danger'><i class='fas fa-exclamation-circle'></i> <strong>Error</strong> loading contacts</div>");
                });
            }

            var searchTimer = null;
            $('.membersusers input[name="search_members"]').keyup(function() {
                if (searchTimer != null) clearTimeout(searchTimer);
                searchTimer = setTimeout(function() {
                    var search = $('.membersusers input[name="search_members"]').val();
                    $('.scroll input[name="limit"]').val(0);
                    $('.membersusers .results').html("");
                    $('.lazy').html("<i class='fas fa-spinner fa-pulse'></i> Loading...").show();
                    allowscroll();
                    $.ajax({
                        url: "{{ url('dashboard/people/users') }}?search=" + search,
                        method: "GET",
                    }).done(function(data) { displayContacts(data); }).fail(function() {
                        $('.membersusers .results').html("<div class='text-danger'><i class='fas fa-exclamation-circle'></i> <strong>Error</strong> loading contacts</div>");
                    });
                }, 1000);
            });

            allowscroll();

            function allowscroll() {
                $('.membersusers .scroll').on('scroll', function() {
                    if ($(this).scrollTop() + $(this).innerHeight() + 1 >= $(this)[0].scrollHeight) {
                        loadmore();
                    }
                });
            }

            function loadmore() {
                $('.membersusers .scroll').unbind();
                $('.membersusers .lazy').show();
                var search = $('input[name="search_members"]').val();
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
                    $('.lazy').html("<span class='text-danger'><i class='fas fa-exclamation-triangle'></i> Something went <strong>wrong</strong></span>");
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
                        "</strong><br><span class='small'>" + objects[i].email + "</span></label>" +
                        "</div></div>");
                }
                if (objects.length < 10) { $('.lazy').html("No more contacts"); }
            }
        });
    </script>
@endpush
