@extends('layouts.dashboard')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h5 class="m-0 text-header"><i class='fas fa-tags'></i> Tags</h5>
                </div>
                <div class="col-sm-6 d-none d-sm-block">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('dashboard/home') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ url('dashboard/settings/funds/sources') }}">Settings</a></li>
                        <li class="breadcrumb-item active">Tags</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12 mb-3">
                    <div class="card">
                        <div class="card-header">
                            <div class='row'>
                                <div class='col'>
                                    <input type="text" class="form-control form-control-sm" id="tag-search" placeholder="Search tags...">
                                </div>
                                <div class='col text-right'>
                                    <button class="btn btn-primary btn-sm btn-add-tag" data-toggle="modal" data-target="#tagModal">
                                        <i class='fas fa-plus'></i> Add Tag
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class='card-body'>
                            <div class="table-responsive">
                                <table class='table w-100' id="tags-table">
                                    <thead>
                                        <tr>
                                            <th>Tag</th>
                                            <th>Name</th>
                                            <th>Users</th>
                                            <th class='text-end'>Action</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Tag Modal -->
    <div class="modal fade" id="tagModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class='fas fa-tag'></i> <span>New Tag</span></h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <form id="tag-form">
                        @csrf
                        <input type="hidden" name="id" value="0">
                        <div class="form-group">
                            <label>Tag Name</label>
                            <input type="text" name="name" class="form-control" required placeholder="e.g. Youth, Elder, Volunteer">
                        </div>
                        <div class="form-group">
                            <label>Color</label>
                            <input type="color" name="color" class="form-control" value="#4e73df" style="height:38px;">
                        </div>
                        <div class='alert tag-feedback border d-none'></div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal"><i class='fas fa-times'></i> Close</button>
                    <button type="button" class="btn btn-primary btn-sm btn-save-tag"><i class='fas fa-paper-plane'></i> Save</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
<script>
$(document).ready(function () {
    var table = $('#tags-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ url('dashboard/settings/tags/datatable') }}",
            data: function (d) {
                d.search = $('#tag-search').val();
            }
        },
        dom: 'rtip',
        columns: [
            { data: 'preview', name: 'preview', orderable: false, searchable: false },
            { data: 'name', name: 'name', orderable: false, searchable: false },
            { data: 'users_count', name: 'users_count', orderable: false, searchable: false },
            { data: 'action', name: 'action', orderable: false, searchable: false },
        ]
    });

    var timer = null;
    $('#tag-search').keyup(function () {
        clearTimeout(timer);
        timer = setTimeout(function () { table.draw(); }, 500);
    });

    $('.btn-add-tag').click(function () {
        $('#tagModal .modal-title span').text('New Tag');
        $('#tag-form input[name=id]').val(0);
        $('#tag-form input[name=name]').val('');
        $('#tag-form input[name=color]').val('#4e73df');
    });

    $(document).on('click', '.btn-edit-tag', function () {
        var row = $(this).closest('tr');
        $('#tagModal .modal-title span').text('Edit Tag');
        $('#tag-form input[name=id]').val(row.find('.tag-id').text());
        $('#tag-form input[name=name]').val(row.find('.tag-name').text());
        $('#tag-form input[name=color]').val(row.find('.tag-color').text());
    });

    $('.btn-save-tag').click(function () {
        var btn = $(this);
        btn.attr('disabled', 'disabled');
        $('.tag-feedback').removeClass('d-none alert-danger alert-success');
        $('.tag-feedback').html("<i class='fas fa-spinner fa-pulse'></i> Saving...");
        $.ajax({
            url: '{{ url("dashboard/settings/tags/add") }}',
            type: 'POST',
            data: $('#tag-form').serialize()
        }).done(function (data) {
            $('.tag-feedback').addClass('alert-success').html("<i class='fas fa-check-circle'></i> " + data.success);
            table.draw();
            setTimeout(() => { $('.tag-feedback').addClass('d-none'); }, 2000);
            btn.removeAttr('disabled');
        }).fail(function (response) {
            let data = response.responseJSON;
            $('.tag-feedback').addClass('alert-danger');
            if (data && data.errors) {
                $('.tag-feedback').html('');
                $.each(data.errors, function(key, msgs) {
                    $.each(msgs, function(i, msg) {
                        $('.tag-feedback').append("<i class='fas fa-exclamation-circle'></i> " + msg + "<br>");
                    });
                });
            } else {
                $('.tag-feedback').html("<i class='fas fa-exclamation-circle'></i> Something went wrong");
            }
            setTimeout(() => { $('.tag-feedback').addClass('d-none'); }, 4000);
            btn.removeAttr('disabled');
        });
    });

    $(document).on('click', '.btn-delete-tag', function () {
        var row = $(this).closest('tr');
        var tagId = row.find('.tag-id').text();
        var tagName = row.find('.tag-name').text();
        if (confirm('Delete tag "' + tagName + '"? This will remove it from all users.')) {
            $.ajax({
                url: '{{ url("dashboard/settings/tags/delete") }}/' + tagId,
                type: 'POST',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
            }).done(function () {
                table.draw();
            });
        }
    });
});
</script>
@endpush
