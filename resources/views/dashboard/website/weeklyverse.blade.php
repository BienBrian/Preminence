@extends('layouts.dashboard')
@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2 d-flex align-items-center">
                <div class="col">
                    <h5 class="m-0 text-header"><i class='fas fa-bible'></i> Weekly Verse</h5>
                </div>
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-xl-12 mb-5 mb-xl-0">
                    <div class="card">
                        <div class="card-body mb-0 p-0">
                            <!-- Projects table -->
                            <div class='card-body'>
                                <form action="{{ url('dashboard/website/weeklyverse/add') }}" method="post" class='row'>
                                    @csrf
                                    <input type="hidden" name="id" value="{{ $verse == null ? '0' : $verse->id }}">
                                    <div class='form-group col-sm-6'>
                                        <label>Verse Content:</label>
                                        <textarea class='form-control summernote' name="description" rows="5" placeholder="Enter Verse Content" required>{{ $verse == null ? '' : $verse->description }}</textarea>
                                    </div>
                                    <div class='form-group col-sm-6'>
                                        <label>Verse:</label>
                                        <input class='form-control' name="verse" placeholder="Verse from"
                                            value="{{ $verse == null ? '' : $verse->verse }}" required>
                                        <label>Version:</label>
                                        <input class='form-control' name="version" placeholder="Version"
                                            value="{{ $verse == null ? '' : $verse->version }}" required>
                                    </div>
                                    <div class="form-group col-sm-12 text-right">
                                        <button type="submit" class="btn btn-primary">Save changes</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
