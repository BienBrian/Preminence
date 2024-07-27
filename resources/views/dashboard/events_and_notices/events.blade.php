@extends('layouts.dashboard')

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h5 class="m-0 text-header"><i class='fas fa-calendar-alt'></i> Events</h5>
                </div><!-- /.col -->
                <div class="col-sm-6 text-right">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('home') }}">Home</a></li>
                        <li class="breadcrumb-item active">Events</li>
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
                                <!--<div class="col">
                                                            <h3 class="mb-0"><i class="fas fa-bell"></i> | Events</h3>
                                                        </div>-->
                                <div class="col text-right">
                                    <button class="btn btn-sm btn-primary btn-show-event" data-toggle="modal"
                                        data-target="#eventsModal"><i class='fas fa-circle-add'></i> Add New</button>
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
                                        <th scope="col">Date</th>
                                        <th scope="col">Time</th>
                                        <th scope="col">Location</th>
                                        <th scope="col">Cost</th>
                                        <th scope="col">Entry</th>
                                        <th scope="col">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if ($events->isEmpty())
                                        <tr>
                                            <td colspan='7' class='text-center'> <i class='fas fa-ban'></i> No events yet
                                            </td>
                                        </tr>
                                    @endif
                                    <?php $count = 1; ?>
                                    @foreach ($events as $event)
                                        <tr>
                                            <td>{{ $count }}</td>
                                            <td scope='row'>
                                                <div class="media align-items-center">
                                                    <img alt="Image placeholder"
                                                        src="{{ $event->banner == '' ? asset('website/homepage/default.jpg') : asset('event/' . $event->banner) }}"
                                                        height='40' width='40' class='mr-3'
                                                        style='border-radius: 50%;'>
                                                    <div class="media-body">
                                                        <span
                                                            class="mb-0 text-sm">{{ \Str::limit($event->title, $limit = 30, $end = '...') }}</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>{{ \Str::limit($event->theme, $limit = 30, $end = '...') }}</td>
                                            <td>{{ \Str::limit($event->description, $limit = 30, $end = '...') }}</td>
                                            <td>{{ \Carbon\Carbon::parse($event->eventdate)->format('d, M Y') }}</td>
                                            <td>{{ \Carbon\Carbon::parse($event->time)->format('H:i:s') }}</td>
                                            <td>{{ \Str::limit($event->location, $limit = 15, $end = '...') }}</td>
                                            <td>{{ number_format($event->cost, 2) }}</td>
                                            <td>{{ number_format($event->entry, 2) }}</td>
                                            <td class='text-right' style='white-space: nowrap;'>
                                                <a href="{{ $event->id }}" class='btn btn-primary btn-sm edit-event'><i
                                                        class='fas fa-edit'></i></a>&nbsp;
                                                <a href="{{ url('dashboard/events_and_seminars/events/delete/' . $event->id) }}"
                                                    class='btn btn-danger btn-sm'><i class='fas fa-trash'></i></a>
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
                        {{ $events->links() }}
                    </div>
                </div>
            </div>
        </div>
    </section>


    <!-- Modal -->
    <div class="modal fade" id="eventsModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel"><i class='fas fa-calendar'></i> Events</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body pt-1 pb-0 mb-0 border-top">
                    <form action="{{ url('dashboard/events_and_notices/events/add') }}" method="post"
                        class="row event-form" enctype="multipart/form-data">
                        @csrf
                        <input type='hidden' name='id' value='0'>
                        <input type='hidden' name='eventtype' value='0'>
                        <input type="file" name="banner" class="d-none" accept="image/*">

                        <div class='col-md-6 mt-1'>
                            <label>Title</label>
                            <input type="text" class="form-control" name="title" placeholder='Event Title' required>
                        </div>

                        <div class='col-md-6 mt-1'>
                            <label>Theme</label>
                            <input type="text" class="form-control" name="theme" placeholder='Event Theme'
                                required>
                        </div>

                        <div class='col-md-6 mt-1'>
                            <label>Location</label>
                            <input type="text" class="form-control" name="location" placeholder='Event Location'
                                required>
                        </div>

                        <div class='col-md-6 mt-1'>
                            <label>Budget</label>
                            <input type="number" class="form-control" name="budget" placeholder='Event&apos;s Budget'
                                required>
                        </div>

                        <div class='col-md-6 mt-1'>
                            <label>Entry Amount</label>
                            <input type="number" class="form-control" name="entry" placeholder='Entry Amount'
                                value="0.00" required>
                        </div>

                        <div class='col-md-6 mt-1'>
                            <label>Date</label>
                            <input name="date" class="form-control mydatepicker" placeholder="Event Date">
                        </div>
                        <div class='col-md-12 mt-1'>
                            <label>Description</label>
                            <textarea name='description' class="form-control" placeholder='Event Description' rows="4">Say Something</textarea>
                        </div>

                        <div class="col-sm-12 d-none banner mb-2">
                            <img src="{{ asset('sermon/default.png') }}" class="img-fluid" id="view-banner">
                        </div>

                        <div class="col-sm-12 text-right mb-2">
                            <a href="#" class="btn btn-outline-primary btn-event-banner">Upload banner</a>
                            <button type="submit" class="btn btn-primary btn-submit-events">Save Event</button>
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
            $('.btn-show-event').click(function() {
                $('.event-form input[name="id"]').val("0");
                $('.event-form input[name="title"]').val("");
                $('.event-form textarea[name="description"]').html("");
                $('.event-form input[name="date"]').val("");
                $('.event-form input[name="location"]').val("");
                $('.event-form img').attr('src', "");
                $('.event-form img').closest('div').addClass('d-none');
                $(".event-form .btn-submit-events").html("Add Event");
            });
            $('.btn-event-banner').click(function(e) {
                e.preventDefault();
                $('.event-form input[name="banner"]').click();
            });
            $('.event-form input[name="banner"]').change(function() {
                var input = $(this);
                var reader = new FileReader();

                reader.onload = function(e) {
                    $('#view-banner').attr('src', e.target.result);
                    $(".banner").removeClass("d-none");
                }

                reader.readAsDataURL(input[0].files[0]);
            });
            $('.edit-event').click(function(e) {
                e.preventDefault();
                var id = $(this).attr('href');
                $.ajax({
                    url: "{{ url('dashboard/events_and_notices/events') }}/" + id,
                    type: "GET",
                    success: function(data) {
                        var mydata = $.parseJSON(data);
                        if (mydata != null) {
                            $('.event-form input[name="id"]').val(mydata.id);
                            $('.event-form input[name="title"]').val(mydata.title);
                            $('.event-form input[name="theme"]').val(mydata.theme);
                            $('.event-form input[name="budget"]').val(mydata.cost);
                            $('.event-form input[name="entry"]').val(mydata.entry);
                            $('.event-form textarea[name="description"]').html(mydata
                                .description);
                            $('.event-form input[name="date"]').val(mydata.eventdate + " " +
                                mydata.time);
                            $('.event-form input[name="location"]').val(mydata.location);
                            $('.event-form img').attr('src', "/event/" + mydata.banner);
                            $(".event-form .btn-submit-events").html("Edit Event");
                            if (mydata.banner != "") {
                                $('.event-form img').closest('div').removeClass('d-none');
                            }
                            $('#eventsModal').modal('show');
                        } else {
                            toastr.error("Invalid request");
                        }
                    },
                    error: function(data) {
                        toastr.error("Whoops! Something went wrong with the server!");
                    }
                });
            });
        });
    </script>
@endpush
