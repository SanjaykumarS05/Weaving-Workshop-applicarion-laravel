<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Set New Password - GST Billing Application</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <script>window.APP_URL = "{{ url('/') }}";</script>
</head>
<body class="auth-wrapper" style="display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 24px;">
    <div style="background: #ffffff; border-radius: 20px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35); padding: 48px 40px; width: min(500px, 92%);">
        <div style="display: flex; align-items: center; gap: 14px; margin-bottom: 24px;">
            <span class="material-symbols-outlined" style="font-size: 36px; color: #6366f1; background: rgba(99, 102, 241, 0.1); padding: 10px; border-radius: 12px;">lock_reset</span>
            <div>
                <h2 style="font-family: 'Outfit', sans-serif; margin: 0; font-size: 1.6rem; font-weight: 800;">Set New Password</h2>
                <span style="color: var(--text-muted); font-size: 0.88rem;">Choose a strong new password for your account</span>
            </div>
        </div>

        <form id="resetPasswordForm" class="stack">
            <input type="hidden" id="resetToken" value="{{ $token }}">
            <label>
                <span>Email Address</span>
                <input type="email" id="resetEmail" required value="{{ $email }}" readonly style="background: #f8fafc; font-weight: 600;">
            </label>
            <label>
                <span>New Password</span>
                <input type="password" id="resetNewPassword" required minlength="6" placeholder="Enter new password (min. 6 chars)">
            </label>
            <label>
                <span>Confirm Password</span>
                <input type="password" id="resetConfirmPassword" required minlength="6" placeholder="Repeat new password">
            </label>
            <button class="btn primary" type="submit" style="width: 100%; margin-top: 10px; padding: 13px; font-size: 0.95rem;">Save New Password</button>
            <a href="{{ route('login') }}" class="btn ghost" style="text-align: center; text-decoration: none; margin-top: 4px;">Back to Sign In</a>
        </form>

        <div id="resetMsg" class="hidden" style="margin-top: 20px; padding: 14px 16px; border-radius: 10px; font-size: 0.9rem; font-weight: 600; text-align: center;"></div>
    </div>

    <script src="{{ asset('js/app.js') }}"></script>
    <script>
        document.getElementById('resetPasswordForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const pass = document.getElementById('resetNewPassword').value;
            const confirmPass = document.getElementById('resetConfirmPassword').value;
            const msgBox = document.getElementById('resetMsg');

            if (pass !== confirmPass) {
                msgBox.className = '';
                msgBox.style.background = '#fee2e2';
                msgBox.style.color = '#dc2626';
                msgBox.textContent = 'Passwords do not match.';
                return;
            }

            const payload = {
                email: document.getElementById('resetEmail').value,
                token: document.getElementById('resetToken').value,
                password: pass,
                password_confirmation: confirmPass
            };

            try {
                const res = await apiFetch('/api/reset-password', {
                    method: 'POST',
                    body: JSON.stringify(payload)
                });

                if (res.success) {
                    msgBox.className = '';
                    msgBox.style.background = '#dcfce7';
                    msgBox.style.color = '#15803d';
                    msgBox.textContent = res.message;
                    setTimeout(() => {
                        window.location.href = res.redirect;
                    }, 2000);
                }
            } catch (err) {
                msgBox.className = '';
                msgBox.style.background = '#fee2e2';
                msgBox.style.color = '#dc2626';
                msgBox.textContent = err.message;
            }
        });
    </script>
</body>
</html>
