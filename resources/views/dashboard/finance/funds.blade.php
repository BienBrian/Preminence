@extends('layouts.dashboard')

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h5 class="m-0 text-header"><i class='fas fa-chart-pie'></i> <b>Overview</b></h5>
                </div>
                <div class="d-none d-sm-block col-sm-6 text-right">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('dashboard/home') }}">Home</a></li>
                        <li class="breadcrumb-item active">Overview</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class='row'>
                <div class="col-xl-12 mb-5 mb-xl-0">
                    <div class="card shadow">
                        <div class="card-header border-0">
                            <div class="row align-items-center">
                                <div class="col text-right">
                                    <a href="{{ url('dashboard/settings/funds/sources') }}"
                                        class="btn btn-sm btn-outline-primary">
                                        <span class="d-none d-md-block"><i class='fas fa-cogs'></i> Payment Settings</span>
                                        <span class="d-md-none"><i class='fas fa-cogs'></i></span>
                                    </a>
                                    <button class="btn btn-sm btn-primary showFundsModal" data-toggle="modal"
                                        data-target="#fundsModal">
                                        <span class="d-none d-md-block"><i class='fas fa-plus-circle'></i> Add</span>
                                        <span class="d-md-none"><i class='fas fa-plus-circle'></i></span>
                                    </button>
                                </div>
                                <div class="col-sm-12">
                                    <ul class="nav nav-tabs" id="fundsTab" role="tablist">
                                        <li class="nav-item">
                                            <a class="nav-link active" id="general-tab" data-toggle="tab" href="#general"
                                                role="tab" aria-controls="general" aria-selected="true">General</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" id="daily-tab" data-toggle="tab" href="#daily"
                                                role="tab" aria-controls="daily" aria-selected="false">Daily</a>
                                        </li>
                                    </ul>
                                    <div class="tab-content border-1" id="fundsTabContent">
                                        <div class="tab-pane fade show active" id="general" role="tabpanel"
                                            aria-labelledby="general-tab">
                                            <form class="row search-funds-form pt-3">
                                                <div class="col-sm-3">
                                                    <input type="text" class="form-control form-control-alternative"
                                                        name="msearch" placeholder="Search name, description" />
                                                </div>
                                                <div class="col-sm-3">
                                                    <input type="text"
                                                        class="form-control form-control-alternative mydatepicker"
                                                        name="from" placeholder="From" readonly />
                                                </div>
                                                <div class="col-sm-3">
                                                    <input type="text"
                                                        class="form-control form-control-alternative mydatepicker"
                                                        name="to" placeholder="To" readonly />
                                                </div>
                                                <div class="col-sm-3">
                                                    <button class='btn btn-primary' type="submit" style='width: 100%;'><i class='fas fa-search'></i> Search</button>
                                                </div>
                                            </form>
                                        </div>
                                        <div class="tab-pane fade" id="daily" role="tabpanel"
                                            aria-labelledby="daily-tab">
                                            <form class="row daily-search-funds-form pt-3">
                                                <div class="col-sm-4">
                                                    <select class="form-control" name="sources">
                                                        <option value="0">All Sources</option>
                                                        @foreach ($sources as $source)
                                                            <option value="{{ $source->id }}">{{ $source->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-sm-4">
                                                    <input type="text" class="form-control mydatepicker"
                                                        name="date" placeholder="Select Date" readonly />
                                                </div>
                                                <div class="col-sm-4">
                                                    <button class='btn btn-primary' type="submit" style='width: 100%;'><i
                                                            class='fas fa-search'></i> Search</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table align-items-center table-flush" id="funds">
                                <thead class="thead-light">
                                    <tr>
                                        <th scope="col">Member</th>
                                        <th scope="col">Amount</th>
                                        <th scope="col">Through</th>
                                        <th scope="col">Type</th>
                                        <th scope="col">Source</th>
                                        <th scope="col">Date</th>
                                        <th scope="col" class="notexport d-none"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th scope="col">Totals</th>
                                        <th scope="col" colspan='6'></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Modal -->
    <div class="modal fade" id="fundsModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header border-bottom">
                    <h4 class="modal-title" id="exampleModalLabel">Funds</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{ url('dashboard/finances/funds/save') }}" method="post">
                        @csrf
                        <input type='hidden' name='id' value=''>
                        <div class='form-group'>
                            <label>Amount:</label>
                            <input type="text" class="form-control" name="amount"
                                placeholder='Enter Amount' required>
                        </div>
                        <div class='form-group'>
                            <label>Source:</label>
                            <select class="custom-select form-control" name="source" placeholder='Enter Name'>
                                @foreach ($sources as $source)
                                    <option value="{{ $source->id }}">{{ $source->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class='form-group'>
                            <label class='small'>Paid Through:</label>
                            <select class="custom-select form-control" name="mode" placeholder='Enter Name'>
                                @foreach ($modes as $mode)
                                    <option value="{{ $mode->id }}">{{ $mode->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class='form-group'>
                            <label>Description:</label>
                            <textarea name='description' class="form-control" placeholder='Description' rows="4">Say Something</textarea>
                        </div>
                        <div class="form-group text-right">
                            <button type="submit" class="btn btn-primary btn-submit-funds">Add Funds</button>
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
            flatpickr(".mydatepicker", {
                enableTime: false,
                dateFormat: "Y-m-d",
            });

            // Funds modal reset
            $('.showFundsModal').click(function() {
                $('#fundsModal input[name="id"]').val(0);
                $('#fundsModal input[name="amount"]').val("");
            });

            $('.fundsedit').click(function(e) {
                e.preventDefault();
                var id = $(this).attr('href');
                var amount = $(this).closest('tr').find('td:nth-child(2)').text();
                $('#fundsModal input[name="id"]').val(id);
                $('#fundsModal input[name="amount"]').val(amount);
                $('#fundsModal select').val(1);
                $('#fundsModal').modal('show');
                $('#fundsModal .modal-body .btn').text("Edit Fund");
            });

            $(".btn").removeAttr('disabled');

            // Track active filter mode
            var filterMode = 'general';

            var fundstable = $('#funds').DataTable({
                processing: true,
                serverSide: true,
                oLanguage: {
                    sProcessing: "<i class='fas fa-spinner fa-pulse'></i> Processing..."
                },
                "language": {
                    "paginate": {
                        "previous": "<i class='fas fa-angle-left'></i>",
                        "next": "<i class='fas fa-angle-right'></i>"
                    }
                },
                dom: 'lBrtip',
                buttons: [
                    {
                        extend: 'excel',
                        text: '<i class="fas fa-file-excel"></i> Excel',
                        className: 'btn btn-success text-white btn-sm',
                        exportOptions: { columns: ':not(.notexport)' }
                    },
                    {
                        extend: 'pdf',
                        text: '<i class="fas fa-file-pdf"></i> PDF',
                        className: 'btn btn-danger text-white btn-sm',
                        exportOptions: { columns: ':not(.notexport)' }
                    },
                    {
                        extend: 'print',
                        text: '<i class="fas fa-print"></i> Print',
                        className: 'btn btn-info text-white btn-sm',
                        exportOptions: { columns: ':not(.notexport)' }
                    }
                ],
                ajax: {
                    url: "{{ url('dashboard/finances/datatables/funds') }}",
                    data: function(d) {
                        if (filterMode === 'general') {
                            d.msearch = $('.search-funds-form input[name=msearch]').val();
                            d.from = $('.search-funds-form input[name=from]').val();
                            d.to = $('.search-funds-form input[name=to]').val();
                        } else {
                            d.source = $('.daily-search-funds-form select[name=sources]').val();
                            d.date = $('.daily-search-funds-form input[name=date]').val();
                        }
                    }
                },
                columns: [
                    { data: 'user', name: "member", orderable: false, searchable: false },
                    { data: "amount", name: "amount", orderable: false, searchable: false },
                    { data: 'mode', name: "mode", orderable: false, searchable: false },
                    { data: 'ftype', name: "type", orderable: false, searchable: false },
                    { data: 'name', name: "source", orderable: false, searchable: false },
                    { data: 'created', name: "date", orderable: false, searchable: false },
                    { data: 'action', name: "action", orderable: false, searchable: false, visible: false }
                ],
                "footerCallback": function(row, data, start, end, display) {
                    var api = this.api();
                    var intVal = function(i) {
                        return typeof i === 'string' ? i.replace(/[\$,]/g, '') * 1 : typeof i === 'number' ? i : 0;
                    };
                    var pageTotal = api.column(1, { page: 'current' }).data().reduce(function(a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);
                    $(api.column(1).footer()).html(Number(pageTotal).toLocaleString('en'));
                }
            });

            // General tab filter
            $('.search-funds-form').on('submit', function(e) {
                e.preventDefault();
                filterMode = 'general';
                fundstable.draw();
            });

            // Daily tab filter
            $('.daily-search-funds-form').on('submit', function(e) {
                e.preventDefault();
                filterMode = 'daily';
                fundstable.draw();
            });

            // Switch filter mode when tab changes
            $('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
                filterMode = $(e.target).attr('href') === '#daily' ? 'daily' : 'general';
                fundstable.draw();
            });
        });
    </script>
@endpush
