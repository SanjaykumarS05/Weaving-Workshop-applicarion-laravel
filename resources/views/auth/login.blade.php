<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>GST Billing Application - Sign In / Sign Up</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <script>window.APP_URL = "{{ url('/') }}";</script>
</head>
<body class="auth-wrapper">
    <div class="auth-container">
        <div class="auth-hero">
            <div style="display: flex; gap: 14px; margin-bottom: 24px;">
                <span class="material-symbols-outlined" style="font-size: 40px; background: rgba(255,255,255,0.2); padding: 10px; border-radius: 12px;">receipt_long</span>
                <span class="material-symbols-outlined" style="font-size: 40px; background: rgba(255,255,255,0.2); padding: 10px; border-radius: 12px;">insights</span>
                <span class="material-symbols-outlined" style="font-size: 40px; background: rgba(255,255,255,0.2); padding: 10px; border-radius: 12px;">verified</span>
            </div>
            <h1 data-i18n="login.title">GST Billing Application</h1>
            <p style="opacity: 0.92; line-height: 1.6; font-size: 1.05rem; margin-bottom: 28px;">
                Invoicing, payments, and GST tax compliance for your business — all in one powerful platform.
            </p>
            <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 16px;">
                <li style="display: flex; align-items: center; gap: 12px; font-weight: 600;">
                    <span class="material-symbols-outlined" style="color: #6ee7b7;">check_circle</span> GST-ready invoices & delivery sheets
                </li>
                <li style="display: flex; align-items: center; gap: 12px; font-weight: 600;">
                    <span class="material-symbols-outlined" style="color: #6ee7b7;">check_circle</span> Payments, customers and sales in one dashboard
                </li>
                <li style="display: flex; align-items: center; gap: 12px; font-weight: 600;">
                    <span class="material-symbols-outlined" style="color: #6ee7b7;">check_circle</span> Start with a free 14-day trial
                </li>
            </ul>
        </div>

        <div class="auth-panel">
            <div id="authHeaderTabs" class="auth-tabs {{ isset($require_otp) && $require_otp ? 'hidden' : '' }}">
                <button type="button" id="tabSignIn" class="auth-tab active">Sign In</button>
                <button type="button" id="tabSignUp" class="auth-tab">Sign Up</button>
            </div>

            <!-- Sign In Form -->
            <form id="signInForm" class="stack {{ isset($require_otp) && $require_otp ? 'hidden' : '' }}" action="{{ route('signin') }}" method="POST">
                @csrf
                <label>
                    <span>Email Address</span>
                    <input type="email" id="signInEmail" name="email" required placeholder="you@example.com" value="admin@example.com">
                </label>
                <label>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span>Password</span>
                        <button type="button" id="forgotPassBtn" class="btn ghost" style="padding: 0; font-size: 0.8rem; color: #6366f1; text-decoration: underline;">Forgot Password?</button>
                    </div>
                    <input type="password" id="signInPassword" name="password" required value="admin123">
                </label>
                <button class="btn primary" type="submit" style="width: 100%; margin-top: 6px; padding: 13px;">Sign In</button>
            </form>

            <!-- Sign Up Form -->
            <form id="signUpForm" class="stack hidden" action="{{ route('signup') }}" method="POST">
                @csrf
                <label>
                    <span>Business Name</span>
                    <input type="text" id="signUpBusinessName" name="business_name" required placeholder="My Business Pvt Ltd">
                </label>
                <label>
                    <span>Email Address</span>
                    <input type="email" id="signUpEmail" name="email" required placeholder="you@example.com">
                </label>
                <label>
                    <span>Password</span>
                    <input type="password" id="signUpPassword" name="password" required minlength="6">
                </label>
                <button class="btn primary" type="submit" style="width: 100%; margin-top: 6px; padding: 13px;">Create Account</button>
                <p style="text-align: center; font-size: 0.82rem; color: var(--text-muted); margin-top: 4px;">Free for 14 days. No credit card required.</p>
            </form>

            <!-- OTP Verification Form -->
            <form id="otpForm" class="stack {{ isset($require_otp) && $require_otp ? '' : 'hidden' }}">
                <div style="text-align: center; margin-bottom: 8px;">
                    <span class="material-symbols-outlined" style="font-size: 44px; color: #6366f1;">mark_email_unread</span>
                    <h3 style="margin: 8px 0 4px; font-family: 'Outfit', sans-serif;">Enter Verification Code</h3>
                    <p style="font-size: 0.88rem; color: var(--text-muted); margin: 0;">We sent a 6-digit OTP code to:</p>
                    <strong id="otpEmailDisplay" style="color: #6366f1; font-size: 0.95rem;">{{ $otp_email ?? '' }}</strong>
                    <p style="font-size: 0.78rem; color: #f59e0b; margin-top: 4px; font-weight: 600;">⏰ Code valid for 10 minutes</p>
                </div>

                <label>
                    <span>6-Digit Verification Code</span>
                    <input type="text" id="otpCode" required maxlength="6" inputmode="numeric" placeholder="123456" style="text-align: center; font-size: 1.3rem; letter-spacing: 0.25em; font-weight: 700;">
                </label>
                
                <button class="btn primary" type="submit" style="width: 100%; padding: 13px;">Verify & Sign In</button>
                <button id="resendOtpBtn" type="button" class="btn secondary" style="width: 100%;">Resend Code</button>
                <button id="backToSignInBtn" type="button" class="btn ghost" style="width: 100%;">Back to Sign In</button>
            </form>

            <!-- Forgot Password Form -->
            <form id="forgotPassForm" class="stack hidden">
                <div style="margin-bottom: 8px;">
                    <h3 style="margin: 0 0 6px; font-family: 'Outfit', sans-serif;">Reset Your Password</h3>
                    <p style="font-size: 0.88rem; color: var(--text-muted); margin: 0;">Enter your registered email address and we will send you a password reset link.</p>
                </div>

                <label>
                    <span>Email Address</span>
                    <input type="email" id="forgotEmail" required placeholder="you@example.com">
                </label>

                <button class="btn primary" type="submit" style="width: 100%; padding: 13px;">Send Reset Link</button>
                <button id="backFromForgotBtn" type="button" class="btn ghost" style="width: 100%;">Back to Sign In</button>
            </form>

            <div id="authAlert" class="hidden" style="margin-top: 16px; padding: 12px 16px; border-radius: 8px; font-size: 0.88rem; font-weight: 600;"></div>
        </div>
    </div>

    <script src="{{ asset('js/app.js') }}"></script>
    <script src="{{ asset('js/i18n.js') }}"></script>
    <script>
        const tabSignIn = document.getElementById('tabSignIn');
        const tabSignUp = document.getElementById('tabSignUp');
        const signInForm = document.getElementById('signInForm');
        const signUpForm = document.getElementById('signUpForm');
        const otpForm = document.getElementById('otpForm');
        const forgotPassForm = document.getElementById('forgotPassForm');
        const authHeaderTabs = document.getElementById('authHeaderTabs');
        const authAlert = document.getElementById('authAlert');

        function showAlert(msg, isSuccess = false) {
            authAlert.className = '';
            authAlert.style.background = isSuccess ? '#dcfce7' : '#fee2e2';
            authAlert.style.color = isSuccess ? '#15803d' : '#b91c1c';
            authAlert.style.borderLeft = isSuccess ? '4px solid #10b981' : '4px solid #ef4444';
            authAlert.textContent = msg;
        }

        tabSignIn.addEventListener('click', () => {
            tabSignIn.classList.add('active');
            tabSignUp.classList.remove('active');
            signInForm.classList.remove('hidden');
            signUpForm.classList.add('hidden');
            otpForm.classList.add('hidden');
            forgotPassForm.classList.add('hidden');
            authAlert.classList.add('hidden');
        });

        tabSignUp.addEventListener('click', () => {
            tabSignUp.classList.add('active');
            tabSignIn.classList.remove('active');
            signUpForm.classList.remove('hidden');
            signInForm.classList.add('hidden');
            otpForm.classList.add('hidden');
            forgotPassForm.classList.add('hidden');
            authAlert.classList.add('hidden');
        });

        // Sign Up Submission
        signUpForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const payload = {
                business_name: document.getElementById('signUpBusinessName').value,
                email: document.getElementById('signUpEmail').value,
                password: document.getElementById('signUpPassword').value
            };

            try {
                const res = await apiFetch('/api/signup', {
                    method: 'POST',
                    body: JSON.stringify(payload)
                });

                if (res.require_otp) {
                    document.getElementById('otpEmailDisplay').textContent = res.email;
                    authHeaderTabs.classList.add('hidden');
                    signUpForm.classList.add('hidden');
                    signInForm.classList.add('hidden');
                    otpForm.classList.remove('hidden');
                    showAlert(res.message, true);
                }
            } catch (err) {
                showAlert(err.message, false);
            }
        });

        // Sign In Submission
        signInForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const payload = {
                email: document.getElementById('signInEmail').value,
                password: document.getElementById('signInPassword').value
            };

            try {
                const res = await apiFetch('/api/signin', {
                    method: 'POST',
                    body: JSON.stringify(payload)
                });

                if (res.success) {
                    window.location.href = res.redirect;
                }
            } catch (err) {
                if (err.require_otp || (err.message && err.message.includes('not verified'))) {
                    document.getElementById('otpEmailDisplay').textContent = payload.email;
                    authHeaderTabs.classList.add('hidden');
                    signInForm.classList.add('hidden');
                    signUpForm.classList.add('hidden');
                    otpForm.classList.remove('hidden');
                    showAlert('Email not verified yet. Verification OTP sent to your email.', false);
                } else {
                    showAlert(err.message || 'Invalid credentials', false);
                }
            }
        });

        // OTP Verification Submission
        otpForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const payload = {
                email: document.getElementById('otpEmailDisplay').textContent,
                otp: document.getElementById('otpCode').value
            };

            try {
                const res = await apiFetch('/api/verify-otp', {
                    method: 'POST',
                    body: JSON.stringify(payload)
                });

                if (res.success) {
                    showAlert(res.message, true);
                    setTimeout(() => {
                        window.location.href = res.redirect;
                    }, 1000);
                }
            } catch (err) {
                showAlert(err.message, false);
            }
        });

        // Resend OTP
        document.getElementById('resendOtpBtn').addEventListener('click', async () => {
            const email = document.getElementById('otpEmailDisplay').textContent;
            try {
                const res = await apiFetch('/api/resend-otp', {
                    method: 'POST',
                    body: JSON.stringify({ email })
                });
                if (res.success) {
                    showAlert(res.message, true);
                }
            } catch (err) {
                showAlert(err.message, false);
            }
        });

        // Forgot Password Handlers
        document.getElementById('forgotPassBtn').addEventListener('click', () => {
            authHeaderTabs.classList.add('hidden');
            signInForm.classList.add('hidden');
            signUpForm.classList.add('hidden');
            otpForm.classList.add('hidden');
            forgotPassForm.classList.remove('hidden');
            authAlert.classList.add('hidden');
        });

        document.getElementById('backFromForgotBtn').addEventListener('click', () => {
            authHeaderTabs.classList.remove('hidden');
            forgotPassForm.classList.add('hidden');
            signInForm.classList.remove('hidden');
            authAlert.classList.add('hidden');
        });

        document.getElementById('backToSignInBtn').addEventListener('click', () => {
            authHeaderTabs.classList.remove('hidden');
            otpForm.classList.add('hidden');
            signInForm.classList.remove('hidden');
            authAlert.classList.add('hidden');
        });

        forgotPassForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const email = document.getElementById('forgotEmail').value;
            try {
                const res = await apiFetch('/api/forgot-password', {
                    method: 'POST',
                    body: JSON.stringify({ email })
                });
                if (res.success) {
                    showAlert(res.message, true);
                }
            } catch (err) {
                showAlert(err.message, false);
            }
        });
    </script>
</body>
</html>
