@extends('layouts.dashboard')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h5 class="m-0 text-header"><i class='fas fa-list'></i> Lookups</h5>
                </div>
                <div class="col-sm-6 d-none d-sm-block">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('dashboard/home') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ url('dashboard/settings/funds/sources') }}">Settings</a></li>
                        <li class="breadcrumb-item active">Lookups</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header p-0">
                    <ul class="nav nav-tabs" id="lookupTabs">
                        <li class="nav-item">
                            <a class="nav-link active" data-toggle="tab" href="#tab-classes">
                                <i class="fas fa-chalkboard-teacher"></i> Sunday School Classes
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#tab-residences">
                                <i class="fas fa-map-marker-alt"></i> Residences
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="card-body tab-content">

                    <!-- ===== Sunday School Classes Tab ===== -->
                    <div class="tab-pane fade show active" id="tab-classes">
                        <div class="row mb-3">
                            <div class="col">
                                <input type="text" class="form-control form-control-sm" id="class-search" placeholder="Search classes...">
                            </div>
                            <div class="col text-right">
                                <button class="btn btn-primary btn-sm btn-add-class" data-toggle="modal" data-target="#classModal">
                                    <i class="fas fa-plus"></i> Add Class
                                </button>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table w-100" id="classes-table">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Children</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>

                    <!-- ===== Residences Tab ===== -->
                    <div class="tab-pane fade" id="tab-residences">
                        <div class="row mb-3">
                            <div class="col">
                                <input type="text" class="form-control form-control-sm" id="residence-search" placeholder="Search residences...">
                            </div>
                            <div class="col text-right">
                                <button class="btn btn-primary btn-sm btn-add-residence" data-toggle="modal" data-target="#residenceModal">
                                    <i class="fas fa-plus"></i> Add Residence
                                </button>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table w-100" id="residences-table">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Usage</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>

    <!-- Sunday School Class Modal -->
    <div class="modal fade" id="classModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-chalkboard-teacher"></i> <span>New Class</span></h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <form id="class-form">
                        @csrf
                        <input type="hidden" name="id" value="0">
                        <div class="form-group">
                            <label>Class Name</label>
                            <input type="text" name="name" class="form-control" required placeholder="e.g. Beginners, Juniors">
                        </div>
                        <div class="alert class-feedback border d-none"></div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal"><i class="fas fa-times"></i> Close</button>
                    <button type="button" class="btn btn-primary btn-sm btn-save-class"><i class="fas fa-paper-plane"></i> Save</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Residence Modal -->
    <div class="modal fade" id="residenceModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-map-marker-alt"></i> <span>New Residence</span></h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <form id="residence-form">
                        @csrf
                        <input type="hidden" name="id" value="0">
                        <div class="form-group">
                            <label>Residence Name</label>
                            <input type="text" name="name" class="form-control" required placeholder="e.g. Ruiru, Juja, Thika">
                        </div>
                        <div class="alert residence-feedback border d-none"></div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal"><i class="fas fa-times"></i> Close</button>
                    <button type="button" class="btn btn-primary btn-sm btn-save-residence"><i class="fas fa-paper-plane"></i> Save</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
<script>
$(document).ready(function () {
    // ===== Sunday School Classes DataTable =====
    var classesTable = $('#classes-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ url('dashboard/settings/sunday-school-classes/datatable') }}",
            data: function (d) {
                d.search = $('#class-search').val();
            }
        },
        dom: 'rtip',
        columns: [
            { data: 'name', name: 'name', orderable: false, searchable: false },
            { data: 'usage_count', name: 'usage_count', orderable: false, searchable: false },
            { data: 'action', name: 'action', orderable: false, searchable: false },
        ]
    });

    var classTimer = null;
    $('#class-search').keyup(function () {
        clearTimeout(classTimer);
        classTimer = setTimeout(function () { classesTable.draw(); }, 500);
    });

    // ===== Residences DataTable =====
    var residencesTable = $('#residences-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ url('dashboard/settings/residences/datatable') }}",
            data: function (d) {
                d.search = $('#residence-search').val();
            }
        },
        dom: 'rtip',
        columns: [
            { data: 'name', name: 'name', orderable: false, searchable: false },
            { data: 'usage_count', name: 'usage_count', orderable: false, searchable: false },
            { data: 'action', name: 'action', orderable: false, searchable: false },
        ]
    });

    var resTimer = null;
    $('#residence-search').keyup(function () {
        clearTimeout(resTimer);
        resTimer = setTimeout(function () { residencesTable.draw(); }, 500);
    });

    // Load residences table when tab is shown
    $('a[href="#tab-residences"]').on('shown.bs.tab', function () {
        residencesTable.columns.adjust().draw();
    });

    // ===== Class CRUD =====
    $('.btn-add-class').click(function () {
        $('#classModal .modal-title span').text('New Class');
        $('#class-form input[name=id]').val(0);
        $('#class-form input[name=name]').val('');
    });

    $(document).on('click', '.btn-edit-class', function () {
        var row = $(this).closest('tr');
        $('#classModal .modal-title span').text('Edit Class');
        $('#class-form input[name=id]').val(row.find('.item-id').text());
        $('#class-form input[name=name]').val(row.find('.item-name').text());
    });

    $('.btn-save-class').click(function () {
        var btn = $(this);
        btn.attr('disabled', 'disabled');
        $('.class-feedback').removeClass('d-none alert-danger alert-success')
            .html("<i class='fas fa-spinner fa-pulse'></i> Saving...");
        $.ajax({
            url: '{{ url("dashboard/settings/sunday-school-classes/add") }}',
            type: 'POST',
            data: $('#class-form').serialize()
        }).done(function (data) {
            $('.class-feedback').addClass('alert-success').html("<i class='fas fa-check-circle'></i> " + data.success);
            classesTable.draw();
            setTimeout(() => { $('.class-feedback').addClass('d-none'); }, 2000);
            btn.removeAttr('disabled');
        }).fail(function (response) {
            let data = response.responseJSON;
            $('.class-feedback').addClass('alert-danger');
            if (data && data.errors) {
                $('.class-feedback').html('');
                $.each(data.errors, function(key, msgs) {
                    $.each(msgs, function(i, msg) {
                        $('.class-feedback').append("<i class='fas fa-exclamation-circle'></i> " + msg + "<br>");
                    });
                });
            } else if (data && data.error) {
                $('.class-feedback').html("<i class='fas fa-exclamation-circle'></i> " + data.error);
            } else {
                $('.class-feedback').html("<i class='fas fa-exclamation-circle'></i> Something went wrong");
            }
            setTimeout(() => { $('.class-feedback').addClass('d-none'); }, 4000);
            btn.removeAttr('disabled');
        });
    });

    $(document).on('click', '.btn-delete-class', function () {
        var row = $(this).closest('tr');
        var id = row.find('.item-id').text();
        var name = row.find('.item-name').text();
        if (confirm('Delete class "' + name + '"?')) {
            $.ajax({
                url: '{{ url("dashboard/settings/sunday-school-classes/delete") }}/' + id,
                type: 'POST',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
            }).done(function () {
                classesTable.draw();
            }).fail(function (r) {
                alert(r.responseJSON?.error || 'Cannot delete');
            });
        }
    });

    // ===== Residence CRUD =====
    $('.btn-add-residence').click(function () {
        $('#residenceModal .modal-title span').text('New Residence');
        $('#residence-form input[name=id]').val(0);
        $('#residence-form input[name=name]').val('');
    });

    $(document).on('click', '.btn-edit-residence', function () {
        var row = $(this).closest('tr');
        $('#residenceModal .modal-title span').text('Edit Residence');
        $('#residence-form input[name=id]').val(row.find('.item-id').text());
        $('#residence-form input[name=name]').val(row.find('.item-name').text());
    });

    $('.btn-save-residence').click(function () {
        var btn = $(this);
        btn.attr('disabled', 'disabled');
        $('.residence-feedback').removeClass('d-none alert-danger alert-success')
            .html("<i class='fas fa-spinner fa-pulse'></i> Saving...");
        $.ajax({
            url: '{{ url("dashboard/settings/residences/add") }}',
            type: 'POST',
            data: $('#residence-form').serialize()
        }).done(function (data) {
            $('.residence-feedback').addClass('alert-success').html("<i class='fas fa-check-circle'></i> " + data.success);
            residencesTable.draw();
            setTimeout(() => { $('.residence-feedback').addClass('d-none'); }, 2000);
            btn.removeAttr('disabled');
        }).fail(function (response) {
            let data = response.responseJSON;
            $('.residence-feedback').addClass('alert-danger');
            if (data && data.errors) {
                $('.residence-feedback').html('');
                $.each(data.errors, function(key, msgs) {
                    $.each(msgs, function(i, msg) {
                        $('.residence-feedback').append("<i class='fas fa-exclamation-circle'></i> " + msg + "<br>");
                    });
                });
            } else if (data && data.error) {
                $('.residence-feedback').html("<i class='fas fa-exclamation-circle'></i> " + data.error);
            } else {
                $('.residence-feedback').html("<i class='fas fa-exclamation-circle'></i> Something went wrong");
            }
            setTimeout(() => { $('.residence-feedback').addClass('d-none'); }, 4000);
            btn.removeAttr('disabled');
        });
    });

    $(document).on('click', '.btn-delete-residence', function () {
        var row = $(this).closest('tr');
        var id = row.find('.item-id').text();
        var name = row.find('.item-name').text();
        if (confirm('Delete residence "' + name + '"?')) {
            $.ajax({
                url: '{{ url("dashboard/settings/residences/delete") }}/' + id,
                type: 'POST',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
            }).done(function () {
                residencesTable.draw();
            }).fail(function (r) {
                alert(r.responseJSON?.error || 'Cannot delete');
            });
        }
    });
});
</script>
@endpush
