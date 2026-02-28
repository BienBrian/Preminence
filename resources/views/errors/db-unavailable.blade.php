<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service Unavailable</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <style>
        html, body {
            height: 100%;
            margin: 0;
        }
        .error-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #f0f4ff 0%, #e8ecf8 100%);
            padding: 2rem;
        }
        .error-card {
            background: #fff;
            border-radius: 1rem;
            box-shadow: 0 8px 30px rgba(0,0,0,0.1);
            padding: 3rem 2.5rem;
            max-width: 480px;
            width: 100%;
            text-align: center;
        }
        .error-icon {
            font-size: 4rem;
            color: #e74c3c;
            margin-bottom: 1.25rem;
        }
        .error-code {
            font-size: 0.875rem;
            font-weight: 600;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: #e74c3c;
            margin-bottom: 0.5rem;
        }
        .error-title {
            font-size: 1.6rem;
            font-weight: 700;
            color: #1a1a2e;
            margin-bottom: 0.75rem;
        }
        .error-body {
            color: #6c757d;
            margin-bottom: 2rem;
            line-height: 1.65;
        }
        .btn-login {
            display: inline-block;
            padding: 0.75rem 2rem;
            background: #4154f1;
            color: #fff;
            border-radius: 0.5rem;
            font-weight: 600;
            text-decoration: none;
            transition: background 0.2s;
        }
        .btn-login:hover {
            background: #2c3fc7;
            color: #fff;
            text-decoration: none;
        }
        .btn-retry {
            display: inline-block;
            margin-left: 0.75rem;
            padding: 0.75rem 1.5rem;
            border: 2px solid #dee2e6;
            border-radius: 0.5rem;
            color: #6c757d;
            font-weight: 600;
            text-decoration: none;
            transition: border-color 0.2s, color 0.2s;
        }
        .btn-retry:hover {
            border-color: #adb5bd;
            color: #343a40;
            text-decoration: none;
        }
        .divider {
            border: none;
            border-top: 1px solid #f0f0f0;
            margin: 2rem 0 1.25rem;
        }
        .hint {
            font-size: 0.8rem;
            color: #adb5bd;
        }
    </style>
</head>
<body>
    <div class="error-wrapper">
        <div class="error-card">
            <div class="error-icon">
                <i class="fa-solid fa-database"></i>
            </div>
            <div class="error-code">503 &mdash; Service Unavailable</div>
            <h1 class="error-title">We're having trouble connecting</h1>
            <p class="error-body">
                The server is temporarily unable to process your request because it cannot reach the database.
                This is usually a brief issue &mdash; please wait a moment and try again.
            </p>
            <div>
                <a href="{{ $loginUrl }}" class="btn-login">
                    <i class="fa-solid fa-right-to-bracket"></i> Go to Login
                </a>
                <a href="javascript:location.reload()" class="btn-retry">
                    <i class="fa-solid fa-rotate-right"></i> Retry
                </a>
            </div>
            <hr class="divider">
            <p class="hint">
                If this keeps happening, please contact your system administrator.
            </p>
        </div>
    </div>

    {{-- Font Awesome (in case the main bundle isn't loaded) --}}
    <script defer src="https://use.fontawesome.com/releases/v6.4.0/js/all.js"></script>
</body>
</html>
