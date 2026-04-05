<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SuperAdmin') | Pisti Platform</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        :root {
            --sidebar-width: 260px;
            --primary-color: #4e73df;
            --secondary-color: #858796;
            --success-color: #1cc88a;
            --info-color: #36b9cc;
            --warning-color: #f6c23e;
            --danger-color: #e74a3b;
        }
        
        body {
            font-family: 'Nunito', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #f8f9fc;
        }
        
        .superadmin-sidebar {
            width: var(--sidebar-width);
            min-height: 100vh;
            background: linear-gradient(180deg, #4e73df 10%, #224abe 100%);
            position: fixed;
            left: 0;
            top: 0;
            z-index: 1000;
            transition: all 0.3s;
        }
        
        .superadmin-sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 1rem;
            border-radius: 0;
            transition: all 0.2s;
        }
        
        .superadmin-sidebar .nav-link:hover,
        .superadmin-sidebar .nav-link.active {
            color: #fff;
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .superadmin-sidebar .nav-link i {
            width: 24px;
            margin-right: 8px;
        }
        
        .superadmin-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            padding: 20px;
        }
        
        .topbar {
            height: 70px;
            background: #fff;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            margin: -20px -20px 20px -20px;
            padding: 0 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .brand-logo {
            color: #fff;
            font-size: 1.5rem;
            font-weight: 800;
            padding: 1.5rem 1rem;
            text-decoration: none;
            display: block;
        }
        
        .brand-logo:hover {
            color: #fff;
            text-decoration: none;
        }
        
        .stat-card {
            border-left: 4px solid;
            border-radius: 0.35rem;
            transition: transform 0.2s;
        }
        
        .stat-card:hover {
            transform: translateY(-3px);
        }
        
        .stat-card.primary { border-left-color: var(--primary-color); }
        .stat-card.success { border-left-color: var(--success-color); }
        .stat-card.info { border-left-color: var(--info-color); }
        .stat-card.warning { border-left-color: var(--warning-color); }
        
        .login-page {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .login-card {
            background: #fff;
            border-radius: 1rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 2.5rem;
            width: 100%;
            max-width: 420px;
        }
        
        @media (max-width: 768px) {
            .superadmin-sidebar {
                transform: translateX(-100%);
            }
            .superadmin-sidebar.show {
                transform: translateX(0);
            }
            .superadmin-content {
                margin-left: 0;
            }
        }
    </style>
    
    @yield('styles')
</head>
<body>
    @auth('superadmin')
    <!-- Sidebar -->
    <nav class="superadmin-sidebar">
        <a href="{{ route('superadmin.dashboard') }}" class="brand-logo">
            <i class="bi bi-shield-check"></i> PISTI
        </a>
        <hr class="text-white-50 my-0">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('superadmin.dashboard') ? 'active' : '' }}"
                   href="{{ route('superadmin.dashboard') }}">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('superadmin.tenants.*') ? 'active' : '' }}" 
                   href="{{ route('superadmin.tenants.index') }}">
                    <i class="bi bi-building"></i> Tenants
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('superadmin.plans.*') ? 'active' : '' }}" 
                   href="{{ route('superadmin.plans.index') }}">
                    <i class="bi bi-credit-card"></i> Plans
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs(['superadmin.modules.*', 'superadmin.plan-modules.*']) ? 'active' : '' }}" 
                   href="{{ route('superadmin.modules.index') }}">
                    <i class="bi bi-box-seam"></i> Module Marketplace
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('superadmin.module-onboarding.*') ? 'active' : '' }}" 
                   href="{{ route('superadmin.module-onboarding.index') }}">
                    <i class="bi bi-clipboard-check"></i> Module Onboarding
                    @php
                        $pendingCount = \App\Models\TenantModuleOnboarding::pending()->count();
                    @endphp
                    @if($pendingCount > 0)
                        <span class="badge bg-danger ms-1">{{ $pendingCount }}</span>
                    @endif
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('superadmin.billing.*') ? 'active' : '' }}" 
                   href="{{ route('superadmin.billing.index') }}">
                    <i class="bi bi-credit-card"></i> Billing & Payments
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('superadmin.admins.*') ? 'active' : '' }}" 
                   href="{{ route('superadmin.admins.index') }}">
                    <i class="bi bi-people"></i> SuperAdmins
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('superadmin.dns.*') ? 'active' : '' }}" 
                   href="{{ route('superadmin.dns.index') }}">
                    <i class="bi bi-globe"></i> DNS Management
                </a>
            </li>
            <li class="nav-item mt-auto">
                <hr class="text-white-50">
                <form action="{{ route('superadmin.logout') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="nav-link border-0 bg-transparent w-100 text-start">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </button>
                </form>
            </li>
        </ul>
    </nav>
    
    <!-- Main Content -->
    <main class="superadmin-content">
        <div class="topbar">
            <h4 class="mb-0">@yield('page-title', 'Dashboard')</h4>
            <div class="d-flex align-items-center">
                <span class="text-muted me-3">
                    <i class="bi bi-person-circle"></i>
                    {{ Auth::guard('superadmin')->user()->name }}
                </span>
            </div>
        </div>
        
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        
        @yield('content')
    </main>
    @else
        @yield('content')
    @endauth
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Dangerous Action Confirmation Modal -->
    <div class="modal fade" id="dangerActionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-danger">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="bi bi-shield-lock"></i> <span id="dangerModalTitle">Confirm Action</span></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle-fill"></i> <span id="dangerModalWarning">This is a destructive action.</span>
                    </div>
                    <p id="dangerModalMessage" class="mb-3">Please confirm your password to proceed.</p>
                    <form id="dangerActionForm" method="POST">
                        @csrf
                        <input type="hidden" name="_method" id="dangerFormMethod" value="DELETE">
                        <div class="mb-3">
                            <label for="confirmPassword" class="form-label fw-bold">Enter your password to confirm:</label>
                            <input type="password" class="form-control form-control-lg border-danger" 
                                   id="confirmPassword" name="confirm_password" 
                                   placeholder="Your superadmin password" required autofocus>
                            <div class="form-text text-danger">
                                <i class="bi bi-info-circle"></i> This action cannot be undone.
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="dangerConfirmBtn" onclick="submitDangerAction()">
                        <i class="bi bi-shield-check"></i> Confirm & Proceed
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Dangerous Action Confirmation System
        let dangerModal = null;
        
        document.addEventListener('DOMContentLoaded', function() {
            dangerModal = new bootstrap.Modal(document.getElementById('dangerActionModal'));
        });
        
        /**
         * Show confirmation modal for dangerous actions
         * @param {string} actionUrl - The form action URL
         * @param {string} title - Modal title
         * @param {string} message - Custom message
         * @param {string} method - HTTP method (DELETE, POST, etc)
         */
        function confirmDangerousAction(actionUrl, title, message, method = 'DELETE') {
            if (!dangerModal) {
                console.error('Danger modal not initialized');
                alert('Error: Confirmation system not ready. Please refresh the page.');
                return;
            }
            
            const form = document.getElementById('dangerActionForm');
            const methodInput = document.getElementById('dangerFormMethod');
            const titleEl = document.getElementById('dangerModalTitle');
            const messageEl = document.getElementById('dangerModalMessage');
            const passwordEl = document.getElementById('confirmPassword');
            
            if (!form || !methodInput || !titleEl || !messageEl || !passwordEl) {
                console.error('Modal elements not found');
                alert('Error: Confirmation modal not found. Please refresh the page.');
                return;
            }
            
            form.action = actionUrl;
            methodInput.value = method;
            titleEl.textContent = title || 'Confirm Action';
            messageEl.textContent = message || 'Please confirm your password to proceed.';
            passwordEl.value = '';
            
            dangerModal.show();
            
            // Focus password field after modal opens
            setTimeout(function() { passwordEl.focus(); }, 200);
        }
        
        function submitDangerAction() {
            const password = document.getElementById('confirmPassword').value.trim();
            if (!password) {
                alert('Please enter your password to confirm this action.');
                return;
            }
            document.getElementById('dangerActionForm').submit();
        }
        
        // Allow Enter key to submit
        document.addEventListener('DOMContentLoaded', function() {
            const passwordField = document.getElementById('confirmPassword');
            if (passwordField) {
                passwordField.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        submitDangerAction();
                    }
                });
            }
        });
    </script>
    
    @yield('scripts')
</body>
</html>
