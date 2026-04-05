@extends('layouts.dashboard')

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6 p-2">
                    <h5 class="m-0 text-header"><i class='fas fa-map-signs'></i> <b>MPESA Reference Mappings</b></h5>
                </div>
                <div class="col-sm-6 d-none d-sm-block p-2">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('dashboard/home') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ url('dashboard/settings/funds/sources') }}">Settings</a></li>
                        <li class="breadcrumb-item active">Reference Mappings</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <!-- Info Cards -->
            <div class="row mb-3">
                <div class="col-md-4">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-0">Total Mappings</h6>
                                    <h3 class="mb-0" id="total-mappings">-</h3>
                                </div>
                                <i class="fas fa-map-signs fa-2x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-warning text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-0">Unmapped References</h6>
                                    <h3 class="mb-0" id="unmapped-count">-</h3>
                                </div>
                                <i class="fas fa-exclamation-triangle fa-2x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-0">Active Mappings</h6>
                                    <h3 class="mb-0" id="active-mappings">-</h3>
                                </div>
                                <i class="fas fa-check-circle fa-2x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12 mb-3">
                    <div class="card shadow-none">
                        <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                            <div class="mb-2 mb-md-0">
                                <button class="btn btn-primary btn-sm mr-2" data-toggle="modal" data-target="#mappingModal" id="btn-add-mapping">
                                    <i class='fas fa-plus'></i> Add Mapping
                                </button>
                                <button class="btn btn-info btn-sm" id="btn-view-unmapped" data-toggle="modal" data-target="#unmappedModal">
                                    <i class='fas fa-exclamation-circle'></i> View Unmapped
                                    <span class="badge badge-light ml-1" id="unmapped-badge">0</span>
                                </button>
                            </div>
                            <div>
                                <button class="btn btn-outline-secondary btn-sm btn-filter">
                                    <i class='fas fa-filter'></i> Filter <i class='fas fa-angle-down'></i>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Filter Section -->
                        <div class="card-header d-none filter-div bg-light">
                            <form id='search-form' class='row'>
                                <div class='col-sm-4 mb-2'>
                                    <label>Search</label>
                                    <input name='search' class='form-control' placeholder="Search reference, mapping..." />
                                </div>
                                <div class='col-sm-3 mb-2'>
                                    <label>Category</label>
                                    <select name='category' id='filter_category' class='form-control'>
                                        <option value="all">All Categories</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class='col-sm-3 mb-2'>
                                    <label>Status</label>
                                    <select name='status' id='filter_status' class='form-control'>
                                        <option value="-1">All</option>
                                        <option value="1">Active</option>
                                        <option value="0">Inactive</option>
                                    </select>
                                </div>
                                <div class='col-sm-2 mb-2 d-flex align-items-end'>
                                    <button type="button" class="btn btn-secondary btn-sm btn-reset-filter">
                                        <i class="fas fa-undo"></i> Reset
                                    </button>
                                </div>
                            </form>
                        </div>

                        <div class='card-body'>
                            <div class="table-responsive">
                                <table class='table table-striped w-100' id="mappings-table">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Original Reference</th>
                                            <th>Mapped To</th>
                                            <th>Category</th>
                                            <th>Description</th>
                                            <th>Status</th>
                                            <th>Created</th>
                                            <th class='text-right'>Action</th>
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

    <!-- Add/Edit Mapping Modal -->
    <div class="modal fade" id="mappingModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class='fas fa-map-signs'></i> <span id="modal-title">Add</span> Reference Mapping</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="mapping-form" class="row">
                        @csrf
                        <input type='hidden' name='id' value='0'>
                        
                        <div class='col-sm-12 form-group'>
                            <label>Original Reference <span class="text-danger">*</span></label>
                            <input type='text' name="original_ref" class='form-control' 
                                placeholder="e.g., ofering123, tithe001" required />
                            <small class="text-muted">The exact reference as it appears in MPESA</small>
                        </div>
                        
                        <div class='col-sm-12 form-group'>
                            <label>Mapped Reference <span class="text-danger">*</span></label>
                            <input type='text' name="mapped_ref" class='form-control' 
                                placeholder="e.g., offering, tithe" required />
                            <small class="text-muted">The normalized/standardized reference</small>
                        </div>
                        
                        <div class='col-sm-12 form-group'>
                            <label>Category</label>
                            <select name="summary_category_id" class="form-control select2" style="width: 100%;">
                                <option value="">-- No Category --</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" data-color="{{ $category->color }}">
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Optional: Assign to a summary category</small>
                        </div>
                        
                        <div class='col-sm-12 form-group'>
                            <label>Description</label>
                            <textarea name="description" class='form-control' rows="2" 
                                placeholder="Optional description..."></textarea>
                        </div>
                        
                        <div class='col-sm-12 form-group'>
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" checked>
                                <label class="custom-control-label" for="is_active">Active</label>
                            </div>
                        </div>
                        
                        <div class='col-12'>
                            <div class="alert feedback d-none"></div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
                        <i class='fas fa-times'></i> Close
                    </button>
                    <button type="button" class="btn btn-primary btn-sm btn-save">
                        <i class='fas fa-paper-plane'></i> Save Changes
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Unmapped References Modal -->
    <div class="modal fade" id="unmappedModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class='fas fa-exclamation-circle'></i> Unmapped References</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> These references appear in MPESA transactions but have no mappings yet.
                        <br><small>Select multiple references and click "Bulk Map" to map them all at once.</small>
                    </div>
                    
                    <!-- Bulk Actions Toolbar -->
                    <div class="d-flex justify-content-between align-items-center mb-2 bg-light p-2 rounded">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="select-all-unmapped">
                            <label class="custom-control-label" for="select-all-unmapped"><strong>Select All</strong></label>
                        </div>
                        <button type="button" class="btn btn-primary btn-sm" id="btn-bulk-map" disabled>
                            <i class="fas fa-map-signs"></i> Bulk Map Selected (<span id="selected-count">0</span>)
                        </button>
                    </div>
                    
                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                        <table class="table table-sm table-hover" id="unmapped-table">
                            <thead class="thead-light">
                                <tr>
                                    <th width="40">
                                        <span class="sr-only">Select</span>
                                    </th>
                                    <th>Reference</th>
                                    <th class="text-right">Transactions</th>
                                    <th class="text-right">Total Amount</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Loaded dynamically -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bulk Map Modal -->
    <div class="modal fade" id="bulkMapModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class='fas fa-map-signs'></i> Bulk Map References</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> 
                        You are about to map <strong id="bulk-map-count">0</strong> references.
                    </div>
                    
                    <form id="bulk-map-form">
                        <div class="form-group">
                            <label>Mapped Reference (Normalized Value) <span class="text-danger">*</span></label>
                            <input type="text" name="mapped_ref" class="form-control" 
                                placeholder="e.g., offering, tithe" required />
                            <small class="text-muted">All selected references will be mapped to this normalized value</small>
                        </div>
                        
                        <div class="form-group">
                            <label>Category</label>
                            <select name="summary_category_id" class="form-control select2" style="width: 100%;">
                                <option value="">-- No Category --</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" data-color="{{ $category->color }}">
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="description" class="form-control" rows="2" 
                                placeholder="Optional description for these mappings..."></textarea>
                        </div>
                        
                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="bulk_is_active" name="is_active" value="1" checked>
                                <label class="custom-control-label" for="bulk_is_active">Active</label>
                            </div>
                        </div>
                    </form>
                    
                    <!-- Selected References Preview -->
                    <div class="form-group">
                        <label>Selected References:</label>
                        <div id="selected-refs-preview" class="border rounded p-2" style="max-height: 150px; overflow-y: auto;">
                            <!-- Populated by JS -->
                        </div>
                    </div>
                    
                    <div class="alert feedback d-none"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary btn-sm btn-bulk-map-save">
                        <i class='fas fa-save'></i> Create Mappings
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('css')
<style>
    .category-badge {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 500;
    }
</style>
@endpush

@push('js')
<script>
$(document).ready(function() {
    // Initialize Select2
    $('.select2').select2({
        width: '100%',
        placeholder: '-- No Category --'
    });

    // Toggle filter section
    $('.btn-filter').click(function() {
        $('.filter-div').toggleClass('d-none');
    });

    // Reset filters
    $('.btn-reset-filter').click(function() {
        $('#search-form')[0].reset();
        $('#filter_category').val('all').trigger('change');
        table.draw();
    });

    // DataTable
    var table = $('#mappings-table').DataTable({
        scrollX: true,
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ url('dashboard/settings/reference-mappings/datatable') }}",
            data: function(d) {
                d.status = $('#filter_status').val();
                d.category = $('#filter_category').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'original_ref', name: 'original_ref' },
            { data: 'mapped_ref', name: 'mapped_ref' },
            { data: 'category_name', name: 'summaryCategory.name' },
            { data: 'description', name: 'description' },
            { data: 'status_badge', name: 'is_active' },
            { data: 'created_at', name: 'created_at' },
            { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-right' }
        ],
        drawCallback: function(settings) {
            // Update stats
            $('#total-mappings').text(settings.json.recordsTotal || 0);
            updateStats();
        }
    });

    // Filter change handlers
    $('#filter_status, #filter_category').change(function() {
        table.draw();
    });

    var searchTimer;
    $('#search-form input[name=search]').keyup(function() {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(function() {
            table.draw();
        }, 500);
    });

    // Stats
    function updateStats() {
        $.ajax({
            url: "{{ url('dashboard/reports/mpesa-logs/unmapped-references') }}",
            type: 'GET',
            timeout: 30000
        }).done(function(data) {
            if (data.success) {
                $('#unmapped-count').text(data.total_unmapped);
                $('#unmapped-badge').text(data.total_unmapped);
            }
        });

        // Get active mappings count
        $.ajax({
            url: "{{ url('dashboard/settings/reference-mappings/datatable') }}",
            type: 'GET',
            data: { status: 1, length: 1 },
            timeout: 10000
        }).done(function(data) {
            $('#active-mappings').text(data.recordsFiltered || 0);
        });
    }

    // Add new mapping
    $('#btn-add-mapping').click(function() {
        $('#mappingModal #modal-title').text('Add');
        $('#mapping-form')[0].reset();
        $('#mapping-form input[name=id]').val(0);
        $('#mapping-form .feedback').addClass('d-none');
        $('.select2').val('').trigger('change');
    });

    // Save mapping
    $('#mappingModal .btn-save').click(function() {
        var btn = $(this);
        var form = $('#mapping-form');
        var id = form.find('input[name=id]').val();
        var isEdit = id > 0;

        btn.prop('disabled', true).html("<i class='fas fa-spinner fa-spin'></i> Saving...");
        form.find('.feedback').removeClass('d-none alert-danger alert-success')
            .addClass('alert-info').html("<i class='fas fa-spinner fa-spin'></i> Saving...");

        var formData = form.serialize();
        if (!form.find('input[name=is_active]').is(':checked')) {
            formData += '&is_active=0';
        }

        var url = isEdit 
            ? "{{ url('dashboard/settings/reference-mappings') }}/" + id 
            : "{{ url('dashboard/settings/reference-mappings') }}";
        var method = isEdit ? 'PUT' : 'POST';

        $.ajax({
            url: url,
            type: method,
            data: formData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        }).done(function(data) {
            form.find('.feedback').removeClass('alert-info alert-danger').addClass('alert-success')
                .html("<i class='fas fa-check-circle'></i> " + data.message);
            table.draw(false);
            setTimeout(function() {
                $('#mappingModal').modal('hide');
                form.find('.feedback').addClass('d-none');
            }, 1500);
        }).fail(function(xhr) {
            var msg = 'Failed to save mapping';
            if (xhr.responseJSON?.message) {
                msg = xhr.responseJSON.message;
            } else if (xhr.responseJSON?.errors) {
                msg = Object.values(xhr.responseJSON.errors).join('<br>');
            }
            form.find('.feedback').removeClass('alert-info alert-success').addClass('alert-danger')
                .html("<i class='fas fa-exclamation-circle'></i> " + msg);
        }).always(function() {
            btn.prop('disabled', false).html("<i class='fas fa-paper-plane'></i> Save Changes");
        });
    });

    // Edit mapping
    $(document).on('click', '.btn-edit', function() {
        var row = $(this).closest('tr');
        var data = table.row(row).data();
        
        $('#mappingModal #modal-title').text('Edit');
        $('#mapping-form input[name=id]').val(data.id);
        $('#mapping-form input[name=original_ref]').val(data.original_ref);
        $('#mapping-form input[name=mapped_ref]').val(data.mapped_ref);
        $('#mapping-form textarea[name=description]').val(data.description || '');
        $('#mapping-form input[name=is_active]').prop('checked', data.is_active == 1);
        
        $('.select2').val(data.summary_category_id || '').trigger('change');
        
        $('#mapping-form .feedback').addClass('d-none');
        $('#mappingModal').modal('show');
    });

    // Delete mapping
    $(document).on('click', '.btn-delete', function() {
        var id = $(this).data('id');
        
        Swal.fire({
            title: 'Delete Mapping?',
            text: "This will remove the reference mapping. Are you sure?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ url('dashboard/settings/reference-mappings') }}/" + id,
                    type: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                }).done(function(data) {
                    Swal.fire('Deleted!', data.message, 'success');
                    table.draw(false);
                    updateStats();
                }).fail(function(xhr) {
                    Swal.fire('Error!', xhr.responseJSON?.message || 'Failed to delete', 'error');
                });
            }
        });
    });

    // Load unmapped references
    var selectedRefs = [];
    
    function loadUnmappedReferences() {
        var tbody = $('#unmapped-table tbody');
        tbody.html('<tr><td colspan="5" class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading...</td></tr>');
        selectedRefs = [];
        updateBulkMapButton();

        $.ajax({
            url: "{{ url('dashboard/settings/reference-mappings/unmapped') }}",
            type: 'GET',
            timeout: 30000
        }).done(function(data) {
            if (data.success && data.unmapped.length > 0) {
                tbody.empty();
                data.unmapped.forEach(function(item) {
                    tbody.append(
                        '<tr>' +
                            '<td>' +
                                '<div class="custom-control custom-checkbox">' +
                                    '<input type="checkbox" class="custom-control-input ref-checkbox" ' +
                                        'id="ref-' + item.reference.replace(/[^a-zA-Z0-9]/g, '-') + '" ' +
                                        'data-ref="' + item.reference + '" ' +
                                        'data-count="' + item.transaction_count + '" ' +
                                        'data-amount="' + item.total_amount + '">' +
                                    '<label class="custom-control-label" for="ref-' + item.reference.replace(/[^a-zA-Z0-9]/g, '-') + '"></label>' +
                                '</div>' +
                            '</td>' +
                            '<td><code>' + item.reference + '</code></td>' +
                            '<td class="text-right">' + item.transaction_count + '</td>' +
                            '<td class="text-right">KES ' + parseFloat(item.total_amount || 0).toLocaleString('en-US', {minimumFractionDigits: 2}) + '</td>' +
                            '<td>' +
                                '<button type="button" class="btn btn-sm btn-primary btn-quick-map" ' +
                                    'data-ref="' + item.reference + '">' +
                                    '<i class="fas fa-plus"></i> Map' +
                                '</button>' +
                            '</td>' +
                        '</tr>'
                    );
                });
            } else {
                tbody.html('<tr><td colspan="5" class="text-center text-muted">No unmapped references found</td></tr>');
            }
        }).fail(function() {
            tbody.html('<tr><td colspan="5" class="text-center text-danger">Failed to load unmapped references</td></tr>');
        });
    }

    // View unmapped button
    $('#btn-view-unmapped').click(function() {
        loadUnmappedReferences();
    });

    // Select all checkbox
    $('#select-all-unmapped').change(function() {
        var isChecked = $(this).is(':checked');
        $('.ref-checkbox').prop('checked', isChecked).trigger('change');
    });

    // Individual checkbox change
    $(document).on('change', '.ref-checkbox', function() {
        var ref = $(this).data('ref');
        var count = $(this).data('count');
        var amount = $(this).data('amount');
        
        if ($(this).is(':checked')) {
            // Add to selection
            if (!selectedRefs.find(r => r.ref === ref)) {
                selectedRefs.push({ref: ref, count: count, amount: amount});
            }
        } else {
            // Remove from selection
            selectedRefs = selectedRefs.filter(r => r.ref !== ref);
        }
        
        updateBulkMapButton();
        
        // Update select all checkbox
        var allChecked = $('.ref-checkbox').length > 0 && $('.ref-checkbox:checked').length === $('.ref-checkbox').length;
        $('#select-all-unmapped').prop('checked', allChecked);
    });

    // Update bulk map button state
    function updateBulkMapButton() {
        var count = selectedRefs.length;
        $('#selected-count').text(count);
        $('#btn-bulk-map').prop('disabled', count === 0);
    }

    // Open bulk map modal
    $('#btn-bulk-map').click(function() {
        if (selectedRefs.length === 0) return;
        
        $('#bulk-map-count').text(selectedRefs.length);
        
        // Build preview
        var previewHtml = '<ul class="list-group list-group-flush">';
        selectedRefs.forEach(function(item) {
            previewHtml += '<li class="list-group-item py-1 d-flex justify-content-between align-items-center">' +
                '<code>' + item.ref + '</code>' +
                '<small class="text-muted">' + item.count + ' txns</small>' +
                '</li>';
        });
        previewHtml += '</ul>';
        $('#selected-refs-preview').html(previewHtml);
        
        // Reset form
        $('#bulk-map-form')[0].reset();
        $('#bulk-map-form .feedback').addClass('d-none');
        
        $('#unmappedModal').modal('hide');
        $('#bulkMapModal').modal('show');
    });

    // Save bulk mappings
    $('.btn-bulk-map-save').click(function() {
        var btn = $(this);
        var mappedRef = $('#bulk-map-form input[name=mapped_ref]').val().trim();
        var categoryId = $('#bulk-map-form select[name=summary_category_id]').val();
        var description = $('#bulk-map-form textarea[name=description]').val();
        var isActive = $('#bulk-map-form input[name=is_active]').is(':checked') ? 1 : 0;
        
        if (!mappedRef) {
            $('#bulk-map-form .feedback').removeClass('d-none alert-success').addClass('alert-danger')
                .html('<i class="fas fa-exclamation-circle"></i> Please enter a mapped reference value.');
            return;
        }
        
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Creating...');
        $('#bulk-map-form .feedback').removeClass('d-none alert-danger alert-success').addClass('alert-info')
            .html('<i class="fas fa-spinner fa-spin"></i> Creating mappings...');
        
        // Prepare mappings data
        var mappings = selectedRefs.map(function(item) {
            return {
                original_ref: item.ref,
                mapped_ref: mappedRef,
                category_id: categoryId,
                description: description,
                is_active: isActive
            };
        });
        
        $.ajax({
            url: "{{ url('dashboard/settings/reference-mappings/bulk-import') }}",
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                mappings: mappings
            },
            timeout: 60000
        }).done(function(data) {
            $('#bulk-map-form .feedback').removeClass('alert-info alert-danger').addClass('alert-success')
                .html('<i class="fas fa-check-circle"></i> ' + data.message);
            
            // Refresh tables
            table.draw(false);
            updateStats();
            
            setTimeout(function() {
                $('#bulkMapModal').modal('hide');
                $('#bulk-map-form .feedback').addClass('d-none');
                btn.prop('disabled', false).html('<i class="fas fa-save"></i> Create Mappings');
            }, 1500);
        }).fail(function(xhr) {
            var msg = xhr.responseJSON?.message || 'Failed to create mappings';
            $('#bulk-map-form .feedback').removeClass('alert-info alert-success').addClass('alert-danger')
                .html('<i class="fas fa-exclamation-circle"></i> ' + msg);
            btn.prop('disabled', false).html('<i class="fas fa-save"></i> Create Mappings');
        });
    });

    // Quick map from unmapped modal
    $(document).on('click', '.btn-quick-map', function() {
        var ref = $(this).data('ref');
        $('#unmappedModal').modal('hide');
        $('#mappingModal').modal('show');
        $('#mapping-form input[name=original_ref]').val(ref);
        $('#mapping-form input[name=mapped_ref]').val(ref.toLowerCase());
        $('#mapping-form input[name=id]').val(0);
        $('#mappingModal #modal-title').text('Add');
    });

    // Initial stats load
    updateStats();
});
</script>
@endpush
