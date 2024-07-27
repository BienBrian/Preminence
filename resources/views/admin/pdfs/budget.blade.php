<!DOCTYPE html>
<html>
<head>
    <title>{{ $site_settings == null?"Church App":$site_settings->name }}</title>
    <style>
        .header{
            font-size: 1.2em;
        }
        .table{
            width: 100%;
        }
        .text-muted{
            font-weight: 400;
        }
        thead th{
            padding-top: 1em;
            border-bottom: 1px solid #ddd;
        }
        tfoot{
            background-color: #ddd;
        }
    </style>
</head>
<body>
    <div class="header">
        <?php $details= $budget->first(); ?>
        <h3>{{ $site_settings == null?"Church App":$site_settings->name }}</h3>
        <h3 class="mb-0"><i class="fas fa-table"></i>Budget | {{$details->bname}}</h3>
    </div>
    <div class="header">
        <h4><u>{{$details->bname}}</u><br>
        <strong>From:</strong> <span class='text-muted'>{{\Carbon\Carbon::parse($details->start)->format('d M, Y')}}</span>, 
            <strong>To:</strong>
            <span class='text-muted'>{{\Carbon\Carbon::parse($details->end)->format('d M, Y')}}</span>
        </h4>
    </div>
    <table class='table'>
        <thead>
            <tr>
                <th>Income Categories</th>
                <th>Budgetted</th>
                <th>Received</th>
                <th>Difference</th>
            </tr>
        </thead>
        
        <tbody>
            @if($budget->isEmpty())
                <tr><td colspan="5"><i class='fas fa-ban'></i> No budget created yet</td></tr>
            @endif
            <?php 
                $count = 1;
                $budgetted = 0;
                $used = 0; 
            ?>
            @foreach($budget as $b)
                @if($b->ftype == 0)
                    <?php 
                        $budgetted += $b->amount; 
                    ?>
                    <tr>
                        <td>{{$count}}. {{$b->name}}</td>
                        <td>{{number_format($b->amount, 2)}}</td>
                        <td>
                            <?php 
                                $myarray = $collected->toArray();
                                $amount = 0;
                                foreach($myarray as $value){
                                    if($value->name == $b->name){
                                        $amount = $value->collected;
                                        $used += $amount;
                                    }
                                }
                            ?>
                            {{number_format($amount, 0)}}
                        </td>
                        <td>{{number_format($amount - $b->amount, 2)}}</td>
                    </tr>
                    <?php $count++; ?>
                @endif
            @endforeach
            </tbody>
                <tfoot class='border-primary'>
                    <td><span class='font-weight-bold'>Income Category Totals:</span></td>
                    <td><span class='font-weight-bold'>{{number_format($budgetted, 2)}}</span></td>
                    <td><span class='font-weight-bold'>{{number_format($used, 2)}}</span></td>
                    <td><span class='font-weight-bold'>{{number_format($used-$budgetted, 2)}}</span></td>
                </tfoot>
            </table>
            
            <table class="table mt-1 bg-secondary">
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
                @foreach($budget as $b)
                    @if($b->ftype == 1)
                        <?php 
                            $budgetted += $b->amount; 
                        ?>
                        <tr>
                            <td>{{$count}}. {{$b->name}}</td>
                            <td>{{number_format($b->amount, 2)}}</td>
                            <td>
                                <?php 
                                    $myarray = $collected->toArray();
                                    $amount = 0;
                                    foreach($myarray as $value){
                                        if($value->name == $b->name){
                                            $amount = $value->collected;
                                            $used += $amount;
                                        }
                                    }
                                ?>
                                {{number_format($amount, 0)}}
                            </td>
                            <td>{{number_format($amount - $b->amount, 2)}}</td>
                        </tr>
                        <?php $count++; ?>
                    @endif
                @endforeach
                </tbody>
                <tfoot class='border-primary'>
                    <td><span class='font-weight-bold'>Expense Category Totals:</span></td>
                    <td><span class='font-weight-bold'>{{number_format($budgetted, 2)}}</span></td>
                    <td><span class='font-weight-bold'>{{number_format($used, 2)}}</span></td>
                    <td><span class='font-weight-bold'>{{number_format($used-$budgetted, 2)}}</span></td>
                </tfoot>
            </table>
</body>
</html>