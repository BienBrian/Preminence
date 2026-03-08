@extends('superadmin.layouts.app')

@section('title', 'DNS Propagation')
@section('page-title', 'Propagation Status: ' . $tenant->name)

@section('content')
<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Global DNS Propagation Status</h6>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i>
                    <strong>Domain:</strong> {{ $tenant->custom_domain ?? $tenant->subdomain_url }}
                    <br>
                    <strong>Last Checked:</strong> {{ now()->format('Y-m-d H:i:s') }}
                </div>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Location</th>
                                <th>Status</th>
                                <th>Response Time</th>
                                <th>Last Update</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($checks as $check)
                                <tr>
                                    <td>
                                        <i class="bi bi-geo-alt"></i> {{ $check['location'] }}
                                    </td>
                                    <td>
                                        @if($check['status'] === 'propagated')
                                            <span class="badge bg-success">
                                                <i class="bi bi-check-circle"></i> Propagated
                                            </span>
                                        @elseif($check['status'] === 'pending')
                                            <span class="badge bg-warning">
                                                <i class="bi bi-hourglass-split"></i> Pending
                                            </span>
                                        @else
                                            <span class="badge bg-danger">
                                                <i class="bi bi-x-circle"></i> Failed
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="text-muted">{{ rand(20, 150) }} ms</span>
                                    </td>
                                    <td>
                                        @if($check['timestamp'])
                                            {{ $check['timestamp']->diffForHumans() }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">
                                        No propagation checks available
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <a href="{{ route('superadmin.dns.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Back
                    </a>
                    <button class="btn btn-primary" disabled>
                        <i class="bi bi-arrow-clockwise"></i> Refresh Status
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Future Feature Notice -->
        <div class="card shadow mt-4">
            <div class="card-header py-3 bg-info text-white">
                <h6 class="m-0 font-weight-bold"><i class="bi bi-lightbulb"></i> Phase 3 Feature Preview</h6>
            </div>
            <div class="card-body">
                <p>Full DNS propagation monitoring coming in Phase 3:</p>
                <ul class="mb-0">
                    <li>Real-time checks from 20+ global locations</li>
                    <li>A, AAAA, CNAME, and MX record verification</li>
                    <li>Propagation time estimates</li>
                    <li>Automated alerts when propagation completes</li>
                    <li>Historical propagation data</li>
                </ul>
                
                <div class="alert alert-warning mt-3 mb-0">
                    <i class="bi bi-exclamation-triangle"></i>
                    <strong>Note:</strong> Currently showing simulated data for UI preview. 
                    Real propagation checks require integration with global DNS monitoring services.
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
