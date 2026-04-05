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
            <div class="card bgshadow">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col">
                            <h3 class="mb-0"><i class="fas fa-book"></i> | Weekly Verse</h3>
                        </div>
                    </div>
                </div>
                <div class="card-body mb-0 p-0">
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
                    <!-- Projects table -->
                    <div class='card-body'>
                        <form action="/addverse" method="post" class='row'>
                            @csrf
                            <input type="hidden" name="id" value="{{$verse == null?'0':$verse->id}}">
                            <div class='form-group col-sm-6'>
                                <label><small>Verse Content:</small></label>
                                <textarea class='form-control' name="description" rows="5" placeholder="Enter Verse Content" required {{($permissions1->websites > 1 || $permissions2->websites > 1) ? "":"disabled"}}>{{$verse == null?'':$verse->description}}</textarea>
                            </div>
                            <div class='form-group col-sm-6'>
                                <label><small>Verse:</small></label>
                                <input class='form-control' name="verse" placeholder="Verse from" value="{{$verse == null?'':$verse->verse}}" required {{($permissions1->websites > 1 || $permissions2->websites > 1) ? "":"disabled"}}>
                                <label><small>Version:</small></label>
                                <input class='form-control' name="version" placeholder="Version" value="{{$verse == null?'':$verse->version}}" required {{($permissions1->websites > 1 || $permissions2->websites > 1) ? "":"disabled"}}>
                            </div>
                            <div class="form-group col-sm-12 text-right">
                                <button type="submit" class="btn btn-primary" {{($permissions1->websites > 1 || $permissions2->websites > 1) ? "":"disabled"}}>Save changes</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
