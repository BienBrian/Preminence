<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Giving Statement</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        
        .email-wrapper {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
        }
        
        .email-header {
            background: linear-gradient(135deg, #2c5282 0%, #1a365d 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .email-header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 300;
        }
        
        .email-header .church-name {
            font-size: 16px;
            opacity: 0.9;
            margin-top: 10px;
        }
        
        .email-body {
            padding: 30px;
        }
        
        .greeting {
            font-size: 18px;
            color: #2c5282;
            margin-bottom: 20px;
        }
        
        .content-section {
            margin-bottom: 25px;
        }
        
        .summary-box {
            background: #f7fafc;
            border-left: 4px solid #2c5282;
            padding: 20px;
            margin: 20px 0;
            border-radius: 0 5px 5px 0;
        }
        
        .summary-title {
            font-size: 14px;
            font-weight: bold;
            color: #2c5282;
            margin-bottom: 15px;
            text-transform: uppercase;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .summary-row:last-child {
            border-bottom: none;
            font-weight: bold;
            color: #2c5282;
            font-size: 16px;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 2px solid #2c5282;
        }
        
        .password-notice {
            background: #fffaf0;
            border: 1px solid #ed8936;
            border-radius: 5px;
            padding: 15px;
            margin: 20px 0;
        }
        
        .password-notice-title {
            color: #c05621;
            font-weight: bold;
            margin-bottom: 8px;
        }
        
        .thank-you-box {
            background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
            border: 1px solid #cbd5e0;
            border-radius: 8px;
            padding: 25px;
            margin: 25px 0;
            text-align: center;
        }
        
        .thank-you-title {
            font-size: 16px;
            font-weight: bold;
            color: #2c5282;
            margin-bottom: 10px;
        }
        
        .thank-you-text {
            font-style: italic;
            color: #4a5568;
            line-height: 1.6;
        }
        
        .email-footer {
            background: #1a365d;
            color: white;
            padding: 20px;
            text-align: center;
            font-size: 12px;
        }
        
        .email-footer a {
            color: #90cdf4;
        }
        
        .disclaimer {
            font-size: 11px;
            color: #718096;
            margin-top: 25px;
            padding: 15px;
            background: #f7fafc;
            border-radius: 5px;
        }
        
        @media only screen and (max-width: 600px) {
            .email-body {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <!-- Header -->
        <div class="email-header">
            <h1><i class="fas fa-file-invoice"></i> Your Giving Statement</h1>
            <div class="church-name">{{ $data['church_name'] }}</div>
        </div>
        
        <!-- Body -->
        <div class="email-body">
            <div class="greeting">
                Dear {{ $data['member_name'] }},
            </div>
            
            <div class="content-section">
                {!! strip_tags($data['body'], '<p><br><strong><em><ul><ol><li><h1><h2><h3><h4><h5><h6><a><img><blockquote><table><thead><tbody><tr><th><td><div><span><hr>') !!
            </div>
            
            <!-- Summary Box -->
            <div class="summary-box">
                <div class="summary-title">Statement Summary</div>
                <div class="summary-row">
                    <span>Period</span>
                    <span>{{ $data['period_from'] }} - {{ $data['period_to'] }}</span>
                </div>
                <div class="summary-row">
                    <span>Transactions</span>
                    <span>{{ $data['transaction_count'] }}</span>
                </div>
                <div class="summary-row">
                    <span>Total Giving</span>
                    <span>KES {{ $data['total_amount'] }}</span>
                </div>
            </div>
            
            <!-- Password Notice -->
            @if($data['password_hint'])
            <div class="password-notice">
                <div class="password-notice-title">
                    <i class="fas fa-lock"></i> Document Password Protection
                </div>
                <p>The attached PDF is password protected.</p>
                <p><strong>Password Hint:</strong> {{ $data['password_hint'] }}</p>
            </div>
            @endif
            
            <!-- Disclaimer -->
            <div class="disclaimer">
                <strong>Important:</strong> This statement is provided for your records. Please consult with your tax advisor regarding the tax deductibility of your contributions. For questions or corrections, please contact us.
            </div>
        </div>
        
        <!-- Footer -->
        <div class="email-footer">
            <p>{{ $data['church_name'] }} &copy; {{ date('Y') }}</p>
            <p>This email was sent automatically. Please do not reply to this email.</p>
        </div>
    </div>
</body>
</html>
