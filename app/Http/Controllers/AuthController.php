<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Setting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class AuthController extends Controller
{
    public function showAuth(Request $request)
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
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

        $user = User::create([
            'name' => $request->business_name,
            'email' => strtolower(trim($request->email)),
            'business_name' => trim($request->business_name),
            'password' => Hash::make($request->password),
            'role' => 'admin',
            'trial_started_at' => Carbon::now(),
            'plan' => 'trial',
            'trial_days' => 14,
            'active' => true,
        ]);

        // Create default settings for user
        Setting::create([
            'user_id' => $user->id,
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

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'user' => $user,
                'trialStatus' => $user->trial_status,
                'redirect' => route('dashboard')
            ]);
        }

        return redirect()->route('dashboard');
    }

    public function signIn(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (Auth::attempt(['email' => strtolower(trim($credentials['email'])), 'password' => $credentials['password']])) {
            $request->session()->regenerate();
            $user = Auth::user();

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'user' => $user,
                    'trialStatus' => $user->trial_status,
                    'redirect' => route('dashboard')
                ]);
            }

            return redirect()->intended(route('dashboard'));
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid email or password.'
            ], 422);
        }

        return back()->withErrors(['email' => 'Invalid credentials.']);
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
        if (!$currentUser || $currentUser->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'username' => 'required|string',
        ]);

        $user = User::create([
            'name' => $request->username,
            'email' => strtolower(trim($request->email)),
            'business_name' => $currentUser->business_name,
            'password' => Hash::make($request->password),
            'role' => 'user',
            'trial_started_at' => $currentUser->trial_started_at,
            'plan' => $currentUser->plan,
            'trial_days' => $currentUser->trial_days,
            'active' => true,
        ]);

        return response()->json(['success' => true, 'user' => $user]);
    }
}
