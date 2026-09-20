<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use Illuminate\Support\Facades\Auth;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $userId = Auth::id();
        $query = Customer::where('user_id', $userId);

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('gstin', 'like', "%{$search}%");
            });
        }

        $customers = $query->orderBy('name', 'asc')->get();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'customers' => $customers]);
        }

        return view('customers.index', compact('customers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string',
            'email' => 'nullable|email',
            'gstin' => 'nullable|string',
            'address' => 'nullable|string',
            'state' => 'nullable|string',
            'state_code' => 'nullable|string',
            'balance' => 'nullable|numeric',
        ]);

        $validated['user_id'] = Auth::id();
        $customer = Customer::create($validated);

        return response()->json(['success' => true, 'customer' => $customer]);
    }

    public function update(Request $request, $id)
    {
        $customer = Customer::where('user_id', Auth::id())->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string',
            'email' => 'nullable|email',
            'gstin' => 'nullable|string',
            'address' => 'nullable|string',
            'state' => 'nullable|string',
            'state_code' => 'nullable|string',
            'balance' => 'nullable|numeric',
        ]);

        $customer->update($validated);

        return response()->json(['success' => true, 'customer' => $customer]);
    }

    public function destroy($id)
    {
        $customer = Customer::where('user_id', Auth::id())->findOrFail($id);
        $customer->delete();
        return response()->json(['success' => true, 'message' => 'Customer deleted successfully.']);
    }
}
