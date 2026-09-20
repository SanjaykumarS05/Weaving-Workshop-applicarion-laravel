<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class SettingController extends Controller
{
    public function index(Request $request)
    {
        $userId = Auth::id();
        $setting = Setting::firstOrCreate(['user_id' => $userId], [
            'profile_name' => Auth::user()->business_name ?? Auth::user()->name,
            'profile_email' => Auth::user()->email,
            'profile_declaration' => 'We declare that this invoice shows the actual price of the goods described and that all particulars are true and correct.',
            'profile_signatory' => 'Authorised Signatory',
            'default_tax_rate' => 12.00,
            'invoice_prefix' => 'GST',
            'invoice_start_value' => 1,
            'composition_valid_days' => 30,
        ]);

        $isOwner = (Auth::id() === 1);
        $teamUsers = $isOwner 
            ? User::where('business_name', Auth::user()->business_name)->orderBy('id', 'asc')->get() 
            : collect([]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'isOwner' => $isOwner,
                'setting' => $setting,
                'teamUsers' => $teamUsers
            ]);
        }

        return view('settings.index', compact('setting', 'teamUsers', 'isOwner'));
    }

    public function update(Request $request)
    {
        $userId = Auth::id();
        $setting = Setting::where('user_id', $userId)->firstOrFail();

        $validated = $request->validate([
            'profile_name' => 'nullable|string',
            'profile_email' => 'nullable|email',
            'profile_gstin' => 'nullable|string',
            'profile_phone' => 'nullable|string',
            'profile_state' => 'nullable|string',
            'profile_state_code' => 'nullable|string',
            'profile_address' => 'nullable|string',
            'profile_bank_name' => 'nullable|string',
            'profile_account_no' => 'nullable|string',
            'profile_branch_name' => 'nullable|string',
            'profile_ifsc' => 'nullable|string',
            'profile_declaration' => 'nullable|string',
            'profile_signatory' => 'nullable|string',
            'profile_signature_data' => 'nullable|string',
            'default_tax_rate' => 'nullable|numeric|min:0',
            'invoice_prefix' => 'nullable|string',
            'invoice_start_value' => 'nullable|integer|min:1',
            'composition_valid_days' => 'nullable|integer|min:1',
        ]);

        $setting->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Settings saved successfully.',
            'setting' => $setting
        ]);
    }
}
