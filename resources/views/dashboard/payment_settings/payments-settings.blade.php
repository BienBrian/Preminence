@extends('layouts.dashboard')

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h5 class="m-0 text-header"><i class='fas fa-cog'></i> <b>Payment</b> Settings</h5>
                </div><!-- /.col -->
                <div class="col-sm-6 text-right">

                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('dashboard/home') }}">Home</a></li>
                        <li class="breadcrumb-item active">Payment Settings</li>
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
            <div class='row'>
                <div class="col-md-7 mb-5 mb-xl-0">
                    <div class="card shadow">
                        <div class="card-header border-0">
                            <div class="row align-items-center">
                                <div class="col">
                                    <h5 class="mb-0">Fund Sources</h5>
                                </div>
                                <div class="col text-right">
                                    <button class="btn btn-sm btn-primary showFundSource" data-toggle="modal"
                                        data-target="#fundSourceModal"><i class='fas fa-plus-circle'></i> Add</button>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <!-- Projects table -->
                            <table class="table align-items-center table-flush">
                                <thead class="thead-light">
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">Name</th>
                                        <th scope="col" class='text-right'>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if ($sources->isEmpty())
                                        <tr>
                                            <td colspan='3' class='text-center'> <i class='fas fa-ban'></i> No fund types
                                                yet</td>
                                        </tr>
                                    @endif
                                    <?php $count = 1; ?>
                                    @foreach ($sources as $fund)
                                        <tr>
                                            <td>{{ $count }}</td>
                                            <td scope='row'>{{ $fund->name }}<br>
                                                @if ($fund->ftype == 0)
                                                    <span class='badge badge-success font-weight-bold'>Collections</span>
                                                @else
                                                    <span class='badge badge-danger font-weight-bold'>Expenses</span>
                                                @endif
                                            </td>
                                            <td class='text-right'>
                                                <a href="{{ $fund->id }}"
                                                    class="btn btn-outline-primary p-1 pr-2 pl-2 editsource"
                                                    data-toggle='tooltip' data-placement='bottom' title='edit'>
                                                    <i class='fas fa-edit'></i>
                                                </a>
                                                <a href="#" onclick="return postAction('{{ url('dashboard/settings/funds/sources/remove/' . $fund->id) }}', 'Delete this fund source?')"
                                                    class="btn btn-danger p-1 pr-2 pl-2" data-toggle='tooltip'
                                                    data-placement='bottom' title='delete'>
                                                    <i class='fas fa-trash-alt'></i>
                                                </a>
                                            </td>
                                        </tr>
                                        <?php $count++; ?>
                                    @endforeach
                                </tbody>
                                </thead>
                            </table>
                        </div>
                    </div>
                    <div class='p-2'>
                        {{ $sources->links() }}
                    </div>
                </div>

                <div class="col-md-5 mb-5 mb-xl-0">
                    <div class="card shadow">
                        <div class="card-header border-0">
                            <div class="row align-items-center">
                                <div class="col">
                                    <h5 class="mb-0">Payment Mode</h5>
                                </div>
                                <div class="col text-right">
                                    <button class="btn btn-sm btn-primary showFundMode" data-toggle="modal"
                                        data-target="#fundModeModal"><i class='fas fa-plus-circle'></i> Add</button>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <!-- Projects table -->
                            <table class="table align-items-center table-flush">
                                <thead class="thead-light">
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">Name</th>
                                        <th scope="col" class='text-right'>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if ($payments->isEmpty())
                                        <tr>
                                            <td colspan='4' class='text-center'> <i class='fas fa-ban'></i> No mode of
                                                payments yet</td>
                                        </tr>
                                    @endif
                                    <?php $count = 1; ?>
                                    @foreach ($payments as $payment)
                                        <tr>
                                            <td>{{ $count }}</td>
                                            <td scope='row'>{{ $payment->name }}</td>
                                            <td class='text-right'>
                                                <a href="{{ $payment->id }}"
                                                    class="btn btn-outline-primary p-1 pr-2 pl-2 editpayment"
                                                    title='edit'>
                                                    <i class='fas fa-edit'></i>
                                                </a>
                                                <a href="#" onclick="return postAction('{{ url('dashboard/settings/funds/mode/remove/' . $payment->id) }}', 'Delete this payment mode?')"
                                                    class="btn btn-danger p-1 pr-2 pl-2" title='delete'>
                                                    <i class='fas fa-trash-alt'></i>
                                                </a>
                                            </td>
                                        </tr>
                                        <?php $count++; ?>
                                    @endforeach
                                </tbody>
                                </thead>
                            </table>
                        </div>
                    </div>
                    <div class='p-2'>
                        {{ $payments->links() }}
                    </div>
                </div>
            </div>
        </div>
    </section>


    <!-- Source Modal -->
    <div class="modal fade" id="fundSourceModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Funds Source</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{ url('dashboard/settings/funds/sources/save') }}" method="post">
                        @csrf
                        <input type="hidden" name="id" value="0">
                        <div class="form-group mb-3">
                            <label>Name</label>
                            <input id="password" type="text" class="form-control" name="name"
                                placeholder='Enter Name'>
                        </div>

                        <div class='form-group'>
                            <label>Funds Type</label>

                            <select class="form-control" name="ftype" placeholder='Enter Name'>
                                <option value="0">Collection</option>
                                <option value="1">Expenditure</option>
                            </select>
                        </div>
                        <div class='form-group'>
                            <label>Description</label>
                            <textarea name='description' class="form-control" rows='3' placeholder='Description' required></textarea>
                        </div>
                        <div class='form-group text-right'>
                            <button type="submit" class="btn btn-primary">Add Funds</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>



    <!-- Mode of payment Modal -->
    <div class="modal fade" id="fundModeModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="exampleModalLabel">Mode of Payment</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{ url('dashboard/settings/funds/mode/save') }}" method="post">
                        @csrf
                        <input type="hidden" name="id" value="0">
                        <div class="form-group mb-3">

                            <input id="password" type="text" class="form-control" name="name"
                                placeholder='Enter Name' required autocomplete="off">
                        </div>
                        <div class='form-group text-right'>
                            <button type="submit" class="btn btn-primary">Add</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('js')
<script>

    $(document).ready(function(){

        $('.showFundSource').click(function(){
            $('#fundSourceModal input[name="id"]').val(0);
            $('#fundSourceModal input[name="name"]').val("");
            $('#fundSourceModal textarea[name="description"]').val("");
            $('#fundSourceModal .modal-body .btn-primary').text("Add Type");
        });


        $('.showFundMode').click(function(){
            $('#fundModeModal input[name="id"]').val(0);
            $('#fundModeModal input[name="name"]').val("");
            $('#fundModeModal .modal-body .btn-primary').text("Add");
            $('#fundModeModal .modal-body .btn-primary').removeAttr('disabled');
        });

        //fundstype edit
        $('.editsource').click(function(e){
            e.preventDefault();
            $('#fundSourceModal .modal-body .btn-primary').attr("disabled", "disabled");
            $('#fundSourceModal .modal-body .btn-primary').html("<i class='fas fa-spinner fa-pulse'></i> Loading...");
            $('#fundSourceModal').modal('show');
            var id = $(this).attr('href');
            var baseurl = "{{url('/')}}";
            $.ajax({
                url: "{{ url('dashboard/settings/ajax/sources') }}/"+id,
                type: 'GET',
            }).done(function(data){
                var mydata = JSON.parse(data);
                if($.isEmptyObject(mydata)){
                    toastr.error('Invalid Request. Try Again');
                }else{
                    $('#fundSourceModal input[name="id"]').val(mydata.id);
                    $('#fundSourceModal input[name="name"]').val(mydata.name);
                    $('#fundSourceModal select[name="ftype"]').val(mydata.ftype);
                    $('#fundSourceModal textarea[name="description"]').val(mydata.description);
                    $('#fundSourceModal .modal-body .btn-primary').text("Edit Type");
                }
                $('#fundSourceModal .modal-body .btn-primary').removeAttr('disabled');
            }).fail(function(data){
                toastr.error('Error retrieving data. Try Again');
            });
        });


        //funds payment edit
        $('.editpayment').click(function(e){
            e.preventDefault();
            $('#fundModeModal .modal-body .btn').attr("disabled", "disabled");
            $('#fundModeModal .modal-body .btn').html("<i class='fas fa-spinner fa-pulse'></i> Loading...");
            $('#fundModeModal').modal('show');
            var id = $(this).attr('href');
            $.ajax({
                url: "{{ url('dashboard/settings/ajax/payment') }}/"+id,
                type: 'GET',
            }).done(function(data){
                var mydata = JSON.parse(data);
                if($.isEmptyObject(mydata)){
                    toastr.error('Invalid Request. Try Again');
                }else{
                    $('#fundModeModal input[name="id"]').val(mydata.id);
                    $('#fundModeModal input[name="name"]').val(mydata.name);
                    $('#fundModeModal .modal-body .btn').text("Edit");
                }
                $('#fundModeModal .modal-body .btn').removeAttr('disabled');
            }).fail(function(data){
                toastr.error('Error retrieving data. Try Again');
            });
        });
    });
</script>

@endpush
