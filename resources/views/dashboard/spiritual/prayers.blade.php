@extends('layouts.dashboard')

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h5 class="m-0 text-header"><i class='fas fa-pray'></i> Prayer</h5>
                </div><!-- /.col -->
                <div class="col-sm-6 text-right">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('dashboard/home') }}">Home</a></li>
                        <li class="breadcrumb-item active">Prayer</li>
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
                                <!--
                                <div class="col">
                                    <h3 class="mb-0"><i class="fas fa-hands"></i> | Prayers</h3>
                                </div>-->
                                <div class="col text-right">
                                    <button class="btn btn-primary btn-sm" data-toggle="modal"
                                        data-target="#addPrayerModal">Add</button>
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <!-- Projects table -->
                            <table class="table align-items-center table-flush">
                                <thead class="thead-light">
                                    <tr>
                                        <th scope="col">Prayer</th>
                                        <th scope="col">Description</th>
                                        <th scope="col">Posted At</th>
                                        <th scope="col">By</th>
                                        <th scope="col">Status</th>
                                        <th scope="col" class='text-right'>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if ($prayers->isEmpty())
                                        <tr>
                                            <td colspan='5' class='text-center'> <i class='fas fa-ban'></i> No prayer yet
                                            </td>
                                        </tr>
                                    @endif
                                    <?php $count = 1; ?>
                                    @foreach ($prayers as $prayer)
                                        <tr>
                                            <td scope='row'>
                                                {{ \Str::limit($prayer->title, $limit = 30, $end = '...') }}
                                            </td>
                                            <td>{{ \Str::limit(strip_tags($prayer->description), $limit = 30, $end = '...') }}</td>
                                            <td>{{ \Carbon\Carbon::parse($prayer->created_at)->setTimezone('Africa/Nairobi')->format('d, M Y h:i A') }}
                                            </td>
                                            <td>{{ $prayer->firstname }} {{ $prayer->lastname }}</td>
                                            <td>
                                                @if ($prayer->status > 0)
                                                    <span class='text-primary'>Public</span>
                                                @else
                                                    <span class='text-danger'>Private</span>
                                                @endif
                                            <td class='text-right'>
                                                <a href="{{ $prayer->id }}"
                                                    class='btn btn-primary btn-sm view-prayer'>View</a>
                                                <a href="#" onclick="return postAction('{{ url('dashboard/spiritual/prayers/delete/' . $prayer->id) }}', 'Delete this prayer?')"
                                                    class='btn btn-danger btn-sm'>Delete</a>
                                            </td>
                                        </tr>
                                        <?php $count++; ?>
                                    @endforeach
                                </tbody>
                                </thead>
                            </table>
                        </div>
                    </div>

                    <div class='col-sm-12 mt-2 mb-2'>
                        {{ $prayers->links() }}
                    </div>
                </div>
            </div>
        </div>
    </section>


    <!-- Add Prayer Modal -->
    <div class="modal fade" id="addPrayerModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header border-bottom">
                    <h4 class="modal-title" id="exampleModalLabel">Add a Public Prayer</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{ url('dashboard/spiritual/prayers/add') }}" method="POST">
                        @csrf
                        <input type="hidden" value="1" name="status" />
                        <div class="form-group">
                            <label>Title</label>
                            <input type="text" class="form-control" name="title" placeholder='Prayer Title' required>
                        </div>
                        <div class="form-group">
                            <label>Description</label>
                            <textarea class="form-control" name="description" placeholder='Description' rows='4' required></textarea>
                        </div>
                        <div class="form-group text-right">
                            <button type="submit" class="btn btn-primary">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <!-- View Prayer Modal -->
    <div class="modal fade" id="prayerModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header pb-0 mb-0">
                    <h4 class="modal-title" id="exampleModalLabel">Prayer Details</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">

                </div>
            </div>
        </div>
    </div>
@endsection
@push('js')
    <script>
        $(document).ready(function() {
            $('.view-prayer').click(function(e) {
                e.preventDefault();
                var id = $(this).attr('href');
                $.ajax({
                    url: "{{ url('dashboard/spiritual/prayers') }}/" + id,
                    type: "get"
                }).done(function(data) {
                    var mydata = $.parseJSON(data);
                    if (mydata != null) {
                        $('#prayerModal .modal-body').html("<strong>Prayer:</strong> " + mydata
                            .title + "<br>");
                        $('#prayerModal .modal-body').append("<strong>Description:</strong> " +
                            mydata.description + "<br>")
                        $('#prayerModal').modal('show');
                    } else {
                        toastr.error("Invalid request");
                    }
                }).fail(function(data) {
                    toastr.error("Whoops! failed");
                });
            });
        });
    </script>
@endpush
