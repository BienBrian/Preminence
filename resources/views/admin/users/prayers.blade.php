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
                            <h3 class="mb-0"><i class="fas fa-hands"></i> | Prayers</h3>
                        </div>
                        <div class="col text-right">
                            <button class="btn btn-sm btn-primary btn-show-prayer" data-toggle="modal" data-target="#prayerModal"><i class='fas fa-circle-add'></i> New Request</button>
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
                                <th scope="col">Prayer</th>
                                <th scope="col">Description</th>
                                <th scope="col">Date</th>
                                <th scope="col" class='text-right'>Action</th>
                            </tr>
                        </thead>
                            <tbody>
                                @if($prayers->isEmpty())
                                    <tr><td colspan='6' class='text-center'> <i class='fas fa-ban'></i> No prayer requests yet</td></tr>
                                @endif
                                <?php $count = 1; ?>
                                @foreach($prayers as $prayer)
                                <tr>
                                        <td>{{ $count }}</td>
                                        <td>
                                            {{ str_limit($prayer->title, $limit = 30, $end = '...') }}</span>
                                        </td>
                                        <td>{{ str_limit($prayer->description, $limit = 30, $end = '...') }}</td>
                                        <td>{{\Carbon\Carbon::parse($prayer->created_at)->format('d, M Y')}}</td>
                                        <td class='text-right'>
                                            <a href="{{$prayer->id}}" class='btn btn-primary btn-sm edit-prayer'>Edit</a>
                                            <a href="{{url('deleteprayer/'.$prayer->id)}}" class='btn btn-danger btn-sm'>Delete</a>
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
                {{$prayers->links()}}
            </div>
        </div>
    </div>


    <!-- Modal -->
    <div class="modal fade" id="prayerModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header pb-0 mb-0">
                        <h4 class="modal-title" id="exampleModalLabel">Prayer</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body pt-1 pb-0 mb-0">
                        <form action="/addprayer" method="post" class="prayer-form">
                            @csrf
                            <input type='hidden' name='id' value='0'>
                            <input type='hidden' name='status' value='0'>
                            <div class='form-group'>
                                <label><small>Prayer</small></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="basic-addon2"><i class='fas fa-hands text-primary'></i></span>
                                    </div>
                                    <input type="text" class="form-control" name="title" placeholder='Event Title' required>
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
                                <button type="submit" class="btn btn-primary">Send Request</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
@endsection
