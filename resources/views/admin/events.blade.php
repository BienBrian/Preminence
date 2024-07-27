@extends('layouts.admin')

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
                            <button class="btn btn-sm btn-primary btn-show-event" data-toggle="modal" data-target="#eventsModal"><i class='fas fa-circle-add'></i> Add New</button>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <!-- Projects table -->
                    <table class="table align-items-center table-flush">
                        <thead class="thead-light">
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">Title</th>
                                <th scope="col">Theme</th>
                                <th scope="col">Description</th>
                                <th scope="col">Date</th>
                                <th scope="col">Time</th>
                                <th scope="col">Location</th>
                                <th scope="col">Cost</th>
                                <th scope="col">Entry</th>
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
                                                    <span class="mb-0 text-sm">{{ \Str::limit($event->title, $limit = 30, $end = '...') }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ \Str::limit($event->theme, $limit = 30, $end = '...')}}</td>
                                        <td>{{ \Str::limit($event->description, $limit = 30, $end = '...') }}</td>
                                        <td>{{\Carbon\Carbon::parse($event->eventdate)->format('d, M Y')}}</td>
                                        <td>{{\Carbon\Carbon::parse($event->time)->format('H:i:s')}}</td>
                                        <td>{{ \Str::limit($event->location, $limit = 15, $end = '...') }}</td>
                                        <td>{{ number_format($event->cost, 2) }}</td>
                                        <td>{{ number_format($event->entry, 2) }}</td>
                                        <td class='text-right'>
                                            <a href="{{$event->id}}" class='btn btn-primary btn-sm edit-event'>Edit</a>
                                            <a href="{{url('deleteevent/'.$event->id)}}" class='btn btn-danger btn-sm'>Delete</a>
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
            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="exampleModalLabel">Events</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body pt-1 pb-0 mb-0 border-top">
                        <form action="/addevent" method="post" class="row event-form" enctype="multipart/form-data" >
                            @csrf
                            <input type='hidden' name='id' value='0'>
                            <input type='hidden' name='eventtype' value='0'>
                            <input type="file" name="banner" class="d-none" accept="image/*">

                            <div class='col-md-6 mt-1'>
                                <label><small>Title</small></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="basic-addon2"><i class='fas fa-angle-right text-primary'></i></span>
                                    </div>
                                    <input type="text" class="form-control" name="title" placeholder='Event Title' required>
                                </div>
                            </div>

                            <div class='col-md-6 mt-1'>
                                <label><small>Theme</small></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="basic-addon2"><i class='fas fa-microphone text-primary'></i></span>
                                    </div>
                                    <input type="text" class="form-control" name="theme" placeholder='Event Theme' required>
                                </div>
                            </div>

                            <div class='col-md-6 mt-1'>
                                <label><small>Location</small></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="basic-addon2"><i class='fas fa-map-marker-alt text-primary'></i></span>
                                    </div>
                                    <input type="text" class="form-control" name="location" placeholder='Event Location' required>
                                </div>
                            </div>

                            <div class='col-md-6 mt-1'>
                                <label><small>Budget</small></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="basic-addon2"><i class='fas fa-coins text-primary'></i></span>
                                    </div>
                                    <input type="number" class="form-control" name="budget" placeholder='Event&apos;s Budget' required>
                                </div>
                            </div>

                            <div class='col-md-6 mt-1'>
                                <label><small>Entry Amount</small></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="basic-addon2"><i class='fas fa-chevron-circle-right text-primary'></i></span>
                                    </div>
                                    <input type="number" class="form-control" name="entry" placeholder='Entry Amount' value="0.00" required>
                                </div>
                            </div>

                            <div class='col-md-6 mt-1'>
                                <label><small>Date</small></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="basic-addon2"><i class='fas fa-calendar-alt text-primary'></i></span>
                                    </div>
                                    <input name="date" class="form-control datepicker" placeholder="Event Date" readonly>
                                </div>
                            </div>
                            <div class='col-md-12 mt-1'>
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

                            <div class="col-sm-12 text-right mb-2">
                                <a href="#" class="btn btn-outline-primary btn-event-banner">Upload banner</a>
                                <button type="submit" class="btn btn-primary btn-submit-events">Save Event</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
@endsection
