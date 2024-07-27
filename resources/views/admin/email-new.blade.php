@extends('layouts.admin')

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
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col">
                            <h3 class="mb-0"><i class="fas fa-envelope"></i> | New Email</h3>
                        </div>
                        <div class="col text-right">
                            <a href="{{url('emails')}}" class='btn btn-primary btn-sm pt-2 pb-2'><i class='fas fa-arrow-left'></i> Go Back</a>
                        </div>
                    </div>
                </div>

                <div class='card-body bg-secondary'>
                    <form action="" method="POST">
                        @csrf
                        <div class="form-group">
                            <div class="input-group input-group-alternative">
                                <div class="input-group-prepend">
                                    <div class="input-group-text">
                                        <i class="fas fa-envelope"></i>
                                    </div>
                                </div>
                                <input name="users" class="form-control" placeholder="User's Email(s)">
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="input-group input-group-alternative">
                                <div class="input-group-prepend">
                                    <div class="input-group-text">
                                        <i class="fas fa-angle-right"></i>
                                    </div>
                                </div>
                                <input name="subject" class="form-control" placeholder="Subject">
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="input-group input-group-alternative">
                                <div class="input-group-prepend">
                                    <div class="input-group-text">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </div>
                                </div>
                                <textarea name="message" rows="5" class="form-control" placeholder="Message Here"></textarea>
                            </div>
                        </div>
                    </form>
                </div> 
            </div>    

        </div>
    </div>
</div>
@endsection
