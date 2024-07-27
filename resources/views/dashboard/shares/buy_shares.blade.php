@extends('layouts.dashboard')

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h5 class="m-0 text-header"><i class='fas fa-hand-holding-usd'></i> <b>Buy</b> Shares</h5>
                </div><!-- /.col -->
                <div class="col-sm-6 text-right">
                    @can('Add Queue Statuses')
                        <button class="btn btn-primary btn-sm btn-launch-modal" data-toggle="modal" data-target="#share-form"><i
                                class='fas fa-plus'></i> Add Status
                        </button>
                    @else
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ url('home') }}">Home</a></li>
                            <li class="breadcrumb-item active">Buy Shares</li>
                        </ol>
                    @endcan
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
                <div class="col-md-12 mb-3">


                    <!-- small box -->
                    <div class="card time-card d-none">
                        <div class="card-header">
                            <h5><i class='fas fa-clock'></i> <b>Auction</b> Opening in</h5>
                        </div>
                        <div class="card-body box-profile">
                            <div class="card-body">
                                <div class='row'>
                                    <div class='col'>
                                        <div class='alert my-card'>
                                            <span class='text-white days big'>--d</span>
                                        </div>
                                    </div>

                                    <div class='col'>
                                        <div class='alert my-card'>
                                            <span class='text-white hours big'>--h</span>
                                        </div>
                                    </div>

                                    <div class='col'>
                                        <div class='alert my-card'>
                                            <span class='text-white minutes big'>--m</span>
                                        </div>
                                    </div>

                                    <div class='col'>
                                        <div class='alert my-card'>
                                            <span class='text-white seconds big'>--s</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--<form class='search-form row d-flex align-items-end' id='search-form'>
                                                                                    <div class="col-sm-4">
                                                                                        <label>Search Name</label>
                                                                                        <input type="text" class="form-control mb-1" name="search" placeholder="Search">
                                                                                    </div>
                                                                                    <div class="col-sm-4">
                                                                                        <label>Status</label>
                                                                                        <select name="status" class="form-control mb-1">
                                                                                            <option value=''>All</option>
                                                                                            <option value='Pending'>Pending</option>
                                                                                            <option value='Active'>Active</option>
                                                                                            <option value='Suspended'>Suspended</option>
                                                                                            <option value='Cancelled'>Cancelled</option>
                                                                                            <option value='Completed'>Completed</option>
                                                                                        </select>
                                                                                    </div>
                                                                                    <div class="col-sm-4">
                                                                                        <label>Active</label>
                                                                                        <select name="active" class="form-control mb-1">
                                                                                            <option value='1'>Yes</option>
                                                                                            <option value='0'>No</option>
                                                                                        </select>
                                                                                    </div>
                                                                                </form>-->
                    </div>

                    <div class='card auction-div d-none'>
                        <div class='card-header'>
                            <h5>Place Your Bid</h5>
                        </div>
                        <div class='card-body'>
                            <form method='POST' action="{{ url('dashboard/shares/buy/add') }}" id='share-form'>
                                <div class='alert alert-info'>
                                    <i class='fas fa-info-circle'></i> You can bid and buy shares worth <b><i
                                            class='fas fa-rupee-sign'></i> 500</b> to <b><i class='fas fa-rupee-sign'></i>
                                        1000</b>
                                </div>
                                @csrf
                                <input type='hidden' value='0' name='auction_time_id' />
                                <div class='form-group'>
                                    <label>Bid Amount (<i class='fas fa-rupee-sign'></i>)</label>
                                    <input type='number' class='form-control' placeholder="Amount" name='amount' required>
                                </div>
                                <div class='form-group'>
                                    <label>Maturity</label>
                                    <select class='form-control' name='maturity' id='maturities'></select>
                                </div>
                                <div class='alert feedback border d-none'>
                                    <i class='fas fa-spinner fa-pulse'></i> Saving... Please wait
                                </div>
                                <div class='form-group text-right'>
                                    <button class='btn btn-primary btnSave' disabled><i class='fas fa-hand-holding-usd'></i>
                                        Buy
                                        Shares</button>
                                </div>
                            </form>
                        </div>
                    </div>

                </div>

                <!-- ./col -->
            </div>
            <!-- /.row -->
        </div><!-- /.container-fluid -->
    </section>
    <!-- /.content -->

    <div class="modal fade" id="infoModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <!--<div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="exampleModalLabel"><i class='fas fa-exclamation-triangle'></i> <span>Bidding
                            Expired</span>
                    </h5>
                    <button type="button" class="btn-close text-white" data-dismiss="modal" aria-label="Close"></button>
                </div>-->
                <div class="modal-body">
                    <div class="overflow-hidden position-relative border-radius-lg bg-cover h-100">
                        <span class="mask bg-gradient-dark"></span>
                        <div class="card-body position-relative z-index-1 d-flex flex-column h-100 p-3">
                            <!--<h5 class="text-white font-weight-bolder mb-4 pt-2"><i class='fas fa-info-circle'></i> <span style='font-weight: 300;'>{{ config('app.name', 'Laravel') }}</span> Auction</h5>-->
                            <p class="info text-white text-center">You can bid between <b><i class='fas fa-rupee-sign'></i>
                                    1000</b> - <b><i class='fas fa-rupee-sign'></i> 10000</b> in an auction</p>
                                    <div class='text-center'>
                                        <a href='{{ url('dashboard/home') }}' class="btn btn-white shadow btn-sm"><i
                                            class='fas fa-arrow-left'></i> Go Back</a>
                                    </div>
                        </div>
                    </div>
                    <!--
                    <div class='alert border-danger'>
                        <table class='w-100'>
                            <tr>
                                <td class='p-2 d-none d-sm-block'>
                                    <i class='fas fa-exclamation-triangle fa-3x text-danger'></i>
                                </td>
                                <td class='p-2'><span>Bidding time is over. Keep checking our dashboard for our next
                                        auction</span>
                                </td>
                            </tr>
                        </table>
                    </div>-->
                </div>
                <!--
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal"><i
                            class='fas fa-times'></i> Close</button>

                                                <button type="button" class="btn btn-primary btn-sm btnSave"><i class='fas fa-paper-plane'></i> Save
                                                    changes</button>
                </div>-->
            </div>
        </div>
    </div>
@endsection
@push('js')
    <script>
        $(document).ready(function() {
            const timeZone = Intl.DateTimeFormat().resolvedOptions().timeZone;
            var last_auction_time = "{{ $auction != null ? $auction->created_at : '' }}";
            $('#maturities').select2({
                width: '100%',
                placeholder: 'Select Maturities',
                //dropdownParent: $('#shareModal'),
                allowClear: true,
                ajax: {
                    url: '{{ url('dashboard/search/maturities') }}',
                    dataType: 'json',
                    delay: 250,
                    processResults: function(data) {
                        return {
                            results: $.map(data, function(item) {
                                return {
                                    text: item.name,
                                    id: item.id
                                }
                            })
                        };
                    },
                    cache: true
                }
            });
            getAuctionTime();

            $('#share-form').submit(function(e) {
                e.preventDefault();
                var btn = $('#share-form .btnSave');
                btn.attr('disabled', 'disabled');
                $('#share-form .feedback').removeClass('d-none');
                $('#share-form .feedback').removeClass('alert-danger');
                $('#share-form .feedback').removeClass('alert-success');
                $('#share-form .feedback').html(
                    "<i class='fas fa-spinner fa-pulse'></i> Saving... Please wait");
                var formData = $('#share-form').serialize();
                $.ajax({
                    url: '{{ url('dashboard/shares/buy/add') }}',
                    type: 'POST',
                    data: formData
                }).done(function(data) {
                    $('#share-form .feedback').addClass('alert-success');
                    $('#share-form .feedback').html("<i class='fas fa-exclamation-circle'></i> " +
                        data.success);
                    setTimeout(() => {
                        $('#share-form .feedback').addClass('d-none');
                    }, 3000);
                    location.reload();
                    //btn.removeAttr('disabled');
                }).fail(function(response) {
                    let data = response.responseJSON;
                    $('#share-form .feedback').addClass('alert-danger');
                    $('#share-form .feedback').html("");
                    if (data.errors) {
                        if (data.errors.amount) {
                            $('#share-form .feedback').html(
                                "<i class='fas fa-exclamation-circle'></i> " + data.errors
                                .amount + "<br>");
                        }
                        if (data.errors.maturity) {
                            $('#share-form .feedback').html(
                                "<i class='fas fa-exclamation-circle'></i> " + data.errors
                                .maturity + "<br>");
                        }
                        if (data.errors.auction_time_id) {
                            $('#share-form .feedback').html(
                                "<i class='fas fa-exclamation-circle'></i> " + data.errors
                                .auction_time_id + "<br>");
                        }
                    } else if (data.error) {
                        $('#share-form .feedback').html(
                            "<i class='fas fa-exclamation-circle'></i> " + data.error);
                    } else {
                        $('#share-form .feedback').html(
                            "<i class='fas fa-exclamation-circle'></i> <b>Whoops</b> Something went wrong with the server!"
                        );
                    }
                    setTimeout(() => {
                        $('#share-form .feedback').addClass('d-none');
                    }, 3000);
                    btn.removeAttr('disabled');
                });
            });
            $('#infoModal .modal-footer .btn-danger, #infoModal .btn-close').click(function(e) {
                e.preventDefault();
                location.href = "{{ url('/dashboard/home') }}";
            });

            function getAuctionTime() {
                $.ajax({
                    url: "{{ url('dashboard/auction_time') }}",
                    type: "GET",
                    data: {
                        timezone: timeZone,
                    },
                }).done(function(data) {
                    $('.shares').text(data.shares);
                    if (data.auction_time != null) {
                        $('input[name=auction_time_id]').val(data.auction_time.id);
                        var date = new Date(Date.parse(data.auction_time.start_time)).toLocaleString('en', /*{
                            timeZone: data.auction_time.name
                        }*/);
                        var closeDate = new Date(Date.parse(data.auction_time.end_time)).toLocaleString(
                            'en', /*{
                                timeZone: data.auction_time.name
                            }*/);

                        var countDownDate = new Date(date).getTime();
                        var closeTimeDate = new Date(closeDate).getTime();
                        var myNow = new Date(new Date().toLocaleString('en', {
                            timeZone: data.auction_time.name
                        })).getTime();//new Date().getTime();
                        if (countDownDate <= myNow && closeTimeDate >= myNow) {
                            $('.time-card').addClass('d-none');
                            $('.auction-div').removeClass('d-none');
                            if (last_auction_time != "") {
                                //var m = moment.utc(last_auction_time).local();

                                var last_auction_date = new Date(Date.parse(moment.utc(last_auction_time).local()))
                                    .toLocaleString(
                                        'en', {
                                            timeZone: data.auction_time.name
                                        });
                                if (last_auction_date >= date && last_auction_date <= closeDate) {
                                    $('#infoModal').modal({
                                        backdrop: 'static',
                                        keyboard: false
                                    }, 'show');
                                    //$('#infoModal .modal-title span').text("Bidding Closed");
                                    $('#infoModal .modal-body .info').text(
                                        "You have already placed a bid in this Auction. One bid allowed for every auction!"
                                    );
                                } else {
                                    $('#share-form .btn').removeAttr('disabled');
                                }
                            } else {
                                $('#share-form .btn').removeAttr('disabled');
                            }
                        } else {
                            $('.time-card').removeClass('d-none');
                            $('.auction-div').addClass('d-none');
                            $('#infoModal').modal({
                                backdrop: 'static',
                                keyboard: false
                            }, 'show');
                            //$('#infoModal .modal-title span').text("Auction Closed");
                            $('#infoModal .modal-body .info').html(
                                "Auction is already closed. Come back in <h5 class='time text-white'><span class='badge bg-warning days'>--</span> : " +
                                "<span class='badge bg-warning hours'>--</span> : <span class='badge bg-warning minutes'>--</span> : " +
                                "<span class='badge bg-warning seconds'>--</span></h5>"
                            );
                            if (closeTimeDate < myNow) {
                                //alert(data.auction_time.auction_date);
                                if (data.auction_time.week_day == null && data.auction_time.auction_date ==
                                    null) {
                                    countDownDate += (60 * 60 * 24 * 1000);
                                }
                            }
                        }

                        var x = setInterval(function() {

                            // Get today's date and time
                            var now = new Date(new Date().toLocaleString('en', {
                                timeZone: data.auction_time.name
                            })).getTime();//new Date().getTime();

                            // Find the distance between now and the count down date
                            var distance = countDownDate - now;


                            // Time calculations for days, hours, minutes and seconds
                            var days = Math.floor(distance / (1000 * 60 * 60 * 24));
                            var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 *
                                60));
                            var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                            var seconds = Math.floor((distance % (1000 * 60)) / 1000);

                            // Display the result in the element with id="demo"
                            /*document.getElementById("demo").innerHTML = days + "d " + hours + "h " +
                                minutes + "m " + seconds + "s ";*/
                            $('.days').text((days).toString().padStart(2, "0") + "d");
                            $('.hours').text((hours).toString().padStart(2, "0") + "h");
                            $('.minutes').text((minutes).toString().padStart(2, "0") + "m");
                            $('.seconds').text((seconds).toString().padStart(2, "0") + "s");

                            // If the count down is finished, write some text
                            if (distance < 0) {
                                clearInterval(x);
                                $('.days').text("--d");
                                $('.hours').text("--h");
                                $('.minutes').text("--m");
                                $('.seconds').text("--s");
                            }
                        }, 1000);
                    }
                    /*else {
                                           alert("No Auction Time Set!!!");
                                       }*/

                })
                /*.fail(function(data) {
                                    alert("fail");
                                })*/
                ;
            }
        });
    </script>
@endpush
