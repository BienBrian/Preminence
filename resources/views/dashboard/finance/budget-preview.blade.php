@extends('layouts.dashboard')

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h5 class="m-0 text-header"><i class='fas fa-table'></i> <b>Budget</b> Preview</h5>
                </div><!-- /.col -->
                <div class="d-none d-sm-block col-sm-6 text-right">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('home') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ url('dashboard/finance/budgets') }}">Budget</a></li>
                        <li class="breadcrumb-item active">Preview</li>
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
                                    <h5 class="mb-0"><i class="fas fa-table"></i> | Budget Preview</h5>
                                </div>
                                <div class="col text-right">
                                    <?php $details = $budget->first(); ?>
                                    <!--<a href="{{ url('dashboard/finances/printpdf/budget/' . $details->bid) }}"class="btn btn-primary btn-sm"><i class='fas fa-print'></i> Print</a>-->
                                </div>
                            </div>
                        </div>
                        <div class='card-body'>
                            <div class='table-responsive'>
                                <table class='table'>
                                    <thead>
                                        <tr>
                                            <th colspan="4">Name: <span class='text-muted'>{{ $details->bname }}</span>
                                            </th>
                                        </tr>
                                        <tr>
                                            <th colspan="4">From:
                                                <span
                                                    class='text-muted'>{{ \Carbon\Carbon::parse($details->start)->format('d M, Y') }}</span>,
                                                To:
                                                <span
                                                    class='text-muted'>{{ \Carbon\Carbon::parse($details->end)->format('d M, Y') }}</span>
                                            </th>
                                        </tr>
                                    </thead>
                                    <thead>
                                        <tr>
                                            <th>Income Categories</th>
                                            <th>Budgetted</th>
                                            <th>Received</th>
                                            <th>Difference</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @if ($budget->isEmpty())
                                            <tr>
                                                <td colspan="5"><i class='fas fa-ban'></i> No budget created yet</td>
                                            </tr>
                                        @endif
                                        <?php
                                        $count = 1;
                                        $budgetted = 0;
                                        $used = 0;
                                        ?>
                                        @foreach ($budget as $b)
                                            @if ($b->ftype == 0)
                                                <?php
                                                $budgetted += $b->amount;
                                                ?>
                                                <tr>
                                                    <td>{{ $count }}. {{ $b->name }}</td>
                                                    <td>{{ number_format($b->amount, 2) }}</td>
                                                    <td>
                                                        <?php
                                                        $myarray = $collected->toArray();
                                                        $amount = 0;
                                                        foreach ($myarray as $value) {
                                                            if ($value->name == $b->name) {
                                                                $amount = $value->collected;
                                                                $used += $amount;
                                                            }
                                                        }
                                                        ?>
                                                        {{ number_format($amount, 0) }}
                                                    </td>
                                                    <td>{{ number_format($amount - $b->amount, 2) }}</td>
                                                </tr>
                                                <?php $count++; ?>
                                            @endif
                                        @endforeach
                                    </tbody>
                                    <tfoot class='border-primary'>
                                        <td><span class='font-weight-bold'>Income Category Totals:</span></td>
                                        <td><span class='font-weight-bold'>{{ number_format($budgetted, 2) }}</span></td>
                                        <td><span class='font-weight-bold'>{{ number_format($used, 2) }}</span></td>
                                        <td><span class='font-weight-bold'>{{ number_format($used - $budgetted, 2) }}</span>
                                        </td>
                                    </tfoot>
                                </table>
                                <table class="table mt-1">
                                    <thead>
                                        <tr>
                                            <th>Expense Categories</th>
                                            <th>Budgetted</th>
                                            <th>Spent</th>
                                            <th>Difference</th>
                                        </tr>
                                    </thead>
                                    <?php
                                    $count = 1;
                                    $budgetted = 0;
                                    $used = 0;
                                    ?>
                                    @foreach ($budget as $b)
                                        @if ($b->ftype == 1)
                                            <?php
                                            $budgetted += $b->amount;
                                            ?>
                                            <tr>
                                                <td>{{ $count }}. {{ $b->name }}</td>
                                                <td>{{ number_format($b->amount, 2) }}</td>
                                                <td>
                                                    <?php
                                                    $myarray = $collected->toArray();
                                                    $amount = 0;
                                                    foreach ($myarray as $value) {
                                                        if ($value->name == $b->name) {
                                                            $amount = $value->collected;
                                                            $used += $amount;
                                                        }
                                                    }
                                                    ?>
                                                    {{ number_format($amount, 0) }}
                                                </td>
                                                <td>{{ number_format($amount - $b->amount, 2) }}</td>
                                            </tr>
                                            <?php $count++; ?>
                                        @endif
                                    @endforeach
                                    </tbody>
                                    <tfoot class='border-primary'>
                                        <td><span class='font-weight-bold'>Expense Category Totals:</span></td>
                                        <td><span class='font-weight-bold'>{{ number_format($budgetted, 2) }}</span></td>
                                        <td><span class='font-weight-bold'>{{ number_format($used, 2) }}</span></td>
                                        <td><span class='font-weight-bold'>{{ number_format($used - $budgetted, 2) }}</span>
                                        </td>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!--
                <div class="tab-pane fade" id="tabs-icons-text-2" role="tabpanel" aria-labelledby="tabs-icons-text-2-tab">
                    <form class='row d-flex align-items-center' method="POST" action="{{ url('budget/save') }}" id='budget-form'>
                        @csrf
                        <div class='col-sm-4'>
                            <div class='form-group'>
                                <label>Budget Name</label>
                                <div class='input-group'>
                                    <div class='input-group-prepend'>
                                        <div class='input-group-text'>
                                            <i class='fas fa-calendar'></i>
                                        </div>
                                    </div>
                                    <input type='text' name="name" class='form-control' placeholder='Budget Name' required>
                                </div>
                            </div>
                        </div>

                        <div class='col-sm-4'>
                            <div class='form-group'>
                                <label>Budget Start Date</label>
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
                                </div>
                            </div>

                            <div class='col-sm-4'>
                                <div class='form-group'>
                                    <label class='text-white'>Budget Start Date</label>
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
                                            <option value='{{ $i }}' {{ $i == date('Y') ? 'selected' : '' }}>{{ $i }}</option>
                                            <?php
                                            }
                                        ?>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class='col-sm-6'>
                                <div class='form-group'>
                                    <label>Categories</label>
                                    <select class='form-control' name='categories'>
                                        @foreach ($sources as $category)
    <option value='{{ $category->id }}' class='{{ $category->ftype }}'>{{ $category->name }}</option>
    @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class='col-sm-6'>
                                <a href='add' class='btn btn-primary'>Add Category</a>
                                <!--<a href='addall' class='btn btn-primary'>Add ALL Categories</a>-->
                    <!--</div>

                            <h3>INCOME CATEGORIES</h3>
                                <div class='table-responsive'>
                                    <table class='table border-1 income'>
                                        <thead class='bg-secondary'>
                                            <th></th>
                                            <th>Budget Amount</th>
                                            <th colspan="4">Budget Item Period</th>
                                        </thead>
                                        <tbody>
                                            <tr class='no-item'>
                                                <td colspan='4'><span class='badge badge-warning'>No Income Categories added yet</span></td>
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
                                            <th colspan="4">Budget Item Period</th>
                                        </thead>
                                        <tbody>
                                            <tr class='no-item'>
                                                <td colspan='4'><span class='badge badge-warning'>No Expenses Categories added yet</span></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <div class='col-sm-12 text-right'>
                                    <button class='btn btn-primary'>Edit Budget</button>
                                </div>
                            </form>
                        </div>

                    </div>
                </div>
            </div>
                                            -->

                </div>
            </div>
        @endsection
