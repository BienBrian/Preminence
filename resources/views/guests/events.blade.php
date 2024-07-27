@extends('layouts.guest')

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
                            <button class="btn btn-primary btn-sm btn-show-activity" data-toggle="modal" data-target="#activityModal"><i class='fas fa-circle-plus'></i> New</button>
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
                                <th scope="col">Deadline</th>
                                <th scope="col">Payments</th>
                                <th scope="col">Status</th>
                                <th class='text-right'>Action</th>
                            </tr>
                        </thead>
                            <tbody>
                                @if($events->isEmpty())
                                    <tr><td colspan='8' class='text-center'> <i class='fas fa-ban'></i> No events yet</td></tr>
                                @endif
                                <?php $count = 1; ?>
                                @foreach($events as $event)
                                <tr>
                                        <td>{{ $count }}</td>
                                        <td>{{str_limit($event->title, $limit = 30, $end = '...')}}</td>
                                        <td>{{str_limit($event->theme, $limit = 30, $end = '...')}}</td>
                                        <td>{{ str_limit($event->description, $limit = 30, $end = '...') }}</td>
                                        <td>
                                            @if(\Carbon\Carbon::parse($event->eventdate) > \Carbon\Carbon::now()->setTimezone("Africa/Nairobi"))
                                                <span class='text-success font-weight-bold'>{{\Carbon\Carbon::parse($event->eventdate)->diffInDays(\Carbon\Carbon::now())}} day(s) remaining</span>
                                            @else
                                                <span class='text-danger font-weight-bold'>{{\Carbon\Carbon::parse($event->eventdate)->format('d M, Y')}}</span>
                                            @endif
                                        </td>
                                        <td>{{ number_format($event->entry, 2) }}</td>
                                        <td>
                                            @if($event->status == 0)
                                                @if($event->entry > 0)
                                                    <span class='text-danger font-weight-bold'>UNPAID</span>
                                                @else
                                                    <span class='text-success font-weight-bold'>FREE</span>
                                                @endif
                                            @else
                                                <span class='text-success font-weight-bold'>PAID</span>
                                            @endif
                                        </td>
                                        <td class='text-right'>
                                            @if($event->status == 0)
                                                <a href="{{url('guests/removeevent/'.$event->id)}}" class='btn btn-danger btn-sm'>Remove</a>
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
            <div class="col-sm-12 mt-3">
                {{$events->links()}}
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="activityModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header border-bottom">
                        <h4 class="modal-title" id="exampleModalLabel">Add Event</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body pt-1 pb-0 mb-0">
                        <form action="{{url('guests/addevent')}}" method="post" enctype="multipart/form-data" class="seminar-form">
                            @csrf
                            <input type='hidden' name='eventtype' value='1'>

                            <div class='form-group'>
                                <label><small>Events</small></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="basic-addon2"><i class='fas fa-bell text-primary'></i></span>
                                    </div>
                                    <select class="custom-select" name="event">
                                        @foreach($sem as $sem)
                                            <option value="{{$sem->id}}">{{$sem->title}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class='form-group'>
                                <label><small>Medical Report</small></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="basic-addon2"><i class='fas fa-file text-primary'></i></span>
                                    </div>
                                    <input name="report" type="file" class="form-control">
                                </div>
                            </div>

                            <div class="form-group text-right">
                                <button type="submit" class="btn btn-primary">Join Event</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
@endsection
