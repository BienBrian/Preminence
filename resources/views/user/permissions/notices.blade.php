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
                            <h3 class="mb-0"><i class="fas fa-bell"></i> | Notices</h3>
                        </div>
                        <div class="col text-right">
                            @if($permissions1->notices > 1 || $permissions2->notices > 1)
                                <button class="btn btn-sm btn-primary btn-show-notice" data-toggle="modal" data-target="#noticesModal"><i class='fas fa-circle-add'></i> Add New</button>
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
                                <th scope="col">Action</th>
                            </tr>
                        </thead>
                            <tbody>
                                @if($notices->isEmpty())
                                    <tr><td colspan='6' class='text-center'> <i class='fas fa-ban'></i> No notices yet</td></tr>
                                @endif
                                <?php $count = 1; ?>
                                @foreach($notices as $notice)
                                <tr>
                                        <td>{{ $count }}</td>
                                        <td scope='row'>
                                            {{ str_limit($notice->title, $limit = 30, $end = '...') }}
                                        </td>
                                        <td>{{ str_limit($notice->description, $limit = 30, $end = '...') }}</td>
                                        <td>{{\Carbon\Carbon::parse($notice->noticedate)->format('d, M Y')}}</td>
                                        <td>{{\Carbon\Carbon::parse($notice->noticedate)->format('h:i A')}}</td>
                                        <td class='text-right'>
                                            @if($permissions1->notices > 1 || $permissions2->notices > 1)
                                                <a href="{{$notice->id}}" class='btn btn-primary btn-sm edit-notice'>Edit</a>
                                            @endif
                                            @if($permissions1->notices > 2 || $permissions2->notices > 2)
                                                <a href="{{url('deletenotice/'.$notice->id)}}" class='btn btn-danger btn-sm'>Delete</a>
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
                {{$notices->links()}}
            </div>
        </div>
    </div>


    <!-- Modal -->
    <div class="modal fade" id="noticesModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header pb-0 mb-0">
                        <h4 class="modal-title" id="exampleModalLabel">Notices</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body pt-1 pb-0 mb-0">
                        <form action="/addnotice" method="post" class="notices-form">
                            @csrf
                            <input type='hidden' name='id' value='0'>

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

                            <div class="form-group text-right">
                                <button type="submit" class="btn btn-primary btn-submit-notice">Save Notice</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
@endsection
