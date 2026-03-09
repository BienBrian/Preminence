@extends('layouts.dashboard')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h5 class="m-0 text-header"><i class="fas fa-link"></i> Link Shortener</h5>
            </div>
            <div class="col-sm-6 d-none d-sm-block">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ url('dashboard/home') }}">Home</a></li>
                    <li class="breadcrumb-item active">Link Shortener</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <!-- Create Short Link Card -->
        <div class="card card-primary mb-4">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-plus-circle mr-1"></i> Create Short Link</h3>
            </div>
            <div class="card-body">
                <form id="createLinkForm">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="original_url">Original URL <span class="text-danger">*</span></label>
                                <input type="url" class="form-control" id="original_url" name="original_url" 
                                    placeholder="https://example.com/very/long/url" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="title">Title (Optional)</label>
                                <input type="text" class="form-control" id="title" name="title" 
                                    placeholder="e.g., Sunday Service Livestream">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="custom_code">Custom Code</label>
                                <input type="text" class="form-control" id="custom_code" name="custom_code" 
                                    placeholder="e.g., sunday25" maxlength="20">
                                <small class="text-muted">Leave blank for auto-generated</small>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="expires_at">Expires At (Optional)</label>
                                <input type="datetime-local" class="form-control" id="expires_at" name="expires_at">
                            </div>
                        </div>
                        <div class="col-md-8 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-cut mr-1"></i> Create Short Link
                            </button>
                        </div>
                    </div>
                </form>
                
                <!-- Result display -->
                <div id="createResult" class="alert alert-success d-none mt-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <strong><i class="fas fa-check-circle"></i> Short link created!</strong>
                            <div class="mt-1">
                                <span class="text-muted">Your short URL:</span>
                                <a href="#" id="resultShortUrl" target="_blank" class="h5 ml-2"></a>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-success" id="copyResultBtn">
                            <i class="fas fa-copy"></i> Copy
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Links Table -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-list mr-1"></i> All Short Links</h3>
            </div>
            <div class="card-body">
                <table class="table table-striped table-bordered" id="linksTable" style="width:100%">
                    <thead>
                        <tr>
                            <th>Short URL</th>
                            <th>Original URL</th>
                            <th>Title</th>
                            <th>Clicks</th>
                            <th>Status</th>
                            <th>Expires</th>
                            <th>Created By</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</section>

<!-- Stats Modal -->
<div class="modal fade" id="statsModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-chart-bar"></i> Link Statistics</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="statsContent">
                    <div class="text-center py-4">
                        <i class="fas fa-spinner fa-spin fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-edit"></i> Edit Short Link</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editLinkForm">
                <div class="modal-body">
                    @csrf
                    <input type="hidden" id="edit_id" name="id">
                    <div class="form-group">
                        <label>Short URL</label>
                        <div id="edit_short_url_display" class="form-control bg-light"></div>
                    </div>
                    <div class="form-group">
                        <label for="edit_original_url">Original URL <span class="text-danger">*</span></label>
                        <input type="url" class="form-control" id="edit_original_url" name="original_url" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_title">Title</label>
                        <input type="text" class="form-control" id="edit_title" name="title">
                    </div>
                    <div class="form-group">
                        <label for="edit_expires_at">Expires At</label>
                        <input type="datetime-local" class="form-control" id="edit_expires_at" name="expires_at">
                    </div>
                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="edit_is_active" name="is_active" value="1">
                            <label class="custom-control-label" for="edit_is_active">Active</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
$(document).ready(function() {
    // Initialize DataTable
    var table = $('#linksTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ url('dashboard/links/datatable') }}",
        columns: [
            { data: 'short_url', name: 'short_url', orderable: false },
            { data: 'original_url_display', name: 'original_url', orderable: false },
            { data: 'title', name: 'title' },
            { data: 'click_count', name: 'click_count' },
            { data: 'status', name: 'status', orderable: false },
            { data: 'expires', name: 'expires_at', orderable: false },
            { data: 'created_by_name', name: 'created_by_name', orderable: false },
            { data: 'actions', name: 'actions', orderable: false, searchable: false },
        ],
        order: [[3, 'desc']], // Order by click count
    });

    // Create short link
    $('#createLinkForm').on('submit', function(e) {
        e.preventDefault();
        var btn = $(this).find('button[type="submit"]');
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Creating...');

        $.ajax({
            url: "{{ url('dashboard/links/store') }}",
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                $('#createLinkForm')[0].reset();
                $('#resultShortUrl').text(response.short_url).attr('href', response.short_url);
                $('#createResult').removeClass('d-none');
                table.ajax.reload();
                
                // Auto-hide after 10 seconds
                setTimeout(function() {
                    $('#createResult').addClass('d-none');
                }, 10000);
            },
            error: function(xhr) {
                var errors = xhr.responseJSON?.errors || {};
                var errorMsg = '';
                for (var field in errors) {
                    errorMsg += errors[field][0] + '\n';
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: errorMsg || 'Failed to create short link'
                });
            },
            complete: function() {
                btn.prop('disabled', false).html('<i class="fas fa-cut mr-1"></i> Create Short Link');
            }
        });
    });

    // Copy result URL
    $('#copyResultBtn').on('click', function() {
        var url = $('#resultShortUrl').text();
        navigator.clipboard.writeText(url).then(function() {
            Swal.fire({
                icon: 'success',
                title: 'Copied!',
                text: 'Short URL copied to clipboard',
                timer: 1500,
                showConfirmButton: false
            });
        });
    });

    // Copy URL from table
    $(document).on('click', '.btn-copy-url', function() {
        var url = $(this).data('url');
        navigator.clipboard.writeText(url).then(function() {
            Swal.fire({
                icon: 'success',
                title: 'Copied!',
                text: 'Short URL copied to clipboard',
                timer: 1500,
                showConfirmButton: false
            });
        });
    });

    // View stats
    $(document).on('click', '.btn-view-stats', function() {
        var id = $(this).data('id');
        $('#statsModal').modal('show');
        $('#statsContent').html('<div class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x"></i></div>');

        $.ajax({
            url: "{{ url('dashboard/links/stats') }}",
            type: 'GET',
            data: { id: id },
            success: function(response) {
                var html = '<div class="row mb-4">';
                html += '<div class="col-md-6"><div class="card bg-light"><div class="card-body text-center">';
                html += '<h3 class="text-primary mb-0">' + response.link.click_count + '</h3>';
                html += '<small class="text-muted">Total Clicks</small>';
                html += '</div></div></div>';
                html += '<div class="col-md-6"><div class="card bg-light"><div class="card-body text-center">';
                html += '<h3 class="text-success mb-0">' + response.link.clicks_count + '</h3>';
                html += '<small class="text-muted">Tracked Clicks</small>';
                html += '</div></div></div>';
                html += '</div>';

                // Recent clicks
                html += '<h6>Recent Clicks</h6>';
                if (response.recent_clicks.length > 0) {
                    html += '<table class="table table-sm table-bordered">';
                    html += '<thead><tr><th>Time</th><th>IP Address</th><th>Referer</th></tr></thead><tbody>';
                    response.recent_clicks.forEach(function(click) {
                        var time = new Date(click.clicked_at).toLocaleString();
                        var ip = click.ip_address || '-';
                        var referer = click.referer ? (click.referer.length > 40 ? click.referer.substring(0, 40) + '...' : click.referer) : '-';
                        html += '<tr><td>' + time + '</td><td>' + ip + '</td><td>' + referer + '</td></tr>';
                    });
                    html += '</tbody></table>';
                } else {
                    html += '<p class="text-muted">No click data available yet.</p>';
                }

                $('#statsContent').html(html);
            },
            error: function() {
                $('#statsContent').html('<div class="alert alert-danger">Failed to load statistics</div>');
            }
        });
    });

    // Edit link
    $(document).on('click', '.btn-edit-link', function() {
        var id = $(this).data('id');
        var row = table.row($(this).closest('tr')).data();
        
        $('#edit_id').val(id);
        $('#edit_short_url_display').html('<a href="' + row.short_url + '" target="_blank">' + row.short_url + '</a>');
        $('#edit_original_url').val(row.original_url);
        $('#edit_title').val(row.title);
        $('#edit_is_active').prop('checked', row.is_active);
        
        // Format datetime for input
        if (row.expires_at) {
            var date = new Date(row.expires_at);
            var formatted = date.toISOString().slice(0, 16);
            $('#edit_expires_at').val(formatted);
        } else {
            $('#edit_expires_at').val('');
        }
        
        $('#editModal').modal('show');
    });

    // Update link
    $('#editLinkForm').on('submit', function(e) {
        e.preventDefault();
        var btn = $(this).find('button[type="submit"]');
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');

        // Handle checkbox value
        var formData = $(this).serialize();
        if (!$('#edit_is_active').is(':checked')) {
            formData += '&is_active=0';
        }

        $.ajax({
            url: "{{ url('dashboard/links/update') }}",
            type: 'POST',
            data: formData,
            success: function(response) {
                $('#editModal').modal('hide');
                table.ajax.reload();
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: response.success,
                    timer: 2000,
                    showConfirmButton: false
                });
            },
            error: function(xhr) {
                var error = xhr.responseJSON?.error || 'Failed to update link';
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error
                });
            },
            complete: function() {
                btn.prop('disabled', false).html('Save Changes');
            }
        });
    });

    // Delete link
    $(document).on('click', '.btn-delete-link', function() {
        var id = $(this).data('id');
        
        Swal.fire({
            title: 'Delete Short Link?',
            text: 'This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete',
            confirmButtonColor: '#d33',
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ url('dashboard/links/delete') }}",
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        id: id
                    },
                    success: function(response) {
                        table.ajax.reload();
                        Swal.fire({
                            icon: 'success',
                            title: 'Deleted',
                            text: response.success,
                            timer: 2000,
                            showConfirmButton: false
                        });
                    },
                    error: function(xhr) {
                        var error = xhr.responseJSON?.error || 'Failed to delete link';
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: error
                        });
                    }
                });
            }
        });
    });
});
</script>
@endpush
