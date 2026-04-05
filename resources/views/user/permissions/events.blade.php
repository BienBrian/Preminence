@extends('layouts.user')

@section('content')
<!-- Header -->
<div class="header bg-gradient-primary pb-6 pt-5 pt-md-6">
    <div class="container-fluid">
        <div class="header-body">
        </div>
    </div>
</div>

<!-- Page content -->
<div class="container-fluid mt--5">
    <div class="row">
        <div class="col-xl-12 mb-5 mb-xl-0">
            <div class="card shadow">
                <div class="card-header border-0">
                    <div class="row align-items-center">
                        <div class="col">
                            <h3 class="mb-0"><i class="fas fa-bell"></i> | Events</h3>
                        </div>
                        <div class="col text-right">
                            @if($permissions1->events > 1 || $permissions2->events > 1)
                                <button class="btn btn-sm btn-primary btn-show-event" data-toggle="modal" data-target="#eventsModal"><i class='fas fa-circle-add'></i> Add New</button>
                            @endif
                        </div>
                    </div>
                </div>
                @if (\Session::has('success'))
                    <div class="alert alert-success alert-dismissable m-1">
                        <a href="#" class="close text-white" data-dismiss="alert" aria-label="close">&times;</a>
                        <i class='fas fa-check-circle'></i> {{ \Session::get('success') }}
                    </div>
                @endif
                @if (\Session::has('error'))
                    <div class="alert alert-danger alert-dismissable m-1">
                        <a href="#" class="close text-white" data-dismiss="alert" aria-label="close">&times;</a>
                            <i class='fas fa-exclamation-circle'></i> {{ \Session::get('error') }}
                    </div>
                @endif
                @if (count($errors) > 0)
                    <div class="alert alert-danger">
                        <a href="#" class="close text-white" data-dismiss="alert" aria-label="close">&times;</a>
                        <strong><i class='fas fa-exclamation-circle'></i> Whoops!</strong> There were some problems with your input.
                        @foreach ($errors->all() as $error)
                            <br><i class='fas fa-angle-right'></i> {{ $error }}</li>
                        @endforeach
                    </div>
                @endif
                <div class="table-responsive">
                    <!-- Projects table -->
                    <table class="table align-items-center table-flush">
                        <thead class="thead-light">
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">Title</th>
                                <th scope="col">Description</th>
                                <th scope="col">Date</th>
                                <th scope="col">Time</th>
                                <th scope="col">Location</th>
                                <th scope="col">Action</th>
                            </tr>
                        </thead>
                            <tbody>
                                @if($events->isEmpty())
                                    <tr><td colspan='7' class='text-center'> <i class='fas fa-ban'></i> No events yet</td></tr>
                                @endif
                                <?php $count = 1; ?>
                                @foreach($events as $event)
                                <tr>
                                        <td>{{ $count }}</td>
                                        <td scope='row'>
                                            <div class="media align-items-center">
                                                <img alt="Image placeholder" src="{{$event->banner == ""?asset('website/homepage/default.jpg'):asset('event/'.$event->banner)}}" height='40' width='40' class='mr-3' style='border-radius: 50%;'>
                                                <div class="media-body">
                                                    <span class="mb-0 text-sm">{{ str_limit($event->title, $limit = 30, $end = '...') }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ str_limit($event->description, $limit = 30, $end = '...') }}</td>
                                        <td>{{\Carbon\Carbon::parse($event->eventdate)->format('d, M Y')}}</td>
                                        <td>{{\Carbon\Carbon::parse($event->time)->format('H:i:s')}}</td>
                                        <td>{{ str_limit($event->location, $limit = 15, $end = '...') }}</td>
                                        <td class='text-right'>
                                            @if($permissions1->events > 1 || $permissions2->events > 1)
                                                <a href="{{$event->id}}" class='btn btn-primary btn-sm edit-event'>Edit</a>
                                            @endif
                                            @if($permissions1->events > 2 || $permissions2->events > 2)
                                                <a href="{{url('deletesermon/'.$event->id)}}" class='btn btn-danger btn-sm'>Delete</a>
                                            @endif
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
                {{$events->links()}}
            </div>
        </div>
    </div>


    <!-- Modal -->
    <div class="modal fade" id="eventsModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header pb-0 mb-0">
                        <h4 class="modal-title" id="exampleModalLabel">Events</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body pt-1 pb-0 mb-0">
                        <form action="/addevent" method="post" class="event-form" enctype="multipart/form-data" >
                            @csrf
                            <input type='hidden' name='id' value='0'>
                            <input type="file" name="banner" class="d-none" accept="image/*">

                            <div class='form-group'>
                                <label><small>Title</small></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="basic-addon2"><i class='fas fa-angle-right text-primary'></i></span>
                                    </div>
                                    <input type="text" class="form-control" name="title" placeholder='Event Title' required>
                                </div>
                            </div>
                            <div class='form-group'>
                                <label><small>Location</small></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="basic-addon2"><i class='fas fa-map-marker-alt text-primary'></i></span>
                                    </div>
                                    <input type="text" class="form-control" name="location" placeholder='Event Location' required>
                                </div>
                            </div>
                            <div class='form-group'>
                                <label><small>Date</small></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="basic-addon2"><i class='fas fa-calendar-alt text-primary'></i></span>
                                    </div>
                                    <input name="date" class="form-control datepicker" placeholder="Event Date" readonly>
                                </div>
                            </div>
                            <div class='form-group'>
                                <label><small>Description</small></label>
                                <div class="input-group mb-3">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="basic-addon2"><i class='fas fa-comments text-primary'></i></span>
                                    </div>
                                    <textarea name='description' class="form-control" placeholder='Event Description' rows="4">Say Something</textarea>
                                </div>
                            </div>

                            <div class="col-sm-12 d-none banner mb-2">
                                <img src="{{asset('sermon/default.png')}}" class="img-fluid" id="view-banner">
                            </div>

                            <div class="form-group text-right">
                                <a href="#" class="btn btn-outline-primary btn-event-banner">Upload banner</a>
                                <button type="submit" class="btn btn-primary btn-submit-events">Save Event</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
@endsection
