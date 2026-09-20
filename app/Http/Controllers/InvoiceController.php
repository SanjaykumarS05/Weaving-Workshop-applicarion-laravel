<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $userId = Auth::id();
        $query = Invoice::where('user_id', $userId)->with(['items', 'customer']);

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_phone', 'like', "%{$search}%");
            });
        }

        if ($request->has('from_date') && !empty($request->from_date)) {
            $query->whereDate('invoice_date', '>=', $request->from_date);
        }

        if ($request->has('to_date') && !empty($request->to_date)) {
            $query->whereDate('invoice_date', '<=', $request->to_date);
        }

        if ($request->has('status') && !empty($request->status) && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $invoices = $query->orderBy('id', 'desc')->paginate(15);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'invoices' => $invoices]);
        }

        return view('invoices.index', compact('invoices'));
    }

    public function show($id)
    {
        $user = Auth::user();
        $invoice = Invoice::where('user_id', $user->id)->with(['items', 'customer', 'payments'])->findOrFail($id);
        return response()->json(['success' => true, 'invoice' => $invoice]);
    }

    public function print($id)
    {
        $user = Auth::user();
        $invoice = Invoice::where('user_id', $user->id)->with(['items', 'customer'])->findOrFail($id);
        $setting = Setting::where('user_id', $user->id)->first() ?? new Setting();
        return view('invoices.print', compact('invoice', 'setting'));
    }

    public function destroy($id)
    {
        $user = Auth::user();
        $invoice = Invoice::where('user_id', $user->id)->with(['items'])->findOrFail($id);

        DB::transaction(function() use ($invoice, $user) {
            // Restore product stock
            foreach ($invoice->items as $item) {
                if ($item->product_id) {
                    $product = Product::where('user_id', $user->id)->find($item->product_id);
                    if ($product) {
                        $product->increment('stock', $item->quantity);
                    }
                }
            }

            // Restore customer balance
            if ($invoice->customer_id) {
                $customer = Customer::where('user_id', $user->id)->find($invoice->customer_id);
                if ($customer) {
                    $dueAmount = max(0, $invoice->grand_total - $invoice->paid_amount);
                    $customer->decrement('balance', $dueAmount);
                }
            }

            // Delete associated payments
            $invoice->payments()->delete();
            $invoice->delete();
        });

        return response()->json(['success' => true, 'message' => 'Invoice deleted successfully.']);
    }
}
