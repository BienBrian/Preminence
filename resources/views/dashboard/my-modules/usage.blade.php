@extends('dashboard.layouts.app')

@section('title', $module->name ?? $subscription->module_key . ' - Usage')

@section('content')
<div class="container py-4">
    <div class="row">
        <div class="col-lg-3">
            {{-- Sidebar --}}
            <div class="list-group mb-4">
                <a href="{{ route('my-modules.index') }}" class="list-group-item list-group-item-action">
                    <i class="bi bi-arrow-left me-2"></i>Back to My Modules
                </a>
            </div>
            
            <div class="card">
                <div class="card-body text-center">
                    <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px;">
                        <i class="bi {{ $module->icon ?? 'bi-box' }} fs-2 text-primary"></i>
                    </div>
                    <h5 class="mb-1">{{ $module->name ?? $subscription->module_key }}</h5>
                    <p class="text-muted small mb-0">{{ $subscription->getBillingPeriodLabel() }}</p>
                </div>
            </div>
        </div>
        
        <div class="col-lg-9">
            <h2 class="h4 mb-4">Usage Statistics</h2>
            
            {{-- Overview Cards --}}
            <div class="row g-4 mb-4">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="bg-primary bg-opacity-10 rounded p-2">
                                        <i class="bi bi-activity text-primary"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <div class="text-muted small">Total Uses</div>
                                    <div class="h4 mb-0">{{ number_format($subscription->usage_count) }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="bg-success bg-opacity-10 rounded p-2">
                                        <i class="bi bi-calendar-check text-success"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <div class="text-muted small">Installed On</div>
                                    <div class="h5 mb-0">
                                        @if($subscription->installed_at)
                                            {{ $subscription->installed_at->format('M d, Y') }}
                                        @else
                                            -
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="bg-info bg-opacity-10 rounded p-2">
                                        <i class="bi bi-clock-history text-info"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <div class="text-muted small">Last Used</div>
                                    <div class="h5 mb-0">
                                        @if($subscription->last_used_at)
                                            {{ $subscription->last_used_at->diffForHumans() }}
                                        @else
                                            Never
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            {{-- Detailed Metrics --}}
            @if(!empty($subscription->usage_metrics))
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Detailed Metrics</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Metric</th>
                                    <th class="text-end">Count</th>
                                    <th>Usage</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($subscription->usage_metrics as $metric => $count)
                                <tr>
                                    <td>{{ ucfirst(str_replace('_', ' ', $metric)) }}</td>
                                    <td class="text-end">{{ number_format($count) }}</td>
                                    <td style="width: 50%;">
                                        @php
                                            $max = max($subscription->usage_metrics);
                                            $percentage = $max > 0 ? ($count / $max) * 100 : 0;
                                        @endphp
                                        <div class="progress" style="height: 8px;">
                                            <div class="progress-bar" style="width: {{ $percentage }}%"></div>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
            
            {{-- Installation Log --}}
            @if(!empty($subscription->installation_log))
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Installation History</h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        @foreach(array_reverse($subscription->installation_log) as $log)
                        <div class="d-flex mb-3">
                            <div class="flex-shrink-0 me-3">
                                @if(($log['status'] ?? '') === 'complete')
                                    <i class="bi bi-check-circle-fill text-success"></i>
                                @elseif(($log['status'] ?? '') === 'error')
                                    <i class="bi bi-x-circle-fill text-danger"></i>
                                @else
                                    <i class="bi bi-circle text-muted"></i>
                                @endif
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between">
                                    <strong>{{ ucfirst($log['step'] ?? 'Unknown') }}</strong>
                                    <small class="text-muted">{{ $log['timestamp'] ?? '' }}</small>
                                </div>
                                @if(!empty($log['message']))
                                <p class="text-muted small mb-0">{{ $log['message'] }}</p>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
