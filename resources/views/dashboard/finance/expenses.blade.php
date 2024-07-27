@extends('layouts.dashboard')

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h5 class="m-0 text-header"><i class='fas fa-receipt'></i> <b>Expenses</b></h5>
                </div><!-- /.col -->
                <div class="d-none d-sm-block col-sm-6 text-right">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('home') }}">Home</a></li>
                        <li class="breadcrumb-item active">Expenses</li>
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
                        <div class="card-header border-0">
                            <div class="row align-items-center">
                                <!--
                                <div class="col">
                                    <h3 class="mb-0">Expenses</h3>
                                </div>-->
                                <div class="col text-right">

                                    <a href="{{ url('dashboard/settings/funds/sources') }}"
                                        class="btn btn-sm btn-outline-primary">
                                        <span class="d-none d-md-block"><i class='fas fa-cogs'></i> Payment Settings</span>
                                        <span class="d-md-none"><i class='fas fa-cogs'></i></span></a>
                                    <button class="btn btn-sm btn-primary showFundsModal" data-toggle="modal"
                                        data-target="#fundsModal">
                                        <span class="d-none d-md-block"><i class='fas fa-plus-circle'></i> Add</span>
                                        <span class="d-md-none"><i class='fas fa-plus-circle'></i></span></a>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="card-body ">
                            <form class="row search-funds-form" method='GET' action="{{ url('funds/search') }}">
                                <div class="col-sm-3">
                                    <input type="text" class="form-control form-control-alternative" name="msearch"
                                        placeholder="search name, email" />
                                </div>
                                <div class="col-sm-3">
                                    <input type="text" class="form-control form-control-alternative mydatepicker"
                                        name="from" placeholder="From" readonly />
                                </div>
                                <div class="col-sm-3">
                                    <input type="text" class="form-control form-control-alternative mydatepicker"
                                        name="to" placeholder="To" readonly />
                                </div>
                                <div class="col-sm-3">
                                    <button class='btn btn-primary' style='width: 100%;'><i class='fas fa-search'></i>
                                        Search</button>
                                </div>
                            </form>
                        </div>
                        <div class="table-responsive">
                            <!-- Projects table -->
                            <table class="table align-items-center table-flush" id="expenses">
                                <thead class="thead-light">
                                    <tr>
                                        <th scope="col">Member</th>
                                        <th scope="col">Amount</th>
                                        <th scope="col">Through</th>
                                        <th scope="col">Type</th>
                                        <th scope="col">Source</th>
                                        <th scope="col">Date</th>
                                        <th scope="col">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th scope="col">Totals</th>
                                        <th scope="col" colspan='6'></th>
                                    </tr>
                                    <tfoot>
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
                    <h4 class="modal-title" id="exampleModalLabel">Expense</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body pt-1 pb-0 mb-0">
                    <form action="{{ url('dashboard/finances/funds/save') }}" method="post">
                        @csrf
                        <input type='hidden' name='id' value=''>
                        <div class='form-group'>
                            <label>Amount</label>
                                <input id="password" type="text" class="form-control" name="amount"
                                    placeholder='Enter Amount' required>
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
                            <label>Paid Through:</label>
                                <select class="custom-select form-control" name="mode" placeholder='Enter Name'>
                                    @foreach ($modes as $mode)
                                        <option value="{{ $mode->id }}">{{ $mode->name }}</option>
                                    @endforeach
                                </select>
                        </div>
                        <div class='form-group'>
                            <label>Description</label>
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
                //defaultDate: new Date(),
            });
            //funds edit
            $('.showFundsModal').click(function() {
                $('#fundsModal input[name="id"]').val(0);
                $('#fundsModal input[name="amount"]').val("");
            });

            $('.fundsedit').click(function(e) {
                e.preventDefault();
                var id = $(this).attr('href');
                var amount = $(this).closest('tr').find('td:nth-child(2)').text();
                var description = $(this).closest('tr').find('td:nth-child(3)').text();
                $('#fundsModal input[name="id"]').val(id);
                $('#fundsModal input[name="amount"]').val(amount);
                $('#fundsModal select').val(1);
                $('#fundsModal').modal('show');
                $('#fundsModal .modal-body .btn').text("Edit Fund");
            });

            $(".btn").removeAttr('disabled');
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
                    /*{
                        extend: 'copy',
                        text: '<i class="fas fa-copy"></i> Copy',
                        className: '',
                        exportOptions: {
                            columns: ':not(.notexport)'
                        }
                    },*/
                    {
                        extend: 'excel',
                        text: '<i class="fas fa-file-excel"></i> Excel',
                        className: 'btn btn-success text-white',
                        exportOptions: {
                            columns: ':not(.notexport)'
                        }
                    }, {
                        extend: 'pdf',
                        text: '<i class="fas fa-file-pdf"></i> PDF',
                        className: 'btn btn-danger text-white',
                        exportOptions: {
                            columns: ':not(.notexport)'
                        }
                    }
                ],
                ajax: //"{{ url('datatables/users') }}",
                {
                    url: "{{ url('dashboard/finances/datatables/funds') }}",
                    data: function(d) {
                        d.msearch = $('input[name=msearch]').val();
                        d.from = $('input[name=from]').val();
                        d.to = $('input[name=to]').val();
                    }
                },
                columns: [{
                        data: 'user',
                        name: "member",
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: "amount",
                        name: "amount",
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'mode',
                        name: "mode",
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'ftype',
                        name: "type",
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'name',
                        name: "source",
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'created',
                        name: "date",
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'action',
                        name: "action",
                        orderable: false,
                        searchable: false
                    }
                ],
                "footerCallback": function(row, data, start, end, display) {
                    var api = this.api(),
                        data;

                    // Remove the formatting to get integer data for summation
                    var intVal = function(i) {
                        return typeof i === 'string' ?
                            i.replace(/[\$,]/g, '') * 1 :
                            typeof i === 'number' ?
                            i : 0;
                    };

                    // Total over all pages
                    total = api
                        .column(1)
                        .data()
                        .reduce(function(a, b) {
                            return intVal(a) + intVal(b);
                        }, 0);

                    // Total over this page
                    pageTotal = api
                        .column(1, {
                            page: 'current'
                        })
                        .data()
                        .reduce(function(a, b) {
                            return intVal(a) + intVal(b);
                        }, 0);

                    // Update footer
                    $(api.column(1).footer()).html(
                        Number(pageTotal).toLocaleString(
                            'en') //'$'+pageTotal +' ( $'+ total +' total)'
                    );
                }
            });

            var expensestable = $('#expenses').DataTable({
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
                    /*{
                        extend: 'copy',
                        text: '<i class="fas fa-copy"></i> Copy',
                        className: '',
                        exportOptions: {
                            columns: ':not(.notexport)'
                        }
                    },*/
                    {
                        extend: 'excel',
                        text: '<i class="fas fa-file-excel"></i> Excel',
                        className: 'btn btn-success text-white',
                        exportOptions: {
                            columns: ':not(.notexport)'
                        }
                    }, {
                        extend: 'pdf',
                        text: '<i class="fas fa-file-pdf"></i> PDF',
                        className: 'btn btn-primary text-white',
                        exportOptions: {
                            columns: ':not(.notexport)'
                        }
                    }
                ],
                ajax: //"{{ url('datatables/users') }}",
                {
                    url: "{{ url('dashboard/finances/datatables/expenses') }}",
                    data: function(d) {
                        d.msearch = $('input[name=msearch]').val();
                        d.from = $('input[name=from]').val();
                        d.to = $('input[name=to]').val();
                    }
                },
                columns: [{
                        data: 'user',
                        name: "member",
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: "amount",
                        name: "amount",
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'mode',
                        name: "mode",
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'ftype',
                        name: "type",
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'name',
                        name: "source",
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'created',
                        name: "date",
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'action',
                        name: "action",
                        orderable: false,
                        searchable: false
                    }
                ],
                "footerCallback": function(row, data, start, end, display) {

                    var api = this.api()
                    var json = api.ajax.json();
                    $(api.column(1).footer()).html(json.total);
                    /*
                        var api = this.api(), data;

                        // Remove the formatting to get integer data for summation
                        var intVal = function ( i ) {
                            return typeof i === 'string' ?
                                i.replace(/[\$,]/g, '')*1 :
                                typeof i === 'number' ?
                                    i : 0;
                        };

                        // Total over all pages
                        total = api
                            .column( 1 )
                            .data()
                            .reduce( function (a, b) {
                                return intVal(a) + intVal(b);
                            }, 0 );

                        // Total over this page
                        pageTotal = api
                            .column( 1, { page: 'current'} )
                            .data()
                            .reduce( function (a, b) {
                                return intVal(a) + intVal(b);
                            }, 0 );

                        // Update footer
                        $( api.column( 1 ).footer() ).html(
                            Number(pageTotal).toLocaleString('en')//'$'+pageTotal +' ( $'+ total +' total)'
                        );*/
                }
            });

            $("input[name='checkall']").on("change", function() {
                if ($(this).prop("checked") == true) {
                    $("#users-table tbody input").prop("checked", true);
                } else {
                    $("#users-table tbody input").prop("checked", false);
                }
            });

            $('.search-funds-form').on('submit', function(e) {
                fundstable.draw();
                expensestable.draw();
                e.preventDefault();
            });

        });
    </script>
@endpush
