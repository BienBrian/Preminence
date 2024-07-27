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
                                <h3 class="mb-0"><i class="fas fa-users"></i> | {{ ucfirst(Request::segment(3)) }}</h3>
                            </div>-->
                                <div class="col text-right">
                                    <a href="{{ url('dashboard/people/new') }}" class='btn btn-primary btn-sm'><i
                                            class='fas fa-arrow-right'></i> Add New</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class='row mt-3 mb-3'>
                        @foreach ($people as $group)
                            <div class='col-sm-6 d-flex align-items-stretch'>
                                <div class="card people mb-3" style='border-width: 2px;'>
                                    <img class="card-img-top"
                                        src="{{ $group->banner == '' ? asset('website/default.jpg') : asset('peoples/' . $group->banner) }}"
                                        alt="Image for describing people">
                                    <div class="card-body">
                                        <h3 class="card-title">{{ Str::words($group->name, 8) }}<br>
                                            <span style='font-size: .8em;'><span style='font-weight: 400;'><i
                                                        class="far fa-user"></i> Headed By:</span> {{ $group->firstname }}
                                                {{ $group->lastname }}</span>
                                        </h3>
                                        <p class="card-text small">{{ Str::words(strip_tags($group->description), 30) }}</p>
                                        <a href="{{ url('dashboard/people/edit/' . $group->user_group . '/' . $group->id) }}"
                                            class="btn btn-default btn-sm" style='padding: .5em .6em;'><i
                                                class='fas fa-edit'></i></a>
                                        <a href="{{ url('dashboard/people/members/' . $group->id) }}"
                                            class="btn btn-primary btn-sm" style='padding: .5em .6em;'><i
                                                class='fas fa-users'></i></a>
                                        <a href="#" class="btn btn-warning btn-sm"
                                            style='padding: .4em .6em; border-width: 2px;'><i class='fas fa-trash'></i></a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="col-sm-12 mt-3">
                        {{ $people->links() }}
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
