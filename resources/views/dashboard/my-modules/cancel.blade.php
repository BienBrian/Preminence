@extends('dashboard.layouts.app')

@section('title', 'Cancel Module')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            {{-- Header --}}
            <div class="d-flex align-items-center mb-4">
                <a href="{{ route('my-modules.index') }}" class="btn btn-link text-decoration-none">
                    <i class="bi bi-arrow-left"></i> Back
                </a>
                <h1 class="h3 mb-0 ms-3">Cancel Module</h1>
            </div>

            @if(!empty($dependents))
            {{-- Dependency Warning --}}
            <div class="alert alert-warning d-flex align-items-start mb-4">
                <i class="bi bi-exclamation-triangle-fill me-2 mt-1"></i>
                <div>
                    <strong>Warning: Other modules depend on this one</strong>
                    <p class="mb-2">The following modules will stop working if you cancel:</p>
                    <ul class="mb-0">
                        @foreach($dependents as $dependent)
                        <li>{{ $dependent['name'] }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
            @endif

            {{-- Cancellation Form --}}
            <div class="card">
                <div class="card-header bg-danger bg-opacity-10">
                    <h5 class="mb-0 text-danger">Confirm Cancellation</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('my-modules.cancel', $subscription) }}" method="POST">
                        @csrf
                        
                        {{-- Refund Info --}}
                        @if($refundAmount > 0)
                        <div class="alert alert-info mb-4">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-cash-refund fs-4 me-3"></i>
                                <div>
                                    <strong>Prorated Refund Available</strong>
                                    <p class="mb-0">You will receive a refund of <strong>KES {{ number_format($refundAmount, 2) }}</strong> for unused time.</p>
                                </div>
                            </div>
                        </div>
                        @endif
                        
                        {{-- Reason --}}
                        <div class="mb-4">
                            <label for="reason" class="form-label">Why are you canceling? <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="reason" name="reason" rows="3" required placeholder="Help us improve by telling us why you're leaving..."></textarea>
                        </div>
                        
                        {{-- Purge Data Option --}}
                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="purge_data" name="purge_data" value="1">
                                <label class="form-check-label" for="purge_data">
                                    <strong>Remove all module data</strong>
                                    <small class="text-muted d-block">This will permanently delete all data associated with this module. This action cannot be undone.</small>
                                </label>
                            </div>
                        </div>
                        
                        {{-- Confirmation --}}
                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="confirm_uninstall" name="confirm_uninstall" value="1" required>
                                <label class="form-check-label" for="confirm_uninstall">
                                    I understand that canceling will immediately stop access to this module and any dependent modules.
                                </label>
                            </div>
                        </div>
                        
                        {{-- Actions --}}
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-danger">
                                <i class="bi bi-x-circle me-2"></i>Yes, Cancel Module
                            </button>
                            <a href="{{ route('my-modules.index') }}" class="btn btn-outline-secondary">
                                Keep Module
                            </a>
                        </div>
                    </form>
                </div>
            </div>
            
            {{-- Alternative Options --}}
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">Not sure about canceling?</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <div class="bg-primary bg-opacity-10 rounded p-2">
                                        <i class="bi bi-pause-circle text-primary"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-1">Pause Instead</h6>
                                    <p class="text-muted small mb-2">Temporarily disable without losing data</p>
                                    <button class="btn btn-sm btn-outline-primary" disabled>Coming Soon</button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <div class="bg-primary bg-opacity-10 rounded p-2">
                                        <i class="bi bi-headset text-primary"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-1">Contact Support</h6>
                                    <p class="text-muted small mb-2">We may be able to help with your concerns</p>
                                    <a href="mailto:support@pisti.com" class="btn btn-sm btn-outline-primary">Get Help</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
