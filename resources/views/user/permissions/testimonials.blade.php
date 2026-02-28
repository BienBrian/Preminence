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
                            <h3 class="mb-0">Testimonials</h3>
                        </div>
                        <!--<div class="col text-right">
                            <a href="#!" class="btn btn-sm btn-primary">See all</a>
                        </div>-->
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
                <div class="table-responsive">
                    <!-- Projects table -->
                    <table class="table align-items-center table-flush">
                        <thead class="thead-light">
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">user</th>
                                <th scope="col">Description</th>
                                <th scope="col">Status</th>
                                <th scope="col">Action</th>
                            </tr>
                        </thead>
                            <tbody>
                                @if($testimonials->isEmpty())
                                    <tr><td colspan='4' class='text-center'> <i class='fas fa-ban'></i> No testimonial yet</td></tr>
                                @endif

                                <?php
                                    $permissions1 = \DB::table("permissions")->where("user_id", \Auth::user()->id)->first();
                                    $permissions2 = \DB::table("permissions")->where("role", \Auth::user()->role)->first();
                                ?>
                                <?php $count = 1; ?>
                                @foreach($testimonials as $testimonial)
                                <tr>
                                        <td>{{ $count }}</td>
                                        <td scope='row'>
                                            <div class="media align-items-center">
                                                <a href="#" class="avatar rounded-circle mr-3">
                                                    <img alt="Image placeholder" src="{{$testimonial->image == "" ? asset('profile_images/default.jpg'): asset('profile_images/'.$testimonial->image)}}">
                                                </a>
                                                <div class="media-body">
                                                    <span class="mb-0 text-sm">{{ $testimonial->firstname." ".$testimonial->lastname}}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ str_limit($testimonial->testimonial, $limit = 30, $end = '...') }}</td>
                                        <td>
                                            @if($testimonial->status == 1)
                                                <span class="badge badge-dot mr-4">
                                                    <i class="bg-success"></i> Activated
                                                </span>
                                            @else
                                                <span class="badge badge-dot mr-4">
                                                    <i class="bg-warning"></i> In Active
                                                </span>
                                            @endif
                                        </td>
                                        <td class='text-right'>
                                            @if($permissions1->testimonials >1 || $permissions2->testimonials >1)
                                                @if($testimonial->status == 1)
                                                    <a href="{{url('testimonial/deactivate/'.$testimonial->id)}}" class="btn btn-danger p-1 pr-2 pl-2" title='de-activate'>
                                                        <i class='fas fa-ban'></i>
                                                    </a>
                                                @else
                                                    <a href="{{url('testimonial/activate/'.$testimonial->id)}}" class="btn btn-success p-1 pr-2 pl-2" title='activate'>
                                                        <i class='fas fa-sync-alt'></i>
                                                    </a>
                                                @endif
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
        </div>
    </div>
@endsection
