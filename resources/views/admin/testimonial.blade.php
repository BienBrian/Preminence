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
                        <h3 class="mb-0"><i class="ni ni-planet"></i> | Testimonial</h3>
                    </div>
                    <div class="col text-right">
                        <a href="{{url('testimonials')}}" class='btn btn-primary btn-sm pt-2 pb-2'><i class='fas fa-arrow-left'></i> More Testimonials</a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row d-flex align-items-center">
                    @csrf
                    <div class="col-sm-6 text-center">
                        <img alt="Image placeholder" src="{{$testimonial->image == "" ? asset('profile_images/default.jpg'): asset('profile_images/'.$testimonial->image)}}">
                    </div>

                    <div class="col-sm-6">
                        <div class="row">
                            <div class='col-sm-12'>
                                Posted by <strong>{{$testimonial->firstname}} {{$testimonial->lastname}} </strong><br>
                                <span class='text-muted'>{{\Carbon\Carbon::parse($testimonial->created_at)->diffForHumans()}}</span>
                            </div>
                            <div class='col-sm-12 alert bg-secondary border shadow mt-2'>
                                {{nl2br($testimonial->testimonial)}}
                                <br>
                                @if($testimonial->status == 1)
                                    <span class="badge badge-dot mr-4">
                                        <i class="bg-success"></i> Activated
                                    </span>
                                @else
                                    <span class="badge badge-dot mr-4">
                                        <i class="bg-warning"></i> In Active
                                    </span>
                                @endif
                                <div class='text-right'>
                                @if($testimonial->status == 1)
                                    <a href="{{url('testimonial/deactivate/'.$testimonial->id)}}" class="btn btn-danger p-1 pr-2 pl-2" title='de-activate'>
                                        <i class='fas fa-ban'></i> De-activate
                                    </a>
                                @else
                                    <a href="{{url('testimonial/activate/'.$testimonial->id)}}" class="btn btn-success p-1 pr-2 pl-2" title='activate'>
                                        <i class='fas fa-sync-alt'></i> Activate
                                    </a>
                                @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>



<!-- Modal -->
<div class="modal fade" id="productImageModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header pb-0 mb-0">
                    <h4 class="modal-title" id="exampleModalLabel">Media Uploads</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body row">
                    <div class="col-sm-12 text-center">
                        <div id="upload-demo" class='d-none'></div>
                        <input type="file" id="upload" style='display: none;'>
                    </div>
                    <div class='col-sm-12 feedback'></div>
                </div>

                <div class="modal-footer" style='border: none;'>
                    <button type="button" class="btn btn-outline-primary upload">Choose Image</button><!--data-dismiss="modal"-->
                    <button type="button" class="btn btn-primary upload-result">Save Image</button>
                </div>
            </div>
        </div>
    </div>
@endsection
