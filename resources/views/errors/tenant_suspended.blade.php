<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Account Suspended - {{ $tenant->name ?? 'Pisti' }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 600px;
            width: 100%;
            padding: 40px;
            animation: slideIn 0.5s ease-out;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .logo {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .logo img {
            max-width: 80px;
            height: auto;
        }
        
        .logo-placeholder {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
            color: white;
            font-size: 32px;
            font-weight: bold;
        }
        
        .suspension-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
            font-size: 40px;
        }
        
        .suspension-icon.financial {
            background: #fff3cd;
            color: #856404;
        }
        
        .suspension-icon.terms {
            background: #f8d7da;
            color: #721c24;
        }
        
        .suspension-icon.admin {
            background: #e2e3e5;
            color: #383d41;
        }
        
        h1 {
            text-align: center;
            color: #1a1a2e;
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 16px;
        }
        
        .description {
            text-align: center;
            color: #6b7280;
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 24px;
        }
        
        .info-box {
            background: #f8fafc;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 24px;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .info-row:last-child {
            border-bottom: none;
        }
        
        .info-label {
            color: #6b7280;
            font-weight: 500;
        }
        
        .info-value {
            color: #1a1a2e;
            font-weight: 600;
        }
        
        .amount-due {
            color: #dc2626;
            font-size: 24px;
            font-weight: 700;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            padding: 14px 24px;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
            text-decoration: none;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
        }
        
        .btn-payment {
            background: white;
            border: 2px solid #e5e7eb;
            color: #1a1a2e;
            margin-bottom: 12px;
        }
        
        .btn-payment:hover {
            border-color: #667eea;
            background: #f8fafc;
        }
        
        .payment-logo {
            height: 24px;
            width: auto;
        }
        
        .payment-section {
            margin-bottom: 24px;
        }
        
        .section-title {
            font-size: 14px;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 16px;
        }
        
        .divider {
            display: flex;
            align-items: center;
            margin: 24px 0;
            color: #6b7280;
            font-size: 14px;
        }
        
        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #e5e7eb;
        }
        
        .divider span {
            padding: 0 16px;
        }
        
        .contact-form {
            display: none;
        }
        
        .contact-form.active {
            display: block;
        }
        
        .form-group {
            margin-bottom: 16px;
        }
        
        .form-label {
            display: block;
            font-size: 14px;
            font-weight: 500;
            color: #374151;
            margin-bottom: 6px;
        }
        
        .form-input,
        .form-textarea {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 15px;
            transition: border-color 0.2s;
        }
        
        .form-input:focus,
        .form-textarea:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .form-textarea {
            min-height: 120px;
            resize: vertical;
        }
        
        .help-link {
            text-align: center;
            margin-top: 24px;
            padding-top: 24px;
            border-top: 1px solid #e5e7eb;
        }
        
        .help-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
        }
        
        .help-link a:hover {
            text-decoration: underline;
        }
        
        .alert {
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: none;
        }
        
        .alert.success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }
        
        .alert.error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
        
        .alert.show {
            display: block;
        }
        
        .loading {
            display: none;
            text-align: center;
            padding: 20px;
        }
        
        .loading.show {
            display: block;
        }
        
        .spinner {
            width: 40px;
            height: 40px;
            border: 3px solid #e5e7eb;
            border-top-color: #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 16px;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        .payment-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .payment-modal.show {
            display: flex;
        }
        
        .payment-modal-content {
            background: white;
            border-radius: 16px;
            max-width: 500px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
            padding: 32px;
            position: relative;
        }
        
        .modal-close {
            position: absolute;
            top: 16px;
            right: 16px;
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #6b7280;
        }
        
        .mpesa-instructions {
            background: #f0fdf4;
            border: 1px solid #86efac;
            border-radius: 8px;
            padding: 16px;
            margin: 16px 0;
        }
        
        .mpesa-steps {
            list-style: none;
            padding: 0;
        }
        
        .mpesa-steps li {
            padding: 8px 0;
            padding-left: 28px;
            position: relative;
        }
        
        .mpesa-steps li::before {
            content: attr(data-step);
            position: absolute;
            left: 0;
            width: 20px;
            height: 20px;
            background: #16a34a;
            color: white;
            border-radius: 50%;
            font-size: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        @media (max-width: 480px) {
            .container {
                padding: 24px;
            }
            
            h1 {
                font-size: 20px;
            }
            
            .amount-due {
                font-size: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        @php
            $suspensionInfo = $tenant ? $tenant->getSuspensionInfo() : [
                'type' => 'admin_action',
                'title' => 'Account Suspended',
                'description' => 'Your account has been temporarily suspended. Please contact our support team for assistance.',
                'icon' => 'lock',
                'color' => 'secondary',
                'reason' => null,
                'amount_due' => null,
                'currency' => 'KES',
                'is_financial' => false,
            ];
        @endphp
        
        <!-- Logo -->
        <div class="logo">
            @if($tenant && $tenant->logo)
                <img src="{{ asset('storage/' . $tenant->logo) }}" alt="{{ $tenant->name }}">
            @else
                <div class="logo-placeholder">P</div>
            @endif
        </div>
        
        <!-- Suspension Icon -->
        <div class="suspension-icon {{ $suspensionInfo['is_financial'] ? 'financial' : ($suspensionInfo['is_terms_violation'] ? 'terms' : 'admin') }}">
            @if($suspensionInfo['is_financial'])
                💳
            @elseif($suspensionInfo['is_terms_violation'])
                ⚠️
            @else
                🔒
            @endif
        </div>
        
        <!-- Title & Description -->
        <h1>{{ $suspensionInfo['title'] }}</h1>
        <p class="description">{{ $suspensionInfo['description'] }}</p>
        
        <!-- Alert Messages -->
        <div id="alert" class="alert"></div>
        
        <!-- Loading -->
        <div id="loading" class="loading">
            <div class="spinner"></div>
            <p>Processing your request...</p>
        </div>
        
        @if($suspensionInfo['reason'])
        <!-- Reason Info -->
        <div class="info-box">
            <div class="info-row">
                <span class="info-label">Reason:</span>
                <span class="info-value">{{ $suspensionInfo['reason'] }}</span>
            </div>
        </div>
        @endif
        
        @if($suspensionInfo['is_financial'])
        <!-- Amount Due (if specified) -->
        @if($suspensionInfo['amount_due'] && $suspensionInfo['amount_due'] > 0)
        <div class="info-box">
            <div class="info-row">
                <span class="info-label">Amount Due:</span>
                <span class="info-value amount-due">{{ $suspensionInfo['currency'] }} {{ number_format($suspensionInfo['amount_due'], 2) }}</span>
            </div>
        </div>
        @endif
        
        <!-- Payment Section -->
        <div class="payment-section">
            <p class="section-title">Select Payment Method</p>
            
            <button class="btn btn-payment" onclick="showPaymentModal('mpesa')">
                <svg width="80" height="24" viewBox="0 0 120 30" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect width="120" height="30" rx="4" fill="#00A650"/>
                    <text x="10" y="20" fill="white" font-size="14" font-weight="bold">M-PESA</text>
                </svg>
                <span>M-PESA</span>
            </button>
            
            <button class="btn btn-payment" onclick="showPaymentModal('card')">
                <svg width="100" height="24" viewBox="0 0 160 30" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="15" cy="15" r="12" fill="#EB001B"/>
                    <circle cx="25" cy="15" r="12" fill="#F79E1B"/>
                    <text x="45" y="20" fill="#1a1a2e" font-size="12" font-weight="600">Visa / Mastercard</text>
                </svg>
                <span>Card Payment</span>
            </button>
            
            <button class="btn btn-payment" onclick="showPaymentModal('airtel')">
                <svg width="80" height="24" viewBox="0 0 120 30" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect width="120" height="30" rx="4" fill="#E40000"/>
                    <text x="10" y="20" fill="white" font-size="14" font-weight="bold">Airtel</text>
                </svg>
                <span>Airtel Money</span>
            </button>
        </div>
        @endif
        
        <div class="divider">
            <span>OR</span>
        </div>
        
        <!-- Contact Admin Button -->
        <button class="btn btn-primary" id="contactBtn" onclick="toggleContactForm()">
            ✉️ Contact Administrator
        </button>
        
        <!-- Contact Form -->
        <form class="contact-form" id="contactForm" action="{{ route('tenant.suspended.contact') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="divider">
                <span>Contact Support</span>
            </div>
            
            <div class="form-group">
                <label class="form-label">Your Name</label>
                <input type="text" name="name" class="form-input" required placeholder="Enter your full name">
            </div>
            
            <div class="form-group">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-input" required placeholder="your@email.com">
            </div>
            
            <div class="form-group">
                <label class="form-label">Phone Number</label>
                <input type="tel" name="phone" class="form-input" placeholder="+254 XXX XXX XXX">
            </div>
            
            <div class="form-group">
                <label class="form-label">Subject</label>
                <input type="text" name="subject" class="form-input" required placeholder="Brief description of your issue">
            </div>
            
            <div class="form-group">
                <label class="form-label">Message</label>
                <textarea name="message" class="form-textarea" required placeholder="Please describe your issue in detail..."></textarea>
            </div>
            
            <div class="form-group">
                <label class="form-label">Attachment (Optional)</label>
                <input type="file" name="attachment" class="form-input" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                <small style="color: #6b7280; font-size: 12px;">Max 10MB. Accepted: PDF, DOC, Images</small>
            </div>
            
            <button type="submit" class="btn btn-primary">Send Message</button>
        </form>
        
        <!-- Help Center Link -->
        <div class="help-link">
            <a href="https://support.getpisti.com" target="_blank" rel="noopener">
                📚 Visit Help Center
            </a>
        </div>
    </div>
    
    <!-- Payment Modal -->
    <div class="payment-modal" id="paymentModal">
        <div class="payment-modal-content">
            <button class="modal-close" onclick="closePaymentModal()">&times;</button>
            
            <div id="mpesaForm">
                <h2 style="margin-bottom: 16px;">Pay with M-PESA</h2>
                <p style="color: #6b7280; margin-bottom: 20px;">
                    Amount: <strong style="color: #1a1a2e;">{{ $suspensionInfo['currency'] }} {{ number_format($suspensionInfo['amount_due'] ?? 0, 2) }}</strong>
                </p>
                
                <div class="mpesa-instructions">
                    <p style="font-weight: 600; margin-bottom: 12px;">How to pay:</p>
                    <ol class="mpesa-steps">
                        <li data-step="1">Go to M-PESA on your phone</li>
                        <li data-step="2">Select Lipa na M-PESA</li>
                        <li data-step="3">Select Pay Bill</li>
                        <li data-step="4">Enter Business Number: <strong>186903</strong></li>
                        <li data-step="5">Enter Account Number: <strong>{{ $tenant->slug ?? 'SUSPENSION' }}</strong></li>
                        <li data-step="6">Enter Amount: <strong>{{ number_format($suspensionInfo['amount_due'] ?? 0, 2) }}</strong></li>
                        <li data-step="7">Enter your M-PESA PIN and confirm</li>
                    </ol>
                </div>
                
                <div class="form-group">
                    <label class="form-label">M-PESA Number (for confirmation)</label>
                    <input type="tel" id="mpesaPhone" class="form-input" placeholder="2547XX XXX XXX">
                </div>
                
                <button class="btn btn-primary" onclick="processPayment('mpesa')">
                    I've Completed the Payment
                </button>
            </div>
            
            <div id="cardForm" style="display: none;">
                <h2 style="margin-bottom: 16px;">Pay with Card</h2>
                <p style="color: #6b7280; margin-bottom: 20px;">
                    Amount: <strong style="color: #1a1a2e;">{{ $suspensionInfo['currency'] }} {{ number_format($suspensionInfo['amount_due'] ?? 0, 2) }}</strong>
                </p>
                
                <div class="form-group">
                    <label class="form-label">Card Number</label>
                    <input type="text" class="form-input" placeholder="1234 5678 9012 3456">
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div class="form-group">
                        <label class="form-label">Expiry Date</label>
                        <input type="text" class="form-input" placeholder="MM/YY">
                    </div>
                    <div class="form-group">
                        <label class="form-label">CVV</label>
                        <input type="text" class="form-input" placeholder="123">
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Cardholder Name</label>
                    <input type="text" class="form-input" placeholder="Name on card">
                </div>
                
                <button class="btn btn-primary" onclick="processPayment('card')">
                    Pay {{ $suspensionInfo['currency'] }} {{ number_format($suspensionInfo['amount_due'] ?? 0, 2) }}
                </button>
                
                <p style="text-align: center; margin-top: 16px; color: #6b7280; font-size: 12px;">
                    🔒 Secured by industry-standard encryption
                </p>
            </div>
            
            <div id="airtelForm" style="display: none;">
                <h2 style="margin-bottom: 16px;">Pay with Airtel Money</h2>
                <p style="color: #6b7280; margin-bottom: 20px;">
                    Amount: <strong style="color: #1a1a2e;">{{ $suspensionInfo['currency'] }} {{ number_format($suspensionInfo['amount_due'] ?? 0, 2) }}</strong>
                </p>
                
                <div class="mpesa-instructions" style="background: #fef2f2; border-color: #fecaca;">
                    <p style="font-weight: 600; margin-bottom: 12px;">How to pay:</p>
                    <ol class="mpesa-steps">
                        <li data-step="1">Dial *334# on your Airtel line</li>
                        <li data-step="2">Select Send Money</li>
                        <li data-step="3">Select Pay Bill</li>
                        <li data-step="4">Enter Business Number</li>
                        <li data-step="5">Enter Amount: <strong>{{ number_format($suspensionInfo['amount_due'] ?? 0, 2) }}</strong></li>
                        <li data-step="6">Enter your PIN and confirm</li>
                    </ol>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Airtel Number (for confirmation)</label>
                    <input type="tel" id="airtelPhone" class="form-input" placeholder="2547XX XXX XXX">
                </div>
                
                <button class="btn btn-primary" onclick="processPayment('airtel')">
                    I've Completed the Payment
                </button>
            </div>
        </div>
    </div>
    
    <script>
        // CSRF Token for AJAX requests
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        // Toggle Contact Form
        function toggleContactForm() {
            const form = document.getElementById('contactForm');
            const btn = document.getElementById('contactBtn');
            
            if (form.classList.contains('active')) {
                form.classList.remove('active');
                btn.textContent = '✉️ Contact Administrator';
            } else {
                form.classList.add('active');
                btn.textContent = '✕ Hide Contact Form';
            }
        }
        
        // Show Alert
        function showAlert(message, type) {
            const alert = document.getElementById('alert');
            alert.textContent = message;
            alert.className = 'alert ' + type + ' show';
            
            setTimeout(() => {
                alert.classList.remove('show');
            }, 5000);
        }
        
        // Show Loading
        function showLoading(show) {
            const loading = document.getElementById('loading');
            if (show) {
                loading.classList.add('show');
            } else {
                loading.classList.remove('show');
            }
        }
        
        // Show Payment Modal
        function showPaymentModal(method) {
            document.getElementById('mpesaForm').style.display = 'none';
            document.getElementById('cardForm').style.display = 'none';
            document.getElementById('airtelForm').style.display = 'none';
            
            document.getElementById(method + 'Form').style.display = 'block';
            document.getElementById('paymentModal').classList.add('show');
        }
        
        // Close Payment Modal
        function closePaymentModal() {
            document.getElementById('paymentModal').classList.remove('show');
        }
        
        // Process Payment
        function processPayment(method) {
            showLoading(true);
            closePaymentModal();
            
            const data = {
                payment_method: method,
                amount: {{ $suspensionInfo['amount_due'] ?? 0 }},
                currency: '{{ $suspensionInfo['currency'] ?? 'KES' }}',
            };
            
            if (method === 'mpesa') {
                data.phone_number = document.getElementById('mpesaPhone').value;
            } else if (method === 'airtel') {
                data.phone_number = document.getElementById('airtelPhone').value;
            }
            
            fetch('{{ route('tenant.suspended.payment') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify(data),
            })
            .then(response => response.json())
            .then(result => {
                showLoading(false);
                
                if (result.success) {
                    showAlert(result.message, 'success');
                    
                    // If auto-reactivated, redirect after delay
                    if (result.reactivated) {
                        setTimeout(() => {
                            window.location.reload();
                        }, 3000);
                    }
                } else {
                    showAlert(result.message || 'Payment processing failed. Please try again.', 'error');
                }
            })
            .catch(error => {
                showLoading(false);
                showAlert('Network error. Please check your connection and try again.', 'error');
                console.error('Payment error:', error);
            });
        }
        
        // Handle Contact Form Submit
        document.getElementById('contactForm').addEventListener('submit', function(e) {
            e.preventDefault();
            showLoading(true);
            
            const formData = new FormData(this);
            
            fetch('{{ route('tenant.suspended.contact') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: formData,
            })
            .then(response => response.json())
            .then(result => {
                showLoading(false);
                
                if (result.success) {
                    showAlert(result.message, 'success');
                    document.getElementById('contactForm').reset();
                    toggleContactForm();
                } else {
                    showAlert(result.message || 'Failed to send message. Please try again.', 'error');
                }
            })
            .catch(error => {
                showLoading(false);
                showAlert('Network error. Please check your connection and try again.', 'error');
                console.error('Contact form error:', error);
            });
        });
        
        // Close modal on backdrop click
        document.getElementById('paymentModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closePaymentModal();
            }
        });
    </script>
</body>
</html>
