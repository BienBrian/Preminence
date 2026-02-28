@extends('layouts.dashboard')

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h5 class="m-0 text-header"><i class='fas fa-building'></i> <b>Assets</b></h5>
                </div><!-- /.col -->
                <div class="d-none d-sm-block col-sm-6 text-right">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('dashboard/home') }}">Home</a></li>
                        <li class="breadcrumb-item active">Assets</li>
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
                                        <h3 class="mb-0">Assets</h3>
                                    </div>-->
                                <div class="col text-right">
                                    <button class="btn btn-sm btn-primary showAssetsModal" data-toggle="modal"
                                        data-target="#assetsModal"><i class='fas fa-plus-circle'></i> Add</button>
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
                                        <th scope="col">Amount</th>
                                        <th scope="col">Description</th>
                                        <th scope="col">Verified by</th>
                                        <th scope="col">Date</th>
                                        <th scope="col" class='text-right'>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if ($assets->isEmpty())
                                        <tr>
                                            <td colspan='7' class='text-center'> <i class='fas fa-ban'></i> No assets yet
                                            </td>
                                        </tr>
                                    @endif
                                    <?php $count = 1; ?>
                                    @foreach ($assets as $asset)
                                        <tr>
                                            <td>{{ $count }}</td>
                                            <td scope='row'>{{ $asset->name }}</td>
                                            <td scop='row'>{{ number_format($asset->amount, 2) }}</td>
                                            <td>{{ \Str::limit($asset->description, $limit = 30, $end = '...') }}</td>
                                            <td>{{ $asset->firstname . ' ' . $asset->lastname }}</td>
                                            <td>{{ \Carbon\Carbon::parse($asset->acquired)->format('d, M Y') }}
                                                <span class='d-none'>{{ \Carbon\Carbon::parse($asset->acquired)->format('Y-m-d') }}</span>
                                            </td>
                                            <td class='text-right'>
                                                <a href="{{ $asset->id }}"
                                                    class='btn btn-primary btn-sm assetsedit'>Edit</a>
                                                <a href="#" onclick="return postAction('{{ url('dashboard/finances/assets/remove/' . $asset->id) }}', 'Delete this asset?')"
                                                    class='btn btn-danger btn-sm'>Delete</a>
                                            </td>
                                        </tr>
                                        <?php $count++; ?>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="2"><strong>Totals</strong></td>
                                        <td colspan="5"><strong
                                                class='text-default'>{{ number_format($totals, 2) }}<strong></td>
                                    </tr>
                                </tfoot>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <!-- Modal -->
    <div class="modal fade" id="assetsModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header pb-0 mb-0">
                    <h4 class="modal-title" id="exampleModalLabel">Assets</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body pt-1 pb-0 mb-0">
                    <form action="{{ url('dashboard/finances/assets/add') }}" method="post">
                        @csrf
                        <input type='hidden' name='id' value='0'>
                        <div class='form-group'>
                            <label>Name</label>
                            <input type="text" class="form-control" name="name" placeholder='Asset Name' required>
                        </div>
                        <div class='form-group'>
                            <label>Amount</label>
                            <input type="text" class="form-control" name="amount" placeholder='Enter Amount' required>
                        </div>
                        <div class='form-group'>
                            <label>Acquired</label>
                            <input name="acquired" class="form-control datepicker1" placeholder="Date Acquired"
                                id='acquired' readonly />
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
            flatpickr("#acquired", {
                enableTime: false,
                dateFormat: "Y-m-d",
                defaultDate: new Date(),
            });

            //assets edit
            $('.showAssetsModal').click(function() {
                $('#assetsModal input[name="id"]').val(0);
                $('#assetsModal input[name="amount"]').val("");
                $('#assetsModal input[name="name"]').val();
                $("textarea[name=description]").summernote("code", "Say something");
                $('#assetsModal .modal-body .btn-primary').text("Save Asset");
            });

            $('.assetsedit').click(function(e) {
                e.preventDefault();
                var id = $(this).attr('href');
                var name = $(this).closest('tr').find('td:nth-child(2)').text();
                var amount = $(this).closest('tr').find('td:nth-child(3)').text();
                var description = $(this).closest('tr').find('td:nth-child(4)').text();
                var date = $(this).closest('tr').find('td:nth-child(6) .d-none').text();

                $('#assetsModal input[name="id"]').val(id);
                $('#assetsModal input[name="amount"]').val(amount.match(/-?[\d\.]+/g).join([]));
                $('#assetsModal input[name="name"]').val(name);
                $('#assetsModal input[name="acquired"]').val(date);
                $("textarea[name=description]").summernote("code", description);
                $('#assetsModal').modal('show');
                $('#assetsModal .modal-body .btn-primary').text("Edit Asset");
            });
        });
    </script>
@endpush
