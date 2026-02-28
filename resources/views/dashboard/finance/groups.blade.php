@extends('layouts.dashboard')
@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h5 class="m-0 text-header"><i class='fas fa-users'></i> <b>Groups</b></h5>
                </div><!-- /.col -->
                <div class="d-none d-sm-block col-sm-6 text-right">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('dashboard/home') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ url('finances/activities') }}">Activities</a></li>
                        <li class="breadcrumb-item active">Groups</li>
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
                        <div class='card-header'>
                            <h5>{{ $activity->name }}</h5>
                        </div>
                        <div class='card-body'>
                            {{ $activity->description }}<br>
                            <p class='font-weight-bold'>Amount: {{ number_format($activity->amount, 2) }} | Deadline:
                                @if (\Carbon\Carbon::parse($activity->closes_on) > \Carbon\Carbon::now())
                                    <span
                                        class='text-success font-weight-bold'>{{ \Carbon\Carbon::parse($activity->closes_on)->diffInDays(\Carbon\Carbon::now()) }}
                                        day(s) remaining</span>
                                @else
                                    <span
                                        class='text-danger font-weight-bold'>{{ \Carbon\Carbon::parse($activity->closes_on)->format('d M, Y') }}</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="card shadow">
                        <div class="card-header border-0">
                            <div class="row align-items-center">
                                <div class="col">
                                    <h5 class="mb-0"><i class="fas fa-users"></i> | Groups</h5>
                                </div>
                                <div class="col text-right">
                                    <button class="btn btn-primary btn-sm" data-toggle="modal"
                                        data-target="#activityModal"><i class='fas fa-plus-circle'></i> New Group</button>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <!-- Projects table -->
                            <table class="table align-items-center table-flush">
                                <thead class="thead-light">
                                    <tr>
                                        <th scope="col">Group</th>
                                        <th scope="col">Created By</th>
                                        <th scope="col">Amount</th>
                                        <th scope="col">Paid</th>
                                        <th scope="col">Balance</th>
                                        <th class='text-right'>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if ($groups->isEmpty())
                                        <tr>
                                            <td colspan='6' class='text-center'> <i class='fas fa-ban'></i> No Groups yet
                                            </td>
                                        </tr>
                                    @endif
                                    @foreach ($groups as $group)
                                        <tr>
                                            <td>{{ $group->name }}</td>
                                            <td>{{ $group->firstname }} {{ $group->lastname }}</td>
                                            <td>{{ number_format($group->amount, 2) }}</td>
                                            <td>
                                                {{ number_format($group->paid, 2) }}
                                            </td>
                                            <td>
                                                {{ number_format($group->amount - $group->paid, 2) }}
                                            </td>
                                            <td class='text-right'>
                                                <a href="{{ url('dashboard/finances/activities/groups/participants/' . $group->id) }}"
                                                    class='btn btn-default btn-sm'>Participants</a>
                                                @if ($group->status == 0)
                                                    <a href="#" onclick="return postAction('{{ url('dashboard/groups/recieved/' . $group->id) }}')"
                                                        class='btn btn-success btn-sm'>Recieved</a>
                                                    <a href="#" onclick="return postAction('{{ url('dashboard/groups/remove/'. $group->id) }}', 'Delete this group?')" class='btn btn-danger btn-sm'>Delete</a>
                                                @endif
                                            </td>
                                        </tr>
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
    <!-- Modal -->
    <div class="modal fade" id="activityModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title" id="exampleModalLabel">New Pledge Group</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body pt-1 pb-0 mb-0">
                    <form action="{{ url('dashboard/finances/activities/users/groups/add') }}" method="post"
                        class="group-form">
                        @csrf
                        <input type='hidden' name='id' value='0'>
                        <input type='hidden' name='activity' value='{{ $activity->id }}'>
                        <div class='form-group'>
                            <label>Group Name</label>
                            <input name="name" class="form-control" placeholder="Group Name">
                        </div>
                        <div class='form-group'>
                            <label>Amount</label>
                            <input name="amount" class="form-control" placeholder="Amount">
                        </div>

                        <div class="form-group text-right">
                            <button type="submit" class="btn btn-primary">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
