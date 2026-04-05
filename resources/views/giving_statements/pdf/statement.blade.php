<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giving Statement - {{ $member_name }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 11pt;
            line-height: 1.5;
            color: #333;
            padding: 40px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 3px double #2c5282;
        }
        
        .church-name {
            font-size: 22pt;
            font-weight: bold;
            color: #2c5282;
            margin-bottom: 5px;
        }
        
        .church-info {
            font-size: 9pt;
            color: #666;
        }
        
        .document-title {
            text-align: center;
            font-size: 16pt;
            font-weight: bold;
            margin: 25px 0 20px 0;
            color: #2c5282;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        
        .member-section {
            margin-bottom: 25px;
        }
        
        .section-title {
            font-size: 10pt;
            font-weight: bold;
            color: #2c5282;
            text-transform: uppercase;
            margin-bottom: 8px;
            padding-bottom: 3px;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .info-grid {
            display: table;
            width: 100%;
        }
        
        .info-row {
            display: table-row;
        }
        
        .info-label {
            display: table-cell;
            width: 30%;
            font-weight: bold;
            color: #4a5568;
            padding: 4px 0;
        }
        
        .info-value {
            display: table-cell;
            width: 70%;
            padding: 4px 0;
        }
        
        .summary-box {
            background: #f7fafc;
            border: 1px solid #e2e8f0;
            border-radius: 5px;
            padding: 15px;
            margin: 20px 0;
        }
        
        .summary-title {
            font-size: 11pt;
            font-weight: bold;
            color: #2c5282;
            margin-bottom: 10px;
        }
        
        .summary-grid {
            display: table;
            width: 100%;
        }
        
        .summary-row {
            display: table-row;
        }
        
        .summary-cell {
            display: table-cell;
            padding: 5px 0;
        }
        
        .summary-cell.amount {
            text-align: right;
            font-weight: bold;
        }
        
        .total-row {
            font-weight: bold;
            border-top: 2px solid #2c5282;
            margin-top: 10px;
            padding-top: 10px;
        }
        
        table.transactions {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
            font-size: 9pt;
        }
        
        table.transactions th {
            background: #2c5282;
            color: white;
            padding: 8px 10px;
            text-align: left;
            font-weight: bold;
        }
        
        table.transactions td {
            padding: 6px 10px;
            border-bottom: 1px solid #e2e8f0;
        }
        
        table.transactions tr:nth-child(even) {
            background: #f7fafc;
        }
        
        table.transactions .amount {
            text-align: right;
        }
        
        .category-header {
            background: #edf2f7;
            font-weight: bold;
            padding: 10px;
            margin-top: 20px;
            border-left: 4px solid #2c5282;
        }
        
        .category-total {
            text-align: right;
            font-weight: bold;
            color: #2c5282;
            padding: 8px 10px;
            background: #f7fafc;
        }
        
        .thank-you-box {
            background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
            border: 1px solid #cbd5e0;
            border-radius: 8px;
            padding: 20px;
            margin: 25px 0;
            text-align: center;
        }
        
        .thank-you-title {
            font-size: 12pt;
            font-weight: bold;
            color: #2c5282;
            margin-bottom: 10px;
        }
        
        .thank-you-text {
            font-style: italic;
            color: #4a5568;
            line-height: 1.6;
        }
        
        .footer {
            margin-top: 40px;
            padding-top: 15px;
            border-top: 1px solid #e2e8f0;
            font-size: 8pt;
            color: #718096;
            text-align: center;
        }
        
        .password-notice {
            background: #fffaf0;
            border: 1px solid #ed8936;
            border-radius: 5px;
            padding: 10px 15px;
            margin: 15px 0;
            font-size: 9pt;
        }
        
        .password-notice strong {
            color: #c05621;
        }
        
        .page-break {
            page-break-after: always;
        }
        
        .disclaimer {
            font-size: 8pt;
            color: #718096;
            margin-top: 30px;
            padding: 10px;
            background: #f7fafc;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <!-- Church Header -->
    <div class="header">
        <div class="church-name">{{ $church_name }}</div>
        @if($church_address)
            <div class="church-info">{{ $church_address }}</div>
        @endif
        @if($church_phone || $church_email)
            <div class="church-info">
                @if($church_phone) Tel: {{ $church_phone }} @endif
                @if($church_phone && $church_email) | @endif
                @if($church_email) Email: {{ $church_email }} @endif
            </div>
        @endif
    </div>
    
    <!-- Document Title -->
    <div class="document-title">Giving Statement</div>
    
    <!-- Member Information -->
    <div class="member-section">
        <div class="section-title">Member Information</div>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Name:</div>
                <div class="info-value">{{ $member_name }}</div>
            </div>
            @if($member_id)
            <div class="info-row">
                <div class="info-label">Member ID:</div>
                <div class="info-value">{{ $member_id }}</div>
            </div>
            @endif
            @if($member_phone)
            <div class="info-row">
                <div class="info-label">Phone:</div>
                <div class="info-value">{{ $member_phone }}</div>
            </div>
            @endif
            @if($member_email)
            <div class="info-row">
                <div class="info-label">Email:</div>
                <div class="info-value">{{ $member_email }}</div>
            </div>
            @endif
        </div>
    </div>
    
    <!-- Report Period -->
    <div class="member-section">
        <div class="section-title">Report Period</div>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">From:</div>
                <div class="info-value">{{ date('F d, Y', strtotime($period_from)) }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">To:</div>
                <div class="info-value">{{ date('F d, Y', strtotime($period_to)) }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Generated:</div>
                <div class="info-value">{{ $generated_date }}</div>
            </div>
        </div>
    </div>
    
    <!-- Summary Box -->
    <div class="summary-box">
        <div class="summary-title">Giving Summary</div>
        <div class="summary-grid">
            @foreach($grouped_data as $group)
            <div class="summary-row">
                <div class="summary-cell">{{ $group['name'] }} ({{ $group['count'] }} transactions)</div>
                <div class="summary-cell amount">KES {{ number_format($group['total'], 2) }}</div>
            </div>
            @endforeach
            <div class="summary-row total-row">
                <div class="summary-cell"><strong>Total Giving</strong></div>
                <div class="summary-cell amount"><strong>KES {{ number_format($total_amount, 2) }}</strong></div>
            </div>
        </div>
    </div>
    
    <!-- Password Notice -->
    @if($password_hint)
    <div class="password-notice">
        <strong>&#128274; Document Protection:</strong>
        This PDF is password protected. {{ $password_hint }}
    </div>
    @endif
    
    <!-- Detailed Transactions -->
    <div class="member-section">
        <div class="section-title">Detailed Transactions</div>
        
        @foreach($grouped_data as $group)
            <div class="category-header">
                {{ $group['name'] }}
            </div>
            
            <table class="transactions">
                <thead>
                    <tr>
                        <th style="width: 15%">Date</th>
                        <th style="width: 45%">Description</th>
                        <th style="width: 20%">Reference</th>
                        <th style="width: 20%" class="amount">Amount (KES)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($group['transactions'] as $transaction)
                    <tr>
                        <td>{{ date('M d, Y', strtotime($transaction->date)) }}</td>
                        <td>{{ $transaction->description ?: 'Contribution' }}</td>
                        <td>#{{ $transaction->id }}</td>
                        <td class="amount">{{ number_format($transaction->amount, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            
            <div class="category-total">
                {{ $group['name'] }} Total: KES {{ number_format($group['total'], 2) }}
            </div>
        @endforeach
    </div>
    
    <!-- Thank You Note -->
    @if($include_thank_you && $thank_you_note)
    <div class="thank-you-box">
        <div class="thank-you-title">With Gratitude</div>
        <div class="thank-you-text">{{ $thank_you_note }}</div>
    </div>
    @endif
    
    <!-- Tax Disclaimer -->
    <div class="disclaimer">
        <strong>Important Notice:</strong> This statement is provided for record-keeping purposes. 
        Please consult with your tax advisor regarding the tax deductibility of your contributions. 
        This document does not constitute official tax documentation. For questions or corrections, 
        please contact {{ $church_name }}.
    </div>
    
    <!-- Footer -->
    <div class="footer">
        <p>This giving statement was generated electronically on {{ $generated_date }}.</p>
        <p>{{ $church_name }} &copy; {{ date('Y') }} - All Rights Reserved.</p>
    </div>
</body>
</html>
