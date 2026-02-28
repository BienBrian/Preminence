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
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col">
                            <h3 class="mb-0"><i class='fa fa-arrow-right'></i> Testimonial</h3>
                        </div>
                    </div>
                </div>
                <div class='card-body'>
                    <div class='row'>
                        <div class='col'>
                            @if (\Session::has('success'))
                                <div class="alert alert-success alert-dismissable">
                                    <a href="#" class="close text-white" data-dismiss="alert" aria-label="close">&times;</a>
                                    <i class='fas fa-check-circle'></i> {{ \Session::get('success') }}
                                </div>
                            @endif
                            @if (\Session::has('error'))
                                <div class="alert alert-danger alert-dismissable">
                                    <a href="#" class="close text-white" data-dismiss="alert" aria-label="close">&times;</a>
                                    <i class='fas fa-exclamation-circle'></i> {{ \Session::get('error') }}
                                </div>
                            @endif
                            <form method="POST" action="{{url('addtestimonial')}}">
                                @csrf
                                <input type='hidden' name='user_id' value="{{\Auth::user()->id}}">
                                <input type='hidden' name='id' value="{{ $testimonial == null?"0":$testimonial->id}}">
                                <div class="input-group mb-3">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="basic-addon2"><i class='fas fa-comments text-primary'></i></span>
                                    </div>
                                    <textarea class='form-control' name='testimonial' rows='6' placeholder="Place your testimonial here" required>{{ $testimonial == null?"":$testimonial->testimonial}}</textarea>
                                </div>
                                <div class='form-group text-right'>
                                    <button class='btn btn-primary'>Save</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
