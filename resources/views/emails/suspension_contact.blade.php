<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Suspension Inquiry - {{ $tenant_name }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .content {
            background: #f9f9f9;
            padding: 20px;
            border: 1px solid #ddd;
        }
        .field {
            margin-bottom: 15px;
        }
        .label {
            font-weight: bold;
            color: #667eea;
        }
        .message-box {
            background: white;
            padding: 15px;
            border-left: 4px solid #667eea;
            margin-top: 10px;
        }
        .footer {
            text-align: center;
            padding: 20px;
            color: #666;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>Account Suspension Inquiry</h2>
    </div>
    
    <div class="content">
        <p>A suspended tenant has submitted a contact form through the suspension page.</p>
        
        <div class="field">
            <span class="label">Tenant Name:</span> {{ $tenant_name }}
        </div>
        
        <div class="field">
            <span class="label">Tenant ID:</span> {{ $tenant_id }}
        </div>
        
        <div class="field">
            <span class="label">Suspension Type:</span> {{ $suspension_type ?? 'Not specified' }}
        </div>
        
        <div class="field">
            <span class="label">Suspension Reason:</span> {{ $suspension_reason ?? 'Not specified' }}
        </div>
        
        <hr style="border: none; border-top: 1px solid #ddd; margin: 20px 0;">
        
        <div class="field">
            <span class="label">Contact Name:</span> {{ $name }}
        </div>
        
        <div class="field">
            <span class="label">Email:</span> {{ $email }}
        </div>
        
        <div class="field">
            <span class="label">Phone:</span> {{ $phone }}
        </div>
        
        <div class="field">
            <span class="label">Subject:</span> {{ $subject }}
        </div>
        
        <div class="field">
            <span class="label">Message:</span>
            <div class="message-box">
                {{ $message }}
            </div>
        </div>
    </div>
    
    <div class="footer">
        <p>This email was sent from the Pisti Platform suspension page.</p>
        <p>© {{ date('Y') }} Pisti. All rights reserved.</p>
    </div>
</body>
</html>
