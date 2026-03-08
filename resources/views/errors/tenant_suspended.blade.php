<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Suspended - {{ $tenant->name ?? 'Church App' }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .suspended-card {
            background: white;
            border-radius: 20px;
            padding: 3rem;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            text-align: center;
            max-width: 500px;
        }
        .icon {
            font-size: 5rem;
            color: #dc3545;
            margin-bottom: 1.5rem;
        }
        .btn-contact {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            padding: 12px 30px;
            border-radius: 25px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            margin-top: 1rem;
        }
        .btn-contact:hover {
            color: white;
            opacity: 0.9;
        }
    </style>
</head>
<body>
    <div class="suspended-card">
        <div class="icon">
            <i class="fas fa-ban"></i>🚫
        </div>
        <h1 class="h3 mb-3">Account Suspended</h1>
        <p class="text-muted mb-4">
            @if($tenant)
                <strong>{{ $tenant->name }}</strong> has been suspended.
            @else
                Your church account has been suspended.
            @endif
        </p>
        <p class="mb-4">
            This may be due to:<br>
            • Subscription payment issues<br>
            • Terms of service violation<br>
            • Administrative action
        </p>
        <p class="text-muted">
            Please contact our support team to resolve this issue.
        </p>
        <a href="mailto:support@pisti.co.ke?subject=Account%20Suspension%20Inquiry" class="btn-contact">
            Contact Support
        </a>
        <div class="mt-4">
            <a href="{{ url('/') }}" class="text-muted text-decoration-none">
                ← Return to Homepage
            </a>
        </div>
    </div>
</body>
</html>
