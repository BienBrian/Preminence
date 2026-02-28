@extends('layouts.dashboard')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">Recovery Journal</h1>
            </div>
             <div class="col-sm-6">
                 <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ url('dashboard/home') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ url('dashboard/spiritual/discipleship') }}">Discipleship</a></li>
                    <li class="breadcrumb-item active">Journal</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
             <div class="col-md-4">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">New Entry</h3>
                    </div>
                    <form action="{{ url('dashboard/spiritual/discipleship/journal/save') }}" method="POST">
                        @csrf
                        <div class="card-body">
                            <div class="form-group">
                                <label>Title</label>
                                <input type="text" class="form-control" name="title" placeholder="Win or Insight...">
                            </div>
                            <div class="form-group">
                                <label>Entry</label>
                                <textarea class="form-control" name="entry" rows="5" required placeholder="Today I felt..."></textarea>
                            </div>
                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input class="custom-control-input" type="checkbox" id="shareCheck" name="share">
                                    <label for="shareCheck" class="custom-control-label">Share with Mentor</label>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary btn-block">Save Entry</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">My Journal Entries</h3>
                    </div>
                    <div class="card-body h-100" style="overflow-y: auto;">
                        @foreach($entries as $entry)
                            <div class="post">
                                <div class="user-block">
                                    <span class="username">
                                        <a href="#">{{ $entry->title ?? 'Untitled' }}</a>
                                    </span>
                                    <span class="description">{{ $entry->created_at->format('M d, Y - h:i A') }}</span>
                                </div>
                                <p>
                                    {{ $entry->entry }}
                                </p>
                                <p>
                                    <span class="badge {{ $entry->is_shared_with_mentor ? 'badge-success' : 'badge-secondary' }}">
                                        {{ $entry->is_shared_with_mentor ? 'Shared with Mentor' : 'Private' }}
                                    </span>
                                </p>
                            </div>
                        @endforeach
                        @if($entries->isEmpty())
                            <p class="text-muted">No journal entries yet. Start writing today!</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
