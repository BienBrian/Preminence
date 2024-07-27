@extends('layouts.admin')

@section('content')
<!-- Header -->
<div class="header bg-gradient-primary pb-6 pt-5 pt-md-6">
    <div class="container-fluid">
        <div class="header-body text-white">
            <hr class="bg-white">
            <h3 class='text-white'>Welcome to <span style='font-weight: 200;'>{{ $site_settings == null?"CHURCH APP":$site_settings->name}}</span>  Management System Dashboard</h3>
            <p>We value your satisfaction</p>
        </div>
    </div>
</div>

<!-- Page content -->
<div class="container-fluid mt--5">
    <div class="row">
        
        <div class="col-sm-6 col-md-4 mb-3">
            <div class="card shadow h-100">
                <div class='card-body'>
                    <p><i class="fas fa-globe fa-3x text-primary"></i></p>
                    <p>Manage Websites Module<br>
                    <span style='font-weight: 200; font-size: 1.2em;'>Websites</span></p>
                    <table class='w-100'>
                        <tr>
                            <td class="p-2"><a href='{{url("/settings")}}' class='text-primary'><i class='fas fa-cog'></i> Settings</a></td>
                            <td class="p-2"><a href='{{url("/homepage")}}' class='text-primary'><i class='fas fa-home'></i> Home Page</a></td>
                        </tr>
                        <tr>
                            <td class="p-2"><a href='{{url("/gallery")}}' class='text-primary'><i class='fas fa-camera'></i> Gallery</a></td>
                            <td class="p-2"><a href='{{url("/pastorsmessage")}}' class='text-primary'><i class='fa fa-comment'></i> Message</a></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="col-sm-6 col-md-4 mb-3">
            <div class="card shadow h-100">
                <div class='card-body'>
                    <p><i class="fas fa-coins fa-3x text-danger"></i></p>
                    <p>Manage Finances Module<br>
                    <span style='font-weight: 200; font-size: 1.2em;'>Finances</span></p>
                    <table class='w-100'>
                        <tr>
                            <td class="p-2"><a href='{{url("/overview")}}' class='text-danger'><i class='fas fa-chart-line'></i> Overview</a></td>
                            <td class="p-2"><a href='{{url("/funds")}}' class='text-danger'><i class='fas fa-coins'></i> Funds</a></td>
                        </tr>
                        <tr>
                            <td class="p-2"><a href='{{url("tithing/individual")}}' class='text-danger'><i class='fas fa-donate'></i> Tithing</a></td>
                            <td class="p-2"><a href='{{url("/budget")}}' class='text-danger'><i class='fa fa-file-pdf'></i> Budgets</a></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="col-sm-6 col-md-4 mb-3">
            <div class="card shadow h-100">
                <div class='card-body'>
                    <p><i class="fas fa-users fa-3x text-success"></i></p>
                    <p>Manage People Module<br>
                    <span style='font-weight: 200; font-size: 1.2em;'>People</span></p>
                    <table class='w-100'>
                        <tr>
                            <td class="p-2"><a href='{{url("/people/add")}}' class='text-success'><i class='fas fa-user-plus'></i> New</a></td>
                            <td class="p-2"><a href='{{url("/users")}}' class='text-success'><i class='fas fa-users'></i> All Users</a></td>
                        </tr>
                        <tr>
                            <td class="p-2"><a href='{{url("pastors")}}' class='text-success'><i class='fas fa-bible'></i> Pastors</a></td>
                            <td class="p-2"><a href='{{url("/budget")}}' class='text-success'><i class='far fa-user'></i> Communities</a></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="col-sm-6 col-md-4 mb-3">
            <div class="card shadow h-100">
                <div class='card-body'>
                    <p><i class="fas fa-bell fa-3x text-dark"></i></p>
                    <p>Manage Events & Notices<br>
                    <span style='font-weight: 200; font-size: 1.2em;'>Events & Notices</span></p>
                    <table class='w-100'>
                        <tr>
                            <td class="p-2"><a href='{{url("events")}}' class='text-dark'><i class='fas fa-bell'></i> Events</a></td>
                            <td class="p-2"><a href='{{url("notices")}}' class='text-dark'><i class='fas fa-calendar-alt'></i> Notices</a></td>
                        </tr>
                        <tr>
                            <td class="p-2"><a href='{{url("admin/seminars")}}' class='text-dark'><i class='fas fa-bible'></i> Seminars</a></td>
                            <td class="p-2"><a href='{{url("attendance")}}' class='text-dark'><i class='far fa-calendar'></i> Attendance</a></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="col-sm-6 col-md-4 mb-3">
            <div class="card shadow h-100">
                <div class='card-body'>
                    <p><i class="fas fa-bible fa-3x text-muted"></i></p>
                    <p>Manage Spiritual Module<br>
                    <span style='font-weight: 200; font-size: 1.2em;'>Spiritual</span></p>
                    <table class='w-100'>
                        <tr>
                            <td class="p-2"><a href='{{url("sermons")}}' class='text-muted'><i class='fas fa-angle-right'></i> Sermons</a></td>
                            <td class="p-2"><a href='{{url("prayers")}}' class='text-muted'><i class='fas fa-angle-right'></i> Prayers</a></td>
                        </tr>
                        <tr>
                            <td class="p-2"><a href='{{url("testimonials")}}' class='text-muted p-2 pt-4'><i class='fas fa-angle-right'></i> Testimonials</a></td>
                            <td></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="col-sm-6 col-md-4 mb-3">
            <div class="card shadow h-100">
                <div class='card-body'>
                    <p><i class="fas fa-envelope fa-3x text-blue"></i></p>
                    <p>Manage Communication<br>
                    <span style='font-weight: 200; font-size: 1.2em;'>Communication</span></p>
                    <table class='w-100'>
                        <tr>
                            <td class='p-2'><a href='{{url("emails")}}' class='text-blue'><i class='fas fa-envelope'></i> Emails</a></td>
                            <td class='p-2'><a href='{{url("sms")}}' class='text-blue'><i class='fas fa-comment'></i> SMS</a></td>
                        </tr>
                        <tr>
                            <td class='p-2'><a href='{{url("schedule/sms")}}' class='text-blue'><i class='fas fa-clock'></i> Scheduled</a></td>
                            <td></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

    </div>
@endsection
