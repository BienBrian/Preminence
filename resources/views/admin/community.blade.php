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
                            <h3 class="mb-0"><i class="fas fa-users"></i> | New Community</h3>
                        </div>
                    </div>
                </div>
                
                <div class="card-body">
                    <form method="POST" action="{{url('/addcommunity')}}" enctype="multipart/form-data" class="row d-flex align-items-center com-form">
                        @csrf
                        <div class="col-sm-6">
                            <img src="{{asset('website/homepage/default.jpg')}}" class="img-fluid">
                            <div class='text-center'>
                                <a href="#" class="btn btn-outline-primary mt-2 comphoto">Upload Image</a>
                            </div>
                        </div>
                        <div class='form-group col-sm-6'>
                            <label><small>Name</small></label>
                            <input type="file" name="photo" class="d-none">
                            <input type="text" name="id" value="0" class="d-none">
                            <input name="name" type="text" placeholder="Community Name" class="form-control">
                            <label class='mt-3'><small>Description</small></label>
                            <textarea name="description" placeholder="Community Description" rows="5" class="form-control"></textarea>
                            <div class='text-right'>
                                <button class="btn btn-primary mt-2">Save</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
