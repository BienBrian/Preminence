@extends('layouts.dashboard')

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h5 class="m-0 text-header"><i class='fas fa-blog'></i> Articles</h5>
                </div>
                <div class="col-sm-6 text-right">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('dashboard/home') }}">Home</a></li>
                        <li class="breadcrumb-item active">Articles</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <!-- Stats row -->
            <div class="row mb-3">
                <div class="col-md-3">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3>{{ $total }}</h3>
                            <p>Total Articles</p>
                        </div>
                        <div class="icon"><i class="fas fa-blog"></i></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3>{{ $published }}</h3>
                            <p>Published</p>
                        </div>
                        <div class="icon"><i class="fas fa-check-circle"></i></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3>{{ $drafts }}</h3>
                            <p>Drafts</p>
                        </div>
                        <div class="icon"><i class="fas fa-pencil-alt"></i></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="small-box bg-primary">
                        <div class="inner">
                            <h3>{{ $featured }}</h3>
                            <p>Featured</p>
                        </div>
                        <div class="icon"><i class="fas fa-star"></i></div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-xl-12 mb-5 mb-xl-0">
                    <div class="card shadow">
                        <div class="card-header border-0">
                            <div class="row align-items-center">
                                <div class="col-sm-4">
                                    <form class="search-form">
                                        <input type="text" class="form-control" name="msearch" placeholder="Search articles...">
                                    </form>
                                </div>
                                <div class="col-sm-3">
                                    <select class="form-control" id="category-filter">
                                        <option value="">All Categories</option>
                                        @foreach($categories as $cat)
                                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-sm-5 text-right">
                                    <a href="{{ url('dashboard/articles/new') }}" class="btn btn-primary btn-sm"><i class='fas fa-plus-circle'></i> New Article</a>
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table align-items-center table-flush">
                                <thead class="thead-light">
                                    <tr>
                                        <th scope="col">Preview</th>
                                        <th scope="col">Author</th>
                                        <th scope="col">Category</th>
                                        <th scope="col">Status</th>
                                        <th scope="col">Stats</th>
                                        <th scope="col">Date</th>
                                        <th scope="col" class="text-right notexport">Actions</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
@push('js')
    <script>
        $(document).ready(function() {
            var table = $('.table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ url('dashboard/articles/datatable') }}",
                    data: function(d) {
                        d.msearch = $('input[name=msearch]').val();
                        d.category_filter = $('#category-filter').val();
                    }
                },
                buttons: [
                    {
                        extend: 'excel',
                        text: '<i class="fas fa-file-excel"></i> Excel',
                        className: 'btn border btn-sm',
                        title: 'Articles',
                        exportOptions: { columns: ':not(.notexport)' }
                    },
                    {
                        extend: 'pdf',
                        text: '<i class="fas fa-file-pdf"></i> PDF',
                        className: 'btn border btn-sm',
                        title: 'Articles',
                        exportOptions: { columns: ':not(.notexport)' }
                    },
                    {
                        extend: 'print',
                        text: '<i class="fas fa-print"></i> Print',
                        className: 'btn border btn-sm',
                        title: 'Articles',
                        exportOptions: { columns: ':not(.notexport)' }
                    }
                ],
                dom: "<'top'B>rt<'bottom'lip><'clear'>",
                "lengthMenu": [[15, 50, 100], [15, 50, 100]],
                columns: [
                    { data: 'preview', name: 'preview', orderable: false, searchable: false },
                    { data: 'author', name: 'author', orderable: false, searchable: false },
                    { data: 'category', name: 'category', orderable: false, searchable: false },
                    { data: 'status_badge', name: 'status_badge', orderable: false, searchable: false },
                    { data: 'stats', name: 'stats', orderable: false, searchable: false },
                    { data: 'date', name: 'date', orderable: false, searchable: false },
                    { data: 'action', name: 'action', orderable: false, searchable: false, className: 'notexport' }
                ]
            });

            $('.search-form').submit(function(e) { e.preventDefault(); });
            var timer = null;
            $('input[name=msearch]').keyup(function() {
                clearTimeout(timer);
                timer = setTimeout(function() { table.draw(); }, 500);
            });
            $('#category-filter').change(function() {
                table.draw();
            });
        });
    </script>
@endpush
