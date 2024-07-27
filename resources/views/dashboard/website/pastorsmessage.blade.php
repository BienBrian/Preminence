@extends('layouts.dashboard')

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2 d-flex align-items-center">
                <div class="col">
                    <h5 class="m-0 text-header"><i class='fas fa-church'></i> Pastor's Message</h5>
                </div>
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-xl-12 mb-5 mb-xl-0">
                    <div class="card bgshadow">
                        <div class="card-header">
                            <div class="row align-items-center">
                                <!--
                                        <div class="col">
                                            <h3 class="mb-0">Pastor's Message</h3>
                                        </div>-->
                                <div class="col text-right">
                                    <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#imageModal"><i
                                            class='fas fa-camera-retro'></i> Add Image</button>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">

                            <!-- Projects table -->
                            <form method="POST" action="{{ url('dashboard/website/pastorsmessage/add') }}"
                                class='row d-flex align-items-center'>
                                @csrf
                                <div class="d-none d-sm-block col-sm-4">
                                    <img src="{{ $message == null ? asset('website/default.png') : ($message->image == '' ? asset('website/default.png') : asset('website/pastors/' . $message->image)) }}"
                                        class="img-fluid">
                                </div>
                                <div class='form-group col-sm-8'>
                                    <input type="hidden" name='id'
                                        value="{{ $message == null ? '0' : $message->id }}" />
                                    <label>Message Title</label>
                                    <input name='title' class='form-control' placeholder='Message Title'
                                        value="{{ $message == null ? '' : $message->title }}" autocomplete="off" required>
                                    <label class='mt-3'>Message Description</label>
                                    <textarea name='description' class='form-control summernote' rows = "8" placeholder='Message Description'>{{ $message == null ? '' : $message->description }}</textarea>
                                </div>
                                <div class='form-group col-sm-12 text-right'>
                                    <button class='btn btn-primary'>Update</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <!-- Add image Modal -->
    <div class="modal fade" id="imageModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header" style='border: none;'>
                    <h5 class="modal-title" id="exampleModalLabel"><i class='fas fa-camera'></i> | Message Image
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true"><i class='fas fa-times'></i></span>
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
                    <button type="button" class="btn btn-outline-primary upload">Choose
                        Image</button><!--data-dismiss="modal"-->
                        <button type="button" class="btn btn-primary upload-result">Save Image</button>
                </div>
            </div>
        </div>
    </div>
    <!-- -->
@endsection
@push('js')
    <script>
        $(document).ready(function() {
            //cropping function
            $('#imageModal').on('shown.bs.modal', function(e) {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                var parentW = $('#upload-demo').closest('.col-sm-12').width();
                var vwidth = parentW;
                var vheight = vwidth * 3 / 4;

                $uploadCrop = $('#upload-demo').croppie({
                    enableExif: true,
                    viewport: {
                        width: vwidth * .9,
                        height: vheight * .9,
                        type: 'canvas'
                    },

                    boundary: {
                        width: parentW,
                        height: (parentW) * 4 / 4
                    }
                });
                $('.upload').click(function() {
                    $('#upload').click();
                });

                $('#upload').on('change', function() {
                    $('.upload-image').removeAttr('disabled');
                    $('#upload-demo').removeClass('d-none');
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        $uploadCrop.croppie('bind', {
                            url: e.target.result
                        }).then(function() {
                            console.log('jQuery bind complete');
                        });
                    }
                    reader.readAsDataURL(this.files[0]);
                });

                $('.upload-result').on('click', function(ev) {
                    $uploadCrop.croppie('result', {
                        type: 'canvas',
                        size: 'viewport'
                    }).then(function(resp) {
                        $('.feedback').html(
                            "<p class='text-center text-info'><i class='fas fa-spinner fa-pulse'></i> Saving... Please wait</p>"
                        );
                        $.ajax({
                            url: "{{ url('dashboard/website/pastorsmessage/image/upload') }}",
                            type: "POST",
                            data: {
                                "image": resp
                            },
                            success: function(data) {
                                try {
                                    json = $.parseJSON(data);
                                } catch (e) {
                                    // not json
                                }
                                $('.feedback').html(data.success);
                                window.location.reload();
                            },
                            error: function() {
                                $('.feedback').html(
                                    "<p class='text-center text-danger'><i class='fas fa-warning'></i> Unable to save image</p>"
                                );
                            }
                        });
                    });
                });
            });
        });
    </script>
@endpush
