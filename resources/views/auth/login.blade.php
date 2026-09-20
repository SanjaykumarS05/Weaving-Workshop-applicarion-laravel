<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>GST Billing Application - Authentication</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <script>window.APP_URL = "{{ url('/') }}";</script>
</head>
<body class="auth-wrapper">
    <div class="auth-container">
        <div class="auth-hero">
            <div style="display: flex; gap: 12px; margin-bottom: 20px;">
                <span class="material-symbols-outlined" style="font-size: 36px;">receipt_long</span>
                <span class="material-symbols-outlined" style="font-size: 36px;">insights</span>
                <span class="material-symbols-outlined" style="font-size: 36px;">verified</span>
            </div>
            <h1 data-i18n="login.title">GST Billing Application</h1>
            <p style="opacity: 0.9; line-height: 1.6; margin-bottom: 24px;">Invoicing, payments and GST compliance for your business — in one place.</p>
            <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 12px;">
                <li style="display: flex; align-items: center; gap: 8px;">
                    <span class="material-symbols-outlined">check_circle</span> GST-ready invoices & delivery sheets
                </li>
                <li style="display: flex; align-items: center; gap: 8px;">
                    <span class="material-symbols-outlined">check_circle</span> Payments, customers and sales in one dashboard
                </li>
                <li style="display: flex; align-items: center; gap: 8px;">
                    <span class="material-symbols-outlined">check_circle</span> Start with a free 14-day trial
                </li>
            </ul>
        </div>

        <div class="auth-panel">
            <div class="auth-tabs">
                <button type="button" id="tabSignIn" class="auth-tab active">Sign In</button>
                <button type="button" id="tabSignUp" class="auth-tab">Sign Up</button>
            </div>

            <!-- Sign In Form -->
            <form id="signInForm" class="stack" action="{{ route('signin') }}" method="POST">
                @csrf
                <label>
                    <span>Email Address</span>
                    <input type="email" name="email" required placeholder="you@example.com">
                </label>
                <label>
                    <span>Password</span>
                    <input type="password" name="password" required>
                </label>
                <button class="btn primary" type="submit" style="width: 100%; margin-top: 8px;">Sign In</button>
            </form>

            <!-- Sign Up Form -->
            <form id="signUpForm" class="stack hidden" action="{{ route('signup') }}" method="POST">
                @csrf
                <label>
                    <span>Business Name</span>
                    <input type="text" name="business_name" required placeholder="My Business Pvt Ltd">
                </label>
                <label>
                    <span>Email Address</span>
                    <input type="email" name="email" required placeholder="you@example.com">
                </label>
                <label>
                    <span>Password</span>
                    <input type="password" name="password" required minlength="6">
                </label>
                <button class="btn primary" type="submit" style="width: 100%; margin-top: 8px;">Create Account</button>
                <p style="text-align: center; font-size: 0.82rem; color: var(--muted); margin-top: 4px;">Free for 14 days. No credit card required.</p>
            </form>

            @if($errors->any())
                <div style="margin-top: 16px; padding: 12px; background: #fee2e2; border-radius: 8px; color: #dc2626; font-size: 0.85rem;">
                    {{ $errors->first() }}
                </div>
            @endif
        </div>
    </div>

    <script src="{{ asset('js/i18n.js') }}"></script>
    <script>
        document.getElementById('tabSignIn').addEventListener('click', () => {
            document.getElementById('tabSignIn').classList.add('active');
            document.getElementById('tabSignUp').classList.remove('active');
            document.getElementById('signInForm').classList.remove('hidden');
            document.getElementById('signUpForm').classList.add('hidden');
        });
        document.getElementById('tabSignUp').addEventListener('click', () => {
            document.getElementById('tabSignUp').classList.add('active');
            document.getElementById('tabSignIn').classList.remove('active');
            document.getElementById('signUpForm').classList.remove('hidden');
            document.getElementById('signInForm').classList.add('hidden');
        });
    </script>
</body>
</html>
