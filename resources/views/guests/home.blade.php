@extends('layouts.guest')

@section('content')
<!-- Header -->
<div class="header bg-gradient-primary pb-8 pt-5 pt-md-8">
    <div class="container-fluid">
        <div class="header-body">
            <!-- Card stats -->
            <div class="row">
                <div class="col-xl-6 col-lg-6">
                    <div class="card card-stats mb-4 mb-xl-0">
                        <div class="card-body">
                            <div class="row">
                                <div class="col">
                                    <h5 class="card-title text-uppercase text-muted mb-0">Seminars</h5>
                                    <span class="h2 font-weight-bold mb-0">{{$seminars}}</span>
                                </div>
                                <div class="col-auto">
                                    <div class="icon icon-shape bg-danger text-white rounded-circle shadow">
                                        <i class="fas fa-chart-bar"></i>
                                    </div>
                                </div>
                            </div>
                            <p class="mt-3 mb-0 text-muted text-sm">
                                <span class="text-nowrap">Greatful you joined us</span>
                            </p>
                        </div>
                    </div>
                </div>
            <div class="col-xl-6 col-lg-6">
                <div class="card card-stats mb-4 mb-xl-0">
                    <div class="card-body">
                        <div class="row">
                            <div class="col">
                                <h5 class="card-title text-uppercase text-muted mb-0">Events</h5>
                                <span class="h2 font-weight-bold mb-0">{{$events}}</span>
                            </div>
                            <div class="col-auto">
                                <div class="icon icon-shape bg-warning text-white rounded-circle shadow">
                                    <i class="fas fa-arrow-down"></i>
                                </div>
                            </div>
                        </div>
                        <p class="mt-3 mb-0 text-muted text-sm">
                            <span class="text-nowrap">Greatful you joined us</span>
                        </p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
</div>


<!-- Page content -->
<div class="container-fluid mt--5">
    <div class="row">
        <div class="col-xl-12 mb-5 mb-xl-0">
            <div class="card shadow">
                <div class="card-header">
                    <h3 class="mb-0"><i class="fas fa-coins"></i> | Thank you for registering.</h3>
                </div>
                <div class="card-body">
                    Make sure you make payments for paid events in time.
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="activityModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header pb-0 mb-0">
                        <h4 class="modal-title" id="exampleModalLabel">Events</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body pt-1 pb-0 mb-0">
                        <form action="/addactivity" method="post" class="activity-form">
                            @csrf
                            <input type='hidden' name='id' value='0'>

                            <div class='form-group'>
                                <label><small>Name</small></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="basic-addon2"><i class='fas fa-angle-right text-primary'></i></span>
                                    </div>
                                    <input type="text" class="form-control" name="name" placeholder='Activity Name' required>
                                </div>
                            </div>
                            <div class='form-group'>
                                <label><small>Amount</small></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="basic-addon2"><i class='fas fa-dollar-sign text-primary'></i></span>
                                    </div>
                                    <input name="amount" class="form-control" placeholder="Amount">
                                </div>
                            </div>
                            <div class='form-group'>
                                <label><small>Deadline</small></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="basic-addon2"><i class='fas fa-calendar-alt text-primary'></i></span>
                                    </div>
                                    <input name="date" class="form-control datepicker" placeholder="Activity Deadline" readonly>
                                </div>
                            </div>
                            <div class='form-group'>
                                <label><small>Description</small></label>
                                <div class="input-group mb-3">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="basic-addon2"><i class='fas fa-comments text-primary'></i></span>
                                    </div>
                                    <textarea name='description' class="form-control" placeholder='Activity Description' rows="4">Say Something</textarea>
                                </div>
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
