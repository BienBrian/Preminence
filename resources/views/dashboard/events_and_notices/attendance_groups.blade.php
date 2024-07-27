@extends('layouts.dashboard')

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h5 class="m-0 text-header"><i class='fas fa-users'></i> <b>Attendance</b> Groups</h5>
                </div><!-- /.col -->
                <div class="col-sm-6 text-right">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('home') }}">Home</a></li>
                        <li class="breadcrumb-item active">Attendance Groups</li>
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
                                    <h3 class="mb-0"><i class="fas fa-users"></i> | Attendance Groups</h3>
                                </div>-->
                                <div class="col text-right">
                                    <button class='btn btn-primary btn-sm' data-toggle="modal"
                                        data-target="#attendanceGroupModal"><i class='fas fa-plus'></i> Add</button>
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
                                        <th scope="col" class='text-right'>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if ($groups->isEmpty())
                                        <tr>
                                            <td colspan='3' class='text-center'> <i class='fas fa-ban'></i> No Attendance
                                                yet</td>
                                        </tr>
                                    @endif
                                    <?php $count = 1; ?>
                                    @foreach ($groups as $group)
                                        <tr>
                                            <td>{{ $count }}</td>
                                            <td>
                                                {{ $group->name }}
                                            </td>
                                            <td class='text-right'>
                                                <!--<a href="{{ url('editcommunity/' . $group->id) }}" class='btn btn-primary btn-sm'>Edit</a>-->
                                                <a href="{{ url('dashboard/events_and_notices/attendance/groups/remove/' . $group->id) }}"
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
                        {{ $groups->links() }}
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Import Modal -->
    <div class="modal fade" id="attendanceGroupModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title" id="exampleModalLabel"><i class='fas fa-users'></i> Add Attendance Group
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{ url('dashboard/events_and_notices/attendance/group/add') }}" method="POST">
                        @csrf
                        <input type='hidden' name='id' value='0'>
                        <label>Group Name</label>
                        <div class="form-group text-right">
                            <div class='input-group input-group-alternative'>
                                <div class='input-group-prepend'>
                                    <span class="input-group-text"><i class='fas fa-users'></i></span>
                                </div>
                                <input type="text" name="name" class="form-control" placeholder="group name" required>
                            </div>
                        </div>
                        <div class="text-right">
                            <button class='btn btn-primary'>Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
