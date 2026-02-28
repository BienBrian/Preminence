@extends('layouts.dashboard')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">Discipleship & Mentorship</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ url('dashboard/home') }}">Home</a></li>
                    <li class="breadcrumb-item active">Discipleship</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Admin Stats Section -->
@if(isset($adminStats) && !empty($adminStats))
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $adminStats['total_tracks'] }}</h3>
                    <p>Total Tracks</p>
                </div>
                <div class="icon"><i class="fas fa-book"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $adminStats['active_enrollments'] }}</h3>
                    <p>Active Enrollments</p>
                </div>
                <div class="icon"><i class="fas fa-user-graduate"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $adminStats['total_mentorships'] }}</h3>
                    <p>Active Mentorships</p>
                </div>
                <div class="icon"><i class="fas fa-hands-helping"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ count($adminStats['latest_enrollments']) }}</h3>
                    <p>Recent Enrollments</p>
                </div>
                <div class="icon"><i class="fas fa-clock"></i></div>
            </div>
        </div>
    </div>
</div>
@endif

<section class="content">
    <div class="container-fluid">
        
        <div class="row">
            <!-- Navigation Cards -->
            <div class="col-12 col-sm-6 col-md-3">
                <div class="info-box mb-3">
                    <span class="info-box-icon bg-primary elevation-1"><i class="fas fa-route"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Browse Tracks</span>
                        <a href="{{ url('dashboard/spiritual/discipleship/tracks') }}" class="small-box-footer">View Catalog <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-3">
                <div class="info-box mb-3">
                    <span class="info-box-icon bg-success elevation-1"><i class="fas fa-hand-holding-heart"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Mentorship</span>
                        <a href="{{ url('dashboard/spiritual/discipleship/mentorship') }}" class="small-box-footer">Connect <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-3">
                <div class="info-box mb-3">
                    <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-book-open"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">My Journal</span>
                        <a href="{{ url('dashboard/spiritual/discipleship/journal') }}" class="small-box-footer">Open Journal <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- My Enrollments -->
            <div class="col-md-6">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-walking mr-1"></i> My Active Tracks</h3>
                    </div>
                    <div class="card-body p-0">
                        @if($myEnrollments->count() > 0)
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Track</th>
                                        <th>Progress</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($myEnrollments as $enrollment)
                                        @php
                                            $total = $enrollment->track->steps->count();
                                            $completed = $enrollment->progress->count();
                                            $percent = $total > 0 ? round(($completed / $total) * 100) : 0;
                                        @endphp
                                        <tr>
                                            <td>{{ $enrollment->track->title }}</td>
                                            <td style="vertical-align: middle;">
                                                <div class="progress progress-xs">
                                                    <div class="progress-bar bg-primary" style="width: {{ $percent }}%"></div>
                                                </div>
                                                <small>{{ $percent }}% Complete</small>
                                            </td>
                                            <td>
                                                <a href="{{ url('dashboard/spiritual/discipleship/tracks/'.$enrollment->track->id) }}" class="btn btn-sm btn-primary">
                                                    Continue <i class="fas fa-arrow-right"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <div class="text-center p-4">
                                <p class="text-muted">You are not enrolled in any tracks yet.</p>
                                <a href="{{ url('dashboard/spiritual/discipleship/tracks') }}" class="btn btn-outline-primary">Browse Available Tracks</a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Mentorship Snapshot -->
            <div class="col-md-6">
                <div class="card card-secondary card-outline">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-users mr-1"></i> Mentorship Snapshot</h3>
                    </div>
                    <div class="card-body">
                        <h5>As Mentor:</h5>
                        @if($asMentor->count() > 0)
                            <ul class="list-group list-group-flush mb-3">
                                @foreach($asMentor as $m)
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        {{ $m->mentee->firstname }} {{ $m->mentee->lastname }}
                                        <a href="{{ url('dashboard/spiritual/discipleship/mentorship') }}" class="btn btn-xs btn-outline-secondary">Chat</a>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-muted mb-3">You are not mentoring anyone.</p>
                        @endif

                        <h5>My Mentors:</h5>
                         @if($asMentee->count() > 0)
                            <ul class="list-group list-group-flush">
                                @foreach($asMentee as $m)
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        {{ $m->mentor->firstname }} {{ $m->mentor->lastname }}
                                        <a href="{{ url('dashboard/spiritual/discipleship/mentorship') }}" class="btn btn-xs btn-outline-secondary">Chat</a>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                             <p class="text-muted">You do not have a mentor assigned.</p>
                             <button class="btn btn-sm btn-outline-success" onclick="alert('Feature coming soon: Request a Mentor')">Request a Mentor</button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
