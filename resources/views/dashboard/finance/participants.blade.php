@extends('layouts.dashboard')

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h5 class="m-0 text-header"><i class='fas fa-users'></i> Group <b>Participants</b></h5>
                </div><!-- /.col -->
                <div class="d-none d-sm-block col-sm-6 text-right">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('home') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ url('dashboard/finances/activities') }}">Activities</a></li>
                        <li class="breadcrumb-item active">Participants</li>
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

                    <div class="card shadow mb-3">
                        <div class="card-header border-0">
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="#">{{ $group->activity }}</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">{{ $group->name }}</li>
                                </ol>
                            </nav>
                        </div>
                        <table class='table'>
                            <tr>
                                <td>Pledged:</td>
                                <td>{{ number_format($group->amount, 2) }}</td>
                            </tr>
                            <tr>
                                <td>Paid:</td>
                                <td>{{ number_format($group->paid, 2) }}</td>
                            </tr>
                            <tr>
                                <td>Balance:</td>
                                <td>{{ number_format($group->amount - $group->paid, 2) }}</td>
                            </tr>
                        </table>
                    </div>

                    <div class="card shadow">
                        <div class="card-header border-0">
                            <div class="row align-items-center">
                                <div class="col">
                                    <!--<h3 class="mb-0">Group Name: {{ $group->name }}</h3>-->
                                </div>
                                <div class="col text-right">
                                    <button class='btn btn-primary btn-sm btnshowgroupusers'>Add Members</button>
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <!-- Projects table -->
                            <table class="table align-items-center table-flush">
                                <thead class="thead-light">
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">Members</th>
                                        <th scope="col">Activity</th>
                                        <th scope="col">Amount</th>
                                        <th scope="col">Paid</th>
                                        <th scope="col">Balance</th>
                                        <th scope="col" class='text-right'>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if ($participants->isEmpty())
                                        <tr>
                                            <td colspan='6' class='text-center'> <i class='fas fa-ban'></i> No
                                                participants yet</td>
                                        </tr>
                                    @endif
                                    <?php $count = 1; ?>
                                    @foreach ($participants as $participant)
                                        <tr>
                                            <td>{{ $count }}</td>
                                            <td>{{ $participant->firstname . ' ' . $participant->lastname }}</td>
                                            <td scope='row'>{{ $participant->name }}</td>
                                            <td>{{ number_format($participant->amount, 2) }}</td>
                                            <td>{{ number_format($participant->paid, 2) }}</td>
                                            <td>{{ number_format($participant->amount - $participant->paid, 2) }}</td>
                                            <td class='text-right'>
                                                <a href="{{ $participant->id }}"
                                                    class="btn btn-outline-primary recieved btn-sm">Pay</a>
                                                <a href="{{ url('dashboard/pledges/remove/' . $participant->id) }}"
                                                    class="btn btn-danger btn-sm">Delete</a>
                                            </td>
                                        </tr>
                                        <?php $count++; ?>
                                    @endforeach
                                </tbody>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Pay Pledge Modal -->
    <div class="modal fade" id="payPledgeModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title" id="exampleModalLabel"><i class="fas fa-coins"></i> Payments Modal</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body pt-3 pb-0 mb-0">
                    <form action='{{ url('dashboard/pledge/pay') }}' method='post' class="row">
                        <input type="hidden" name="id" value="0">
                        @csrf
                        <div class=" col-sm-12 form-group">
                            <label>Member Name:</label>
                                <input type='text' class="form-control" name='name' placeholder="+254791162496"
                                    disabled />
                        </div>
                        <div class='col-sm-12'>
                            <table class='table m-0'>
                                <tr>
                                    <td class='border-0 p-1'>Amount:</td>
                                    <td class='border-0 p-1'><strong class='amount'><i class='fas fa-spinner fa-pulse'></i>
                                            updating</strong></td>
                                </tr>
                                <tr>
                                    <td class='border-0 p-1'>Paid:</td>
                                    <td class='border-0 p-1'><strong class='paid'><i class='fas fa-spinner fa-pulse'></i>
                                            updating</strong></td>
                                </tr>
                                <tr class='border-bottom'>
                                    <td class='border-0 p-1'>Balance:</td>
                                    <td class='border-0 p-1'><strong class='balance'><i
                                                class='fas fa-spinner fa-pulse'></i> updating</strong></td>
                                </tr>
                            </table>
                            <input type='hidden' name='amount' class="form-control" placeholder="amount" disabled>
                            <input type='hidden' name='paid' class="form-control" placeholder="amount" disabled>
                            <input type='hidden' name='balance' class="form-control" placeholder="amount" disabled>
                        </div>
                        <div class="col-sm-12 form-group mt-3">
                            <label>Amount being paid</label>
                                <input type='number' name='payments' class="form-control"
                                    placeholder="Enter amount here">
                        </div>
                        <div class="col-sm-12 form-group text-right">
                            <button type='submit' class="btn btn-primary btn-pay">Save Payments</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="pledgegroups" style="background-color: rgba(0,0,0,.5);">
        <div class="message-div row">
            <div class='col-sm-12 bg-gradient-primary p-4 text-white mheader' style='max-height: 10vh;'>
                <button class='btn text-white close-message'
                    style='background: transparent; sbox-shadow: none; position: absolute; top: 10px; right: 30px;'><i
                        class='fas fa-times'></i></button>
                New Message
            </div>
            <div class="col-sm-12 p-4">
                <form action="{{ url('dashboard/finances/activities/groups/pledges/members/add') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label>Search Members</label>
                        <input name="search" type="text" class="form-control" placeholder="Search">
                    </div>

                    <div class='escroll'>
                        <div class="results mt-2">
                            <p class='small text-muted'><i class="fas fa-spinner fa-pulse"></i> Please Wait</p>
                        </div>
                        <input type='hidden' name='limit' value='0'>
                        <input type='hidden' name='group_id' value='{{ $group->id }}'>

                        <p class='small font-weight-bold lazy'><i class='fas fa-spinner fa-pulse'></i> Loading...
                        </p>
                    </div>
                    <div class='row'>

                        <div class='pl-3 pr-3 text-center'>
                            <div class='p-2 text-right'>
                                <button class='btn btn-primary'>Add Members</button>
                            </div>
                            <!--<div class="custom-control custom-checkbox mb-3 mt-3">
                                            <input name="notify" class="custom-control-input" id="notify" type="checkbox" value="1" checked>
                                            <label class="custom-control-label text-primary" for="notify">Notify members about pledges via SMS</label>
                                        </div>-->
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@push('js')
    <script>
        $(document).ready(function() {

            var timer = null;
            allowscroll();

            function allowscroll() {
                $('.pledgesusers .escroll').on('scroll', function(e) {
                    if ($(this).scrollTop() + $(this).innerHeight() + 1 >= $(this)[0].scrollHeight) {
                        loadmore();
                    }
                });
            }

            function loadmore() {
                $('.escroll').unbind();
                $('.lazy').show();
                var search = $('.pledgesusers input[name="search"]').val();
                var limit = parseInt($('.escroll input[name="limit"]').val()) + 10;
                $.ajax({
                    url: "{{ url('dashboard/ajax/pledges/users') }}?search=" + search + "&limit=" + limit,
                    type: 'GET',
                }).done(function(data) {
                    displayContacts(data);
                    var objects = JSON.parse(data);
                    if (objects.length < 10) {
                        $('.lazy').html("No more contacts");
                    } else {
                        $('.escroll input[name="limit"]').val(limit);
                        allowscroll();
                    }
                }).fail(function() {
                    $('.lazy').html(
                        "<span class='text-danger'><i class='fas fa-exclamation-triangle'></i> Something went <strong>wrong</strong></span>"
                        );
                });
            }


    $('a.recieved').click(function(e){
        e.preventDefault();
        var id = $(this).attr('href');
        $('#payPledgeModal').modal('show');
        $('#payPledgeModal .modal-body .btn-pay').attr("disabled", "disabled");
        $('#payPledgeModal .modal-body .btn-pay').html("<i class='fas fa-circle-notch fa-spin'></i> Loading...");
        $.ajax({
            url: "{{url('dashboard/ajax/pledge')}}/" + id,
            type: "GET",
        }).done(function(data) {
            var objects = JSON.parse(data);
            $('#payPledgeModal .modal-body .btn-pay').html("<i class='fas fa-paper-plane'></i> Save Payments");
            if (objects != null) {
                var balance = parseFloat(objects.amount) - parseFloat(objects.paid);
                $('#payPledgeModal input[name="id"]').val(id);
                $('#payPledgeModal input[name="name"]').val(objects.firstname+" "+objects.lastname);
                $('#payPledgeModal input[name="amount"]').val(objects.amount);
                $('#payPledgeModal input[name="paid"]').val(objects.paid);
                $('#payPledgeModal input[name="balance"]').val(balance);
                $('.paid').html((objects.paid).toLocaleString());
                $('.amount').html((objects.amount).toLocaleString());
                $('.balance').html((balance).toLocaleString());
                $('#payPledgeModal .modal-body .btn-pay').removeAttr('disabled');
            } else {
                swal({
                    text: "Invalid Request",
                    icon: "error"
                });
            }
        }).fail(function(data) {
            $('#pledgeModal .modal-body .btn-submit-pledge').html("<i class='fas fa-paper-plane'></i> Edit Pledge");
            swal({
                text: "Unable to complete request",
                icon: "error"
            });
        });

    });

            //Groups Pledges
            $('.pledgegroups .close-message').click(function() {
                $('.pledgegroups').fadeOut('slow');
            });
            $('.btnshowgroupusers').click(function() {
                defaultGroupUsers();
                $('.pledgegroups').fadeIn('slow');
            });

            function defaultGroupUsers() {
                var search = $('.pledgegroups input[name="search"]').val();
                $.ajax({
                    url: "{{ url('dashboard/ajax/groups/users') }}?search=" + search,
                    type: "GET",
                }).done(function(data) {
                    $('.pledgegroups .results').html("");
                    displayGroupUsers(data);
                }).fail(function(data) {
                    $('.pledgesusers .results').html(
                        "<div class='text-danger'><i class='fas fa-exclamation-circle'></i> <strong>Error</strong> loading contacts</div>"
                    );
                });
            }

            $('.pledgegroups input[name="search"]').keyup(function() {
                if (timer != null)
                    clearTimeout(timer);

                timer = setTimeout(function() {
                    var search = $('.pledgegroups input[name="search"]').val();
                    $('.escroll input[name="limit"]').val(0);
                    $('.pledgegroups .results').html("");
                    $('.lazy').html("<i class='fas fa-spinner fa-pulse'></i> Loading...");
                    $('.lazy').show();
                    allowscroll();
                    $.ajax({
                        url: "{{ url('dashboard/ajax/groups/users') }}?search=" + search,
                        method: "GET",
                    }).done(function(data) {
                        displayGroupUsers(data);
                    }).fail(function() {
                        $('.pledgegroups .results').html(
                            "<div class='text-danger'><i class='fas fa-exclamation-circle'></i> <strong>Error</strong> loading contacts</div>"
                        );
                    });
                }, 1000);
            });

            function displayGroupUsers(data) {
                var objects = JSON.parse(data);
                for (var i = 0; i < objects.length; i++) {
                    $('.pledgegroups .results').append("<div class='bg-white mt-2'>" +
                        "<div class='custom-control custom-control-alternative custom-checkbox mb-3'>" +
                        "<input class='custom-control-input' name='contacts[]' id='" + objects[i].id +
                        "' value='" + objects[i].id + "' type='checkbox'>" +
                        "<label class='custom-control-label' for='" + objects[i].id + "'>" + objects[i]
                        .firstname + " " + objects[i].lastname + "<br>" +
                        objects[i].phone + " </label>" +
                        "</div></div>");
                }
                if (objects.length < 10) {
                    $('.lazy').html("No more users");
                    $('.lazy').show();
                }
            }

            allowgroupscroll();

            function allowgroupscroll() {
                $('.pledgegroups .escroll').on('scroll', function(e) {
                    if ($(this).scrollTop() + $(this).innerHeight() + 1 >= $(this)[0].scrollHeight) {
                        loadmoregroups();
                    }
                });
            }

            function loadmoregroups() {
                $('.escroll').unbind();
                $('.lazy').show();
                var search = $('.pledgegroups input[name="search"]').val();
                var limit = parseInt($('.escroll input[name="limit"]').val()) + 10;
                $.ajax({
                    url: "{{ url('dashboard/ajax/groups/users') }}?search=" + search + "&limit=" + limit,
                    type: 'GET',
                }).done(function(data) {
                    displayGroupUsers(data);
                    var objects = JSON.parse(data);
                    if (objects.length < 10) {
                        $('.lazy').html("No more contacts");
                    } else {
                        $('.escroll input[name="limit"]').val(limit);
                        allowgroupscroll();
                    }
                }).fail(function() {
                    $('.lazy').html(
                        "<span class='text-danger'><i class='fas fa-exclamation-triangle'></i> Something went <strong>wrong</strong></span>"
                    );
                });
            }
        });
    </script>
@endpush
