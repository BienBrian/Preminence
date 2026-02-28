@extends('layouts.dashboard')

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h5 class="m-0 text-header"><i class='fas fa-campground'></i> <b>Activities</b></h5>
                </div><!-- /.col -->
                <div class="d-none d-sm-block col-sm-6 text-right">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('dashboard/home') }}">Home</a></li>
                        <li class="breadcrumb-item active">Activities</li>
                    </ol>
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class='row'>
                <div class="col-xl-12 mb-5 mb-xl-0">
                    <div class="card shadow">
                        <div class="card-header border-0">
                            <div class="row align-items-center">
                                <!--
                                    <div class="col">
                                        <h3 class="mb-0"><i class="fas fa-coins"></i> | Activities</h3>
                                    </div>-->
                                <div class="col text-right">
                                    <button class="btn btn-primary btn-sm" data-toggle="modal"
                                        data-target="#activityModal"><i class='fas fa-circle-plus'></i> New</button>
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <!-- Projects table -->
                            <table class="table align-items-center table-flush">
                                <thead class="thead-light">
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">Name</th>
                                        <th scope="col">Description</th>
                                        <th scope="col">Deadline</th>
                                        <th class='text-right'>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if ($activities->isEmpty())
                                        <tr>
                                            <td colspan='6' class='text-center'> <i class='fas fa-ban'></i> No activities
                                                yet</td>
                                        </tr>
                                    @endif
                                    <?php $count = 1; ?>
                                    @foreach ($activities as $activity)
                                        <tr>
                                            <td>{{ $count }}</td>
                                            <td>{{ $activity->name }}</td>
                                            <td>{{ \Str::limit($activity->description, $limit = 30, $end = '...') }}</td>
                                            <td>
                                                @if (\Carbon\Carbon::parse($activity->closes_on) > \Carbon\Carbon::now())
                                                    <span
                                                        class='text-success font-weight-bold'>{{ \Carbon\Carbon::parse($activity->closes_on)->diffInDays(\Carbon\Carbon::now()) }}
                                                        day(s) remaining</span>
                                                @else
                                                    <span
                                                        class='text-danger font-weight-bold'>{{ \Carbon\Carbon::parse($activity->closes_on)->format('d M, Y') }}</span>
                                                @endif
                                            </td>
                                            <td class='text-right'>
                                                <a href="{{ url('dashboard/finances/activities/pledges/' . $activity->id) }}"
                                                    class='btn btn-default btn-sm'>pledges</a>
                                                <a href="{{ $activity->id }}"
                                                    class='btn btn-primary btn-sm btn-edit-activity'>Edit</a>
                                                <a href="#" onclick="return postAction('{{ url('dashboard/finances/activities/remove/' . $activity->id) }}', 'Delete this activity?')"
                                                    class='btn btn-danger btn-sm'>Delete</a>
                                            </td>
                                        </tr>
                                        <?php $count++; ?>
                                    @endforeach
                                </tbody>
                                </thead>
                            </table>
                        </div>
                    </div>
                    <div class="col-sm-12 mt-3">
                        {{ $activities->links() }}
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Modal -->
    <div class="modal fade" id="activityModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header pb-0 mb-0">
                    <h4 class="modal-title" id="exampleModalLabel">Activities</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body pt-1 pb-0 mb-0">
                    <form action="{{ url('dashboard/finances/activities/add') }}" method="post" class="activity-form">
                        @csrf
                        <input type='hidden' name='id' value='0'>

                        <div class='form-group'>
                            <label>Name</label>
                            <input type="text" class="form-control" name="name" placeholder='Activity Name' required>
                        </div>
                        <div class='form-group'>
                            <label>Amount</label>
                            <input name="amount" class="form-control" placeholder="Amount">
                        </div>
                        <div class='form-group'>
                            <label>Deadline</label>
                                <input name="date" class="form-control datepicker" placeholder="Activity Deadline"
                                    readonly>
                        </div>
                        <div class='form-group'>
                            <label>Description</label>
                                <textarea name='description' class="form-control" placeholder='Activity Description' rows="4">Say Something</textarea>
                        </div>

                        <div class="form-group text-right">
                            <button type="submit" class="btn btn-primary btn-submit-activity">Save Activity</button>
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
            flatpickr(".datepicker", {
                enableTime: false,
                dateFormat: "Y-m-d",
                defaultDate: new Date(),
            });
            $('.btn-show-activity').click(function() {
                $('.activity-form input[name="id"]').val("0");
                $('.activity-form input[name="name"]').val("");
                $('.activity-form input[name="amount"]').val("");
                $('.activity-form input[name="date"]').val("");
                $('.activity-form textarea[name="description"]').val("Say something");

                $('.btn-submit-activity').removeAttr('disabled');
                $('.btn-submit-activity').html("Add Activity");
            });
            $('.btn-edit-activity').click(function(e) {
                e.preventDefault();
                var id = $(this).attr('href');
                $('.btn-submit-activity').attr('disabled', 'disabled');
                $('.btn-submit-activity').html('<i class="fas fa-spinner fa-pulse"></i> Loading...');
                $('#activityModal').modal('show');
                $.ajax({
                    url: "{{ url('dashboard/getactivity') }}/" + id,
                    type: "GET",
                    success: function(data) {
                        var mydata = $.parseJSON(data);
                        if (mydata != null) {
                            $('.activity-form input[name="id"]').val(mydata.id);
                            $('.activity-form input[name="name"]').val(mydata.name);
                            $('.activity-form input[name="amount"]').val(mydata.amount);
                            $('.activity-form input[name="date"]').val(mydata.closes_on);
                            $('.activity-form textarea[name="description"]').val(mydata
                                .description);
                            $('.btn-submit-activity').removeAttr('disabled');
                            $('.btn-submit-activity').html("Edit Activity");
                        } else {
                            alert("invalid request!")
                        }
                    },
                    error: function(data) {
                        alert("error");
                    }
                });
            });
        })
    </script>
@endpush
