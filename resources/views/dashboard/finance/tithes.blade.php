@extends('layouts.dashboard')

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h5 class="m-0 text-header"><i class='fas fa-donate'></i> <b>User Contributions</b></h5>
                </div><!-- /.col -->
                <div class="d-none d-sm-block col-sm-6 text-right">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('dashboard/home') }}">Home</a></li>
                        <li class="breadcrumb-item active">User Contributions</li>
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
                        <div class='card-header'>
                            <div class='row'>
                                <div class='col'>
                                    <h5>{{ $user->firstname }} {{ $user->lastname }}</h5>
                                </div>
                                <div class='col text-right'>

                                    <a href="{{ $user->id }}" class='btn btn-primary btn-sm add-tithe'><i
                                            class='fas fa-plus'></i> Add</a>
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
                                        <th scope="col">Email</th>
                                        <th scope="col">Date</th>
                                        <th scope="col">Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if ($tithes->isEmpty())
                                        <tr>
                                            <td colspan='5' class='text-center'> <i class='fas fa-ban'></i> No tithes yet
                                            </td>
                                        </tr>
                                    @endif
                                    <?php $count = 1; ?>
                                    @foreach ($tithes as $tithe)
                                        <tr>
                                            <td>{{ $count }}</td>
                                            <td scope='row'>{{ $tithe->firstname }} {{ $tithe->lastname }}</td>
                                            <td>{{ $tithe->email }}</td>
                                            <td>{{ \Carbon\Carbon::parse($tithe->created_at)->diffForHumans() }}</td>
                                            <td>{{ number_format($tithe->amount, 2) }}</td>
                                            <!--<td class='text-right'>
                                                    <a href="{{ $tithe->id }}"
                                                        class='btn btn-outline-primary btn-sm add-tithe'>Add</a>
                                                </td>-->
                                        </tr>
                                        <?php $count++; ?>
                                    @endforeach
                                </tbody>
                                </thead>
                            </table>
                        </div>
                    </div>
                    <div class='col-sm-12 mt-2 mb-2'>
                        {{ $tithes->links() }}
                    </div>
                </div>
            </div>


            <!-- Modal -->
            <div class="modal fade" id="titheModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header border-bottom">
                            <h4 class="modal-title" id="exampleModalLabel">Funds</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body pt-1 pb-0 mb-0">
                            <form action="{{ url('dashboard/finances/tithes/individual/save') }}" method="post">
                                @csrf
                                <input type='hidden' name='id' value='0'>
                                <div class='form-group'>
                                    <label>Amount</label>

                                    <input id="password" type="text" class="form-control" name="amount"
                                        placeholder='Enter Amount' required>
                                </div>

                                <div class='form-group'>
                                    <label>Paid Through:</label>
                                        <select class="custom-select form-control" name="mode" placeholder='Enter Name'>
                                            @foreach ($modes as $mode)
                                                <option value="{{ $mode->id }}">{{ $mode->name }}</option>
                                            @endforeach
                                        </select>
                                </div>
                                <div class='form-group'>
                                    <label>Source</label>
                                    <select class="custom-select form-control" name="source" placeholder='Enter Name'>
                                        @foreach ($sources as $source)
                                            <option value="{{ $source->id }}">{{ $source->name }}</option>
                                        @endforeach
                                    </select>

                                </div>
                                <div class='form-group'>
                                    <label>Note To User</label>
                                    <textarea name='note' class="form-control" placeholder='Note to User' rows="4">Your Contribution has been received. May peace be within your walls and prosperity in your palaces</textarea>

                                </div>
                                <div class="form-group text-right">
                                    <button type="submit" class="btn btn-primary btn-submit-funds">Add Tithe</button>
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
                    $('.add-tithe').click(function(e) {
                        e.preventDefault();
                        var id = $(this).attr('href');
                        $("#titheModal input[name='id']").val(id);
                        $("#titheModal").modal("show");
                    });
                });
            </script>
        @endpush
