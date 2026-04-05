@extends('layouts.user')

@section('content')
<!-- Header -->
<div class="header bg-gradient-primary pb-6 pt-5 pt-md-6">
    <div class="container-fluid">
        <div class="header-body">
        </div>
    </div>
</div>



<!-- Page content -->
<div class="container-fluid mt--5">
    <div class="row">
        <div class="col-xl-12 mb-5 mb-xl-0">
            <div class="card shadow">
                <div class="card-header border-0">
                    <div class="row align-items-center">
                        <div class="col">
                            <h3 class="mb-0">Funds Sources</h3>
                        </div>
                        <div class="col text-right">
                            @if($permissions1->finances > 1 || $permissions2->finances > 1)
                                <button class="btn btn-sm btn-primary showFundSource" data-toggle="modal" data-target="#fundSourceModal"><i class='fas fa-plus-circle'></i> Add</button>
                            @endif
                        </div>
                    </div>
                </div>
                @if (\Session::has('success'))
                    <div class="alert alert-success alert-dismissable m-1">
                        <a href="#" class="close text-white" data-dismiss="alert" aria-label="close">&times;</a>
                        <i class='fas fa-check-circle'></i> {{ \Session::get('success') }}
                    </div>
                @endif
                @if (\Session::has('error'))
                    <div class="alert alert-danger alert-dismissable m-1">
                        <a href="#" class="close text-white" data-dismiss="alert" aria-label="close">&times;</a>
                            <i class='fas fa-exclamation-circle'></i> {{ \Session::get('error') }}
                    </div>
                @endif
                @if (count($errors) > 0)
                    <div class="alert alert-danger">
                        <a href="#" class="close text-white" data-dismiss="alert" aria-label="close">&times;</a>
                        <strong><i class='fas fa-exclamation-circle'></i> Whoops!</strong> There were some problems with your input.
                        @foreach ($errors->all() as $error)
                            <br><i class='fas fa-angle-right'></i> {{ $error }}</li>
                        @endforeach
                    </div>
                @endif
                <div class="table-responsive">
                    <!-- Projects table -->
                    <table class="table align-items-center table-flush">
                        <thead class="thead-light">
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">Type</th>
                                <th scope="col">Description</th>
                                <th scope="col" class='text-right'>Action</th>
                            </tr>
                        </thead>
                            <tbody>
                                @if($sources->isEmpty())
                                    <tr><td colspan='4' class='text-center'> <i class='fas fa-ban'></i> No types yet</td></tr>
                                @endif
                                <?php $count = 1; ?>
                                @foreach($sources as $fund)
                                <tr>
                                        <td>{{ $count }}</td>
                                        <td scope='row'>{{ $fund->name}}</td>
                                        <td>{{$fund->description}}</td>
                                        <td class='text-right'>
                                            @if($permissions1->finances > 1 || $permissions2->finances > 1)
                                                <a href="{{$fund->id}}" class="btn btn-outline-primary p-1 pr-2 pl-2 editsource" title='edit'>
                                                    <i class='fas fa-edit'></i>
                                                </a>
                                            @endif
                                            @if($permissions1->finances > 2 || $permissions2->finances > 2)
                                                <a href="{{url('removefundsource/'.$fund->id)}}" class="btn btn-danger p-1 pr-2 pl-2" title='delete'>
                                                    <i class='fas fa-trash-alt'></i>
                                                </a>
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
        </div>
    </div>


    <!-- Modal -->
    <div class="modal fade" id="fundSourceModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="exampleModalLabel">Funds Source</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="/savefundsource" method="post">
                        @csrf
                        <input type="hidden" name="id" value="0">
                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                <span class="input-group-text" id="basic-addon2"><i class='fas fa-bookmark text-primary'></i></span>
                            </div>
                            <input id="password" type="text" class="form-control" name="name" placeholder='Enter Name' required autocomplete="current-password">
                        </div>
                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                <span class="input-group-text" id="basic-addon2"><i class='fas fa-comments text-primary'></i></span>
                            </div>
                            <textarea name='description' class="form-control" placeholder='Description' required></textarea>
                        </div>
                        <div class='form-group text-right'>
                            <button type="submit" class="btn btn-primary">Add Funds</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
