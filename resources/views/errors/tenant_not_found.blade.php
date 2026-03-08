@extends('layouts.app')

@section('title', 'Church Not Found')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 text-center">
            <div class="mb-4">
                <i class="bi bi-search text-muted" style="font-size: 5rem;"></i>
            </div>
            <h1 class="display-5 mb-3">Church Not Found</h1>
            <p class="lead text-muted mb-4">
                We couldn't find a church with the subdomain <strong>{{ $subdomain }}</strong>.
            </p>
            
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="card-title">What you can do:</h5>
                    <ul class="list-unstyled mt-3 mb-0">
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-primary"></i>
                            Check that the URL is spelled correctly
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-primary"></i>
                            Contact your church administrator
                        </li>
                        <li class="mb-0">
                            <i class="bi bi-check-circle text-primary"></i>
                            If you're looking to register a new church, 
                            <a href="https://happychurchruiru.org">click here</a>
                        </li>
                    </ul>
                </div>
            </div>
            
            <div class="d-flex justify-content-center gap-3">
                <a href="https://happychurchruiru.org" class="btn btn-primary btn-lg">
                    <i class="bi bi-house"></i> Go to Happy Church Ruiru
                </a>
            </div>
            
            <hr class="my-5">
            
            <p class="text-muted small">
                <strong>Future Feature:</strong> Online church registration coming soon!<br>
                You'll be able to create your church's own subdomain and start managing your congregation.
            </p>
        </div>
    </div>
</div>
@endsection
