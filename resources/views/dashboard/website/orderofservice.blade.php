@extends('layouts.dashboard')

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2 d-flex align-items-center">
                <div class="col">
                    <h5 class="m-0 text-header"><i class='fas fa-clock'></i> Order of Service</h5>
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
                                    <h3 class="mb-0">Order of Services</h3>
                                </div>-->
                                <div class="col text-right">
                                    <button class="btn btn-sm btn-primary showServiceModal" data-toggle="modal"
                                        data-target="#services"><i class='fas fa-plus-circle'></i> New</button>
                                </div>
                            </div>
                        </div>
                        <div class="card-body mb-0 p-0">
                            <!-- Projects table -->
                            <div class='table-responsive'>
                                <table class="table align-items-center table-flush">
                                    <thead class='thead-light'>
                                        <tr>
                                            <th scope="col">#</th>
                                            <th scope="col">Description</th>
                                            <th scope="col">Venue</th>
                                            <th scope="col">Day</th>
                                            <th scope="col">Time</th>
                                            <th scope="col" class="text-right">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if ($services->count() == 0)
                                            <tr>
                                                <td colspan='5' class='text-center'>
                                                    <i class='fas fa-ban'></i> No items yet
                                                </td>
                                            </tr>
                                        @endif
                                        <?php $count = 1; ?>
                                        @foreach ($services as $service)
                                            <tr>
                                                <td>{{ $count }}</td>
                                                <td scope="row">{{ strip_tags($service->description) }}</td>
                                                <td scope="row">{{ $service->venue }}</td>
                                                <td scope="row">{{ $service->day }}</td>
                                                <td scope="row">{{ $service->time }}</td>
                                                <td class="text-right">
                                                    <a href="{{ $service->id }}"
                                                        class='btn text-primary btn-sm btn-edit-service'
                                                        title='Edit'><i class='fas fa-edit'></i></a>
                                                    <a href="{{ url('dashboard/website/service/remove/' . $service->id) }}"
                                                        class='btn text-danger btn-sm' title='Delete'><i
                                                            class='fas fa-trash'></i></a>
                                                </td>
                                            </tr>
                                            <?php $count++; ?>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Modal -->
    <div class="modal fade" id="services" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="exampleModalLabel">Services</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body pt-0 pb-0">
                    <form action="{{ url('dashboard/website/service/add') }}" method="post" class='service-form'>
                        @csrf
                        <input type="hidden" name="id" value="0">
                        <div class='form-group mt-2 mb-0 pb-0'>
                            <label><small>Venue:</small></label>
                            <input class='form-control' name="venue" placeholder="Venue" required>
                        </div>
                        <div class='form-group mt-2 mb-0 pb-0'>
                            <label><small>Day:</small></label>
                            <select class='custom-select' name="days">
                                <option value="Sunday">Sunday</option>
                                <option value="Monday">Monday</option>
                                <option value="Tuesday">Tuesday</option>
                                <option value="Wednessday">Wednessday</option>
                                <option value="Thurday">Thursday</option>
                                <option value="Friday">Friday</option>
                                <option value="Saturday">Saturday</option>
                            </select>
                        </div>
                        <div class='form-group mt-2 mb-0 pb-0'>
                            <label><small>time range:</small></label>
                            <input class='form-control' name="time" placeholder="Time range" required>
                        </div>
                        <div class='form-group mt-2 mb-0 pb-0'>
                            <label><small>Description:</small></label>
                            <textarea class='form-control' name="description" rows="3" placeholder="Enter description" required></textarea>
                        </div>
                        <div class="form-group mt-2 text-right">
                            <button type="submit" class="btn btn-primary btn-submit-service">Save
                                changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="container gallery-view d-none">
        <div class="row d-flex align-items-center">
            <div class='col-sm-12 text-right'>
                <a href="#" class='btn text-white btn-close-gallery'><i class='fas fa-times fa-2x'></i></a>
            </div>
            <div class='col-sm-12'>
                <img src='' class='img-fluid' style='max-height: 90vh'>
            </div>
        </div>
    </div>
@endsection
@push('js')
<script>
    $(document).ready(function(){

        $('.showServiceModal').click(function(){
            $('#services input[name="id"]').val(0);
            $('#services input[name="time"]').val("");
            $('#services textarea[name="description"]').val("");
            $('#services select[name="days"]').val("Sunday");
            $('#services input[name="venue"]').val("");
            $('#services .modal-body .btn').text("Add Service");
        });
        //fundstype edit
        $('.btn-edit-service').click(function(e){
            e.preventDefault();
            var id = $(this).attr('href');
            var time = $(this).closest('tr').find('td:nth-child(5)').text();
            var description = $(this).closest('tr').find('td:nth-child(2)').text();
            var venue = $(this).closest('tr').find('td:nth-child(3)').text();
            $('#services input[name="id"]').val(id);
            $('#services input[name="time"]').val(time);
            $('#services textarea[name="description"]').val(description);
            $("input[name='venue']").val(venue);
            $('#services').modal('show');
            $('#services .modal-body .btn-primary').text("Edit Service");
        });
    });
</script>

@endpush
