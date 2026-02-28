@extends('layouts.dashboard')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h5 class="m-0 text-header"><i class='fas fa-plug'></i> Integrations</h5>
                </div>
                <div class="col-sm-6 d-none d-sm-block">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('dashboard/home') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ url('dashboard/settings/funds/sources') }}">Settings</a></li>
                        <li class="breadcrumb-item active">Integrations</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-md-3 mb-2 mb-md-0">
                            <select class="form-control form-control-sm" id="filter-type">
                                <option value="all">All Types</option>
                                <option value="mpesa">Mpesa</option>
                                <option value="sms">SMS</option>
                                <option value="email">Email</option>
                                <option value="custom">Custom</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-2 mb-md-0">
                            <input type="text" class="form-control form-control-sm" id="integration-search" placeholder="Search integrations...">
                        </div>
                        <div class="col-md-5 text-right">
                            <button class="btn btn-primary btn-sm" id="btn-add-integration" data-toggle="modal" data-target="#integrationModal">
                                <i class="fas fa-plus"></i> Add Integration
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table w-100" id="integrations-table">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Name</th>
                                    <th>Provider</th>
                                    <th>Status</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Integration Modal -->
    <div class="modal fade" id="integrationModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-plug"></i> <span>New Integration</span></h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <form id="integration-form">
                        @csrf
                        <input type="hidden" name="id" value="0">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Type <span class="text-danger">*</span></label>
                                    <select class="form-control form-control-sm" name="type" id="integration-type" required>
                                        <option value="mpesa">Mpesa</option>
                                        <option value="sms">SMS</option>
                                        <option value="email">Email</option>
                                        <option value="custom">Custom</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-control-sm" name="name" required placeholder="e.g. Main Paybill">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Provider</label>
                                    <input type="text" class="form-control form-control-sm" name="provider" placeholder="e.g. Safaricom, AdvantaSMS">
                                </div>
                            </div>
                        </div>

                        <hr class="mt-0">

                        <!-- Dynamic config fields container -->
                        <div id="config-fields-container">
                            <div class="text-center text-muted py-3">
                                <i class="fas fa-spinner fa-pulse"></i> Loading fields...
                            </div>
                        </div>

                        <!-- Custom type key-value builder (hidden by default) -->
                        <div id="custom-fields-container" style="display:none;">
                            <label class="font-weight-bold">Configuration Fields</label>
                            <div id="custom-fields"></div>
                            <button type="button" class="btn btn-sm btn-outline-primary mt-2" id="add-custom-field">
                                <i class="fas fa-plus"></i> Add Field
                            </button>
                        </div>

                        <hr>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="chk-active" name="is_active" checked>
                                    <label class="custom-control-label" for="chk-active">Active</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="chk-default" name="is_default">
                                    <label class="custom-control-label" for="chk-default">Set as Default</label>
                                </div>
                            </div>
                        </div>

                        <div class="alert integration-feedback border d-none mt-3"></div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal"><i class="fas fa-times"></i> Close</button>
                    <button type="button" class="btn btn-primary btn-sm" id="btn-save-integration"><i class="fas fa-paper-plane"></i> Save</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
<script>
$(document).ready(function () {
    var csrfToken = $('meta[name="csrf-token"]').attr('content');
    var editingRawConfig = {}; // stores unmasked config when editing

    // ===== DataTable =====
    var integrationsTable = $('#integrations-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ url('dashboard/settings/integrations/datatable') }}",
            data: function (d) {
                d.type = $('#filter-type').val();
                d.search = $('#integration-search').val();
            }
        },
        dom: 'rtip',
        columns: [
            { data: 'type_badge', name: 'type', orderable: false, searchable: false },
            { data: 'name', name: 'name', orderable: false, searchable: false },
            { data: 'provider', name: 'provider', orderable: false, searchable: false },
            { data: 'status', name: 'status', orderable: false, searchable: false },
            { data: 'action', name: 'action', orderable: false, searchable: false },
        ]
    });

    // Filter handlers
    var searchTimer = null;
    $('#integration-search').keyup(function () {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(function () { integrationsTable.draw(); }, 500);
    });
    $('#filter-type').change(function () { integrationsTable.draw(); });

    // ===== Load config fields based on type =====
    function loadConfigFields(type, values) {
        if (type === 'custom') {
            $('#config-fields-container').hide();
            $('#custom-fields-container').show();
            $('#custom-fields').empty();
            if (values && typeof values === 'object') {
                $.each(values, function (key, val) {
                    addCustomFieldRow(key, val);
                });
            }
            if ($('#custom-fields .custom-field-row').length === 0) {
                addCustomFieldRow('', '');
            }
            return;
        }

        $('#custom-fields-container').hide();
        $('#config-fields-container').show().html('<div class="text-center text-muted py-3"><i class="fas fa-spinner fa-pulse"></i> Loading fields...</div>');

        var integrationId = $('input[name=id]').val();
        $.get("{{ url('dashboard/settings/integrations/schema') }}", { type: type, id: integrationId }, function (data) {
            var html = '<div class="row">';
            $.each(data.schema, function (i, field) {
                var val = '';
                if (values && values[field.key] !== undefined) {
                    val = values[field.key];
                } else if (data.values && data.values[field.key] !== undefined) {
                    val = data.values[field.key];
                } else if (field.default) {
                    val = field.default;
                }

                var inputType = field.type === 'password' ? 'password' : (field.type || 'text');
                var required = field.required ? 'required' : '';
                var requiredStar = field.required ? ' <span class="text-danger">*</span>' : '';

                html += '<div class="col-md-6">';
                html += '<div class="form-group">';
                html += '<label>' + field.label + requiredStar + '</label>';
                html += '<div class="input-group input-group-sm">';
                html += '<input type="' + inputType + '" class="form-control form-control-sm" name="config_' + field.key + '" value="' + escapeHtml(val) + '" ' + required + ' placeholder="' + field.label + '">';
                if (field.type === 'password') {
                    html += '<div class="input-group-append">';
                    html += '<button class="btn btn-outline-secondary btn-toggle-password" type="button" tabindex="-1"><i class="fas fa-eye"></i></button>';
                    html += '</div>';
                }
                html += '</div>';
                html += '</div>';
                html += '</div>';
            });
            html += '</div>';
            $('#config-fields-container').html(html);
        });
    }

    // Type change handler
    $('#integration-type').change(function () {
        loadConfigFields($(this).val(), null);
    });

    // ===== Custom field builder =====
    function addCustomFieldRow(key, value) {
        var row = '<div class="row mb-2 custom-field-row">' +
            '<div class="col-5"><input type="text" class="form-control form-control-sm" placeholder="Key" name="config_keys[]" value="' + escapeHtml(key) + '"></div>' +
            '<div class="col-5"><input type="text" class="form-control form-control-sm" placeholder="Value" name="config_values[]" value="' + escapeHtml(value) + '"></div>' +
            '<div class="col-2"><button type="button" class="btn btn-sm btn-danger remove-field"><i class="fas fa-times"></i></button></div>' +
            '</div>';
        $('#custom-fields').append(row);
    }

    $('#add-custom-field').click(function () {
        addCustomFieldRow('', '');
    });

    $(document).on('click', '.remove-field', function () {
        $(this).closest('.custom-field-row').remove();
        if ($('#custom-fields .custom-field-row').length === 0) {
            addCustomFieldRow('', '');
        }
    });

    // ===== Toggle password visibility =====
    $(document).on('click', '.btn-toggle-password', function () {
        var input = $(this).closest('.input-group').find('input');
        var icon = $(this).find('i');
        if (input.attr('type') === 'password') {
            input.attr('type', 'text');
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            input.attr('type', 'password');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });

    // ===== Add Integration =====
    $('#btn-add-integration').click(function () {
        $('#integrationModal .modal-title span').text('New Integration');
        $('#integration-form')[0].reset();
        $('input[name=id]').val(0);
        $('#chk-active').prop('checked', true);
        $('#chk-default').prop('checked', false);
        $('#integration-type').val('mpesa').prop('disabled', false);
        editingRawConfig = {};
        loadConfigFields('mpesa', null);
    });

    // ===== Edit Integration =====
    $(document).on('click', '.btn-edit-integration', function () {
        var id = $(this).data('id');
        $('#integrationModal .modal-title span').text('Edit Integration');
        $('input[name=id]').val(id);
        $('.integration-feedback').addClass('d-none');

        // Fetch integration data
        $.get("{{ url('dashboard/settings/integrations/schema') }}", { type: 'mpesa', id: id }, function () {
            // We need the actual record data — fetch it via datatable trick or a separate call
        });

        // Get the row data from the datatable
        var row = $(this).closest('tr');
        var rowData = integrationsTable.row(row).data();

        if (rowData) {
            $('input[name=name]').val(rowData.name);
            $('input[name=provider]').val(rowData.provider || '');
            $('#integration-type').val(rowData.type).prop('disabled', false);
            $('#chk-active').prop('checked', rowData.is_active);
            $('#chk-default').prop('checked', rowData.is_default);

            // Load config fields with masked values from schema endpoint
            $.get("{{ url('dashboard/settings/integrations/schema') }}", { type: rowData.type, id: id }, function (data) {
                if (rowData.type === 'custom') {
                    $('#config-fields-container').hide();
                    $('#custom-fields-container').show();
                    $('#custom-fields').empty();
                    if (data.values && typeof data.values === 'object') {
                        $.each(data.values, function (key, val) {
                            addCustomFieldRow(key, val);
                        });
                    }
                    if ($('#custom-fields .custom-field-row').length === 0) {
                        addCustomFieldRow('', '');
                    }
                } else {
                    $('#custom-fields-container').hide();
                    $('#config-fields-container').show();
                    var html = '<div class="row">';
                    $.each(data.schema, function (i, field) {
                        var val = (data.values && data.values[field.key] !== undefined) ? data.values[field.key] : (field.default || '');
                        var inputType = field.type === 'password' ? 'password' : (field.type || 'text');
                        var required = field.required ? 'required' : '';
                        var requiredStar = field.required ? ' <span class="text-danger">*</span>' : '';
                        var placeholder = field.type === 'password' ? 'Leave empty to keep current value' : field.label;

                        html += '<div class="col-md-6">';
                        html += '<div class="form-group">';
                        html += '<label>' + field.label + requiredStar + '</label>';
                        html += '<div class="input-group input-group-sm">';
                        // For password fields during editing, show empty with placeholder
                        if (field.type === 'password') {
                            html += '<input type="password" class="form-control form-control-sm" name="config_' + field.key + '" value="" placeholder="' + placeholder + '">';
                        } else {
                            html += '<input type="' + inputType + '" class="form-control form-control-sm" name="config_' + field.key + '" value="' + escapeHtml(val) + '" ' + required + ' placeholder="' + placeholder + '">';
                        }
                        if (field.type === 'password') {
                            html += '<div class="input-group-append">';
                            html += '<button class="btn btn-outline-secondary btn-toggle-password" type="button" tabindex="-1"><i class="fas fa-eye"></i></button>';
                            html += '</div>';
                        }
                        html += '</div>';
                        html += '</div>';
                        html += '</div>';
                    });
                    html += '</div>';
                    $('#config-fields-container').html(html);
                }
            });

            $('#integrationModal').modal('show');
        }
    });

    // ===== Save Integration =====
    $('#btn-save-integration').click(function () {
        var btn = $(this);
        btn.attr('disabled', 'disabled');
        var feedback = $('.integration-feedback');
        feedback.removeClass('d-none alert-danger alert-success').html("<i class='fas fa-spinner fa-pulse'></i> Saving...");

        $.ajax({
            url: "{{ url('dashboard/settings/integrations/save') }}",
            type: 'POST',
            data: $('#integration-form').serialize()
        }).done(function (data) {
            feedback.addClass('alert-success').html("<i class='fas fa-check-circle'></i> " + data.success);
            integrationsTable.draw();
            setTimeout(function () {
                feedback.addClass('d-none');
                $('#integrationModal').modal('hide');
            }, 1500);
            btn.removeAttr('disabled');
        }).fail(function (response) {
            var data = response.responseJSON;
            feedback.addClass('alert-danger');
            if (data && data.errors) {
                feedback.html('');
                $.each(data.errors, function (key, msgs) {
                    $.each(msgs, function (i, msg) {
                        feedback.append("<i class='fas fa-exclamation-circle'></i> " + msg + "<br>");
                    });
                });
            } else if (data && data.error) {
                feedback.html("<i class='fas fa-exclamation-circle'></i> " + data.error);
            } else {
                feedback.html("<i class='fas fa-exclamation-circle'></i> Something went wrong");
            }
            setTimeout(function () { feedback.addClass('d-none'); }, 4000);
            btn.removeAttr('disabled');
        });
    });

    // ===== Toggle Active =====
    $(document).on('click', '.btn-toggle-integration', function () {
        var id = $(this).data('id');
        $.ajax({
            url: "{{ url('dashboard/settings/integrations/toggle') }}/" + id,
            type: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken }
        }).done(function (data) {
            integrationsTable.draw();
        }).fail(function (r) {
            alert(r.responseJSON?.error || 'Something went wrong');
        });
    });

    // ===== Set Default =====
    $(document).on('click', '.btn-set-default', function () {
        var id = $(this).data('id');
        $.ajax({
            url: "{{ url('dashboard/settings/integrations/default') }}/" + id,
            type: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken }
        }).done(function (data) {
            integrationsTable.draw();
        }).fail(function (r) {
            alert(r.responseJSON?.error || 'Something went wrong');
        });
    });

    // ===== Delete Integration =====
    $(document).on('click', '.btn-delete-integration', function () {
        var id = $(this).data('id');
        if (confirm('Are you sure you want to delete this integration?')) {
            $.ajax({
                url: "{{ url('dashboard/settings/integrations/delete') }}/" + id,
                type: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken }
            }).done(function () {
                integrationsTable.draw();
            }).fail(function (r) {
                alert(r.responseJSON?.error || 'Cannot delete');
            });
        }
    });

    // ===== Helper: Escape HTML =====
    function escapeHtml(str) {
        if (!str) return '';
        return String(str).replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }

    // Load initial config fields
    loadConfigFields('mpesa', null);
});
</script>
@endpush
