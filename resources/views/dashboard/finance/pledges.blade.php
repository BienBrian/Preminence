@extends('layouts.dashboard')

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h5 class="m-0 text-header"><i class='fas fa-campground'></i> View <b>Activity</b></h5>
                </div><!-- /.col -->
                <div class="d-none d-sm-block col-sm-6 text-right">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('home') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ url('dashboard/finances/activities') }}">Activities</a></li>
                        <li class="breadcrumb-item active">View</li>
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

                <div class='col-md-5 mb-4'>
                    <div class='card shadow h-100' style='border-width: 2px;'>
                        <div class='card-header'>
                            <h6>Activity Details</h6>
                        </div>
                        <div class='table-responsive'>
                            <table class='table'>
                                <tr>
                                    <td>Name:</td>
                                    <td> {{ $activity->name }}</td>
                                </tr>
                                <tr>
                                    <td>Required</td>
                                    <td> <strong>KSH {{ number_format($activity->amount, 2) }}</strong></td>
                                </tr>
                                <tr>
                                    <td>Amount Pledged</td>
                                    <td> <strong>KSH {{ number_format($pledged, 2) }}</strong></td>
                                </tr>
                                <tr>
                                    <td>Amount Paid</td>
                                    <td> <strong>KSH {{ number_format($paid, 2) }}</strong></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                <div class='col-md-7 mb-4'>
                    <div class='card shadow h-100' style='border-width: 2px;'>
                        <div class='card-header'>
                            <h6>Activity Description</h6>
                        </div>
                        <div class='card-body'>
                            <strong>Deadline:</strong>
                            @if (\Carbon\Carbon::parse($activity->closes_on) > \Carbon\Carbon::now())
                                <span
                                    class='text-success font-weight-bold'>{{ \Carbon\Carbon::parse($activity->closes_on)->diffInDays(\Carbon\Carbon::now()) }}
                                    day(s) remaining</span>
                            @else
                                <span
                                    class='text-danger font-weight-bold'>{{ \Carbon\Carbon::parse($activity->closes_on)->format('d M, Y') }}</span>
                            @endif
                            <br>
                            {!! html_entity_decode($activity->description) !!}
                        </div>
                    </div>
                </div>

                <div class="col-xl-12 mb-5 mb-xl-0">
                    <div class="card shadow">
                        <div class="card-header border-0">
                            <div class="row align-items-center">
                                <!--<div class="col-sm-6">
                                        <h5><i class="fas fa-donate"></i> | Pledges</h5>
                                    </div>-->
                                <div class="col text-right">
                                    <button class="btn btn-outline-primary btn-sm" data-toggle="modal"
                                        data-target="#importPledgesModal"><i class='fas fa-file-excel'></i> Import</button>
                                        <button class="btn btn-outline-primary btn-sm btn-remind-all" data-toggle="modal"
                                            data-target="#notifyModal"><i class='fas fa-bell'></i> Remind all</button>
                                    <button class="btn btn-outline-primary btn-sm showpledgeusers"><i
                                            class='fas fa-circle-plus'></i> Add pledges</button>
                                    <a href="{{ url('dashboard/finances/activities/pledges/groups/' . $activity->id) }}"
                                        class="btn btn-primary btn-sm"><i class='fas fa-circle-plus'></i> Grouped
                                        Pledges</a>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <!-- Projects table -->
                            <table class="table align-items-center table-flush">
                                <thead class="thead-light">
                                    <tr>
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">User</th>
                                        <th scope="col">Amount</th>
                                        <th scope="col">Paid</th>
                                        <th scope="col">Balance</th>
                                        <th class='text-right'>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if ($pledges->isEmpty())
                                        <tr>
                                            <td colspan='6' class='text-center'> <i class='fas fa-ban'></i> No pledges
                                                yet</td>
                                        </tr>
                                    @endif
                                    <?php $count = 1; ?>
                                    @foreach ($pledges as $act)
                                        <tr>
                                            <td>{{ $count }}</td>
                                            <td>{{ $act->firstname }} {{ $act->lastname }}</td>
                                            <td>{{ number_format($act->amount, 2) }}</td>
                                            <td>{{ number_format($act->paid, 2) }}</td>
                                            <td>{{ number_format($act->amount - $act->paid, 2) }}</td>
                                            <td class='text-right'>
                                                @if ($act->amount > $act->paid)
                                                    <div class="dropdown">
                                                        <a class="btn btn-outline-primary p-1 pr-2 pl-2 small"
                                                            href="#" role="button" data-toggle="dropdown"
                                                            aria-haspopup="true" aria-expanded="false"
                                                            data-placement="bottom" title="user roles">
                                                            Options <i class="fas fa-angle-down"></i>
                                                        </a>
                                                        <div class="dropdown-menu dropdown-menu-right dropdown-menu-arrow">
                                                            @if (\Carbon\Carbon::parse($act->closes_on) > \Carbon\Carbon::now())
                                                                <a href="{{ $act->id }}"
                                                                    class='dropdown-item btn-edit-pledge'><i
                                                                        class='fas fa-edit'></i> Edit</a>
                                                            @endif

                                                            <a href="{{ $act->id }}" class="dropdown-item notify">
                                                                <i class='fas fa-bell'></i> Send Reminder
                                                            </a>

                                                            <a href="{{ $act->id }}" class='dropdown-item recieved'>
                                                                <i class='fas fa-donate'></i> Payments
                                                            </a>
                                                            <a href="{{ url('dashboard/pledges/remove/' . $act->id) }}"
                                                                class='dropdown-item bg-danger text-white'>
                                                                <i class='fas fa-trash-alt'></i> Delete
                                                            </a>
                                                        </div>
                                                    </div>
                                                @endif
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
                        {{ $pledges->links() }}
                    </div>
                </div>
            </div>

        </div>
    </section>

    <!-- Modal -->
    <div class="modal fade" id="pledgeModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header border-bottom">
                    <h4 class="modal-title" id="exampleModalLabel"><i class='fas fa-edit'></i> Pledge Edit</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body pt-1 pb-0 mb-0">
                    <form action="{{ url('dashboard/pledge/edit') }}" method="post" class="row d-flex ">
                        @csrf
                        <input type='hidden' name='id' value='0'>

                        <div class='col-sm-12 form-group'>
                            <label>Member Name</label>
                            <input type="text" disabled class="form-control" name="name" placeholder='Member Name'
                                required>
                        </div>

                        <div class='col-sm-6 form-group'>
                            <label>Amount</label>
                            <input type="number" name="amount" class="form-control" placeholder="Amount Pledged"
                                required>
                        </div>
                        <div class='col-sm-6 form-group'>
                            <label>Paid</label>
                            <input type="number" name="paid" class="form-control" placeholder="Amount Paid "
                                disabled>
                        </div>

                        <div class="col-sm-6 ">
                            <div class="custom-control custom-checkbox mb-3 mt-3">
                                <input name="notify" class="custom-control-input" id="notifymember" type="checkbox"
                                    value="1" checked>
                                <label class="custom-control-label text-primary" for="notifymember"> Notify user
                                    of change</label>
                            </div>
                        </div>

                        <div class="col-sm-6 text-right">
                            <button type="submit" class="btn btn-primary btn-submit-pledge">Save
                                Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <!-- Send Reminder Modal -->
    <div class="modal fade" id="notifyModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title" id="exampleModalLabel"><i class='fas fa-envelope'></i> Send SMS
                        Reminder</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body pt-1 pb-0 mb-0">
                    <form action="{{ url('dashboard/finances/activities/pledge/remind') }}" method="post"
                        class="row">
                        @csrf
                        <input type='hidden' name='id' value='0'>
                        <input type='hidden' name='activity_id' value='{{ $activity->id }}'>

                        <div class='col-sm-12 form-group'>
                            <label>Member Name</label>
                            <input type="text" disabled class="form-control" name="name" placeholder='Member Name'
                                value="Sending to all in this activity" required>
                        </div>

                        <div class='col-sm-12 form-group'>
                            <label>Reminder</label>
                            <textarea name="message" rows="4" class="form-control" placeholder="Reminder Note" required>Greetings Brethren. This is to remind you of your balances on the pledges you made. As we approach the deadline, purpose to pay. Blessings.</textarea>
                        </div>
                        <p class='text-danger small font-weight-bold'>&#123;&#123;NAME&#125;&#125;,
                            &#123;&#123;ACTIVITY&#125;&#125; and &#123;&#123;BALANCE&#125;&#125; should not be
                            edited.</p>
                </div>

                <div class="col-sm-12 form-group text-right">
                    <button type="submit" class="btn btn-primary btn-notify">Send</button>
                </div>
                </form>
            </div>
        </div>
    </div>
    </div>

    <!-- Pay Pledge Modal -->
    <div class="modal fade" id="payPledgeModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title" id="exampleModalLabel"><i class="fas fa-coins"></i> Payments Modal
                    </h5>
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
                                    <td class='border-0 p-1'><strong class='amount'><i
                                                class='fas fa-spinner fa-pulse'></i> updating</strong></td>
                                </tr>
                                <tr>
                                    <td class='border-0 p-1'>Paid:</td>
                                    <td class='border-0 p-1'><strong class='paid'><i
                                                class='fas fa-spinner fa-pulse'></i> updating</strong></td>
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
                            <input type='number' name='payments' class="form-control" placeholder="Enter amount here">
                        </div>
                        <div class="col-sm-12 form-group text-right">
                            <button type='submit' class="btn btn-primary btn-pay">Save Payments</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Email Modal -->
    <div class="modal fade" id="emailModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="exampleModalLabel">Message Modal</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body pt-3 pb-0 mb-0 bg-secondary">
                    <form action='{{ url('users/sendemail') }}' method='post' class="email-form">
                        @csrf
                        <div class="form-group">
                            <label><small>Email Address</small></label>
                            <div class="input-group input-group-alternative">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                </div>
                                <input type='text' class="form-control form-control-alternative" name='email'
                                    placeholder="email address" readonly />
                            </div>
                        </div>
                        <div class="form-group">
                            <label><small>Subject</small></label>
                            <div class="input-group input-group-alternative">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                </div>
                                <input type='text' class="form-control form-control-alternative" name='subject'
                                    placeholder="Email Subject" value="Pledges Reminder" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label><small>Message</small></label>
                            <div class="input-group input-group-alternative">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-comments"></i></span>
                                </div>
                                <textarea name='message' class="form-control form-control-alternative" placeholder="Your Message here"
                                    rows="5"></textarea>
                            </div>
                        </div>
                        <div class="form-group text-right">
                            <button type='submit' class="btn btn-primary">Send Email</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Import Pledges Modal -->
    <div class="modal fade" id="importPledgesModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel"><i class="fas fa-file-import"></i> Import Pledges</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">

                    <form action='{{ url('dashboard/finances/activities/pledges/import') }}' method='post' enctype="multipart/form-data">
                        <input type='hidden' name='activity_id' value="{{ $activity->id }}"/>
                        <label>*Upload excel file as the sample below</label>
                        <img src="{{ asset('images/import_pledge_sample.png') }}" class="img-fluid"/>
                        @csrf
                        <div class="form-group">
                            <label>Pledge Excel File</label>
                            <div class="form-group">
                                <input type='file' class="form-control bg-white" name='file'
                                    placeholder="upload file" />
                            </div>
                        </div>
                        <div class='form-group'>
                            <label>Message (SMS)</label>
                            <textarea name="message" rows="4" class="form-control" placeholder="SMS Message" required>Dear ***NAME***  Thank you for honouring the LORD with your finances. Your support of Ksh***AMOUNT*** through TITHE account will support the ministry in great ways.
                                God loves a cheerful giver II Cor 9:7 May the Lord bless and keep you, be  enriched you in every way . Reverend Hosea. For Prayers call 0721895977.
                            </textarea>

                        <p class='text-danger small font-weight-bold'>***NAME*** and ***AMOUNT*** should not be
                            edited.</p>
                            </div>
                        <div class="form-group text-right">
                            <button type='submit' class="btn btn-primary"><i class='fas fa-file-upload'></i> Upload</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="pledgesusers" style="background-color: rgba(0,0,0,.5);">
        <div class="message-div row">
            <div class='col-sm-12 bg-gradient-primary p-4 text-white mheader' style='max-height: 10vh;'>
                <button class='btn text-white close-message'
                    style='background: transparent; sbox-shadow: none; position: absolute; top: 10px; right: 30px;'><i
                        class='fas fa-times'></i></button>
                <i class='fas fa-comment'></i> New Message
            </div>
            <div class="col-sm-12 p-4">
                <form action="{{ url('dashboard/finances/activities/pledges/sms') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label>Search Members</label>
                        <!--<div class='row'>
                                    <div class='col-xs-6 pl-3 pr-3'>
                                        <div class="custom-control custom-radio mb-3">
                                            <input name="choice" class="custom-control-input" id="individual" type="radio" value="0" checked>
                                            <label class="custom-control-label" for="individual">Individual</label>
                                        </div>
                                    </div>
                                    <div class='col-xs-6 pl-3 pr-3'>
                                        <div class="custom-control custom-radio mb-3">
                                            <input name="choice" class="custom-control-input" id="groups" type="radio" value="1">
                                            <label class="custom-control-label" for="groups">All</label>
                                        </div>
                                    </div>
                                </div>-->

                        <input name="search" type="text" class="form-control" placeholder="Search">
                    </div>

                    <div class='escroll'>
                        <div class="results mt-2">
                            <p class='small text-muted'><i class="fas fa-spinner fa-pulse"></i> Please Wait</p>
                        </div>
                        <input type='hidden' name='limit' value='0'>
                        <input type='hidden' name='activity_name' value='{{ $activity->name }}'>
                        <input type='hidden' name='activity_id' value='{{ $activity->id }}'>

                        <p class='small font-weight-bold lazy'><i class='fas fa-spinner fa-pulse'></i> Loading...
                        </p>
                    </div>
                    <div class='row'>

                        <div class='pl-3 pr-3'>
                            <div class="custom-control custom-checkbox mb-3 mt-3">
                                <input name="notify" class="custom-control-input" id="notify" type="checkbox"
                                    value="1" checked>
                                <label class="custom-control-label text-primary" for="notify">Notify members
                                    about pledges via SMS</label>
                            </div>
                            <div class='input-group'>
                                <input type='number' name='amount' class='form-control' required
                                    placeholder='pledge amount'>
                                <div class='input-group-append'>
                                    <button class='btn btn-primary'>Add</button>
                                </div>
                            </div>
                        </div>
                        <!--<div class='input-group'>
                                    <textarea class='form-control' name="message" rows="2" style="border: 2px solid #ddd; border-radius: 0px;"
                                        placeholder="Enter message here"></textarea>
                                    <div class='input-group-append' style="border: 2px solid #ddd; border-radius: 0px;">
                                        <button class='input-group-text' style="border: 2px solid #ddd; border-radius: 0px;">
                                            <i class='fas fa-paper-plane'></i>
                                        </button>
                                    </div>
                                </div>-->
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@push('js')
    <script>
        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            /*var groups = 0;
            $('.phonenumbers input[name="choice"]').change(function(){
                if($("#groups").prop("checked")){
                    groups = 1;
                    defaultUsers();
                }else{
                    groups = 0;
                    defaultUsers();
                }
            });*/

            $('.pledgesusers .close-message').click(function() {
                $('.pledgesusers').hide();
            });
            $('.showpledgeusers').click(function() {
                defaultUsers();
                $('.pledgesusers').show();
                /*$(document).click(function(event) {
                    if ( !$(event.target).hasClass('pledgesusers')) {
                         $(".pledgesusers").hide();
                    }
                });*/
            });

            function defaultUsers() {
                var search = $('.pledgesusers input[name="search"]').val();
                $.ajax({
                    url: "{{ url('dashboard/finances/ajax/pledges/users') }}?search=" + search,
                    type: "GET",
                }).done(function(data) {
                    $('.pledgesusers .results').html("");
                    displayContacts(data);
                }).fail(function(data) {
                    $('.pledgesusers .results').html(
                        "<div class='text-danger'><i class='fas fa-exclamation-circle'></i> <strong>Error</strong> loading contacts</div>"
                        );
                });
            }

            var timer = null;
            $('.pledgesusers input[name="search"]').keyup(function() {
                if (timer != null)
                    clearTimeout(timer);
                timer = setTimeout(function() {
                    var search = $('.pledgesusers input[name="search"]').val();
                    $('.escroll input[name="limit"]').val(0);
                    $('.pledgesusers .results').html("");
                    $('.lazy').html("<i class='fas fa-spinner fa-pulse'></i> Loading...");
                    $('.lazy').show();
                    allowscroll();
                    $.ajax({
                        url: "{{ url('dashboard/finances/ajax/pledges/users') }}?search=" +
                            search,
                        method: "GET",
                    }).done(function(data) {
                        displayContacts(data);
                    }).fail(function() {
                        $('.pledgesusers .results').html(
                            "<div class='text-danger'><i class='fas fa-exclamation-circle'></i> <strong>Error</strong> loading contacts</div>"
                            );
                    });
                }, 1000);
            });

            $('.pledgesusers form').submit(function(e) {
                e.preventDefault();
                var amount = parseInt($('.pledgesusers input[name="amount"]').val())
                if (amount < 50) {
                    swal({
                        text: "Amount must be 50 and above",
                        icon: "error"
                    });
                } else {
                    $('.pledgesusers form').unbind();
                    $('.pledgesusers form').submit();
                }
            });

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
                    url: "{{ url('dashboard/finances/ajax/pledges/users') }}?search=" + search +
                        "&limit=" + limit,
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

            function displayContacts(data) {
                var objects = JSON.parse(data);
                for (var i = 0; i < objects.length; i++) {
                    $('.pledgesusers .results').append("<div class='bg-white mt-2'>" +
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

            $('.btn-edit-pledge').click(function(e) {
                e.preventDefault();
                var id = $(this).attr("href");
                $('#pledgeModal').modal('show');
                $('#pledgeModal .modal-body .btn-submit-pledge').attr('disabled', 'disabled');
                $('#pledgeModal .modal-body .btn-submit-pledge').html(
                    "<i class='fas fa-circle-notch fa-spin'></i> Loading...");

                $.ajax({
                    url: "{{ url('dashboard/ajax/pledge') }}/" + id,
                    type: "GET",
                }).done(function(data) {
                    var objects = JSON.parse(data);
                    $('#pledgeModal .modal-body .btn-submit-pledge').html(
                        "<i class='fas fa-paper-plane'></i> Edit Pledge");
                    if (objects != null) {
                        $('#pledgeModal input[name="id"]').val(id);
                        $('#pledgeModal input[name="name"]').val(objects.firstname + " " + objects
                            .lastname);
                        $('#pledgeModal input[name="amount"]').val(objects.amount);
                        $('#pledgeModal input[name="paid"]').val(objects.paid);
                        $('#pledgeModal .modal-body .btn-submit-pledge').removeAttr('disabled');
                    } else {
                        swal({
                            text: "Invalid Request",
                            icon: "error"
                        });
                    }
                }).fail(function(data) {
                    $('#pledgeModal .modal-body .btn-submit-pledge').html(
                        "<i class='fas fa-paper-plane'></i> Edit Pledge");
                    swal({
                        text: "Unable to complete request",
                        icon: "error"
                    });
                });
            });

            $('.btn-remind-all').click(function() {
                $('#notifyModal .modal-body .small').show();
                $('#notifyModal input[name="id"]').val(0);
                $('#notifyModal input[name="name"]').val("Sending to all");
                $('#notifyModal textarea[name="message"]').summernote("code", "Greetings {\{NAME\}}," +
                    "\nWe are reminding you about the balances on a pledge you made on {\{ACTIVITY\}}" +
                    ". Your balance is {\{BALANCE\}}");
                $('#notifyModal .modal-body .btn-notify').removeAttr('disabled');
            });
            $('a.recieved').click(function(e) {
                e.preventDefault();
                var id = $(this).attr('href');
                $('#payPledgeModal').modal('show');
                $('#payPledgeModal .modal-body .btn-pay').attr("disabled", "disabled");
                $('#payPledgeModal .modal-body .btn-pay').html(
                    "<i class='fas fa-circle-notch fa-spin'></i> Loading...");
                $.ajax({
                    url: "{{url('dashboard/ajax/pledge')}}/" + id,
                    type: "GET",
                }).done(function(data) {
                    var objects = JSON.parse(data);
                    $('#payPledgeModal .modal-body .btn-pay').html(
                        "<i class='fas fa-paper-plane'></i> Save Payments");
                    if (objects != null) {
                        var balance = parseFloat(objects.amount) - parseFloat(objects.paid);
                        $('#payPledgeModal input[name="id"]').val(id);
                        $('#payPledgeModal input[name="name"]').val(objects.firstname + " " +
                            objects.lastname);
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
                    $('#pledgeModal .modal-body .btn-submit-pledge').html(
                        "<i class='fas fa-paper-plane'></i> Edit Pledge");
                    swal({
                        text: "Unable to complete request",
                        icon: "error"
                    });
                });

            });
            $('#payPledgeModal input[name="payments"]').keyup(function() {
                var payments = parseFloat($(this).val());

                var paid = parseFloat($('#payPledgeModal input[name="paid"]').val());
                var amount = parseFloat($('#payPledgeModal input[name="amount"]').val());
                var balance = parseFloat($('#payPledgeModal input[name="balance"]').val());

                if ($.isNumeric(payments)) {
                    $('.paid').html((paid + payments).toLocaleString());
                    $('.amount').html((amount).toLocaleString());
                    $('.balance').html((balance - payments).toLocaleString());
                } else {
                    $('.paid').html((paid).toLocaleString());
                    $('.amount').html((amount).toLocaleString());
                    $('.balance').html((balance).toLocaleString());
                }
            });

            $('a.notify').click(function(e) {
                e.preventDefault();
                var id = $(this).attr('href');
                $('#notifyModal .modal-body .small').hide();
                $('#notifyModal').modal('show');
                $('#notifyModal .modal-body .btn-notify').attr("disabled", "disabled");
                $('#notifyModal .modal-body .btn-notify').html(
                    "<i class='fas fa-circle-notch fa-spin'></i> Loading...");
                $.ajax({
                    url: "/ajax/pledge/" + id,
                    type: "GET",
                }).done(function(data) {
                    var objects = JSON.parse(data);
                    $('#notifyModal .modal-body .btn-notify').html(
                        "<i class='fas fa-paper-plane'></i> Send Reminder");
                    if (objects != null) {
                        var balance = parseFloat(objects.amount) - parseFloat(objects.paid);
                        $('#notifyModal input[name="id"]').val(id);
                        $('#notifyModal input[name="name"]').val(objects.firstname + " " + objects
                            .lastname);
                        $('#notifyModal textarea[name="message"]').val("Greetings " + (objects
                                .firstname).toUpperCase() +
                            "\nWe are reminding you about the balances on a pledge you made on " +
                            (objects.name).toUpperCase() +
                            ". Your balance is " + balance.toLocaleString());
                        $('#notifyModal .modal-body .btn-notify').removeAttr('disabled');
                    } else {
                        swal({
                            text: "Invalid Request",
                            icon: "error"
                        });
                    }
                }).fail(function(data) {
                    $('#pledgeModal .modal-body .btn-submit-pledge').html(
                        "<i class='fas fa-paper-plane'></i> Edit Pledge");
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
                    url: "/ajax/groups/users?search=" + search,
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
                        url: "/ajax/groups/users?search=" + search,
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
                    url: "/ajax/groups/users?search=" + search + "&limit=" + limit,
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
