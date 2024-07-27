@extends('layouts.user')

@section('content')
<!-- Header -->
<div class="header bg-gradient-primary pb-6 pt-5 pt-md-6">
    <div class="container-fluid">
        <div class="header-body">
        </div>
    </div>
</div>
<?php
    $permissions1 = \DB::table("permissions")->where("user_id", \Auth::user()->id)->first();
    $permissions2 = \DB::table("permissions")->where("role", \Auth::user()->role)->first();
?>

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
                    </div>
                </div>

                @if (\Session::has('success'))
                    <div class="alert alert-success alert-dismissable m-1">
                        <a href="#" class="close text-white" data-dismiss="alert" aria-label="close">&times;</a>
                        <i class='fas fa-check-circle'></i> {!! \Session::get('success') !!}
                    </div>
                @endif
                @if (\Session::has('error'))
                    <div class="alert alert-danger alert-dismissable m-1">
                        <a href="#" class="close text-white" data-dismiss="alert" aria-label="close">&times;</a>
                            <i class='fas fa-exclamation-circle'></i> {!! \Session::get('error') !!}
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
                                <th scope="col">Posted By</th>
                                <th scope="col">By</th>
                                <th scope="col" class='text-right'>Action</th>
                            </tr>
                        </thead>
                            <tbody>
                                @if($prayers->isEmpty())
                                    <tr><td colspan='6' class='text-center'> <i class='fas fa-ban'></i> No prayer yet</td></tr>
                                @endif
                                <?php $count = 1; ?>
                                @foreach($prayers as $prayer)
                                <tr>
                                        <td>{{ $count }}</td>
                                        <td scope='row'>
                                            {{ str_limit($prayer->title, $limit = 30, $end = '...') }}
                                        </td>
                                        <td>{{ str_limit($prayer->description, $limit = 30, $end = '...') }}</td>
                                        <td>{{\Carbon\Carbon::parse($prayer->created_at)->format('d, M Y')}}</td>
                                        <td>{{ $prayer->firstname}} {{ $prayer->lastname}}</td>
                                        <td class='text-right'>
                                            <a href="{{$prayer->id}}" class='btn btn-primary btn-sm view-prayer'>View</a>
                                            @if($permissions1->events > 2 || $permissions2->events > 2)
                                                <a href="{{url('deleteprayer/'.$prayer->id)}}" class='btn btn-danger btn-sm'>Delete</a>
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
                {{$prayers->links()}}
            </div>
        </div>
    </div>


    <!-- Modal -->
    <div class="modal fade" id="prayerModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header pb-0 mb-0">
                    <h4 class="modal-title" id="exampleModalLabel">Prayer Details</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">

                </div>
            </div>
        </div>
    </div>
@endsection
