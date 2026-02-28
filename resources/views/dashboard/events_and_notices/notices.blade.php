@extends('layouts.dashboard')

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h5 class="m-0 text-header"><i class='fas fa-bell'></i> Notices</h5>
                </div><!-- /.col -->
                <div class="col-sm-6 text-right">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('dashboard/home') }}">Home</a></li>
                        <li class="breadcrumb-item active">Notices</li>
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
                                                    <h3 class="mb-0"><i class="fas fa-bell"></i> | Notices</h3>
                                                </div>-->
                                <div class="col text-right">
                                    <button class="btn btn-sm btn-primary btn-show-notice" data-toggle="modal"
                                        data-target="#noticesModal"><i class='fas fa-circle-plus'></i> Add New</button>
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <!-- Projects table -->
                            <table class="table align-items-center table-flush">
                                <thead class="thead-light">
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">Title</th>
                                        <th scope="col">Description</th>
                                        <th scope="col">Target</th>
                                        <th scope="col">Posted On</th>
                                        <th scope="col" class='text-center'>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if ($notices->isEmpty())
                                        <tr>
                                            <td colspan='6' class='text-center'> <i class='fas fa-ban'></i> No notices
                                                yet</td>
                                        </tr>
                                    @endif
                                    <?php $count = 1; ?>
                                    @foreach ($notices as $notice)
                                        <tr>
                                            <td>{{ $count }}</td>
                                            <td scope='row'>
                                                {{ \Str::limit($notice->title, $limit = 30, $end = '...') }}
                                            </td>
                                            <td>{{ \Str::limit($notice->description, $limit = 30, $end = '...') }}</td>
                                            <td>
                                                @if ($notice->status == 0)
                                                    <span class='text-muted'>Public</span>
                                                @else
                                                    @if ($notice->community != '')
                                                        <span class='text-muted'>{{ $notice->community }}</span>
                                                    @elseif($notice->department != '')
                                                        <span class='text-muted'>{{ $notice->department }}</span>
                                                    @else
                                                        <span class='text-muted'>{{ $notice->role }}</span>
                                                    @endif
                                                @endif
                                            </td>
                                            <td>{{ \Carbon\Carbon::parse($notice->noticedate)->diffForHumans() }}</td>
                                            <td class='text-right'>
                                                <a href="{{ $notice->id }}"
                                                    class='btn btn-primary btn-sm edit-notice'>Edit</a>
                                                <a href="#" onclick="return postAction('{{ url('dashboard/events_and_notices/notices/delete/' . $notice->id) }}', 'Delete this notice?')"
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
                        {{ $notices->links() }}
                    </div>
                </div>
            </div>
        </div>
    </section>


    <!-- Modal -->
    <div class="modal fade" id="noticesModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title" id="exampleModalLabel"><i class='fas fa-bell'></i> Notices</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{ url('dashboard/events_and_notices/notices/add') }}" method="post"
                        class="notices-form">
                        @csrf
                        <input type='hidden' name='id' value='0'>
                        <div class='alert border'>
                            <div class='row'>
                                <div class='col-sm-12'>
                                    <label>Notice for:</label>
                                </div>
                                <div class="col-sm-6 custom-control custom-radio mb-3">
                                    <input value="0" name="target" class="custom-control-input" id="customRadio1"
                                        type="radio" checked='checked'>
                                    <label class="custom-control-label" for="customRadio1">Public</label>
                                </div>
                                <div class="col-sm-6 custom-control custom-radio mb-3">
                                    <input value="1" name="target" class="custom-control-input" id="customRadio2"
                                        type="radio">
                                    <label class="custom-control-label" for="customRadio2">Communities</label>
                                </div>
                                <div class="col-sm-6 custom-control custom-radio mb-3">
                                    <input value="2" name="target" class="custom-control-input" id="customRadio3"
                                        type="radio">
                                    <label class="custom-control-label" for="customRadio3">Departments</label>
                                </div>
                                <div class="col-sm-6 custom-control custom-radio mb-3">
                                    <input value="3" name="target" class="custom-control-input" id="customRadio4"
                                        type="radio">
                                    <label class="custom-control-label" for="customRadio4">User Groups</label>
                                </div>
                            </div>
                        </div>
                        <div class='form-group mt-0 dep d-none'>
                            <label>Departments</label>
                            <select class="form-control" name="department">
                                @foreach ($departments as $department)
                                    <option value='{{ $department->id }}'>{{ $department->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class='form-group mt-0 com d-none'>
                            <label>Communities</label>
                            <select class="form-control" name="community">
                                @foreach ($communities as $community)
                                    <option value='{{ $community->id }}'>{{ $community->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class='form-group mt-0 user d-none'>
                            <label>User Group</label>
                            <select class="form-control" name="role">
                                @foreach ($roles as $role)
                                    <option value='{{ $role->id }}'>{{ $role->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class='form-group'>
                            <label>Title</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text" id="basic-addon2"><i
                                            class='fas fa-angle-right text-primary'></i></span>
                                </div>
                                <input type="text" class="form-control" name="title" placeholder='Notice Title'
                                    required>
                            </div>
                        </div>
                        <div class='form-group'>
                            <label>Description</label>
                            <textarea name='description' class="form-control" placeholder='Notice Description' rows="4">Say Something</textarea>
                        </div>

                        <div class="form-group text-right">
                            <button type="submit" class="btn btn-primary btn-submit-notice">Save Notice</button>
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

            $('.edit-notice').click(function(e) {
                e.preventDefault();
                var id = $(this).attr('href');
                $.ajax({
                    url: "{{ url('dashboard/events_and_notices/notices') }}/" + id,
                    type: "get"
                }).done(function(data) {
                    var mydata = $.parseJSON(data);
                    if (mydata != null) {
                        $('.notices-form input[name="id"]').val(mydata.id);
                        $('.notices-form input[name="title"]').val(mydata.title);
                        $('.notices-form textarea[name="description"]').html(mydata.description);
                        if (mydata.status == 1) {
                            $(".notices-form #customRadio2").prop("checked", true);
                            $('.user, .dep').addClass('d-none');
                            $('.com').removeClass('d-none');
                            $('.notices-form select[name="community"]').val(mydata.com_id);
                        }
                        if (mydata.status == 2) {
                            $(".notices-form #customRadio3").prop("checked", true);
                            $('.user, .com').addClass('d-none');
                            $('.dep').removeClass('d-none');
                            $('.notices-form select[name="department"]').val(mydata.dep_id);
                        }
                        if (mydata.status == 3) {
                            $(".notices-form #customRadio4").prop("checked", true);
                            $('.com, .dep').addClass('d-none');
                            $('.user').removeClass('d-none');
                            $('.notices-form select[name="role"]').val(mydata.user_group);
                        }
                        $('.btn-submit-notice').html("Edit Notice");
                        $('#noticesModal').modal('show');
                    } else {
                        toastr.error("Invalid request");
                    }
                }).fail(function(data) {
                    toastr.error("Whoops! Something went wrong with the server!");
                });
            });

            $('.btn-show-notice').click(function() {
                $('.notices-form input[name="id"]').val("0");
                $('.notices-form input[name="title"]').val("");
                $('.notices-form textarea[name="description"]').html("");
                $(".notices-form #customRadio1").prop("checked", true);
                $('.com, .user, .dep').addClass('d-none');
                $('.btn-submit-notice').html("Save Notice");
            });
            $(".notices-form #customRadio1").change(function() {
                $('.com, .user, .dep').addClass('d-none');
            });
            $(".notices-form #customRadio2").change(function() {
                $('.user, .dep').addClass('d-none');
                $('.com').removeClass('d-none');
            });
            $(".notices-form #customRadio3").change(function() {
                $('.user, .com').addClass('d-none');
                $('.dep').removeClass('d-none');
            });
            $(".notices-form #customRadio4").change(function() {
                $('.dep, .com').addClass('d-none');
                $('.user').removeClass('d-none');
            });
        });
    </script>
@endpush
