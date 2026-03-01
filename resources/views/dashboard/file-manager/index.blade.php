@extends('layouts.dashboard')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h5 class="m-0 text-header"><i class='fas fa-folder-open'></i> File Manager</h5>
                </div>
                <div class="col-sm-6 d-none d-sm-block">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('dashboard/home') }}">Home</a></li>
                        <li class="breadcrumb-item active">File Manager</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <!-- Folder Tree Panel -->
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-sitemap"></i> Folders</h3>
                            @can('Manage File Manager')
                            <div class="card-tools">
                                <button class="btn btn-primary btn-sm" id="btn-new-folder" data-parent="">
                                    <i class="fas fa-plus"></i> New
                                </button>
                            </div>
                            @endcan
                        </div>
                        <div class="card-body p-0">
                            <div class="list-group list-group-flush" id="folder-tree">
                                @foreach($folders as $folder)
                                    <a href="#" class="list-group-item list-group-item-action folder-item d-flex justify-content-between align-items-center" data-id="{{ $folder->id }}" data-name="{{ $folder->name }}">
                                        <span><i class="fas fa-folder text-warning mr-2"></i> {{ $folder->name }}</span>
                                        <span class="badge badge-light">{{ $folder->files->count() }}</span>
                                    </a>
                                @endforeach
                                @if($folders->isEmpty())
                                    <div class="p-3 text-muted text-center small">
                                        <i class="fas fa-folder-plus fa-2x mb-2 d-block"></i>
                                        No folders yet. Click "New" to create one.
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Files Panel -->
                <div class="col-md-9">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title" id="files-panel-title"><i class="fas fa-photo-video"></i> Select a folder</h3>
                            @can('Manage File Manager')
                            <div class="card-tools" id="file-actions" style="display:none;">
                                <button class="btn btn-info btn-sm mr-1" id="btn-upload-files"><i class="fas fa-upload"></i> Upload</button>
                                <button class="btn btn-warning btn-sm mr-1" id="btn-edit-folder"><i class="fas fa-edit"></i> Edit Folder</button>
                                <button class="btn btn-danger btn-sm" id="btn-delete-folder"><i class="fas fa-trash"></i> Delete Folder</button>
                            </div>
                            @endcan
                        </div>
                        <div class="card-body">
                            @can('Manage File Manager')
                            <!-- Upload area (hidden until triggered) -->
                            <div id="upload-area" style="display:none;" class="mb-3">
                                <form id="upload-form" enctype="multipart/form-data">
                                    @csrf
                                    <input type="hidden" name="folder_id" id="upload-folder-id">
                                    <div class="border rounded p-4 text-center" style="border-style:dashed !important;background:#f8f9fc;">
                                        <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-2"></i>
                                        <p class="mb-2">Drag files here or click to browse</p>
                                        <input type="file" name="files[]" multiple class="d-none" id="file-input" accept="image/*,video/*,audio/*,.pdf,.doc,.docx,.xls,.xlsx">
                                        <button type="button" class="btn btn-outline-primary btn-sm" id="btn-browse-files"><i class="fas fa-search"></i> Browse Files</button>
                                        <button type="submit" class="btn btn-primary btn-sm ml-2" id="btn-do-upload" style="display:none;"><i class="fas fa-upload"></i> Upload <span id="file-count"></span></button>
                                    </div>
                                </form>
                            </div>
                            @endcan

                            <!-- Files grid -->
                            <div id="files-grid" class="row">
                                <div class="col-12 text-center text-muted py-5">
                                    <i class="fas fa-hand-pointer fa-3x mb-3 d-block"></i>
                                    <p>Select a folder from the left panel to view its files</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Create/Edit Folder Modal -->
    <div class="modal fade" id="folderModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="folderModalTitle">New Folder</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <form id="folder-form">
                    <div class="modal-body">
                        <input type="hidden" name="id" id="folder-id">
                        <input type="hidden" name="parent_id" id="folder-parent-id">
                        <div class="form-group">
                            <label>Folder Name</label>
                            <input type="text" class="form-control" name="name" id="folder-name" required>
                        </div>
                        <div class="form-group">
                            <label>Description</label>
                            <textarea class="form-control" name="description" id="folder-description" rows="2"></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Type</label>
                                    <select class="form-control" name="type" id="folder-type">
                                        <option value="general">General</option>
                                        <option value="event">Event</option>
                                        <option value="gallery">Gallery</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Show on Frontend</label>
                                    <div class="custom-control custom-switch mt-2">
                                        <input type="checkbox" class="custom-control-input" id="folder-frontend" name="show_on_frontend">
                                        <label class="custom-control-label" for="folder-frontend">Visible</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('js')
<script>
$(document).ready(function() {
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }});

    var currentFolderId = null;
    var canManage = {{ auth()->user()->can('Manage File Manager') ? 'true' : 'false' }};

    // Select folder
    $(document).on('click', '.folder-item', function(e) {
        e.preventDefault();
        $('.folder-item').removeClass('active');
        $(this).addClass('active');
        currentFolderId = $(this).data('id');
        var folderName = $(this).data('name');
        $('#files-panel-title').html('<i class="fas fa-folder-open text-warning"></i> ' + folderName);
        $('#file-actions').show();
        $('#upload-folder-id').val(currentFolderId);
        loadFiles(currentFolderId);
    });

    function loadFiles(folderId) {
        $('#files-grid').html('<div class="col-12 text-center py-3"><i class="fas fa-spinner fa-pulse"></i> Loading...</div>');
        $.get("{{ url('dashboard/file-manager/files') }}", { folder_id: folderId }, function(files) {
            var html = '';
            if (files.length === 0) {
                html = '<div class="col-12 text-center text-muted py-4"><i class="fas fa-inbox fa-3x mb-2 d-block"></i>No files in this folder. Click "Upload" to add files.</div>';
            } else {
                files.forEach(function(file) {
                    var deleteBtn = canManage ? '<button class="btn btn-outline-danger btn-xs btn-delete-file" data-id="' + file.id + '"><i class="fas fa-trash"></i></button>' : '';
                    if (file.is_image) {
                        html += '<div class="col-6 col-sm-4 col-md-3 mb-3">' +
                            '<div class="card h-100 shadow-sm file-card" data-id="' + file.id + '">' +
                            '<img src="' + file.url + '" class="card-img-top" style="height:120px;object-fit:cover;">' +
                            '<div class="card-body p-2">' +
                            '<p class="small mb-0 text-truncate" title="' + file.original_name + '">' + file.original_name + '</p>' +
                            '<small class="text-muted">' + file.size_formatted + '</small>' +
                            '</div>' +
                            '<div class="card-footer p-1 text-center">' +
                            '<a href="' + file.url + '" target="_blank" class="btn btn-outline-info btn-xs mr-1"><i class="fas fa-eye"></i></a>' +
                            deleteBtn +
                            '</div></div></div>';
                    } else {
                        var icon = 'fa-file';
                        if (file.mime_type && file.mime_type.indexOf('pdf') > -1) icon = 'fa-file-pdf text-danger';
                        else if (file.mime_type && file.mime_type.indexOf('video') > -1) icon = 'fa-file-video text-info';
                        else if (file.mime_type && file.mime_type.indexOf('audio') > -1) icon = 'fa-file-audio text-warning';
                        else if (file.mime_type && (file.mime_type.indexOf('word') > -1 || file.mime_type.indexOf('document') > -1)) icon = 'fa-file-word text-primary';
                        else if (file.mime_type && (file.mime_type.indexOf('excel') > -1 || file.mime_type.indexOf('spreadsheet') > -1)) icon = 'fa-file-excel text-success';

                        html += '<div class="col-6 col-sm-4 col-md-3 mb-3">' +
                            '<div class="card h-100 shadow-sm file-card" data-id="' + file.id + '">' +
                            '<div class="card-body text-center py-4"><i class="fas ' + icon + ' fa-3x"></i></div>' +
                            '<div class="card-body p-2">' +
                            '<p class="small mb-0 text-truncate" title="' + file.original_name + '">' + file.original_name + '</p>' +
                            '<small class="text-muted">' + file.size_formatted + '</small>' +
                            '</div>' +
                            '<div class="card-footer p-1 text-center">' +
                            '<a href="' + file.url + '" target="_blank" class="btn btn-outline-info btn-xs mr-1"><i class="fas fa-download"></i></a>' +
                            deleteBtn +
                            '</div></div></div>';
                    }
                });
            }
            $('#files-grid').html(html);
        });
    }

    // New folder
    $('#btn-new-folder').click(function() {
        $('#folderModalTitle').text('New Folder');
        $('#folder-id').val('');
        $('#folder-name').val('');
        $('#folder-description').val('');
        $('#folder-type').val('general');
        $('#folder-frontend').prop('checked', false);
        $('#folder-parent-id').val($(this).data('parent') || '');
        $('#folderModal').modal('show');
    });

    // Edit folder
    $('#btn-edit-folder').click(function() {
        if (!currentFolderId) return;
        var $item = $('.folder-item[data-id="' + currentFolderId + '"]');
        $('#folderModalTitle').text('Edit Folder');
        $('#folder-id').val(currentFolderId);
        $('#folder-name').val($item.data('name'));
        $('#folderModal').modal('show');
    });

    // Save folder
    $('#folder-form').submit(function(e) {
        e.preventDefault();
        var url = $('#folder-id').val()
            ? "{{ url('dashboard/file-manager/folder/update') }}"
            : "{{ url('dashboard/file-manager/folder/create') }}";
        $.ajax({
            url: url,
            method: 'POST',
            data: $(this).serialize(),
            success: function(data) {
                toastr.success(data.success);
                $('#folderModal').modal('hide');
                location.reload();
            },
            error: function(xhr) {
                var msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Error saving folder';
                toastr.error(msg);
            }
        });
    });

    // Delete folder
    $('#btn-delete-folder').click(function() {
        if (!currentFolderId) return;
        if (!confirm('Delete this folder and ALL its files? This cannot be undone.')) return;
        $.ajax({
            url: "{{ url('dashboard/file-manager/folder/delete') }}",
            method: 'POST',
            data: { id: currentFolderId },
            success: function(data) {
                toastr.success(data.success);
                location.reload();
            },
            error: function() { toastr.error('Failed to delete folder'); }
        });
    });

    // Upload toggle
    $('#btn-upload-files').click(function() {
        $('#upload-area').slideToggle();
    });

    $('#btn-browse-files').click(function() {
        $('#file-input').click();
    });

    $('#file-input').change(function() {
        var count = this.files.length;
        if (count > 0) {
            $('#btn-do-upload').show();
            $('#file-count').text('(' + count + ' file' + (count > 1 ? 's' : '') + ')');
        } else {
            $('#btn-do-upload').hide();
        }
    });

    // Upload files
    $('#upload-form').submit(function(e) {
        e.preventDefault();
        var formData = new FormData(this);
        $('#btn-do-upload').prop('disabled', true).html('<i class="fas fa-spinner fa-pulse"></i> Uploading...');
        $.ajax({
            url: "{{ url('dashboard/file-manager/upload') }}",
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(data) {
                toastr.success(data.success);
                $('#upload-area').slideUp();
                $('#file-input').val('');
                $('#btn-do-upload').prop('disabled', false).html('<i class="fas fa-upload"></i> Upload').hide();
                loadFiles(currentFolderId);
                // Update count badge
                var $item = $('.folder-item[data-id="' + currentFolderId + '"]');
                var currentCount = parseInt($item.find('.badge').text()) || 0;
                $item.find('.badge').text(currentCount + data.files.length);
            },
            error: function(xhr) {
                var msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Upload failed';
                toastr.error(msg);
                $('#btn-do-upload').prop('disabled', false).html('<i class="fas fa-upload"></i> Upload');
            }
        });
    });

    // Delete file
    $(document).on('click', '.btn-delete-file', function() {
        if (!confirm('Delete this file?')) return;
        var btn = $(this);
        var fileId = btn.data('id');
        $.ajax({
            url: "{{ url('dashboard/file-manager/file/delete') }}",
            method: 'POST',
            data: { id: fileId },
            success: function(data) {
                toastr.success(data.success);
                btn.closest('.col-6, .col-sm-4, .col-md-3').fadeOut(300, function() { $(this).remove(); });
                // Update count badge
                var $item = $('.folder-item[data-id="' + currentFolderId + '"]');
                var currentCount = parseInt($item.find('.badge').text()) || 0;
                $item.find('.badge').text(Math.max(0, currentCount - 1));
            },
            error: function() { toastr.error('Failed to delete file'); }
        });
    });

    // Drag and drop support on upload area
    var dropZone = document.querySelector('#upload-area .border');
    if (dropZone) {
        ['dragenter', 'dragover'].forEach(function(event) {
            dropZone.addEventListener(event, function(e) {
                e.preventDefault();
                dropZone.style.borderColor = '#4e73df';
                dropZone.style.background = '#eef2ff';
            });
        });
        ['dragleave', 'drop'].forEach(function(event) {
            dropZone.addEventListener(event, function(e) {
                e.preventDefault();
                dropZone.style.borderColor = '';
                dropZone.style.background = '#f8f9fc';
            });
        });
        dropZone.addEventListener('drop', function(e) {
            var dt = e.dataTransfer;
            $('#file-input')[0].files = dt.files;
            $('#file-input').trigger('change');
        });
    }
});
</script>
@endpush
