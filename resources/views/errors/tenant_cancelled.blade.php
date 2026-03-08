<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Cancelled - {{ $tenant->name ?? 'Church App' }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .cancelled-card {
            background: white;
            border-radius: 20px;
            padding: 3rem;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            text-align: center;
            max-width: 500px;
        }
        .icon {
            font-size: 5rem;
            color: #6c757d;
            margin-bottom: 1.5rem;
        }
        .btn-restore {
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
        .btn-restore:hover {
            color: white;
            opacity: 0.9;
        }
    </style>
</head>
<body>
    <div class="cancelled-card">
        <div class="icon">
            ✕
        </div>
        <h1 class="h3 mb-3">Account Cancelled</h1>
        <p class="text-muted mb-4">
            @if($tenant)
                <strong>{{ $tenant->name }}</strong>'s subscription has been cancelled.
            @else
                Your church account has been cancelled.
            @endif
        </p>
        <p class="mb-4">
            We're sorry to see you go. Your account and data have been preserved for 30 days in case you change your mind.
        </p>
        <p class="text-muted">
            Want to reactivate your account?
        </p>
        <a href="mailto:support@pisti.co.ke?subject=Account%20Reactivation%20Request" class="btn-restore">
            Reactivate Account
        </a>
        <div class="mt-4">
            <a href="{{ url('/') }}" class="text-muted text-decoration-none">
                ← Return to Homepage
            </a>
        </div>
    </div>
</body>
</html>
