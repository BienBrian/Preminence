@extends('layouts.dashboard')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">Mentorship Hub</h1>
            </div>
            <div class="col-sm-6">
                 <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ url('dashboard/home') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ url('dashboard/spiritual/discipleship') }}">Discipleship</a></li>
                    <li class="breadcrumb-item active">Mentorship</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Connections</h3>
                        @can('Manage Discipleship')
                        <div class="card-tools">
                            <button class="btn btn-tool" data-toggle="modal" data-target="#matchMentorModal" title="Match Mentor"><i class="fas fa-plus"></i></button>
                        </div>
                        @endcan
                    </div>
                    <div class="card-body p-0">
                        <ul class="nav nav-pills flex-column">
                            @foreach($mentorships as $m)
                                <li class="nav-item">
                                    <a href="#" class="nav-link">
                                        <i class="fas fa-user-friends"></i>
                                        @if($m->mentor_id == Auth::id())
                                            {{ $m->mentee->firstname }} {{ $m->mentee->lastname }} (Mentee)
                                        @elseif($m->mentee_id == Auth::id())
                                            {{ $m->mentor->firstname }} {{ $m->mentor->lastname }} (Mentor)
                                        @else
                                            <!-- Admin View of others -->
                                            {{ $m->mentor->firstname }} & {{ $m->mentee->firstname }}
                                        @endif
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                        @if($mentorships->isEmpty())
                            <div class="p-3 text-muted text-center">No active connections.</div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-md-9">
                <!-- Admin: All Mentorships Table -->
                @can('Manage Discipleship')
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title">All Mentorship Matches</h3>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Mentor</th>
                                    <th>Mentee</th>
                                    <th>Started</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($mentorships as $m)
                                <tr>
                                    <td>{{ $m->mentor->firstname }} {{ $m->mentor->lastname }}</td>
                                    <td>{{ $m->mentee->firstname }} {{ $m->mentee->lastname }}</td>
                                    <td>{{ $m->started_at ? $m->started_at->format('M d, Y') : 'N/A' }}</td>
                                    <td><span class="badge badge-success">Active</span></td>
                                </tr>
                                @empty
                                <tr><td colspan="4" class="text-center">No mentorships found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @endcan

                <!-- Session Log (Simplified for now) -->
                <div class="card card-primary card-outline direct-chat direct-chat-primary">
                    <div class="card-header">
                        <h3 class="card-title">Session Log</h3>
                    </div>
                    <div class="card-body">
                         <div class="direct-chat-messages" style="height: 400px;">
                            @forelse($mentorships as $m)
                                @foreach($m->sessions as $session)
                                    <div class="direct-chat-msg {{ $session->created_by == Auth::id() ? 'right' : '' }}">
                                        <div class="direct-chat-infos clearfix">
                                            <span class="direct-chat-name float-{{ $session->created_by == Auth::id() ? 'right' : 'left' }}">
                                                {{ $session->creator->firstname ?? 'Unknown' }}
                                            </span>
                                            <span class="direct-chat-timestamp float-{{ $session->created_by == Auth::id() ? 'left' : 'right' }}">
                                                {{ $session->created_at->format('d M h:i A') }}
                                            </span>
                                        </div>
                                        <div class="direct-chat-text">
                                            {{ $session->notes }}
                                        </div>
                                    </div>
                                @endforeach
                            @empty
                                <div class="text-center mt-5 text-muted">Select a connection to view history.</div>
                            @endforelse
                         </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@can('Manage Discipleship')
<!-- Match Mentor Modal -->
<div class="modal fade" id="matchMentorModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Match Mentor & Mentee</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form action="{{ url('dashboard/spiritual/discipleship/mentorship/match') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>Mentor</label>
                        <select name="mentor_id" class="form-control select2-user" style="width: 100%;" required>
                             <!-- Populated via Ajax -->
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Mentee</label>
                        <select name="mentee_id" class="form-control select2-user" style="width: 100%;" required>
                             <!-- Populated via Ajax -->
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Create Match</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('.select2-user').select2({
            placeholder: 'Search for a user...',
            dropdownParent: $('#matchMentorModal'),
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
