@extends('layouts.dashboard')

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h5 class="m-0 text-header"><i class='fas fa-table'></i> <b>Budgets</b></h5>
                </div><!-- /.col -->
                <div class="d-none d-sm-block col-sm-6 text-right">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('home') }}">Home</a></li>
                        <li class="breadcrumb-item active">Budgets</li>
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
                                <div class="col">
                                    <h3 class="mb-0"><i class="fas fa-table"></i> | Budgets</h3>
                                </div>
                                <div class="col text-right">
                                    <!--<button class="btn btn-primary btn-sm btn-show-activity" data-toggle="modal" data-target="#activityModal"><i class='fas fa-circle-plus'></i> New</button>-->
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="nav-wrapper">
                        <ul class="nav nav-pills nav-fill flex-column flex-md-row" id="tabs-icons-text" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link mb-sm-3 mb-md-0 active" id="tabs-icons-text-1-tab" data-toggle="tab"
                                    href="#tabs-icons-text-1" role="tab" aria-controls="tabs-icons-text-1"
                                    aria-selected="true">
                                    <i class="fas fa-table"></i> All
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link mb-sm-3 mb-md-0" id="tabs-icons-text-2-tab" data-toggle="tab"
                                    href="#tabs-icons-text-2" role="tab" aria-controls="tabs-icons-text-2"
                                    aria-selected="false">
                                    <i class="far fa-file-excel"></i> New Budget
                                </a>
                            </li>
                        </ul>
                    </div>

                    <div class="card shadow">
                        <div class="card-body">
                            <div class="tab-content" id="myTabContent">
                                <div class="tab-pane fade show active" id="tabs-icons-text-1" role="tabpanel"
                                    aria-labelledby="tabs-icons-text-1-tab">
                                    <div class='table-responsive'>
                                        <table class='table'>
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Name</th>
                                                    <th>Start</th>
                                                    <th>End</th>
                                                    <th class='text-right'>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @if ($budget->isEmpty())
                                                    <tr>
                                                        <td colspan="5"><i class='fas fa-ban'></i> No budget created yet
                                                        </td>
                                                    </tr>
                                                @endif
                                                <?php $count = 1; ?>
                                                @foreach ($budget as $b)
                                                    <tr>
                                                        <td>{{ $count }}</td>
                                                        <td>{{ $b->name }}</td>
                                                        <td>{{ \Carbon\Carbon::parse($b->start)->format('d M, Y') }}</td>
                                                        <td>{{ \Carbon\Carbon::parse($b->end)->format('d M, Y') }}</td>
                                                        <td class='text-right'>
                                                            <a href='{{ url('dashboard/finances/budgets/preview/' . $b->id) }}'
                                                                class='btn btn-outline-primary btn-sm'><i
                                                                    class='fas fa-eye'></i> Preview</a>
                                                            <a href='{{ url('dashboard/finances/budgets/edit/' . $b->id) }}'
                                                                class='btn btn-primary btn-sm'><i class='fas fa-edit'></i>
                                                                Edit</a>
                                                            <a href='{{ url('dashboard/finances/budgets/remove/' . $b->id) }}'
                                                                class='btn btn-danger btn-sm'><i class='fas fa-trash'></i>
                                                                Delete</a>
                                                        </td>
                                                    </tr>
                                                    <?php $count++; ?>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class='p-2 mt-3'>
                                        {{ $budget->links() }}
                                    </div>
                                </div>

                                <div class="tab-pane fade" id="tabs-icons-text-2" role="tabpanel"
                                    aria-labelledby="tabs-icons-text-2-tab">
                                    <form class='row d-flex align-items-center' method="POST"
                                        action="{{ url('dashboard/finances/budget/save') }}" id='budget-form'>
                                        @csrf
                                        <div class='col-sm-12'>

                                            <div class='form-group'>
                                                <label>Budget Name</label>
                                                <div class='input-group'>
                                                    <div class='input-group-prepend'>
                                                        <div class='input-group-text'>
                                                            <i class='fas fa-calendar'></i>
                                                        </div>
                                                    </div>
                                                    <input type='text' name="name" class='form-control'
                                                        placeholder='Budget Name' required>
                                                </div>
                                            </div>
                                        </div>

                                        <div class='col-sm-6'>
                                            <div class='form-group'>
                                                <label>Budget Start Date</label>
                                                <table>
                                                    <tr>
                                                        <td>
                                                            <div class='input-group'>
                                                                <div class='input-group-prepend'>
                                                                    <div class='input-group-text'>
                                                                        <i class='fas fa-calendar'></i>
                                                                    </div>
                                                                </div>
                                                                <select class='form-control' name='month'>
                                                                    <option value='1 January'>1 January</option>
                                                                    <option value='1 February'>1 February</option>
                                                                    <option value='1 March'>1 March</option>
                                                                    <option value='1 April'>1 April</option>
                                                                    <option value='1 March'>1 March</option>
                                                                    <option value='1 June'>1 June</option>
                                                                    <option value='1 July'>1 July</option>
                                                                    <option value='1 August'>1 August</option>
                                                                    <option value='1 September'>1 September</option>
                                                                    <option value='1 October'>1 October</option>
                                                                    <option value='1 November'>1 November</option>
                                                                    <option value='1 December'>1 December</option>
                                                                </select>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class='input-group'>
                                                                <div class='input-group-prepend'>
                                                                    <div class='input-group-text'>
                                                                        <i class='fas fa-calendar'></i>
                                                                    </div>
                                                                </div>
                                                                <select class='form-control' name='through'>
                                                                    <?php
                                                                $start = date('Y') - 5;
                                                                $end = date('Y') +5;
                                                                for($i = $start; $i <= $end; $i++){
                                                            ?>
                                                                    <option value='{{ $i }}'
                                                                        {{ $i == date('Y') ? 'selected' : '' }}>
                                                                        {{ $i }}</option>
                                                                    <?php
                                                                }
                                                            ?>
                                                                </select>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </div>

                                        <div class='col-sm-6'>
                                            <div class='form-group'>
                                                <label>Budget End Date</label>
                                                <table>
                                                    <tr>
                                                        <td>
                                                            <div class='input-group'>
                                                                <div class='input-group-prepend'>
                                                                    <div class='input-group-text'>
                                                                        <i class='fas fa-calendar'></i>
                                                                    </div>
                                                                </div>
                                                                <select class='form-control' name='endmonth'>
                                                                    <option value='January'>31 January</option>
                                                                    <option value='February'>28 February</option>
                                                                    <option value='March'>31 March</option>
                                                                    <option value='April'>30 April</option>
                                                                    <option value='March'>31 March</option>
                                                                    <option value='June'>30 June</option>
                                                                    <option value='July'>31 July</option>
                                                                    <option value='August'>31 August</option>
                                                                    <option value='September'>30 September</option>
                                                                    <option value='October'>31 October</option>
                                                                    <option value='November'>30 November</option>
                                                                    <option value='December' selected>31 December</option>
                                                                </select>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class='input-group'>
                                                                <div class='input-group-prepend'>
                                                                    <div class='input-group-text'>
                                                                        <i class='fas fa-calendar'></i>
                                                                    </div>
                                                                </div>
                                                                <select class='form-control' name='endthrough'>
                                                                    <?php
                                                                $start = date('Y') - 5;
                                                                $end = date('Y') +5;
                                                                for($i = $start; $i <= $end; $i++){
                                                            ?>
                                                                    <option value='{{ $i }}'
                                                                        {{ $i == date('Y') ? 'selected' : '' }}>
                                                                        {{ $i }}</option>
                                                                    <?php
                                                                }
                                                            ?>
                                                                </select>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </div>

                                        <div class='col-sm-6'>
                                            <div class='form-group'>
                                                <label>Categories</label>
                                                <select class='form-control' name='categories'>
                                                    @foreach ($sources as $category)
                                                        <option value='{{ $category->id }}'
                                                            class='{{ $category->ftype }}'>{{ $category->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>

                                        <div class='col-sm-6'>
                                            <a href='add' class='btn btn-primary'>Add Category</a>

                                            <!--<a href='addall' class='btn btn-primary'>Add ALL Categories</a>-->
                                        </div>
                                        <h3>INCOME CATEGORIES</h3>
                                        <div class='table-responsive'>
                                            <table class='table border-1 income'>
                                                <thead class='bg-secondary'>
                                                    <th></th>
                                                    <th>Budget Amount</th>
                                                    <th></th>
                                                </thead>
                                                <tbody>
                                                    <tr class='no-item'>
                                                        <td colspan='2'><span class='badge badge-warning'>No Income
                                                                Categories added yet</span></td>
                                                    </tr>
                                                </tbody>
                                            </table>

                                        </div>

                                        <h3>EXPENSES CATEGORIES</h3>
                                        <div class='table-responsive'>
                                            <table class='table border-1 expenses'>
                                                <thead class='bg-secondary'>
                                                    <th></th>
                                                    <th>Budget Amount</th>
                                                    <th></th>
                                                </thead>
                                                <tbody>
                                                    <tr class='no-item'>
                                                        <td colspan='3'><span class='badge badge-warning'>No Expenses
                                                                Categories added yet</span></td>
                                                    </tr>
                                                </tbody>
                                            </table>

                                        </div>
                                        <div class='col-sm-12 text-right'>
                                            <button class='btn btn-primary'>Save Budget</button>
                                        </div>
                                    </form>
                                </div>

                            </div>
                        </div>
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
                <div class="modal-header pb-0 mb-0">
                    <h4 class="modal-title" id="exampleModalLabel">Events</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body pt-1 pb-0 mb-0">
                    <form action="/addactivity" method="post" class="activity-form">
                        @csrf
                        <input type='hidden' name='id' value='0'>

                        <div class='form-group'>
                            <label><small>Name</small></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text" id="basic-addon2"><i
                                            class='fas fa-angle-right text-primary'></i></span>
                                </div>
                                <input type="text" class="form-control" name="name" placeholder='Activity Name'
                                    required>
                            </div>
                        </div>
                        <div class='form-group'>
                            <label><small>Amount</small></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text" id="basic-addon2"><i
                                            class='fas fa-dollar-sign text-primary'></i></span>
                                </div>
                                <input name="amount" class="form-control" placeholder="Amount">
                            </div>
                        </div>
                        <div class='form-group'>
                            <label><small>Deadline</small></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text" id="basic-addon2"><i
                                            class='fas fa-calendar-alt text-primary'></i></span>
                                </div>
                                <input name="date" class="form-control datepicker" placeholder="Activity Deadline"
                                    readonly>
                            </div>
                        </div>
                        <div class='form-group'>
                            <label><small>Description</small></label>
                            <div class="input-group mb-3">
                                <div class="input-group-prepend">
                                    <span class="input-group-text" id="basic-addon2"><i
                                            class='fas fa-comments text-primary'></i></span>
                                </div>
                                <textarea name='description' class="form-control" placeholder='Activity Description' rows="4">Say Something</textarea>
                            </div>
                        </div>

                        <div class="form-group text-right">
                            <button type="submit" class="btn btn-primary btn-submit-activity">Save
                                Activity</button>
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
            /*var base_url = window.location.origin;

      var host = window.location.host;

      var pathArray = window.location.pathname.split( '/' );*/


            var categories = [];
            if ($(".edit-items").length) {
                $(".edit-items span").each(function() {
                    categories.push($(this).text());
                });
                addActions();
            }
            $('#budget-form a').click(function(e) {
                e.preventDefault();
                var button = $(this).attr('href');
                var category = $('select[name="categories"]').val();
                var name = $('select[name="categories"] option:selected').text();
                var ftype = parseInt($('select[name="categories"] option:selected').attr('class'));

                if (button == "add") {
                    if (jQuery.inArray(category, categories) === -1) {
                        categories.push(category);
                        if (ftype > 0) {
                            $('.expenses .no-item').remove();
                            $("#budget-form .expenses tbody").prepend("<tr><td class='p-0'>" + name +
                                "</td><td class='p-0'><input type='number' name='amount" + category +
                                "' class='form-control' placeholder='Amount' required></td><td class='text-right'><a href='delete' class='btn btn-outline-danger btn-sm delete'><i class='fas fa-trash'></i></a>" +
                                "</td></tr>");
                        } else {
                            $('.income .no-item').remove();
                            $("#budget-form .income tbody").prepend("<tr><td class='p-0'>" + name +
                                "</td><td class='p-0'><input type='number' name='amount" + category +
                                "' class='form-control' placeholder='Amount' required></td><td class='text-right'><a href='delete' class='btn btn-outline-danger btn-sm delete'><i class='fas fa-trash'></i></a>" +
                                "</td></tr>");
                        }
                        addActions();
                    } else {
                        swal({
                            text: "Item already added!",
                            icon: "error",
                        });
                    }
                    /*swal({
                        title: "Are you sure?",
                        text: "Once deleted, you will not be able to recover this imaginary file!",
                        icon: "warning",
                        buttons: true,
                        dangerMode: true,
                      })
                      .then((willDelete) => {
                        if (willDelete) {
                          swal("Poof! Your imaginary file has been deleted!", {
                            icon: "success",
                          });
                        } else {
                          swal("Your imaginary file is safe!",{
                            icon: "wait",
                          });
                        }
                      });*/
                } else {
                    /*$('select[name="categories"] > option').each(function() {
                        //alert(this.text + ' ' + this.value);
                        var category = this.value;
                        var name = this.value;
                        var ftype = this.attr;
                        alert(ftype);
                    });*/
                }
            });

            function addActions() {
                $('.delete').click(function(e) {
                    e.preventDefault();
                    var cat = ($(this).closest('tr').find('td:nth-child(2) input').attr('name')).replace(
                        /\D/g, '');
                    alert(cat);
                    categories.splice($.inArray(cat, categories), 1);
                    $(this).closest('tr').remove();
                });

                $('#budget-form .table .range-choices').change(function() {
                    var value = parseInt($(this).val());
                    if (value === 2) {
                        $(this).closest('tr').find('td:nth-child(4)').removeClass('ranges-column');
                        $(this).closest('tr').find('td:nth-child(5)').removeClass('ranges-column');
                        /*swal({text: "Date range working!",
                            icon: "success",
                          });*/
                    } else {
                        $(this).closest('tr').find('td:nth-child(4)').addClass('ranges-column');
                        $(this).closest('tr').find('td:nth-child(5)').addClass('ranges-column');
                        /*swal({text: "Date range working!",
                            icon: "success",
                          });*/
                    }
                });
            }

            $('#budget-form').submit(function(e) {
                e.preventDefault();
                if (categories.length > 0) {
                    $('#budget-form').unbind();
                } else {
                    swal({
                        text: "Budget form empty! Select some items before proceeding!",
                        icon: "error",
                    });
                }
            });

        });
    </script>
@endpush
