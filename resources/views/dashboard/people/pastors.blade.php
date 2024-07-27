@extends('layouts.dashboard')

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h5 class="m-0 text-header"><i class='fas fa-users'></i> Pastors</h5>
                </div><!-- /.col -->
                <div class="col-sm-6 text-right">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('home') }}">Home</a></li>
                        <li class="breadcrumb-item active">Pastors</li>
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
                                <h3 class="mb-0"><i class="fas fa-users"></i> | Pastors</h3>
                            </div>-->
                                <div class="col text-right">
                                    <button class="btn btn-primary btn-sm showpastors"><i class='fas fa-plus'></i> Add
                                        Pastor</button>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <!-- Projects table -->
                            <table class="table align-items-center table-flush">
                                <thead class="thead-light">
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">Name</th>
                                        <th scope="col">Email</th>
                                        <th scope="col">Position</th>
                                        <th scope="col" class="text-right">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if ($pastors->isEmpty())
                                        <tr>
                                            <td colspan="5" class="text-center font-weight-bold"><i
                                                    class="fas fa-ban"></i> No pastors yet</td>
                                        </tr>
                                    @endif
                                    <?php $count = 1; ?>
                                    @foreach ($pastors as $pastor)
                                        <tr>
                                            <td>{{ $count }}</td>
                                            <td>{{ $pastor->firstname }} {{ $pastor->lastname }}</td>
                                            <td>{{ $pastor->email }}</td>
                                            <td>
                                                @if ($pastor->status == 0)
                                                    Pastor
                                                @else
                                                    Senior Pastor
                                                @endif
                                            </td>
                                            <td class="text-right">
                                                @if ($pastor->status == 0)
                                                    <a class="btn btn-sm btn-success"
                                                        href="{{ url('dashboard/pastor/senior/' . $pastor->id) }}"
                                                        data-toggle="tooltip" title="Make Senior Pastor">
                                                        <i class="fas fa-sync"></i>
                                                    </a>
                                                @endif
                                                <a class="btn btn-sm btn-danger"
                                                    href="{{ url('dashboard/remove/pastor/' . $pastor->id) }}">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        <?php $count++; ?>
                                    @endforeach
                                </tbody>
                                </thead>
                            </table>
                        </div>
                    </div>

                    <div class='col-sm-12 mt-2 mb-2'>
                        {{ $pastors->links() }}
                    </div>
                </div>
            </div>
        </div>
    </section>


    <div class="pastorsusers" style="background-color: rgba(0,0,0,.5)">
        <div class="message-div">
            <div class='col-sm-12 bg-gradient-primary p-4 text-white'>
                <button class='btn text-white close'
                    style='background: transparent; box-shadow: none; position: absolute; top: 10px; right: 30px; font-size: 1.3em;'><i
                        class='fas fa-times'></i></button>
                <i class='fas fa-user-plus'></i> Choose Pastors
            </div>
            <div class="col-sm-12 p-4">

                <div class="form-group">
                    <label>Whom do you want to make Pastor?</label>

                    <div class="input-group">
                        <div class="input-group-prepend">
                            <div class="input-group-text"><i class="fas fa-search"></i></div>
                        </div>
                        <input name="search" type="text" class="form-control" placeholder="Search">
                    </div>
                </div>
                <form action="{{ url('dashboard/addpastor') }}" method="POST">
                    @csrf
                    <div class='scroll'>
                        <div class="results mt-2">
                            <p class='small text-muted'><i class="fas fa-spinner fa-pulse"></i> Please Wait</p>
                        </div>
                        <input type='hidden' name='limit' value='0'>
                        <p class='small font-weight-bold lazy'><i class='fas fa-spinner fa-pulse'></i> Loading...</p>
                    </div>
                    <div class='form-group text-center mt-1'>
                        <button class='btn btn-outline-primary'>Add Pastor(s)</button>
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

            $('.pastorsusers .close').click(function() {
                $('.pastorsusers').hide();
            });

            var clicked = false;
            $('.showpastors').click(function() {
                if (!clicked) {
                    defaultUsers();
                }
                clicked = true;
                $('.pastorsusers').show();
            });

            function defaultUsers() {
                $.ajax({
                    url: "{{ url('dashboard/people/users') }}",
                    type: "GET",
                }).done(function(data) {
                    $('.pastorsusers .results').html("");
                    displayContacts(data);
                }).fail(function(data) {
                    $('.pastorsusers .results').html(
                        "<div class='text-danger'><i class='fas fa-exclamation-circle'></i> <strong>Error</strong> loading contacts</div>"
                        );
                });
            }

            var timer = null;
            $('.pastorsusers input[name="search"]').keyup(function() {
                if (timer != null)
                    clearTimeout(timer);
                timer = setTimeout(function() {
                    var search = $('.pastorsusers input[name="search"]').val();
                    $('.scroll input[name="limit"]').val(0);
                    $('.pastorsusers .results').html("");
                    $('.lazy').html("<i class='fas fa-spinner fa-pulse'></i> Loading...");
                    $('.lazy').show();
                    allowscroll();
                    $.ajax({
                        url: "/people/users?search=" + search,
                        method: "GET",
                    }).done(function(data) {
                        displayContacts(data);
                    }).fail(function() {
                        $('.pastorsusers .results').html(
                            "<div class='text-danger'><i class='fas fa-exclamation-circle'></i> <strong>Error</strong> loading contacts</div>"
                            );
                    });
                }, 1000);
            });

            allowscroll();

            function allowscroll() {
                $('.pastorsusers .scroll').on('scroll', function(e) {
                    if ($(this).scrollTop() + $(this).innerHeight() + 1 >= $(this)[0].scrollHeight) {
                        loadmore();
                    }
                });
            }

            function loadmore() {
                $('.pastorsusers .scroll').unbind();
                $('.pastorsusers .lazy').show();
                var search = $('input[name="search"]').val();
                var limit = parseInt($('.scroll input[name="limit"]').val()) + 10;
                $.ajax({
                    url: '/people/users?search=' + search + "&limit=" + limit,
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
                    $('.pastorsusers .results').append("<div class='bg-white mt-2'>" +
                        "<div class='custom-control custom-control-alternative custom-checkbox mb-3'>" +
                        "<input class='custom-control-input' name='contacts[]' id='" + objects[i].email +
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
