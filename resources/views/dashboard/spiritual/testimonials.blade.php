@extends('layouts.dashboard')

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h5 class="m-0 text-header"><i class='fas fa-comment'></i> Testimonials</h5>
                </div><!-- /.col -->
                <div class="col-sm-6 text-right">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('dashboard/home') }}">Home</a></li>
                        <li class="breadcrumb-item active">Testimonials</li>
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
                                <!--<div class="col">
                                        <h3 class="mb-0">Testimonials</h3>
                                    </div>
                                    <div class="col text-right">
                                    <a href="#!" class="btn btn-sm btn-primary">See all</a>
                                </div>-->
                            </div>
                        </div>
                        <div class="table-responsive">
                            <!-- Projects table -->
                            <table class="table align-items-center table-flush">
                                <thead class="thead-light">
                                    <tr>
                                        <th scope="col">user</th>
                                        <th scope="col">Description</th>
                                        <th scope="col">Status</th>
                                        <th scope="col" class='text-right'>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if ($testimonials->isEmpty())
                                        <tr>
                                            <td colspan='4' class='text-center'> <i class='fas fa-ban'></i> No
                                                testimonial yet</td>
                                        </tr>
                                    @endif
                                    @foreach ($testimonials as $testimonial)
                                        <tr>
                                            <td scope='row'>
                                                <div class="media align-items-center">
                                                    <span class="avatar rounded-circle mr-3">
                                                        <img alt="Image placeholder"
                                                            src="{{ $testimonial->image == '' ? asset('profile_images/default.jpg') : asset('profile_images/' . $testimonial->image) }}">
                                                    </span>
                                                    <div class="media-body">
                                                        <span
                                                            class="mb-0 text-sm">{{ $testimonial->firstname . ' ' . $testimonial->lastname }}</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>{{ str_limit($testimonial->testimonial, $limit = 30, $end = '...') }}</td>
                                            <td>
                                                @if ($testimonial->status == 1)
                                                    <span class="badge badge-dot mr-4">
                                                        <i class="bg-success"></i> Activated
                                                    </span>
                                                @else
                                                    <span class="badge badge-dot mr-4">
                                                        <i class="bg-warning"></i> In Active
                                                    </span>
                                                @endif
                                            </td>
                                            <td class='text-right'>
                                                <a href="{{ url('dashboard/spiritual/testimonials/' . $testimonial->id) }}"
                                                    class="btn btn-primary p-1 pr-2 pl-2">View</a>
                                                @if ($testimonial->status == 1)
                                                    <a href="{{ url('dashboard/spiritual/testimonials/deactivate/' . $testimonial->id) }}"
                                                        class="btn btn-danger p-1 pr-2 pl-2" data-toggle='tooltip'
                                                        title='de-activate'>
                                                        <i class='fas fa-ban'></i>
                                                    </a>
                                                @else
                                                    <a href="{{ url('dashboard/spiritual/testimonials/activate/' . $testimonial->id) }}"
                                                        class="btn btn-success p-1 pr-2 pl-2" data-toggle='tooltip'
                                                        title='activate'>
                                                        <i class='fas fa-sync-alt'></i>
                                                    </a>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                </thead>
                            </table>
                        </div>
                    </div>
                    <div class='col-sm-12 mt-2 mb-2'>
                        {{ $testimonials->links() }}
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
