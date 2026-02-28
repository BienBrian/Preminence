@extends('layouts.dashboard')

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h5 class="m-0 text-header"><i class='fas fa-users'></i> {{ ucfirst(Request::segment(3)) }}</h5>
                </div>
                <div class="col-sm-6 text-right">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('dashboard/home') }}">Home</a></li>
                        <li class="breadcrumb-item active">{{ ucfirst(Request::segment(3)) }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <!-- Stats row -->
            <div class="row mb-3">
                <div class="col-6 col-md-3">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3>{{ $totalGroups }}</h3>
                            <p>Total Groups</p>
                        </div>
                        <div class="icon"><i class="fas fa-layer-group"></i></div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3>{{ $totalMembers }}</h3>
                            <p>Total Members</p>
                        </div>
                        <div class="icon"><i class="fas fa-user-friends"></i></div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="small-box bg-primary">
                        <div class="inner">
                            <h3>{{ $communitiesCount }}</h3>
                            <p>Communities</p>
                        </div>
                        <div class="icon"><i class="fas fa-church"></i></div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3>{{ $departmentsCount }}</h3>
                            <p>Departments</p>
                        </div>
                        <div class="icon"><i class="fas fa-building"></i></div>
                    </div>
                </div>
            </div>

            <!-- Header with search and add button -->
            <div class="card shadow mb-3">
                <div class="card-header border-0">
                    <div class="row align-items-center">
                        <div class="col-sm-6">
                            <input type="text" class="form-control" id="groupSearch" placeholder="Search {{ strtolower(Request::segment(3)) }}...">
                        </div>
                        <div class="col-sm-6 text-right mt-2 mt-sm-0">
                            <a href="{{ url('dashboard/people/new') }}" class='btn btn-primary btn-sm'><i class='fas fa-plus-circle'></i> Add New</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Groups grid -->
            <div class='row' id="groupsContainer">
                @forelse ($people as $group)
                    <div class='col-sm-6 col-lg-4 d-flex align-items-stretch mb-3 group-card' data-name="{{ strtolower($group->name) }}">
                        <div class="card shadow-sm w-100" style="overflow: hidden; transition: box-shadow 0.3s;">
                            <div style="position: relative;">
                                <img class="card-img-top"
                                    src="{{ $group->banner == '' ? asset('website/default.jpg') : asset('peoples/' . $group->banner) }}"
                                    alt="{{ $group->name }}" style="height: 180px; object-fit: cover;">
                                <div style="position: absolute; bottom: 0; left: 0; right: 0; padding: 10px 15px; background: linear-gradient(transparent, rgba(0,0,0,0.7));">
                                    <h5 class="text-white mb-0 font-weight-bold">{{ Str::words($group->name, 6) }}</h5>
                                </div>
                            </div>
                            <div class="card-body pb-2">
                                <p class="mb-1">
                                    <i class="far fa-user text-primary"></i>
                                    <span class="small font-weight-bold">{{ $group->firstname }} {{ $group->lastname }}</span>
                                    <span class="text-muted small">(Leader)</span>
                                </p>
                                <p class="mb-2">
                                    <span class="badge badge-info"><i class="fas fa-users"></i> {{ $group->member_count }} members</span>
                                </p>
                                <p class="card-text small text-muted">{{ Str::words(strip_tags($group->description), 25) }}</p>
                            </div>
                            <div class="card-footer bg-white border-top pt-2 pb-2">
                                <a href="{{ url('dashboard/people/members/' . $group->id) }}" class="btn btn-primary btn-sm">
                                    <i class='fas fa-users'></i> View Members
                                </a>
                                <a href="{{ url('dashboard/people/edit/' . $group->user_group . '/' . $group->id) }}" class="btn btn-outline-secondary btn-sm">
                                    <i class='fas fa-edit'></i> Edit
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class='col-sm-12'>
                        <div class='alert alert-warning text-center'>
                            <i class='fas fa-ban'></i> No {{ strtolower(Request::segment(3)) }} yet. Click "Add New" to create one.
                        </div>
                    </div>
                @endforelse
            </div>
            <div class="col-sm-12 mt-3">
                {{ $people->links() }}
            </div>
        </div>
    </section>
@endsection
@push('js')
    <script>
        $(document).ready(function() {
            $('#groupSearch').keyup(function() {
                var search = $(this).val().toLowerCase();
                $('.group-card').each(function() {
                    var name = $(this).data('name');
                    $(this).toggle(name.indexOf(search) > -1);
                });
            });
        });
    </script>
@endpush
