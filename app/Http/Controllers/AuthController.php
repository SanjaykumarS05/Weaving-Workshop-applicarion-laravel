<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Setting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AuthController extends Controller
{
    public function showAuth(Request $request)
    {
        if (Auth::check()) {
            return redirect()->to(Auth::user()->getFirstAvailableNavUrl());
        }
        return view('auth.login');
    }

    public function signUp(Request $request)
    {
        $request->validate([
            'business_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
        ]);

        $otp = sprintf("%06d", mt_rand(100000, 999999));
        $expiresAt = Carbon::now()->addMinutes(10);

        $user = User::create([
            'name' => $request->business_name,
            'email' => strtolower(trim($request->email)),
            'business_name' => trim($request->business_name),
            'password' => Hash::make($request->password),
            'role' => 'admin',
            'trial_started_at' => Carbon::now(),
            'plan' => 'unlimited',
            'trial_days' => 0,
            'active' => true,
            'email_otp' => $otp,
            'otp_expires_at' => $expiresAt,
            'is_verified' => false,
        ]);

        // Send OTP via Email
        $this->sendOtpEmail($user->email, $otp);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'require_otp' => true,
                'email' => $user->email,
                'message' => 'Verification code sent to your email. Valid for 10 minutes.',
                'debug_otp' => config('app.debug') ? $otp : null
            ]);
        }

        return view('auth.login', [
            'require_otp' => true,
            'otp_email' => $user->email,
            'otp_message' => "Verification code sent to {$user->email}. Valid for 10 minutes.",
            'debug_otp' => config('app.debug') ? $otp : null
        ]);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|string|size:6',
        ]);

        $user = User::where('email', strtolower(trim($request->email)))->first();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found.'], 404);
        }

        if ($user->is_verified) {
            Auth::login($user);
            return response()->json(['success' => true, 'message' => 'Account already verified.', 'redirect' => $user->getFirstAvailableNavUrl()]);
        }

        if ($user->email_otp !== trim($request->otp)) {
            return response()->json(['success' => false, 'message' => 'Invalid verification code.'], 422);
        }

        if (Carbon::now()->greaterThan($user->otp_expires_at)) {
            return response()->json(['success' => false, 'message' => 'Verification code has expired. Please request a new code.'], 422);
        }

        // Mark verified
        $user->update([
            'is_verified' => true,
            'email_otp' => null,
            'otp_expires_at' => null,
            'email_verified_at' => Carbon::now()
        ]);

        // Create default settings if not exists
        Setting::firstOrCreate(['user_id' => $user->id], [
            'profile_name' => $user->business_name,
            'profile_email' => $user->email,
            'profile_declaration' => 'We declare that this invoice shows the actual price of the goods described and that all particulars are true and correct.',
            'profile_signatory' => 'Authorised Signatory',
            'default_tax_rate' => 12.00,
            'invoice_prefix' => 'GST',
            'invoice_start_value' => 1,
            'composition_valid_days' => 30,
        ]);

        Auth::login($user);

        return response()->json([
            'success' => true,
            'message' => 'Email verified successfully!',
            'redirect' => $user->getFirstAvailableNavUrl()
        ]);
    }

    public function resendOtp(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        $user = User::where('email', strtolower(trim($request->email)))->first();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found.'], 404);
        }

        $otp = sprintf("%06d", mt_rand(100000, 999999));
        $user->update([
            'email_otp' => $otp,
            'otp_expires_at' => Carbon::now()->addMinutes(10)
        ]);

        $this->sendOtpEmail($user->email, $otp);

        return response()->json([
            'success' => true,
            'message' => 'New verification code sent to your email. Valid for 10 minutes.',
            'debug_otp' => config('app.debug') ? $otp : null
        ]);
    }

    public function signIn(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', strtolower(trim($credentials['email'])))->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Invalid email or password.'], 422);
            }
            return back()->withInput()->withErrors(['email' => 'Invalid email or password.']);
        }

        if (!$user->active) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Your account has been deactivated by system administrator.'], 403);
            }
            return back()->withInput()->withErrors(['email' => 'Your account has been deactivated by system administrator.']);
        }

        if (!$user->is_verified) {
            // Generate OTP for unverified user
            $otp = sprintf("%06d", mt_rand(100000, 999999));
            $user->update([
                'email_otp' => $otp,
                'otp_expires_at' => Carbon::now()->addMinutes(10)
            ]);
            $this->sendOtpEmail($user->email, $otp);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'require_otp' => true,
                    'email' => $user->email,
                    'message' => 'Your email is not verified yet. Verification code sent to email.'
                ], 403);
            }

            return view('auth.login', [
                'require_otp' => true,
                'otp_email' => $user->email,
                'otp_message' => "Your email is not verified. Verification code sent to {$user->email}.",
                'debug_otp' => config('app.debug') ? $otp : null
            ]);
        }

        Auth::login($user);
        $request->session()->regenerate();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'user' => $user,
                'trialStatus' => $user->trial_status,
                'redirect' => $user->getFirstAvailableNavUrl()
            ]);
        }

        return redirect()->intended($user->getFirstAvailableNavUrl());
    }

    // Forgot Password Handling
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        $user = User::where('email', strtolower(trim($request->email)))->first();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'We could not find a user with that email address.'], 404);
        }

        $token = Str::random(64);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            ['token' => Hash::make($token), 'created_at' => Carbon::now()]
        );

        $resetUrl = route('password.reset', ['token' => $token, 'email' => $user->email]);

        try {
            Mail::raw("Hello,\n\nYou requested a password reset for your GST Billing Application account.\nClick the link below to set a new password:\n\n{$resetUrl}\n\nThis link will expire shortly.\n\nIf you did not request this, please ignore this email.", function ($message) use ($user) {
                $message->to($user->email)->subject('Reset Password Notification - GST Billing');
            });
        } catch (\Exception $e) {
            // Log fallback
            \Log::info("Password reset link for {$user->email}: {$resetUrl}");
        }

        return response()->json([
            'success' => true,
            'message' => 'Password reset link sent to your email address.',
            'reset_url' => config('app.debug') ? $resetUrl : null
        ]);
    }

    public function showResetForm(Request $request)
    {
        return view('auth.reset-password', [
            'token' => $request->token,
            'email' => $request->email
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $record = DB::table('password_reset_tokens')->where('email', strtolower(trim($request->email)))->first();

        if (!$record || !Hash::check($request->token, $record->token)) {
            return response()->json(['success' => false, 'message' => 'This password reset token is invalid or expired.'], 422);
        }

        $user = User::where('email', strtolower(trim($request->email)))->first();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found.'], 404);
        }

        $user->update([
            'password' => Hash::make($request->password)
        ]);

        DB::table('password_reset_tokens')->where('email', $user->email)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Password has been reset successfully! You can now sign in with your new password.',
            'redirect' => route('login')
        ]);
    }

    private function sendOtpEmail($email, $otp)
    {
        try {
            Mail::raw("Your GST Billing Application verification code is: {$otp}\n\nThis code will expire in 10 minutes. Do not share this code with anyone.", function ($message) use ($email) {
                $message->to($email)->subject('Email Verification OTP Code - GST Billing');
            });
        } catch (\Exception $e) {
            \Log::info("OTP Code for {$email}: {$otp}");
        }
    }

    public function signOut(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'redirect' => route('login')]);
        }

        return redirect()->route('login');
    }

    public function me(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'user' => null], 401);
        }
        return response()->json([
            'success' => true,
            'user' => $user,
            'trialStatus' => $user->trial_status
        ]);
    }

    public function createTeamUser(Request $request)
    {
        $currentUser = Auth::user();
        if (!$currentUser || $currentUser->id !== 1) {
            return response()->json(['success' => false, 'message' => 'Unauthorized. Only system owner (User ID 1) can manage user accounts.'], 403);
        }

        $request->validate([
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'username' => 'required|string',
            'business_name' => 'nullable|string',
        ]);

        $bizName = trim($request->business_name ?? $request->username);

        $user = User::create([
            'name' => trim($request->username),
            'email' => strtolower(trim($request->email)),
            'business_name' => $bizName,
            'password' => Hash::make($request->password),
            'role' => 'admin',
            'trial_started_at' => Carbon::now(),
            'plan' => 'unlimited',
            'trial_days' => 0,
            'active' => true,
            'is_verified' => true,
        ]);

        Setting::firstOrCreate(['user_id' => $user->id], [
            'profile_name' => $user->business_name,
            'profile_email' => $user->email,
            'profile_declaration' => 'We declare that this invoice shows the actual price of the goods described and that all particulars are true and correct.',
            'profile_signatory' => 'Authorised Signatory',
            'default_tax_rate' => 12.00,
            'invoice_prefix' => 'GST',
            'invoice_start_value' => 1,
            'composition_valid_days' => 30,
        ]);

        return response()->json(['success' => true, 'user' => $user]);
    }

    public function updateTeamUser(Request $request, $id)
    {
        $currentUser = Auth::user();
        if (!$currentUser || $currentUser->id !== 1) {
            return response()->json(['success' => false, 'message' => 'Unauthorized. Only system owner (User ID 1) can edit user accounts.'], 403);
        }

        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email,' . $id,
            'active' => 'required|boolean',
            'password' => 'nullable|string|min:6',
            'business_name' => 'nullable|string',
            'allowed_navs' => 'nullable|array',
        ]);

        $data = [
            'name' => trim($request->name),
            'email' => strtolower(trim($request->email)),
            'active' => (bool)$request->active,
        ];

        if ($request->has('allowed_navs')) {
            $data['allowed_navs'] = $request->allowed_navs;
        }

        if (!empty($request->business_name)) {
            $data['business_name'] = trim($request->business_name);
        }

        if (!empty($request->password)) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return response()->json(['success' => true, 'message' => 'User account updated successfully.', 'user' => $user]);
    }

    public function deleteTeamUser($id)
    {
        $currentUser = Auth::user();
        if (!$currentUser || $currentUser->id !== 1) {
            return response()->json(['success' => false, 'message' => 'Unauthorized. Only system owner (User ID 1) can delete user accounts.'], 403);
        }

        $user = User::findOrFail($id);
        if ($user->id === $currentUser->id || $user->id === 1) {
            return response()->json(['success' => false, 'message' => 'Owner account cannot be deleted.'], 422);
        }

        $user->delete();
        return response()->json(['success' => true, 'message' => 'User account deleted successfully.']);
    }
}
