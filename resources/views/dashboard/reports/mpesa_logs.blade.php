@extends('layouts.dashboard')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h5 class="m-0 text-header"><i class='fas fa-mobile-alt'></i> Mpesa Transaction Logs</h5>
                </div>
                <div class="col-sm-6 d-none d-sm-block">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('dashboard/home') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ url('dashboard/reports') }}">Reports</a></li>
                        <li class="breadcrumb-item active">Mpesa Logs</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <!-- Summary Cards -->
            <div class="row mb-3">
                <div class="col-md-3">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h4>{{ number_format($totalTransactions) }}</h4>
                            <p>Total Transactions</p>
                        </div>
                        <div class="icon"><i class="fas fa-exchange-alt"></i></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h4>{{ number_format($matchedTransactions) }}</h4>
                            <p>Matched to Members</p>
                        </div>
                        <div class="icon"><i class="fas fa-user-check"></i></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h4>{{ number_format($unmatchedTransactions) }}</h4>
                            <p>Unmatched</p>
                        </div>
                        <div class="icon"><i class="fas fa-user-times"></i></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="small-box bg-secondary">
                        <div class="inner">
                            <h4>{{ number_format($totalHashes) }}</h4>
                            <p>Phone Hashes Stored</p>
                        </div>
                        <div class="icon"><i class="fas fa-fingerprint"></i></div>
                    </div>
                </div>
            </div>

            <!-- Category Summary Panel -->
            <div class="card mb-3 d-none" id="category-summary-panel">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="fas fa-chart-pie"></i> Summary by Category</h6>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="btn-clear-summary">
                        <i class="fas fa-times"></i> Hide
                    </button>
                </div>
                <div class="card-body">
                    <div id="category-summary-content">
                        <p class="text-muted">Loading summary...</p>
                    </div>
                </div>
            </div>

            <!-- Unmapped References Alert -->
            <div class="alert alert-warning d-none" id="unmapped-refs-alert">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <i class="fas fa-exclamation-triangle"></i> 
                        <strong>Unmapped References Found:</strong> 
                        <span id="unmapped-count">0</span> reference types need to be mapped to categories.
                    </div>
                    <button type="button" class="btn btn-sm btn-warning" id="btn-view-unmapped" data-toggle="modal" data-target="#unmappedRefsModal">
                        <i class="fas fa-map-signs"></i> Map Now
                    </button>
                </div>
            </div>

            <!-- Main Controls Toolbar -->
            <div class="card mb-3">
                <div class="card-header py-2">
                    <div class="row align-items-center">
                        <div class="col-md-12">
                            <div class="btn-toolbar" role="toolbar">
                                <!-- Filter Dropdown -->
                                <div class="btn-group mr-2" role="group">
                                    <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                                        <i class="fas fa-filter"></i> Filter
                                    </button>
                                    <div class="dropdown-menu p-3" style="min-width: 300px;">
                                        <h6 class="dropdown-header">Date Range</h6>
                                        <div class="form-group">
                                            <label class="small">From:</label>
                                            <input type="date" class="form-control form-control-sm" name="date_from" id="date_from">
                                        </div>
                                        <div class="form-group">
                                            <label class="small">To:</label>
                                            <input type="date" class="form-control form-control-sm" name="date_to" id="date_to">
                                        </div>
                                        <div class="dropdown-divider"></div>
                                        <h6 class="dropdown-header">Status & Matching</h6>
                                        <div class="form-group">
                                            <select class="form-control form-control-sm" name="match_status" id="match_status">
                                                <option value="">All Status</option>
                                                <option value="matched">Matched to Members</option>
                                                <option value="unmatched">Unmatched</option>
                                            </select>
                                        </div>
                                        <div class="dropdown-divider"></div>
                                        <h6 class="dropdown-header">Categories & References</h6>
                                        <div class="form-group">
                                            <select class="form-control form-control-sm select2" name="summary_category" id="summary_category">
                                                <option value="">All Categories</option>
                                                @foreach($summaryCategories as $category)
                                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <select class="form-control form-control-sm select2" name="reference_type" id="reference_type">
                                                <option value="">All References</option>
                                                @foreach($referenceTypes as $type)
                                                    <option value="{{ $type }}">{{ $type }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="dropdown-divider"></div>
                                        <div class="form-group mb-0">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" id="group_by_category" name="group_by_category" value="1">
                                                <label class="custom-control-label" for="group_by_category">Group by Category</label>
                                            </div>
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" id="group_references" name="group_references" value="1">
                                                <label class="custom-control-label" for="group_references">Group by Reference</label>
                                            </div>
                                        </div>
                                        <div class="dropdown-divider"></div>
                                        <button type="button" class="btn btn-sm btn-primary btn-block" id="btn-apply-filter">
                                            <i class="fas fa-check"></i> Apply Filters
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary btn-block mt-1" id="btn-clear-filter">
                                            <i class="fas fa-times"></i> Clear Filters
                                        </button>
                                    </div>
                                </div>

                                @canany(['Map MPESA References', 'Manage MPESA Categories'])
                                <!-- Map Transactions Dropdown -->
                                <div class="btn-group mr-2" role="group">
                                    <button type="button" class="btn btn-warning dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                                        <i class="fas fa-map-signs"></i> Map Transactions
                                        <span id="unmapped-badge-toolbar" class="badge badge-danger d-none">0</span>
                                    </button>
                                    <div class="dropdown-menu">
                                        @can('Manage MPESA Categories')
                                        <a class="dropdown-item" href="#" data-toggle="modal" data-target="#categoryManagementModal">
                                            <i class="fas fa-folder text-warning mr-2"></i> Manage Categories
                                        </a>
                                        @endcan
                                        @can('Map MPESA References')
                                        <a class="dropdown-item" href="#" data-toggle="modal" data-target="#unmappedRefsModal">
                                            <i class="fas fa-exclamation-circle text-danger mr-2"></i> Map Unmapped References
                                        </a>
                                        @endcan
                                        @can('Auto-discover MPESA References')
                                        <div class="dropdown-divider"></div>
                                        <button class="dropdown-item" id="btn-auto-discover">
                                            <i class="fas fa-magic text-primary mr-2"></i> Auto-Discover New References
                                        </button>
                                        @endcan
                                        @can('Map MPESA References')
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item" href="{{ url('dashboard/settings/reference-mappings') }}" target="_blank">
                                            <i class="fas fa-cog text-muted mr-2"></i> Advanced Mapping Settings
                                        </a>
                                        @endcan
                                    </div>
                                </div>
                                @endcanany

                                @can('Export MPESA Reports')
                                <!-- Print/Export Dropdown -->
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-success" data-toggle="modal" data-target="#printDurationModal">
                                        <i class="fas fa-print"></i> Print / Export
                                    </button>
                                </div>
                                @endcan
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Data Table -->
            <div class="card">
                <div class="card-body">
                    <div id="bulk-result" class="alert alert-info d-none"></div>
                    
                    <!-- Grouped Summary -->
                    <div id="grouped-summary" class="mb-3 d-none">
                        <h6><i class="fas fa-chart-pie"></i> Grouped Summary</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered table-striped" id="summary-table">
                                <thead>
                                    <tr>
                                        <th>Group</th>
                                        <th>Count</th>
                                        <th>Total Amount</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                                <tfoot>
                                    <tr class="font-weight-bold">
                                        <td>Grand Total</td>
                                        <td id="summary-total-count">0</td>
                                        <td id="summary-total-amount">0.00</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <table class="table table-striped table-bordered" id="mpesa-table" style="width:100%">
                        <thead>
                            <tr>
                                <th>Trans ID</th>
                                <th>Name</th>
                                <th>Amount</th>
                                <th>Account</th>
                                <th>Category</th>
                                <th>MSISDN</th>
                                <th>Match Status</th>
                                <th>Date</th>
                                <th style="width:50px"></th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </section>

    <!-- Print Duration Selection Modal (Step 1) -->
    <div class="modal fade" id="printDurationModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-calendar-alt"></i> Select Duration</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Choose the time period for your report.
                    </div>
                    
                    <!-- Preset Durations -->
                    <div class="form-group">
                        <label><strong>Quick Select:</strong></label>
                        <div class="row">
                            <div class="col-4 mb-2">
                                <button type="button" class="btn btn-outline-primary btn-block btn-duration-preset" data-days="1">
                                    <i class="fas fa-calendar-day"></i> 1 Day
                                </button>
                            </div>
                            <div class="col-4 mb-2">
                                <button type="button" class="btn btn-outline-primary btn-block btn-duration-preset" data-days="3">
                                    <i class="fas fa-calendar-week"></i> 3 Days
                                </button>
                            </div>
                            <div class="col-4 mb-2">
                                <button type="button" class="btn btn-outline-primary btn-block btn-duration-preset" data-days="7">
                                    <i class="fas fa-calendar-week"></i> 1 Week
                                </button>
                            </div>
                            <div class="col-4 mb-2">
                                <button type="button" class="btn btn-outline-primary btn-block btn-duration-preset" data-days="30">
                                    <i class="fas fa-calendar-alt"></i> 1 Month
                                </button>
                            </div>
                            <div class="col-4 mb-2">
                                <button type="button" class="btn btn-outline-primary btn-block btn-duration-preset" data-days="90">
                                    <i class="fas fa-calendar-alt"></i> 3 Months
                                </button>
                            </div>
                            <div class="col-4 mb-2">
                                <button type="button" class="btn btn-outline-primary btn-block btn-duration-preset" data-days="365">
                                    <i class="fas fa-calendar-alt"></i> 1 Year
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="dropdown-divider"></div>
                    
                    <!-- Custom Duration -->
                    <div class="form-group">
                        <label><strong>Custom Range:</strong></label>
                        <div class="row">
                            <div class="col-6">
                                <label class="small">From:</label>
                                <input type="date" class="form-control" id="print-date-from">
                            </div>
                            <div class="col-6">
                                <label class="small">To:</label>
                                <input type="date" class="form-control" id="print-date-to">
                            </div>
                        </div>
                    </div>
                    
                    <div class="dropdown-divider"></div>
                    
                    <!-- Fund Source Filter -->
                    <div class="form-group mb-0">
                        <label><strong>Filter by Fund Source (Optional):</strong></label>
                        <select class="form-control" id="print-fund-source">
                            <option value="">-- All Fund Sources --</option>
                            @foreach($summaryCategories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted">Select a specific fund source to print only its transactions</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="btn-duration-next" disabled>
                        Next: Select Columns <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Print Column Selection Modal (Step 2) -->
    <div class="modal fade" id="printColumnsModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-columns"></i> Select Columns</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Choose which columns to include in your report.
                    </div>
                    
                    <div class="form-group">
                        <label><strong>Available Columns:</strong></label>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input print-column" id="col_trans_id" value="trans_id" checked>
                                    <label class="custom-control-label" for="col_trans_id">Transaction ID</label>
                                </div>
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input print-column" id="col_name" value="name" checked>
                                    <label class="custom-control-label" for="col_name">Name</label>
                                </div>
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input print-column" id="col_amount" value="amount" checked>
                                    <label class="custom-control-label" for="col_amount">Amount</label>
                                </div>
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input print-column" id="col_account" value="account" checked>
                                    <label class="custom-control-label" for="col_account">Account (Ref)</label>
                                </div>
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input print-column" id="col_category" value="category" checked>
                                    <label class="custom-control-label" for="col_category">Category</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input print-column" id="col_msisdn" value="msisdn">
                                    <label class="custom-control-label" for="col_msisdn">MSISDN</label>
                                </div>
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input print-column" id="col_match_status" value="match_status">
                                    <label class="custom-control-label" for="col_match_status">Match Status</label>
                                </div>
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input print-column" id="col_date" value="date" checked>
                                    <label class="custom-control-label" for="col_date">Date</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="dropdown-divider"></div>
                    
                    <!-- Grouping Options -->
                    <div class="form-group">
                        <label><strong>Grouping Options:</strong></label>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="print-group-by-category" value="1" checked>
                            <label class="custom-control-label" for="print-group-by-category">
                                <i class="fas fa-folder text-primary"></i> Group by Fund Source (Category) <small class="text-muted">Recommended</small>
                            </label>
                        </div>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="print-group-by-ref" value="1">
                            <label class="custom-control-label" for="print-group-by-ref">Group by Reference Type</label>
                        </div>
                    </div>
                    
                    <div class="dropdown-divider"></div>
                    
                    <!-- Report Options -->
                    <div class="form-group mb-0">
                        <label><strong>Report Options:</strong></label>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="print-include-summary" value="1" checked>
                            <label class="custom-control-label" for="print-include-summary">
                                <i class="fas fa-chart-pie text-info"></i> Include Fund Source Summary
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" id="btn-columns-back">
                        <i class="fas fa-arrow-left"></i> Back
                    </button>
                    <button type="button" class="btn btn-success" id="btn-print-preview">
                        <i class="fas fa-eye"></i> Preview
                    </button>
                    <button type="button" class="btn btn-primary" id="btn-print-pdf">
                        <i class="fas fa-file-pdf"></i> Export PDF
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Unmapped References Modal -->
    <div class="modal fade" id="unmappedRefsModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-exclamation-circle"></i> Unmapped References</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> These reference types appear in MPESA transactions but don't have category mappings yet.
                        <a href="{{ url('dashboard/settings/reference-mappings') }}" target="_blank" class="alert-link">Manage all mappings</a>
                    </div>
                    <div id="unmapped-refs-content">
                        <div class="text-center py-4">
                            <i class="fas fa-spinner fa-spin fa-2x"></i>
                            <p class="mt-2">Loading unmapped references...</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <a href="{{ url('dashboard/settings/reference-mappings') }}" class="btn btn-primary" target="_blank">
                        <i class="fas fa-cog"></i> Open Mapping Settings
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Category Management Modal -->
    <div class="modal fade" id="categoryManagementModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-folder"></i> Manage Categories</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-5">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <strong>Categories</strong>
                                    <button type="button" class="btn btn-sm btn-primary float-right" id="btn-add-category">
                                        <i class="fas fa-plus"></i> New
                                    </button>
                                </div>
                                <div class="card-body p-0">
                                    <div id="categories-list" class="list-group list-group-flush">
                                        <div class="p-3 text-center text-muted">
                                            <i class="fas fa-spinner fa-spin"></i> Loading...
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-7">
                            <div id="category-form-container">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i> Select a category to edit or click "New" to create one.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-info" id="btn-sync-fund-sources">
                        <i class="fas fa-sync-alt"></i> Sync from Fund Sources
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Inline Mapping Modal -->
    <div class="modal fade" id="inlineMappingModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-map-signs"></i> Categorize Reference</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="inline-ref-to-map">
                    
                    <div class="alert alert-info" id="inline-map-existing">
                        <i class="fas fa-info-circle"></i> 
                        Reference <code id="inline-ref-display"></code> is not yet categorized.
                    </div>
                    
                    <div class="alert alert-success d-none" id="inline-map-already">
                        <i class="fas fa-check-circle"></i>
                        This reference is already mapped to: <strong id="inline-mapped-category"></strong>
                    </div>
                    
                    <div class="form-group">
                        <label>Reference Type</label>
                        <input type="text" class="form-control" id="inline-ref-input" readonly>
                    </div>
                    
                    <div class="form-group">
                        <label>Mapped To (Normalized)</label>
                        <input type="text" class="form-control" id="inline-mapped-ref-input" placeholder="e.g., offering, tithe">
                    </div>
                    
                    <div class="form-group">
                        <label>Category (Fund Source) <span class="text-danger">*</span></label>
                        <select class="form-control" id="inline-category-select">
                            <option value="">-- Select Category --</option>
                            @foreach($summaryCategories as $category)
                                <option value="{{ $category->id }}" data-color="{{ $category->color }}">
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Assign this reference to a collection fund source</small>
                    </div>
                    
                    <div class="form-group" id="inline-suggestions-container">
                        <label>Similar Mappings:</label>
                        <div id="inline-similar-mappings" class="list-group list-group-flush">
                            <!-- Populated dynamically -->
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="btn-save-inline-map">
                        <i class="fas fa-save"></i> Save Mapping
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('css')
<style>
    .small-box {
        border-radius: 0.5rem;
        box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
    }
    .category-badge {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 500;
    }
    .category-color-dot {
        display: inline-block;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        margin-right: 6px;
    }
    .btn-inline-map {
        padding: 2px 6px;
        font-size: 10px;
    }
    .mapping-badge {
        display: inline-block;
        padding: 2px 6px;
        border-radius: 3px;
        font-size: 10px;
        cursor: pointer;
    }
    .mapping-badge-uncategorized {
        background: #ffc107;
        color: #000;
    }
    .mapping-badge-mapped {
        background: #28a745;
        color: #fff;
    }
    .dropdown-action-menu {
        min-width: 180px;
    }
    .dropdown-action-menu .dropdown-item {
        font-size: 12px;
        padding: 6px 12px;
    }
    .dropdown-action-menu .dropdown-item i {
        width: 16px;
        text-align: center;
    }
    .btn-duration-preset.active {
        background-color: #007bff;
        color: white;
        border-color: #007bff;
    }
    .dropdown-menu {
        max-height: 400px;
        overflow-y: auto;
    }
</style>
@endpush

@push('js')
<script>
$(document).ready(function() {
    // Initialize Select2
    $('.select2').select2({
        width: '100%',
        dropdownParent: $('#filter-form')
    });

    // Set default date range (last 90 days)
    var today = new Date();
    var ninetyDaysAgo = new Date();
    ninetyDaysAgo.setDate(today.getDate() - 90);
    
    $('#date_to').val(today.toISOString().split('T')[0]);
    $('#date_from').val(ninetyDaysAgo.toISOString().split('T')[0]);

    // Initialize DataTable
    var table = $('#mpesa-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ url('dashboard/reports/mpesa-logs/datatable') }}",
            data: function(d) {
                d.date_from = $('#date_from').val();
                d.date_to = $('#date_to').val();
                d.match_status = $('#match_status').val();
                d.summary_category = $('#summary_category').val();
                d.reference_type = $('#reference_type').val();
                d.group_by_category = $('#group_by_category').is(':checked') ? 1 : 0;
                d.group_references = $('#group_references').is(':checked') ? 1 : 0;
            }
        },
        columns: [
            { data: 'TransID', name: 'TransID' },
            { data: 'name', name: 'name', orderable: false },
            { data: 'TransAmount', name: 'TransAmount' },
            { data: 'BillRefNumber', name: 'BillRefNumber' },
            { data: 'category_name', name: 'category_name', orderable: false },
            { data: 'msisdn_display', name: 'MSISDN', orderable: false },
            { data: 'match_status', name: 'match_status', orderable: false },
            { data: 'date_fmt', name: 'created_at' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        order: [[8, 'desc']],
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]]
    });

    // Apply Filter button
    $('#btn-apply-filter').click(function() {
        table.draw();
        // Close dropdown
        $('.dropdown-menu').removeClass('show');
    });

    // Clear Filter button
    $('#btn-clear-filter').click(function() {
        $('#filter-form')[0].reset();
        $('#date_to').val(today.toISOString().split('T')[0]);
        $('#date_from').val(ninetyDaysAgo.toISOString().split('T')[0]);
        $('.select2').val('').trigger('change');
        table.draw();
    });

    // Check for unmapped references
    function checkUnmappedReferences() {
        $.ajax({
            url: "{{ url('dashboard/reports/mpesa-logs/unmapped-references') }}",
            type: 'GET',
            timeout: 60000
        }).done(function(data) {
            if (data && data.needs_attention && data.total_unmapped > 0) {
                $('#unmapped-count').text(data.total_unmapped);
                $('#unmapped-badge-toolbar').text(data.total_unmapped).removeClass('d-none');
                $('#unmapped-refs-alert').removeClass('d-none');
            } else {
                $('#unmapped-badge-toolbar').addClass('d-none');
                $('#unmapped-refs-alert').addClass('d-none');
            }
        });
    }

    // Check on page load
    checkUnmappedReferences();

    // Category Summary
    $('#btn-load-summary').click(function() {
        var btn = $(this);
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Loading...');
        
        $.ajax({
            url: "{{ url('dashboard/reports/mpesa-logs/category-summary') }}",
            type: 'GET',
            timeout: 60000,
            data: {
                date_from: $('#date_from').val(),
                date_to: $('#date_to').val(),
            }
        }).done(function(data) {
            var html = '<div class="row">';
            for (var category in data.summary) {
                if (data.summary.hasOwnProperty(category)) {
                    var item = data.summary[category];
                    html += '<div class="col-md-3 mb-3">' +
                        '<div class="p-3 rounded" style="background-color: ' + item.color + '20; border-left: 4px solid ' + item.color + '">' +
                            '<h6 class="mb-1" style="color: ' + item.color + '">' + category + '</h6>' +
                            '<p class="mb-0 small">' + item.count + ' transactions</p>' +
                            '<p class="mb-0 font-weight-bold">KES ' + parseFloat(item.total).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + '</p>' +
                        '</div>' +
                    '</div>';
                }
            }
            html += '</div>';
            html += '<div class="mt-3 pt-2 border-top text-right font-weight-bold">' +
                '<span class="mr-3">Total: ' + data.total_count + '</span>' +
                '<span>Grand Total: KES ' + parseFloat(data.total_amount).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + '</span>' +
            '</div>';
            
            $('#category-summary-content').html(html);
            $('#category-summary-panel').removeClass('d-none');
            $('#btn-clear-summary').removeClass('d-none');
            btn.addClass('d-none');
        }).fail(function() {
            toastr.error('Failed to load category summary');
        }).always(function() {
            btn.prop('disabled', false).html('<i class="fas fa-sync"></i> Load Summary');
        });
    });

    $('#btn-clear-summary').click(function() {
        $('#category-summary-panel').addClass('d-none');
        $('#btn-load-summary').removeClass('d-none');
    });

    // ==================== PRINT WORKFLOW ====================
    
    // Store selected dates - persist until explicitly cleared or new selection made
    var selectedPrintDates = {
        from: '',
        to: ''
    };

    // Duration preset buttons
    $('.btn-duration-preset').click(function() {
        $('.btn-duration-preset').removeClass('active');
        $(this).addClass('active');
        
        var days = parseInt($(this).data('days'));
        var end = new Date();
        var start = new Date();
        start.setDate(end.getDate() - days);
        
        // Format as YYYY-MM-DD
        selectedPrintDates.from = start.toISOString().split('T')[0];
        selectedPrintDates.to = end.toISOString().split('T')[0];
        
        $('#print-date-from').val(selectedPrintDates.from);
        $('#print-date-to').val(selectedPrintDates.to);
        
        $('#btn-duration-next').prop('disabled', false);
    });

    // Custom date change
    $('#print-date-from, #print-date-to').on('change input', function() {
        $('.btn-duration-preset').removeClass('active');
        selectedPrintDates.from = $('#print-date-from').val();
        selectedPrintDates.to = $('#print-date-to').val();
        
        if (selectedPrintDates.from && selectedPrintDates.to) {
            $('#btn-duration-next').prop('disabled', false);
        } else {
            $('#btn-duration-next').prop('disabled', true);
        }
    });

    // Next to columns
    $('#btn-duration-next').click(function() {
        // Validate dates before proceeding
        if (!selectedPrintDates.from || !selectedPrintDates.to) {
            alert('Please select a date range first.');
            return;
        }
        $('#printDurationModal').modal('hide');
        $('#printColumnsModal').modal('show');
    });

    // Back to duration
    $('#btn-columns-back').click(function() {
        $('#printColumnsModal').modal('hide');
        $('#printDurationModal').modal('show');
    });

    // ==================== REHASH / MATCH CHECK ====================
    
    // Single transaction re-check
    $(document).on('click', '.btn-rehash', function(e) {
        e.preventDefault();
        var btn = $(this);
        var id = btn.data('id');
        
        // Show loading state
        btn.addClass('disabled').html('<i class="fas fa-spinner fa-spin"></i> Checking...');
        
        $.ajax({
            url: "{{ url('dashboard/reports/mpesa-logs/rehash') }}",
            type: 'POST',
            data: { 
                _token: '{{ csrf_token() }}',
                id: id 
            },
            timeout: 30000
        }).done(function(data) {
            if (data.status === 'matched' || data.status === 'hash_matched') {
                toastr.success(data.message);
            } else if (data.status === 'unidentified') {
                toastr.warning(data.message);
            } else {
                toastr.info(data.message);
            }
            // Refresh the table row
            table.draw(false);
        }).fail(function(xhr) {
            var msg = xhr.responseJSON?.message || 'Failed to re-check transaction';
            toastr.error(msg);
        }).always(function() {
            btn.removeClass('disabled').html('<i class="fas fa-sync-alt"></i> Re-check Hash Match');
        });
    });

    // Print/PDF buttons
    function getPrintParams() {
        var columns = [];
        $('.print-column:checked').each(function() {
            columns.push($(this).val());
        });
        
        // Use selected dates, fallback to main filter dates only if no selection made
        var dateFrom = selectedPrintDates.from;
        var dateTo = selectedPrintDates.to;
        
        // Only fallback if no dates were selected in the print modal
        if (!dateFrom || !dateTo) {
            dateFrom = $('#date_from').val() || '{{ \Carbon\Carbon::now()->subDays(90)->toDateString() }}';
            dateTo = $('#date_to').val() || '{{ \Carbon\Carbon::now()->toDateString() }}';
        }
        
        var params = {
            date_from: dateFrom,
            date_to: dateTo,
            columns: columns,
            group_by_category: $('#print-group-by-category').is(':checked') ? 1 : 0,
            group_by_ref: $('#print-group-by-ref').is(':checked') ? 1 : 0,
            include_summary: $('#print-include-summary').is(':checked') ? 1 : 0
        };
        
        // Add fund source filter if selected
        var fundSourceId = $('#print-fund-source').val();
        if (fundSourceId) {
            params.summary_category = fundSourceId;
        }
        
        return params;
    }

    $('#btn-print-preview').click(function() {
        var params = getPrintParams();
        var url = "{{ url('dashboard/reports/mpesa-logs/print') }}?" + $.param(params);
        window.open(url, '_blank');
    });

    $('#btn-print-pdf').click(function() {
        var params = getPrintParams();
        params.format = 'pdf';
        var url = "{{ url('dashboard/reports/mpesa-logs/print') }}?" + $.param(params);
        window.open(url, '_blank');
    });

    // Only clear dates when starting a fresh print workflow
    $('#printDurationModal').on('show.bs.modal', function() {
        // Reset dates when opening the modal fresh
        selectedPrintDates.from = '';
        selectedPrintDates.to = '';
        $('.btn-duration-preset').removeClass('active');
        $('#print-date-from, #print-date-to').val('');
        $('#btn-duration-next').prop('disabled', true);
    });
    
    // Mutual exclusivity for grouping options
    $('#print-group-by-category').change(function() {
        if ($(this).is(':checked')) {
            $('#print-group-by-ref').prop('checked', false);
        }
    });
    
    $('#print-group-by-ref').change(function() {
        if ($(this).is(':checked')) {
            $('#print-group-by-category').prop('checked', false);
        }
    });

    // ==================== CATEGORY MANAGEMENT ====================
    
    var categoriesData = [];

    function loadCategories() {
        $.ajax({
            url: "{{ url('dashboard/reports/mpesa-logs/summary-categories') }}",
            type: 'GET',
            timeout: 30000
        }).done(function(data) {
            categoriesData = data || [];
            var list = $('#categories-list');
            list.empty();
            
            if (data && data.length > 0) {
                data.forEach(function(category) {
                    var badge = category.is_default ? '<span class="badge badge-info float-right">Auto</span>' : 
                        '<span class="badge badge-light float-right">' + (category.reference_mappings_count || 0) + '</span>';
                    list.append('<a href="#" class="list-group-item list-group-item-action category-item" ' +
                        'data-id="' + category.id + '">' +
                        '<span class="category-color-dot" style="background-color: ' + (category.color || '#007bff') + '"></span>' +
                        category.name + 
                        badge +
                    '</a>');
                });
            } else {
                list.append('<div class="p-3 text-muted text-center">No categories found</div>');
            }
        }).fail(function() {
            $('#categories-list').html('<div class="p-3 text-danger text-center">Failed to load categories</div>');
        });
    }

    $('#categoryManagementModal').on('show.bs.modal', function() {
        loadCategories();
    });

    // ==================== UNMAPPED REFERENCES ====================
    
    $('#unmappedRefsModal').on('show.bs.modal', function() {
        var content = $('#unmapped-refs-content');
        content.html('<div class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x"></i><p class="mt-2">Loading...</p></div>');
        
        $.ajax({
            url: "{{ url('dashboard/reports/mpesa-logs/unmapped-references') }}",
            type: 'GET',
            timeout: 60000
        }).done(function(data) {
            if (data.success && data.unmapped && data.unmapped.length > 0) {
                var html = '<div class="table-responsive"><table class="table table-sm table-hover">' +
                    '<thead><tr><th>Reference</th><th class="text-right">Transactions</th><th class="text-right">Total Amount</th></tr></thead><tbody>';
                data.unmapped.forEach(function(item) {
                    html += '<tr>' +
                        '<td><code>' + item.reference + '</code></td>' +
                        '<td class="text-right">' + item.transaction_count + '</td>' +
                        '<td class="text-right">KES ' + parseFloat(item.total_amount || 0).toLocaleString('en-US', {minimumFractionDigits: 2}) + '</td>' +
                    '</tr>';
                });
                html += '</tbody></table></div>';
                content.html(html);
            } else {
                content.html('<div class="alert alert-success"><i class="fas fa-check-circle"></i> All references are mapped! No unmapped references found.</div>');
            }
        }).fail(function() {
            content.html('<div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> Failed to load unmapped references.</div>');
        });
    });

    // ==================== INLINE MAPPING ====================
    
    // Open inline mapping modal from table row
    $(document).on('click', '.btn-inline-map', function() {
        var ref = $(this).data('ref');
        $('#inline-ref-to-map').val(ref);
        $('#inline-ref-display').text(ref);
        $('#inline-ref-input').val(ref);
        
        // Reset modal state
        $('#inline-map-existing').removeClass('d-none');
        $('#inline-map-already').addClass('d-none');
        $('#inline-suggestions-container').addClass('d-none');
        $('#inline-category-select').val('');
        $('#inline-mapped-ref-input').val('');
        
        // Load suggestions
        $.ajax({
            url: "{{ url('dashboard/reports/mpesa-logs/reference-suggestions') }}",
            type: 'GET',
            data: { reference: ref },
            timeout: 10000
        }).done(function(data) {
            if (data.mapped && data.mapping) {
                // Already mapped
                $('#inline-map-existing').addClass('d-none');
                $('#inline-map-already').removeClass('d-none');
                $('#inline-mapped-category').text(data.category_name || 'Unknown');
                $('#inline-mapped-category').css('color', data.category_color || '#28a745');
                $('#inline-category-select').val(data.mapping.summary_category_id);
                $('#inline-mapped-ref-input').val(data.mapping.mapped_ref);
            } else {
                // Not mapped - show suggestions
                $('#inline-mapped-ref-input').val(data.suggested_mapped_ref);
                
                if (data.similar_mappings && data.similar_mappings.length > 0) {
                    $('#inline-suggestions-container').removeClass('d-none');
                    var html = '';
                    data.similar_mappings.forEach(function(item) {
                        html += '<button type="button" class="list-group-item list-group-item-action suggestion-item" ' +
                            'data-category-id="' + item.category_id + '" ' +
                            'data-mapped-ref="' + item.mapped_to + '">' +
                            '<div class="d-flex justify-content-between">' +
                                '<span><strong>' + item.reference + '</strong> → ' + item.mapped_to + '</span>' +
                                '<span class="badge badge-info">' + item.similarity + '% match</span>' +
                            '</div>' +
                            '<small class="text-muted">Category: ' + (item.category || 'None') + '</small>' +
                        '</button>';
                    });
                    $('#inline-similar-mappings').html(html);
                }
            }
        });
        
        $('#inlineMappingModal').modal('show');
    });
    
    // Click on similar mapping suggestion
    $(document).on('click', '.suggestion-item', function() {
        var categoryId = $(this).data('category-id');
        var mappedRef = $(this).data('mapped-ref');
        
        if (categoryId) {
            $('#inline-category-select').val(categoryId);
        }
        if (mappedRef) {
            $('#inline-mapped-ref-input').val(mappedRef);
        }
    });
    
    // Save inline mapping
    $('#btn-save-inline-map').click(function() {
        var ref = $('#inline-ref-to-map').val();
        var categoryId = $('#inline-category-select').val();
        var mappedRef = $('#inline-mapped-ref-input').val();
        
        if (!categoryId) {
            alert('Please select a category.');
            return;
        }
        
        var btn = $(this);
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');
        
        $.ajax({
            url: "{{ url('dashboard/reports/mpesa-logs/inline-map') }}",
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                original_ref: ref,
                summary_category_id: categoryId,
                mapped_ref: mappedRef
            },
            timeout: 15000
        }).done(function(data) {
            if (data.success) {
                toastr.success(data.message);
                $('#inlineMappingModal').modal('hide');
                // Refresh table to show updated category
                table.draw(false);
                // Refresh unmapped count
                checkUnmappedReferences();
            } else {
                toastr.error(data.message);
            }
        }).fail(function(xhr) {
            toastr.error(xhr.responseJSON?.message || 'Failed to save mapping');
        }).always(function() {
            btn.prop('disabled', false).html('<i class="fas fa-save"></i> Save Mapping');
        });
    });
    
    // Auto-discover new references button
    $('#btn-auto-discover').click(function() {
        var btn = $(this);
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Discovering...');
        
        $.ajax({
            url: "{{ url('dashboard/reports/mpesa-logs/auto-discover') }}",
            type: 'POST',
            data: { _token: '{{ csrf_token() }}' },
            timeout: 60000
        }).done(function(data) {
            toastr.success(data.message);
            checkUnmappedReferences();
        }).fail(function() {
            toastr.error('Auto-discovery failed');
        }).always(function() {
            btn.prop('disabled', false).html('<i class="fas fa-magic"></i> Auto-Discover');
        });
    });
});
</script>
@endpush
