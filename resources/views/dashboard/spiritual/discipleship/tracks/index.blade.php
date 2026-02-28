@extends('layouts.dashboard')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">Discipleship Tracks</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ url('dashboard/home') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ url('dashboard/spiritual/discipleship') }}">Discipleship</a></li>
                    <li class="breadcrumb-item active">Tracks</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        
        <div class="row mb-3">
            <div class="col-12 text-right">
                <button class="btn btn-primary" data-toggle="modal" data-target="#createTrackModal"><i class="fas fa-plus"></i> Create New Track</button>
            </div>
        </div>

        <div class="row">
            @foreach($tracks as $track)
            <div class="col-md-4">
                <div class="card card-widget widget-user-2 shadow-sm">
                    <div class="widget-user-header bg-info">
                        <div class="widget-user-image">
                            <!-- Placeholder icon if no image -->
                            <img class="img-circle elevation-2" src="{{ asset('website/icon.png') }}" alt="Track Icon">
                        </div>
                        <h3 class="widget-user-username">{{ $track->title }}</h3>
                        <h5 class="widget-user-desc">{{ Str::limit($track->description, 50) }}</h5>
                    </div>
                    <div class="card-footer p-0">
                        <ul class="nav flex-column">
                            <li class="nav-item">
                                <span class="nav-link">
                                    Steps <span class="float-right badge bg-primary">{{ $track->steps->count() }}</span>
                                </span>
                            </li>
                            <li class="nav-item">
                                <span class="nav-link">
                                    Enrolled <span class="float-right badge bg-success">{{ $track->enrollments->count() }}</span>
                                </span>
                            </li>
                            <li class="nav-item p-2 text-center">
                                <a href="{{ url('dashboard/spiritual/discipleship/tracks/'.$track->id) }}" class="btn btn-block btn-outline-info btn-sm">View Track</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Create Modal -->
        <div class="modal fade" id="createTrackModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Create New Track</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form action="{{ url('dashboard/spiritual/discipleship/tracks/create') }}" method="POST">
                        @csrf
                        <div class="modal-body">
                            <div class="form-group">
                                <label>Track Title</label>
                                <input type="text" class="form-control" name="title" required placeholder="e.g. New Believers">
                            </div>
                            <div class="form-group">
                                <label>Description</label>
                                <textarea class="form-control" name="description" rows="3"></textarea>
                            </div>
                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="publicSwitch" name="is_public" checked>
                                    <label class="custom-control-label" for="publicSwitch">Publicly Visible</label>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Create Track</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</section>
@endsection
