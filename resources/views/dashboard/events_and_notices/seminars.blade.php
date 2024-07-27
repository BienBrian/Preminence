@extends('layouts.dashboard')

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h5 class="m-0 text-header"><i class='fas fa-calendar-alt'></i> Seminars</h5>
                </div><!-- /.col -->
                <div class="col-sm-6 text-right">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('home') }}">Home</a></li>
                        <li class="breadcrumb-item active">Seminars</li>
                    </ol>
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <!-- Small boxes (Stat box) -->
            <div class="row">
                <div class="col-xl-12 mb-5 mb-xl-0">
                    <div class="card shadow">
                        <div class="card-header border-0">
                            <div class="row align-items-center">
                                <!--
                                                    <div class="col">
                                                        <h3 class="mb-0"><i class="fas fa-calendar"></i> | Seminars</h3>
                                                    </div>-->
                                <div class="col text-right">
                                    <button class="btn btn-sm btn-primary btn-show-seminar" data-toggle="modal"
                                        data-target="#seminarModal"><i class='fas fa-circle-plus'></i> Add New</button>
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <!-- Projects table -->
                            <table class="table align-items-center table-flush">
                                <thead class="thead-light">
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">Title</th>
                                        <th scope="col">Theme</th>
                                        <th scope="col">Description</th>
                                        <th scope="col">Start</th>
                                        <th scope="col">End</th>
                                        <th scope="col">Location</th>
                                        <th scope="col">Cost</th>
                                        <th scope="col">Entry</th>
                                        <th scope="col">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if ($seminars->isEmpty())
                                        <tr>
                                            <td colspan='10' class='text-center'> <i class='fas fa-ban'></i> No seminars
                                                yet</td>
                                        </tr>
                                    @endif
                                    <?php $count = 1; ?>
                                    @foreach ($seminars as $seminar)
                                        <tr>
                                            <td>{{ $count }}</td>
                                            <td scope='row'>
                                                <div class="media align-items-center">
                                                    <img alt="Image placeholder"
                                                        src="{{ $seminar->banner == '' ? asset('website/homepage/default.jpg') : asset('seminar/' . $seminar->banner) }}"
                                                        height='40' width='40' class='mr-3'
                                                        style='border-radius: 50%;'>
                                                    <div class="media-body">
                                                        <span
                                                            class="mb-0 text-sm">{{ \Str::limit($seminar->title, $limit = 30, $end = '...') }}</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>{{ \Str::limit($seminar->theme, $limit = 30, $end = '...') }}</td>
                                            <td>{{ \Str::limit($seminar->description, $limit = 30, $end = '...') }}</td>
                                            <td>{{ \Carbon\Carbon::parse($seminar->start)->format('D d, M Y h:i A') }}</td>
                                            <td>{{ \Carbon\Carbon::parse($seminar->end)->format('D d, M Y h:i A') }}</td>
                                            <td>{{ \Str::limit($seminar->location, $limit = 15, $end = '...') }}</td>
                                            <td>{{ number_format($seminar->cost, 2) }}</td>
                                            <td>{{ number_format($seminar->entry, 2) }}</td>
                                            <td class='text-right' style="white-space: nowrap;">
                                                <a href="{{ $seminar->id }}"
                                                    class='btn btn-primary btn-sm edit-seminar'><i
                                                        class='fas fa-edit'></i></a>
                                                <!--<a href="{{ url('deleteseminar/' . $seminar->id) }}"
                                                                        class='btn btn-danger btn-sm'><i class='fas fa-trash'></i></a>-->
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
                        {{ $seminars->links() }}
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Modal -->
    <div class="modal fade" id="seminarModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel"><i class='fas fa-edit'></i> Seminar</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{ url('dashboard/events_and_notices/seminars/add') }}" method="post" class="row seminar-form"
                        enctype="multipart/form-data">
                        @csrf
                        <input type='hidden' name='id' value='0'>
                        <input type="file" name="banner" class="d-none" accept="image/*">

                        <div class='col-md-6 mt-1'>
                            <label>Title</label>
                            <input type="text" class="form-control" name="title" placeholder='Seminar&apos;s Title'
                                required>
                        </div>

                        <div class='col-md-6 mt-1'>
                            <label>Theme</label>
                            <input type="text" class="form-control" name="theme" placeholder='Seminar&apos;s Theme'
                                required>
                        </div>

                        <div class='col-md-6 mt-1'>
                            <label>Location</label>
                            <input type="text" class="form-control" name="location"
                                placeholder='Seminar&apos;s Location' required>
                        </div>

                        <div class='col-md-6 mt-1'>
                            <label>Budget</label>
                            <input type="number" class="form-control" name="budget"
                                placeholder='Seminar&apos;s Budget' required>
                        </div>

                        <div class='col-md-12 mt-1'>
                            <label>Entry Amount</label>
                            <input type="number" class="form-control" name="entry" placeholder='Entry Amount'
                                value="0.00" required>
                        </div>

                        <div class='col-md-6 mt-1'>
                            <label>Start Date</label>
                            <input name="startdate" class="form-control mydatepicker" placeholder="Start Date">
                        </div>

                        <div class='col-md-6 mt-1'>
                            <label>End Date</label>
                            <input name="enddate" class="form-control mydatepicker" placeholder="End Date">
                        </div>
                        <div class='col-md-12 mt-1'>
                            <label>Description</label>
                            <textarea name='description' class="form-control" placeholder='Seminar&apos;s Description' rows="4">Say Something</textarea>
                        </div>

                        <div class="col-sm-12 d-none banner mb-2">
                            <img src="{{ asset('sermon/default.png') }}" class="img-fluid" id="view-banner">
                        </div>

                        <div class="col-sm-12 text-right mb-2">
                            <a href="#" class="btn btn-outline-primary btn-seminar-banner">Upload banner</a>
                            <button type="submit" class="btn btn-primary btn-submit-seminar">Save Event</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('js')
    <script>
        $(document).ready(function() {

            flatpickr(".mydatepicker", {
                enableTime: true,
                dateFormat: "Y-m-d H:i",
                //defaultDate: new Date(),
            });
            //Seminar
            $('.btn-seminar-banner').click(function(e) {
                e.preventDefault();
                $('.seminar-form input[name="banner"]').click();
            });
            $('.seminar-form input[name="banner"]').change(function() {
                var input = $(this);
                var reader = new FileReader();

                reader.onload = function(e) {
                    $('#view-banner').attr('src', e.target.result);
                    $(".banner").removeClass("d-none");
                }

                reader.readAsDataURL(input[0].files[0]);
            });

            $('.edit-seminar').click(function(e) {
                e.preventDefault();
                var id = $(this).attr('href');
                $.ajax({
                    url: "{{ url('dashboard/events_and_notices/seminars') }}/" + id,
                    type: "GET",
                    success: function(data) {
                        var mydata = $.parseJSON(data);
                        if (mydata != null) {
                            $('.seminar-form input[name="id"]').val(mydata.id);
                            $('.seminar-form input[name="title"]').val(mydata.title);
                            $('.seminar-form input[name="theme"]').val(mydata.theme);
                            $('.seminar-form input[name="budget"]').val(mydata.cost);
                            $('.seminar-form input[name="entry"]').val(mydata.entry);
                            $('.seminar-form textarea[name="description"]').html(mydata
                                .description);
                            $('.seminar-form input[name="startdate"]').val(mydata.start);
                            $('.seminar-form input[name="enddate"]').val(mydata.end);
                            $('.seminar-form input[name="location"]').val(mydata.location);
                            $('.seminar-form img').attr('src', "/seminar/" + mydata.banner);
                            $(".seminar-form .btn-submit-seminar").html("Edit Seminar");
                            if (mydata.banner != "") {
                                $('.seminar-form img').closest('div').removeClass('d-none');
                            }
                            $('#seminarModal').modal('show');
                        } else {
                            toastr.error("Invalid request");
                        }
                    },
                    error: function(data) {
                        toastr.error("error");
                    }
                });
            });
        });
    </script>
@endpush
