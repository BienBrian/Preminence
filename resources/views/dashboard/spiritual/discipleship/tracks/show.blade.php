@extends('layouts.dashboard')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">{{ $track->title }}</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ url('dashboard/spiritual/discipleship/tracks') }}">Tracks</a></li>
                    <li class="breadcrumb-item active">{{ $track->title }}</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        
        <div class="row">
            <!-- Track Info Sidebar -->
            <div class="col-md-4">
                <div class="card card-primary card-outline">
                    <div class="card-body box-profile">
                         <h3 class="profile-username text-center">{{ $track->title }}</h3>
                         <p class="text-muted text-center">{{ $track->description }}</p>

                         @if($enrollment)
                            <div class="progress mb-3">
                                @php
                                    $total = $track->steps->count();
                                    $completed = $enrollment->progress->count();
                                    $percent = $total > 0 ? round(($completed / $total) * 100) : 0;
                                @endphp
                                <div class="progress-bar bg-success" role="progressbar" style="width: {{ $percent }}%" aria-valuenow="{{ $percent }}" aria-valuemin="0" aria-valuemax="100">{{ $percent }}%</div>
                            </div>
                            <div class="text-center mb-2">
                                <small>{{ $percent }}% Complete</small>
                            </div>
                            
                            @if($percent == 100)
                                <div class="alert alert-success text-center"><i class="fas fa-certificate"></i> Track Completed!</div>
                            @else
                                <div class="alert alert-info text-center">In Progress</div>
                            @endif
                         @else
                            <form action="{{ url('dashboard/spiritual/discipleship/tracks/'.$track->id.'/enroll') }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-primary btn-block"><b>Enroll in Track</b></button>
                            </form>
                         @endif
                    </div>
                </div>

                @can('Manage Discipleship')
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Admin Controls</h3>
                    </div>
                    <div class="card-body">
                        <button class="btn btn-app" data-toggle="modal" data-target="#addStepModal">
                            <i class="fas fa-plus"></i> Add Step
                        </button>
                        <button class="btn btn-app" data-toggle="modal" data-target="#enrollStudentModal">
                            <i class="fas fa-user-plus"></i> Enroll User
                        </button>
                    </div>
                </div>
                @endcan
            </div>

            <!-- Main Content Area -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header p-2">
                        <ul class="nav nav-pills">
                            <li class="nav-item"><a class="nav-link active" href="#curriculum" data-toggle="pill">Curriculum</a></li>
                            @can('Manage Discipleship')
                            <li class="nav-item"><a class="nav-link" href="#students" data-toggle="pill">Enrolled Students</a></li>
                            @endcan
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="tab-content">
                            <!-- Curriculum Tab -->
                            <div class="active tab-pane" id="curriculum">
                                <div class="timeline timeline-inverse">
                                    @foreach($track->steps as $step)
                                        @php
                                            $isCompleted = false;
                                            if($enrollment) {
                                                $isCompleted = $enrollment->progress->where('step_id', $step->id)->count() > 0;
                                            }
                                            $iconColor = $isCompleted ? 'bg-success' : 'bg-secondary';
                                        @endphp
                                        <div>
                                            <i class="fas fa-chalkboard-teacher {{ $iconColor }}"></i>
                                            <div class="timeline-item">
                                                <h3 class="timeline-header">{{ $step->title }}</h3>
                                                <div class="timeline-body">
                                                    {{ $step->description }}
                                                    @if($step->content_type == 'video' && $step->content_url)
                                                        <div class="mt-2">
                                                            <a href="{{ $step->content_url }}" target="_blank" class="btn btn-xs btn-info"><i class="fas fa-video"></i> Watch Video</a>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="timeline-footer">
                                                    @if($enrollment && !$isCompleted)
                                                        <form action="{{ url('dashboard/spiritual/discipleship/steps/'.$step->id.'/complete') }}" method="POST">
                                                            @csrf
                                                            <input type="hidden" name="enrollment_id" value="{{ $enrollment->id }}">
                                                            <button type="submit" class="btn btn-primary btn-sm">Mark Complete</button>
                                                        </form>
                                                    @elseif($isCompleted)
                                                        <span class="badge badge-success">Completed</span>
                                                    @else
                                                        <span class="text-muted font-italic">Enroll to start</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                    <div>
                                        <i class="fas fa-clock bg-gray"></i>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Enrolled Students Tab (Admin Only) -->
                            @can('Manage Discipleship')
                            <div class="tab-pane" id="students">
                                <table class="table table-hover table-striped">
                                    <thead>
                                        <tr>
                                            <th>Student</th>
                                            <th>Progress</th>
                                            <th>Started</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($enrolledUsers as $e)
                                        <tr>
                                            <td>
                                                <div class="user-block">
                                                    <span class="username"><a href="#">{{ $e->user->firstname }} {{ $e->user->lastname }}</a></span>
                                                    <span class="description">{{ $e->user->email }}</span>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="progress progress-xs">
                                                    @php
                                                        $t = $track->steps->count();
                                                        $c = $e->progress->count();
                                                        $p = $t > 0 ? round(($c / $t) * 100) : 0;
                                                    @endphp
                                                    <div class="progress-bar bg-success" style="width: {{ $p }}%"></div>
                                                </div>
                                                <small>{{ $p }}% Complete</small>
                                            </td>
                                            <td>{{ $e->started_at->format('M d, Y') }}</td>
                                            <td>
                                                @if($e->completed_at)
                                                    <span class="badge badge-success">Graduated</span>
                                                @else
                                                    <span class="badge badge-warning">In Progress</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @empty
                                        <tr><td colspan="4" class="text-center">No students enrolled.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</section>

<!-- Admin Modals -->
@can('Manage Discipleship')
<!-- Add Step Modal -->
<div class="modal fade" id="addStepModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Step to Track</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form action="{{ url('dashboard/spiritual/discipleship/tracks/'.$track->id.'/add-step') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>Step Title</label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Description (Content)</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Content Type</label>
                        <select name="content_type" class="form-control">
                            <option value="text">Text / Reading</option>
                            <option value="video">Video Link</option>
                            <option value="assignment">Assignment</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Content URL (Optional)</label>
                        <input type="url" name="content_url" class="form-control" placeholder="https://youtube.com/...">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Add Step</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Enroll Student Modal -->
<div class="modal fade" id="enrollStudentModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Manually Enroll Student</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form action="{{ url('dashboard/spiritual/discipleship/tracks/assign') }}" method="POST">
                @csrf
                <input type="hidden" name="track_id" value="{{ $track->id }}">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Search User</label>
                        <select name="user_id" class="form-control" id="enrollUserSelect" style="width: 100%;" required>
                            <!-- Populated via Ajax -->
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Enroll User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#enrollUserSelect').select2({
            placeholder: 'Search for a user...',
            dropdownParent: $('#enrollStudentModal'),
            ajax: {
                url: '/dashboard/search/users',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        q: params.term // search term
                    };
                },
                processResults: function (data) {
                    return {
                        results: $.map(data, function (item) {
                            return {
                                text: item.name + ' (' + item.email + ')',
                                id: item.id
                            }
                        })
                    };
                },
                cache: true
            }
        });
    });
</script>
@endcan

@endsection
