<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="shortcut icon" href="{{ $site_settings == null?'favicon.ico':asset('website/'.$site_settings->favicon) }}">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $site_settings == null?"Church App":$site_settings->name }}</title>

    <!-- Scripts -->
    <!--<script src="{{ asset('js/app.js') }}" defer></script>-->

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Montserrat:300,400,500,600,700,800&display=swap" rel="stylesheet">

    <!-- Styles -->
    <!--<link href="{{ asset('css/app.css') }}" rel="stylesheet">--><!-- Icons -->
    <link href="{{asset('assets/vendor/nucleo/css/nucleo.css')}}" rel="stylesheet">
    <link href="{{asset('assets/vendor/@fortawesome/fontawesome-free/css/all.min.css')}}" rel="stylesheet">
    <!-- Argon CSS -->
    <link type="text/css" href="{{asset('assets/css/argon.css?v=1.0.0')}}" rel="stylesheet">

    <link href="https://cdnjs.cloudflare.com/ajax/libs/croppie/2.6.1/croppie.css" rel="stylesheet">
    <link href="{{asset('datepicker/css/bootstrap-datetimepicker.min.css')}}" rel="stylesheet">

    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.css" rel="stylesheet">
    <!-- include summernote css/js -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.9/summernote-bs4.css" rel="stylesheet">

    <!-- Datatables-->
    <link href="https://cdn.datatables.net/1.10.20/css/dataTables.bootstrap4.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/buttons/1.6.0/css/buttons.bootstrap4.min.css" rel="stylesheet">
    <!-- Jquery UI -->
    <link href = "https://code.jquery.com/ui/1.10.4/themes/ui-lightness/jquery-ui.css" rel = "stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/imgareaselect/0.9.10/css/imgareaselect-default.css" rel="stylesheet">

    <!-- Custom style -->
    <link type="text/css" href="{{asset('css/styles.css')}}" rel="stylesheet">
    <link type="text/css" href="{{ $site_settings == null?'':asset('css/'.$site_settings->theme) }}" rel="stylesheet">
    <style>
        div.dt-buttons {
            /*background-image: none !important;*/
            float: right;
        }
        .table td{
            vertical-align: middle !important;
            font-size: .9em;
            word-wrap: break-word;
            font-weight: 400 !important;
        }
        .btn-light{
            border-color: #172b4d;
            background-color: #172b4d;
        }
        .btn-light:not(:disabled):not(.disabled):active, .btn-light:not(:disabled):not(.disabled).active, .show > .btn-light.dropdown-toggle {
            border-color: #172b4d;
            background-color: #0b1526;
        }
    </style>
</head>
<body>

@include('includes.admin_header')
@yield('content')
@include('includes.admin_footer')
<!-- Argon Scripts -->
    <!-- Core -->
    <script src="{{asset('assets/vendor/jquery/dist/jquery.min.js')}}"></script>
    <script src="{{asset('assets/vendor/bootstrap/dist/js/bootstrap.bundle.min.js')}}"></script>

    <!-- Optional JS -->
      <script src="{{asset('assets/vendor/chart.js/dist/Chart.min.js')}}"></script>
      <script src="{{asset('assets/vendor/chart.js/dist/Chart.extension.js')}}"></script>

      <!-- Argon JS -->
      <script src="{{asset('assets/js/argon.js?v=1.0.0')}}"></script>
      <script src="{{asset('datepicker/js/bootstrap-datetimepicker.js')}}"></script>
      <script src="https://cdnjs.cloudflare.com/ajax/libs/croppie/2.6.1/croppie.js"></script>

        <!-- include summernote js -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.9/summernote-bs4.js"></script>

        <!-- Datatable -->
        <script src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.10.20/js/dataTables.bootstrap4.min.js"></script>
        <script src="https://cdn.datatables.net/buttons/1.6.0/js/dataTables.buttons.min.js"></script>
        <script src="https://cdn.datatables.net/buttons/1.6.0/js/buttons.bootstrap4.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
        <script src="https://cdn.datatables.net/buttons/1.6.0/js/buttons.html5.min.js"></script>
        <script src="https://cdn.datatables.net/buttons/1.6.0/js/buttons.print.min.js"></script>
        <script src="https://cdn.datatables.net/buttons/1.6.0/js/buttons.colVis.min.js"></script>

        <!-- JQUERY UI -->
      <script src = "https://code.jquery.com/ui/1.10.4/jquery-ui.js"></script>
      <!-- Image Select -->
      <script src="https://cdnjs.cloudflare.com/ajax/libs/imgareaselect/0.9.10/js/jquery.imgareaselect.min.js"></script>
      <script src="https://cdnjs.cloudflare.com/ajax/libs/imgareaselect/0.9.10/js/jquery.imgareaselect.pack.js"></script>

      <!-- Argon Datepicker -->
      <script src="{{asset('assets/vendor/bootstrap-datepicker/js/bootstrap-datepicker.min.js')}}"></script>

      <!-- sweet alert -->
      <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>

      <!-- datepicker -->
      <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.js"></script>

      @stack('js')

        <!-- Custom Script -->
        <script src="{{asset('js/sms.js')}}"></script>
        <script src="{{asset('js/people.js')}}"></script>
        <script src="{{asset('js/pastors.js')}}"></script>
        <script src="{{asset('js/members.js')}}"></script>
        <script src="{{asset('js/notifications.js')}}"></script>
        <script src="{{asset('js/budget.js')}}"></script>
        <script src="{{asset('js/funds.js')}}"></script>
        <script src="{{asset('js/attendance.js')}}"></script>
        <script src="{{asset('js/pledges.js')}}"></script>
        <script src="{{asset('js/email.js')}}"></script>

      <script>
          $(document).ready(function(){
            $('a.logout').click(function(e){
                e.preventDefault();
                $('#logout-form').submit();
            });

            $('#funds').DataTable();
            var baseurl = "<?php echo url('/'); ?>";
            $('.mydatepicker').datepicker({
              format: "d MM, yyyy"
            });
            //$('.previous a').html("<i class='fas fa-angle-right'></i>");
            $('.datepicker').datetimepicker({
                weekStart: 1,
                todayBtn:  1,
                autoclose: 1,
                todayHighlight: 1,
                startView: 2,
                forceParse: 0,
                showMeridian: 1,
                //format:'d MM, yyyy HH:mm:ss',
            });
            $('.datepicker1').datepicker({ changeYear: true, changeMonth: true, yearRange: "-100:+0" });
            $('.previous').html("<i class='fas fa-angle-left'></i>");
            $('.next').html("<i class='fas fa-angle-right'></i>");
            $('.pagination').addClass('justify-content-end');
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }

            });





        //assets edit
        $('.showAssetsModal').click(function(){
            $('#assetsModal input[name="id"]').val(0);
            $('#assetsModal input[name="amount"]').val("");
            $('#assetsModal input[name="name"]').val();
            $('#assetsModal textarea[name="description"]').text("Say something");
            $('#assetsModal .modal-body .btn').text("Save Asset");
        });

        $('.assetsedit').click(function(e){
            e.preventDefault();
            var id = $(this).attr('href');
            var name = $(this).closest('tr').find('td:nth-child(2)').text();
            var amount = $(this).closest('tr').find('td:nth-child(3)').text();
            var description = $(this).closest('tr').find('td:nth-child(3)').text();
            $('#assetsModal input[name="id"]').val(id);
            $('#assetsModal input[name="amount"]').val(amount);
            $('#assetsModal input[name="name"]').val(name);
            $('#assetsModal').modal('show');
            $('#assetsModal .modal-body .btn').text("Edit Asset");
        });

            $('.btn-show-prayer').click(function(){
                $('.prayer-form input[name="id"]').val("0");
                $('.prayer-form input[name="title"]').val("");
                $('.prayer-form textarea[name="description"]').html("");
            });

            $('.edit-department').click(function(e){
                e.preventDefault();
                baseurl = "<?php echo url('/'); ?>";
                var id = $(this).attr('href');
                $.ajax({
                    url: baseurl+"/departments/"+id,
                    type: "get"
                }).done(function(data){
                    var mydata = $.parseJSON(data);
                    if(mydata != null){
                        $('.department-form input[name="id"]').val(mydata.id);
                        $('.department-form input[name="name"]').val(mydata.name);
                        $('.department-form textarea[name="description"]').html(mydata.description);
                        $('.department-form input[name="contact"]').val(mydata.contact);
                        $('.department-form select[name="user_id"]').val(mydata.leader);
                        $('.btn-submit-departments').html("Update Department");
                        $('#departmentModal').modal('show');
                    }else{
                        alert("Invalid request");
                    }
                }).fail(function(data){
                    alert("failed");
                });
            });

            $('.btn-show-department').click(function(){
                $('.department-form input[name="id"]').val("");
                $('.department-form input[name="name"]').val("");
                $('.department-form textarea[name="description"]').html("");
                $('.department-form input[name="contact"]').val("");
                $('.btn-submit-departments').html("Save Department");
            });


            //cropping function
            $('#profileModal').on('shown.bs.modal', function (e) {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                var parentW = $('#upload-profile').closest('.col-sm-12').width();
                var vwidth = parentW;
                var vheight = vwidth;

                $uploadCrop = $('#upload-profile').croppie({
                    enableExif: true,
                    viewport: {
                        width: vwidth*.9,
                        height: vheight*.9,
                        type: 'canvas'
                    },

                    boundary: {
                        width: parentW,
                        height: (parentW)*4/4
                    }
                });
                $('.btn-add-profile').click(function(){
                    $('#upload').click();
                });

                $('#upload').on('change', function () {
                    $('.upload-result').removeAttr('disabled');
                    $('#upload-profile').removeClass('d-none');
                    var reader = new FileReader();
                    reader.onload = function (e) {
                        $uploadCrop.croppie('bind', {
                            url: e.target.result
                        }).then(function(){
                            console.log('jQuery bind complete');
                        });
                    }
                    reader.readAsDataURL(this.files[0]);
                });

                $('.upload-result').on('click', function (ev) {
                    $uploadCrop.croppie('result', {
                        type: 'canvas',
                        size: 'viewport'
                    }).then(function (resp) {
                        baseurl = "<?php echo url('/'); ?>";
                        $('.feedback').html("<p class='text-center text-info'><i class='fas fa-spinner fa-pulse'></i> Saving... Please wait</p>");
                        $.ajax({
                            url: baseurl+"/profileimage",
                            type: "POST",
                            data: {"image":resp},
                            success: function (data) {
                                try {
                                    json = $.parseJSON(data);
                                } catch (e) {
                                    // not json
                                }
                                $('.feedback').html(data.success);
                                window.location.reload();
                            },
                            error: function(){
                                $('.feedback').html("<p class='text-center text-danger'><i class='fas fa-warning'></i> Unable to save image</p>");
                            }
                        });
                    });
                });
            });

            $('.edit-role').click(function(e){
                e.preventDefault();
                baseurl = "<?php echo url('/'); ?>";
                var id = $(this).attr('href');
                $('#roleModal').modal('show');
                $('.btn-submit-role').attr('disabled', 'disabled');
                $('.btn-submit-role').html('<i class="fas fa-spinner fa-pulse"></i> Please Wait');

                $.ajax({
                    url: baseurl+"/userroles/"+id,
                    type: "get"
                }).done(function(data){
                    var mydata = $.parseJSON(data);
                    if(mydata != null){
                        $('.role-form input[name="id"]').val(mydata.id);
                        $('.role-form input[name="name"]').val(mydata.name);
                        $('.btn-submit-role').html("Edit Role");
                        $('.btn-submit-role').removeAttr('disabled');

                    }else{
                        alert("Invalid request");
                    }
                }).fail(function(data){
                    alert("failed");
                });
            });

            $('.btn-show-role').click(function(){
                $('.role-form input[name="id"]').val("0");
                $('.role-form input[name="name"]').val("");
                $('.btn-role-departments').html("Save Role");
            });

            $('.edit-permissions').click(function(e){
                e.preventDefault();
                baseurl = "<?php echo url('/'); ?>";
                var id = $(this).attr('href');
                $(".permission-form input[name='role']").val(id);
                $('#permissionModal').modal('show');
                $('#permissionModal .btn-submit-permissions').attr('disabled', 'disabled');
                $('#permissionModal .btn-submit-permissions').html('<i class="fas fa-spinner fa-pulse"></i> Loading... Please wait');
                $.ajax({
                    url: baseurl+"/permissions/"+id,
                    type: "get"
                }).done(function(data){
                    var mydata = $.parseJSON(data);
                    if(mydata != null){
                        $('.permission-form input[name="role"]').val(mydata.role);
                        if(mydata.dashboard > 0){
                            $('#dashboardc').prop("checked", true);
                            if(mydata.dashboard == 1){
                                $('#dashboardr').prop("checked", true);
                            }else if(mydata.dashboard == 2){
                                $('#dashboardr2').prop("checked", true);
                            }else{
                                $('#dashboardr3').prop("checked", true);
                            }
                        }else{
                            $('#dashboardr').prop("checked", true);
                            $('#dashboardc').prop("checked", false);
                        }
                        if(mydata.testimonials > 0){
                            $('#testimonialc').prop("checked", true);
                            if(mydata.testimonials == 1){
                                $('#testimonialr').prop("checked", true);
                            }else if(mydata.testimonials == 2){
                                $('#testimonialr2').prop("checked", true);
                            }else{
                                $('#testimonialr3').prop("checked", true);
                            }
                        }else{
                            $('#testimonialr').prop("checked", true);
                            $('#testimonialc').prop("checked", false);
                        }
                        if(mydata.websites > 0){
                            $('#websitec').prop("checked", true);
                            if(mydata.websites == 1){
                                $('#websiter').prop("checked", true);
                            }else if(mydata.websites == 2){
                                $('#websiter2').prop("checked", true);
                            }else{
                                $('#websiter3').prop("checked", true);
                            }
                        }else{
                            $('#websiter').prop("checked", true);
                            $('#websitec').prop("checked", false);
                        }
                        if(mydata.finances > 0){
                            $('#financec').prop("checked", true);
                            if(mydata.finances == 1){
                                $('#financer').prop("checked", true);
                            }else if(mydata.finances == 2){
                                $('#financer2').prop("checked", true);
                            }else{
                                $('#financer3').prop("checked", true);
                            }
                        }else{
                            $('#financer').prop("checked", true);
                            $('#financec').prop("checked", false);
                        }
                        if(mydata.spiritual > 0){
                            $('#spiritualc').prop("checked", true);
                            if(mydata.spiritual == 1){
                                $('#spiritualr').prop("checked", true);
                            }else if(mydata.spiritual == 2){
                                $('#spiritualr2').prop("checked", true);
                            }else{
                                $('#spiritualr3').prop("checked", true);
                            }
                        }else{
                            $('#spiritualc').prop("checked", true);
                            $('#spiritualc').prop("checked", false);
                        }

                        if(mydata.events > 0){
                            $('#eventc').prop("checked", true);
                            if(mydata.events == 1){
                                $('#eventr').prop("checked", true);
                            }else if(mydata.events == 2){
                                $('#eventr2').prop("checked", true);
                            }else{
                                $('#eventr3').prop("checked", true);
                            }
                        }else{
                            $('#eventc').prop("checked", true);
                            $('#eventc').prop("checked", false);
                        }
                        if(mydata.shop > 0){
                            $('#shopc').prop("checked", true);
                            if(mydata.shop == 1){
                                $('#shopr').prop("checked", true);
                            }else if(mydata.shop == 2){
                                $('#shopr2').prop("checked", true);
                            }else{
                                $('#shopr3').prop("checked", true);
                            }
                        }else{
                            $('#shopc').prop("checked", true);
                            $('#shopc').prop("checked", false);
                        }

                        if(mydata.communication > 0){
                            $('#communicationc').prop("checked", true);
                            if(mydata.communication == 1){
                                $('#communicationr').prop("checked", true);
                            }else if(mydata.communication == 2){
                                $('#communicationr2').prop("checked", true);
                            }else{
                                $('#communicationr3').prop("checked", true);
                            }
                        }else{
                            $('#communicationc').prop("checked", true);
                            $('#communicationc').prop("checked", false);
                        }
                        if(mydata.articles > 0){
                            $('#articlec').prop("checked", true);
                            if(mydata.articles == 1){
                                $('#articler').prop("checked", true);
                            }else if(mydata.articles == 2){
                                $('#articler2').prop("checked", true);
                            }else{
                                $('#articler3').prop("checked", true);
                            }
                        }else{
                            $('#articlec').prop("checked", true);
                            $('#articlec').prop("checked", false);
                        }
                        if(mydata.users > 0){
                            $('#userc').prop("checked", true);
                            if(mydata.users == 1){
                                $('#userr').prop("checked", true);
                            }else if(mydata.users == 2){
                                $('#userr2').prop("checked", true);
                            }else{
                                $('#userr3').prop("checked", true);
                            }
                        }else{
                            $('#userc').prop("checked", true);
                            $('#userc').prop("checked", false);
                        }
                        $('.btn-submit-permissions').html("Edit Permissions");
                        $('.btn-submit-permissions').removeAttr('disabled');

                    }else{
                        $('#dashboardc').prop("checked", false);
                        $('#testimonialc').prop("checked", false);
                        $('#websitec').prop("checked", false);
                        $('#financec').prop("checked", false);
                        $('#spiritualc').prop("checked", false);
                        $('#eventc').prop("checked", false);
                        $('#shopc').prop("checked", false);
                        $('#communicationc').prop("checked", false);
                        $('#articlec').prop("checked", false);
                        $('#userc').prop("checked", false);

                        $('.btn-submit-permissions').html("Add Permissions");
                        $('.btn-submit-permissions').removeAttr('disabled');
                    }
                }).fail(function(data){
                    alert("failed");
                });
            });




            $('.btn-save-pastor').click(function(){
                $("#pastor form").submit();
            });

            $(".notifications .btn").click(function(){
                $(".notifications").fadeOut('slow');
            });

            setTimeout(function(){
                $(".notifications").fadeOut('slow');
            }, 4000);


        $('.btn-show-event').click(function(){
            $('.event-form input[name="id"]').val("0");
            $('.event-form input[name="title"]').val("");
            $('.event-form textarea[name="description"]').html("");
            $('.event-form input[name="date"]').val("");
            $('.event-form input[name="location"]').val("");
            $('.event-form img').attr('src', "");
            $('.event-form img').closest('div').addClass('d-none');
            $(".event-form .btn-submit-events").html("Add Event");
        });
        $('.emails-form .btn, sms-form .btn').click(function(){
            $(this).attr("disabled", "disabled");
            $(this).html("<i class='fas fa-spinner fa-pulse'></i> Sending...");
        });


        $(document).on('click', '#users-table .btn-email', function(e){
            baseurl = "<?php echo url('/'); ?>";
            e.preventDefault();
            var username = $(this).closest("tr").find("td:nth-child(2)").text();
            $('#emailModal form .btn').html("<i class='fas fa-spinner fa-pulse'></i> Please wait...");
            $('#emailModal form .btn').attr("disabled", "disabled");
            $('#emailModal').modal("show");
            var id = $(this).attr('href');
            $('#emailModal .modal-header h4').html("Sending to <strong class='text-primary'>"+username+"</strong>")
            $.ajax({
                url: baseurl+"/user/email/"+id,
                type: "GET",
            }).done(function(data){
                mydata = $.parseJSON(data);
                if(mydata != null){
                    $('#emailModal form input[name="email"]').val(mydata.email);
                    $('#emailModal form .btn').addClass("bg-primary").removeClass("bg-danger").html("<i class='fas fa-paper-plane'></i> Send");
                    $('#emailModal form .btn').removeAttr("disabled");
                }else{
                    $('#emailModal form .btn').removeClass("bg-primary").addClass("bg-danger").html("<i class='fas fa-exclamation-triangle'></i> Contact Unavailable");
                }
            }).fail(function(data){
                $('#emailModal form .btn').removeClass("bg-primary").addClass("bg-danger").html("<i class='fas fa-exclamation-triangle'></i> Error");
            });
        });
        /*$('#smsModal').on('show.bs.modal', function () {
            $('#smsModal form .btn').html("<i class='fas fa-spinner fa-pulse'></i> Please wait...");
            $('#smsModal form .btn').attr("disabled", "disabled");
        });*/





            //Search user
            $("#contact input[name='emergency']").keyup(function(){
                $('#searchdiv1').show();
                $('#searchdiv1').html("<p class='p-2'><i class='fas fa-spinner fa-pulse'></i> Searching...</p>")
                var timeout;
                clearTimeout(timeout);
                timeout = setTimeout(function(){
                    var search = $("input[name='emergency']").val()
                    $.ajax({
                        url: baseurl+"/users/search/"+search,
                        type: "GET",
                    }).done(function(data){
                        var mydata = $.parseJSON(data);
                        //alert(data);
                        if(mydata.length > 0){
                            $("#searchdiv1").html("<ul class='list-group'>");
                            mydata.forEach(function(myitems){
                                $("#searchdiv1").append("<li class='list-group-item'><small><a class='nav-link font-weight-bold' href='"+myitems.id+"'>"+myitems.firstname+" "+myitems.lastname+", "+myitems.phone+", "+myitems.email+"</a></small></li>");
                            });
                            $("#searchdiv1")/append("</ul>");
                        }else{
                            $("#searchdiv1").html("<p class='text-muted'><i class='fas fa-exclamation-circle'></i> No results</p>");
                        }
                    });
                }, 1000);
            });

            $("body").click(function(e) {
                var $trg = $(e.target).closest("div").attr('id');
                var $trg2 = $(e.target).closest("div").attr("id");
                //alert($trg2);
                if($trg === "searchdiv" || $trg2 === "searchdiv1"){
                    $('#searchdiv1').show();
                }else{
                    $('#searchdiv1').hide();
                }
                /*if($trg === "searchdiv"){
                    $('.searchdiv').hide();
                }else if($trg === "searchdiv1"){
                    $('.searchdiv').hide();
                }else{
                    $('.searchdiv').hide();
                }*/
            });
            $(document).on('click', '#searchdiv1 a', function(e){
                e.preventDefault();
                var id = $(this).attr('href');
                var htm = $(this).text();
                $("input[name='userid']").val(id);
                $("#contact input[name='emergency']").val(htm);
                $.ajax({
                    url: baseurl+"/users/details/"+id,
                    type: "GET",
                }).done(function(data){
                    mydata = $.parseJSON(data);
                    if(mydata != null){
                        var phone = "";
                        if(mydata.phone != null){
                            var str = mydata.phone;
                            phone = str.substring(1, 10);
                        }
                        $("#contact input[name='emergency-no']").val(phone);
                        $('#searchdiv1').hide();
                    }
                });
            });

            //Search user
            $("#family input[name='name']").keyup(function(){
                $('#searchdiv2').show();
                $('#searchdiv2').html("<p class='p-2'><i class='fas fa-spinner fa-pulse'></i> Searching...</p>")
                var timeout;
                clearTimeout(timeout);
                timeout = setTimeout(function(){
                    var search = $("#family input[name='name']").val()
                    $.ajax({
                        url: baseurl+"/users/search/"+search,
                        type: "GET",
                    }).done(function(data){
                        var mydata = $.parseJSON(data);
                        if(mydata.length > 0){
                            $("#searchdiv2").html("<ul class='list-group'>");
                            mydata.forEach(function(myitems){
                                $("#searchdiv2").append("<li class='list-group-item'><small><a class='nav-link font-weight-bold' href='"+myitems.id+"'>"+myitems.firstname+" "+myitems.lastname+", "+myitems.phone+", "+myitems.email+"</a></small></li>");
                            });
                            $("#searchdiv2")/append("</ul>");
                        }else{
                            $("#searchdiv2").html("<p class='text-muted'><i class='fas fa-exclamation-circle'></i> No results</p>");
                        }
                    });
                }, 1000);
            });

            $(document).on('click', '#searchdiv2 a', function(e){
                e.preventDefault();

                var id = $(this).attr('href');
                var htm = $(this).text();
                var result = htm.split(',');

                $("input[name='userid']").val(id);
                $("#family input[name='name']").val(result[0]);
                $("#family input[name='email']").val(result[2]);
                $("#family input[name='phone']").val(result[1].substring(2, 11));
                $('#searchdiv2').hide();
            });

            var p = $("#previewimage");
            $("#family input[type='file']").change(function(){
                var imageReader = new FileReader();
                imageReader.readAsDataURL(document.querySelector(".image").files[0]);

                imageReader.onload = function (oFREvent) {
                    $("#previewimage").attr('src', oFREvent.target.result).fadeIn();
                    /*$('#previewimage').imgAreaSelect({
                        aspectRatio: '1:1',
                        handles: true,
                        show: true,
                        enable:true,
                        onSelectEnd: function (img, selection) {
                            $('input[name="x1"]').val(selection.x1);
                            $('input[name="y1"]').val(selection.y1);
                            $('input[name="w"]').val(selection.width);
                            $('input[name="h"]').val(selection.height);
                        }
                    });*/
                    var start = $('#previewimage').position().left;
                    var height = $('#previewimage').height();
                    var width = $('#previewimage').width();
                    var m = 0;
                    var h = 0;
                    if(height > width){
                        m = parseInt(width/4);
                        h = parseInt(width/2);
                    }else{
                        m = parseInt(height/4);
                        h = parseInt(height/2);
                    }

                    $('input[name="x1"]').val(start+m);
                    $('input[name="y1"]').val(start+m);
                    $('input[name="w"]').val(start+200);
                    $('input[name="h"]').val(start+200);

                    $('#previewimage').imgAreaSelect({
                        aspectRatio: '1:1',
                        x1:start+m, y1:start+m, x2:(start+200), y2:(start+200),
                        handles:true,
                        show: true,
                        onSelectEnd: function (img, selection) {
                            $('input[name="x1"]').val(selection.x1);
                            $('input[name="y1"]').val(selection.y1);
                            $('input[name="w"]').val(selection.width);
                            $('input[name="h"]').val(selection.height);
                        }
                    });
                };

                /*

                var originalH = $('#previewimage').height;
                var originalW = $('#previewimage').width;
                var size =  Math.min($('#previewimage').width()/ratio, $('#previewimage').height());
                var xBiggerThanY = $('#previewimage').width()/ratio > $('#previewimage').height();
                var fullSize = Math.max($('#previewimage').width()/ratio, $('#previewimage').height());
                var sizeX1 = xBiggerThanY ? (fullSize - size) * ratio / 2 : 0;
                var sizeX2 = xBiggerThanY ? size*ratio-1 + (fullSize - size) * ratio / 2 : size*ratio-1;
                var sizeY1 = xBiggerThanY ? 0 : (fullSize - size) / 2;
                var sizeY2 = xBiggerThanY ? size - 1 : size - 1 + (fullSize - size) / 2;

                var initSelection = {x1: sizeX1, y1: sizeY1, x2: sizeX2, y2: sizeY2};
                $('#previewimage').imgAreaSelect( {x1:3, y1:3, x2:6, y2: 6} );
                alert("Crap");*/
            });




            $('.delete-sms').click(function () {
                var success = 0;
                var failed = 0;
                var count = 0;
                var totals = 0;
                $('.sms').find('input[type="checkbox"]:checked').each(function () {
                    count++;
                });
                $('.user-not').html("<p class='text-white'><i class='fas fa-spinner fa-pulse'></i> Deleting <strong>"+(count-success)+"</strong> of <strong>"+count+"</strong> SMS(s)...</p>");
                $('.user-not').show();
                if(count == 0){
                    $('.user-not').html("<p class='text-white'><i class='fas fa-exclamation-triangle'></i> No SMS(s) selected for deleting!</p>");
                    setTimeout(() => {
                        $('.user-not').hide();
                    }, 2000);
                }
                $('.sms').find('input[type="checkbox"]:checked').each(function () {
                    var i = $(this).val();
                    var myurl = "{{url('removesms/')}}/"+i;
                    $.ajax({
                        url: myurl,
                        type: "GET",
                    }).done(function(data){
                        success++;
                        totals = success + failed;
                        if(count == totals){
                            $('.user-not').html("<p class='text-white'><i class='fas fa-check-circle'></i> <strong>"+success+"</strong> Deleted<br><i class='fas fa-exclamation-circle'></i> <strong>"+failed+"</strong> Failed</p>");
                            setTimeout(() => {
                                location.reload();
                                $('.user-not').hide();
                            }, 2000);
                        }
                    }).fail(function(){
                        failed++;
                        totals = success + failed;
                        if(count == totals){
                            $('.user-not').html("<p class='text-white'><i class='fas fa-check-circle'></i> <strong>"+success+"</strong> Deleted<br><i class='fas fa-exclamation-circle'></i> <strong>"+failed+"</strong> Failed</p>");
                            setTimeout(() => {
                                location.reload();
                                $('.user-not').hide();
                            }, 2000);
                        }
                    });
                });
            });

            $('.search-users-form').on('submit', function(e) {
                userstable.draw();
                e.preventDefault();
                $('html, body').animate({
                    scrollTop: $("#users-table").offset().top
                }, 2000);
            });
            $(document).on('click', '#users-table .btn-edit-user', function(e){
                e.preventDefault();
                var id = $(this).attr('href');
                $("#addUserModal .modal-title").text("Edit User");
                $("#addUserModal .form-add-user .btn").attr("disabled", "disabled");
                $("#addUserModal .form-add-user .btn").html("<i class='fas fa-spinner fa-pulse'></i> Loading... Please wait");
                $("#addUserModal").modal("show");
                $.ajax({
                    url: baseurl+"/users/details/"+id,
                    type: "GET",
                }).done(function(data){
                    mydata = $.parseJSON(data);
                    if(mydata != null){
                        var phone = "";
                        if(mydata.phone != null){
                            var str = mydata.phone;
                            phone = str.substring(1, 10);
                        }
                        $(".form-add-user input[name='id']").val(id);
                        $(".form-add-user input[name='fname']").val(mydata.firstname);
                        $(".form-add-user input[name='lname']").val(mydata.lastname);
                        $(".form-add-user input[name='email']").val(mydata.email);
                        $(".form-add-user input[name='phone']").val(phone);
                        $("#addUserModal .modal-title").text("Edit User");
                        $("#addUserModal .form-add-user .btn").text("Edit User");
                        $("#addUserModal .form-add-user .btn").removeAttr("disabled");
                    }
                });
                $("#editUserModal input[name='id']").val(id);
                $("#editUserModal").modal("show");
            });

            $(".btn-add-user").click(function(){
               $(".form-add-user input[name='id']").val(0);
               $(".form-add-user input[name='lname']").val("");
               $(".form-add-user input[name='fname']").val("");
               $(".form-add-user input[name='phone']").val("");
               $(".form-add-user input[name='email']").val("");
               $("#addUserModal .modal-title").text("Add New User");
               $("#addUserModal .form-add-user .btn").text("Save User");
               $("#addUserModal .form-add-user .btn").removeAttr("disabled");
            });


            $('#summernote').summernote({
                placeholder: 'Enter Content Here',
                tabsize: 2,
                height: 200,
                dialogsInBody: false
            });

            $('.summernote').summernote({
                placeholder: 'Enter Content Here',
                tabsize: 2,
                height: 200,
                dialogsInBody: false
            });
            //summaries
            $(".summary-table tbody td:nth-child(3)").click(function(){
                var a = $.trim($(this).text());
                var b = $.trim($(this).closest("tr").find("td:nth-child(4)").text());
                $(this).closest("tr").find("td:nth-child(3)").text(b);
                $(this).closest("tr").find("td:nth-child(4)").text(a);
                var deductions  = 0;
                var collections = 0;
                $('.summary-table tbody tr').each(function (i, row) {
                    var d = $.trim($(row).find("td:nth-child(3)").text().replace(',',''));
                    if($.isNumeric(d)){
                        deductions += parseFloat(d);
                    }
                    var c = $.trim($(row).find("td:nth-child(4)").text().replace(',',''));
                    if($.isNumeric(c)){
                        collections += parseFloat(c);
                    }
                });
                var difference = collections - deductions;
                $('.difference').html("<strong>"+collections+" - "+deductions+"="+(collections-deductions));

                $('.summary-table tfoot td:nth-child(3)').html("<strong>"+deductions+"</strong>");
                $('.summary-table tfoot td:nth-child(4)').html("<strong>"+collections+"</strong>");
            });
            $(".summary-table tbody td:nth-child(4)").click(function(){
                var a = $.trim($(this).text());
                var b = $.trim($(this).closest("tr").find("td:nth-child(3)").text());

                $(this).closest("tr").find("td:nth-child(3)").text(a);
                $(this).closest("tr").find("td:nth-child(4)").text(b);
                var deductions  = 0;
                var collections = 0;
                $('.summary-table tbody tr').each(function (i, row) {
                    var d = $.trim($(row).find("td:nth-child(3)").text().replace(',',''));
                    if($.isNumeric(d)){
                        deductions += parseFloat(d);
                    }
                    var c = $.trim($(row).find("td:nth-child(4)").text().replace(',',''));
                    if($.isNumeric(c)){
                        collections += parseFloat(c);
                    }
                });
                var difference = collections - deductions;
                $('.difference').html("<strong>"+collections+" - "+deductions+"="+(collections-deductions));
                $('.summary-table tfoot td:nth-child(3)').html("<strong>"+deductions+"</strong>");
                $('.summary-table tfoot td:nth-child(4)').html("<strong>"+collections+"</strong>");
            });
        });
        $(document).ready(function(){
            var show = 0;
            $('.write-message').click(function(e){
                e.preventDefault();
                var button = $(this);
                if(show == 0){
                    $('.contacts').hide();
                    $('.message').show();
                    button.html("<i class='fas fa-arrow-left'></i> Show Contacts");
                    show = 1;
                }else{
                    $('.contacts').show();
                    $('.message').hide();
                    button.html("Write Message <i class='fas fa-arrow-right'></i>");
                    show = 0;
                }
            });
        });
        </script>
</body>
</html>
