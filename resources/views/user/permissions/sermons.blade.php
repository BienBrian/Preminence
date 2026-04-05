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
                            <h3 class="mb-0"><i class="fas fa-microphone"></i> | Sermons</h3>
                        </div>
                        <div class="col text-right">
                            @if($permissions1->sermons > 1 || $permissions2->sermons > 1)
                                <a href="{{url('users/newsermon')}}" class="btn btn-sm btn-outline-primary"><i class='fas fa-arrow-right'></i> New</a>
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
                                <th scope="col">Video</th>
                                <th scope="col">Youtube</th>
                                <th scope="col">Audio</th>
                                <th scope="col">Action</th>
                            </tr>
                        </thead>
                            <tbody>
                                @if($sermons->isEmpty())
                                    <tr><td colspan='9' class='text-center'> <i class='fas fa-ban'></i> No sermons yet</td></tr>
                                @endif
                                <?php $count = 1; ?>
                                @foreach($sermons as $sermon)
                                <tr>
                                        <td>{{ $count }}</td>
                                        <td scope='row'>
                                            <div class="media align-items-center">
                                                <img alt="Image placeholder" src="{{$sermon->banner == ""?asset('website/homepage/default.jpg'):asset('sermon/'.$sermon->banner)}}" height='40' width='40' class='mr-3' style='border-radius: 50%;'>
                                                <div class="media-body">
                                                    <span class="mb-0 text-sm">{{ str_limit($sermon->title, $limit = 30, $end = '...') }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ str_limit($sermon->description, $limit = 30, $end = '...') }}</td>
                                        <td>{{\Carbon\Carbon::parse($sermon->sermondate)->format('d, M Y')}}</td>
                                        <td>{{$sermon->time}}</td>
                                        <td>{{ str_limit($sermon->video, $limit = 15, $end = '...') }}</td>
                                        <td>{{ str_limit($sermon->youtube, $limit = 30, $end = '...') }}</td>
                                        <td>{{ str_limit($sermon->audio, $limit = 30, $end = '...') }}</td>
                                        <td class='text-right'>
                                            @if($permissions1->sermons > 1 || $permissions2->sermons > 1)
                                                <a href="{{url('editsermon/'.$sermon->id)}}" class='btn btn-primary btn-sm'>Edit</a>
                                            @endif
                                            @if($permissions1->sermons > 2 || $permissions2->sermons > 2)
                                                <a href="{{url('deletesermon/'.$sermon->id)}}" class='btn btn-danger btn-sm'>Delete</a>
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
                {{$sermons->links()}}
            </div>
        </div>
    </div>
@endsection
