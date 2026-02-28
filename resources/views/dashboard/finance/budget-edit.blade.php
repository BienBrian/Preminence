@extends('layouts.dashboard')

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h5 class="m-0 text-header"><i class='fas fa-edit'></i> <b>Budget</b> Edit</h5>
                </div><!-- /.col -->
                <div class="d-none d-sm-block col-sm-6 text-right">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('dashboard/home') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ url('dashboard/finance/budgets') }}">Budget</a></li>
                        <li class="breadcrumb-item active">Edit</li>
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
                        <!--
                        <div class="card-header border-0">
                            <div class="row align-items-center">
                                <div class="col">
                                    <h5 class="mb-0"><i class="fas fa-table"></i> | Edit Budget</h5>
                                </div>
                                <div class="col text-right">
                                    <a href="{{ url('/budgets') }}" class="btn btn-primary btn-sm"><i class='fas fa-arrow-left'></i> Budgets</a>
                                </div>
                            </div>
                        </div>-->
                        <div class="card-body">
                            <form class='row d-flex align-items-center' method="POST"
                                action="{{ url('dashboard/finances/budget/save') }}" id='budget-form'>
                                @csrf
                                <input type="hidden" name="bid" value="{{ $budget->id }}">
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
                                                placeholder='Budget Name' value="{{ $budget->name }}" required>
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
                                                        <?php
                                                        $month = \Carbon\Carbon::parse($budget->start)->format('F');
                                                        $year = \Carbon\Carbon::parse($budget->start)->format('Y');
                                                        ?>
                                                        <select class='form-control' name='month'>
                                                            <option value='1 January'
                                                                {{ $month == 'January' ? 'selected' : '' }}>1 January
                                                            </option>
                                                            <option value='1 February'
                                                                {{ $month == 'February' ? 'selected' : '' }}>1 February
                                                            </option>
                                                            <option value='1 March'
                                                                {{ $month == 'March' ? 'selected' : '' }}>1
                                                                March</option>
                                                            <option value='1 April'
                                                                {{ $month == 'April' ? 'selected' : '' }}>1
                                                                April</option>
                                                            <option value='1 March'
                                                                {{ $month == 'March' ? 'selected' : '' }}>1
                                                                March</option>
                                                            <option value='1 June'
                                                                {{ $month == 'June' ? 'selected' : '' }}>1
                                                                June</option>
                                                            <option value='1 July'
                                                                {{ $month == 'July' ? 'selected' : '' }}>1
                                                                July</option>
                                                            <option value='1 August'
                                                                {{ $month == 'August' ? 'selected' : '' }}>1 August
                                                            </option>
                                                            <option value='1 September'
                                                                {{ $month == 'September' ? 'selected' : '' }}>1 September
                                                            </option>
                                                            <option value='1 October'
                                                                {{ $month == 'October' ? 'selected' : '' }}>1 October
                                                            </option>
                                                            <option value='1 November'
                                                                {{ $month == 'November' ? 'selected' : '' }}>1 November
                                                            </option>
                                                            <option value='1 December'
                                                                {{ $month == 'December' ? 'selected' : '' }}>1 December
                                                            </option>
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
                                                                {{ $i == $year ? 'selected' : '' }}>{{ $i }}
                                                            </option>
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
                                                        <?php
                                                        $month = \Carbon\Carbon::parse($budget->end)->format('F');
                                                        $year = \Carbon\Carbon::parse($budget->end)->format('Y');
                                                        ?>
                                                        <select class='form-control' name='endmonth'>
                                                            <option value='January'
                                                                {{ $month == 'January' ? 'selected' : '' }}>31 January
                                                            </option>
                                                            <option value='February'
                                                                {{ $month == 'February' ? 'selected' : '' }}>28 February
                                                            </option>
                                                            <option value='March'
                                                                {{ $month == 'March' ? 'selected' : '' }}>31
                                                                March</option>
                                                            <option value='April'
                                                                {{ $month == 'April' ? 'selected' : '' }}>30
                                                                April</option>
                                                            <option value='March'
                                                                {{ $month == 'March' ? 'selected' : '' }}>31
                                                                March</option>
                                                            <option value='June'
                                                                {{ $month == 'June' ? 'selected' : '' }}>30
                                                                June</option>
                                                            <option value='July'
                                                                {{ $month == 'July' ? 'selected' : '' }}>31
                                                                July</option>
                                                            <option value='August'
                                                                {{ $month == 'August' ? 'selected' : '' }}>
                                                                31 August</option>
                                                            <option value='September'
                                                                {{ $month == 'September' ? 'selected' : '' }}>30 September
                                                            </option>
                                                            <option value='October'
                                                                {{ $month == 'October' ? 'selected' : '' }}>31 October
                                                            </option>
                                                            <option value='November'
                                                                {{ $month == 'November' ? 'selected' : '' }}>30 November
                                                            </option>
                                                            <option value='December'
                                                                {{ $month == 'December' ? 'selected' : '' }}>31 December
                                                            </option>
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
                                                                {{ $i == $year ? 'selected' : '' }}>{{ $i }}
                                                            </option>
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
                                                <option value='{{ $category->id }}' class='{{ $category->ftype }}'>
                                                    {{ $category->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class='col-sm-6'>
                                    <a href='add' class='btn btn-primary'>Add Category</a>
                                    <!--<a href='addall' class='btn btn-primary'>Add ALL Categories</a>-->
                                </div>
                                <h5>INCOME CATEGORIES</h5>
                                <div class='table-responsive'>
                                    <table class='table border-1 income'>
                                        <thead class='bg-secondary'>
                                            <th></th>
                                            <th>Budget Amount</th>
                                            <th></th>
                                        </thead>
                                        <tbody>
                                            @if ($budget_items->where('ftype', 0)->count() == 0)
                                                <tr class='no-item'>
                                                    <td colspan='2'><span class='badge badge-warning'>No Income
                                                            Categories added yet</span></td>
                                                </tr>
                                            @endif

                                            @foreach ($budget_items as $item)
                                                @if ($item->ftype == 0)
                                                    <tr>
                                                        <td>
                                                            {{ $item->name }}
                                                        </td>
                                                        <td>
                                                            <input type="number" name="amount{{ $item->id }}"
                                                                class="form-control" placeholder="amount"
                                                                value="{{ $item->amount }}">
                                                        </td>
                                                        <td class='text-right'>
                                                            <a href='delete'
                                                                class='btn btn-outline-danger btn-sm delete'><i
                                                                    class='fas fa-trash'></i></a>
                                                        </td>
                                                    </tr>
                                                @endif
                                            @endforeach
                                            <div class='edit-items' style='display:none;'>
                                                @foreach ($budget_items as $item)
                                                    <span>{{ $item->id }}</span>
                                                @endforeach
                                                <div>
                                        </tbody>
                                    </table>
                                </div>

                                <h5>EXPENSES CATEGORIES</h5>
                                <div class='table-responsive'>
                                    <table class='table border-1 expenses'>
                                        <thead class='bg-secondary'>
                                            <th></th>
                                            <th>Budget Amount</th>
                                            <th></th>
                                        </thead>
                                        <tbody>
                                            @if ($budget_items->where('ftype', 1)->count() == 0)
                                                <tr class='no-item'>
                                                    <td colspan='3'><span class='badge badge-warning'>No Expenses
                                                            Categories added yet</span></td>
                                                </tr>
                                            @endif


                                            @foreach ($budget_items as $item)
                                                @if ($item->ftype == 1)
                                                    <tr>
                                                        <td>
                                                            {{ $item->name }}
                                                        </td>
                                                        <td>
                                                            <input type="number" name="amount{{ $item->id }}"
                                                                class="form-control" placeholder="amount"
                                                                value="{{ $item->amount }}">
                                                        </td>
                                                        <td class='text-right'>
                                                            <a href='delete'
                                                                class='btn btn-outline-danger btn-sm delete'><i
                                                                    class='fas fa-trash'></i></a>
                                                        </td>
                                                    </tr>
                                                @endif
                                            @endforeach
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
    </section>
@endsection
@push('js')
<script>$(document).ready(function() {
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
