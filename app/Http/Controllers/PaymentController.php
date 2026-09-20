<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\Invoice;
use App\Models\Customer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $userId = Auth::id();
        $query = Payment::where('user_id', $userId)->with(['customer', 'invoice']);

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('payment_number', 'like', "%{$search}%")
                  ->orWhere('reference_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', function($cq) use ($search) {
                      $cq->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('invoice', function($iq) use ($search) {
                      $iq->where('invoice_number', 'like', "%{$search}%");
                  });
            });
        }

        $payments = $query->orderBy('id', 'desc')->get();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'payments' => $payments]);
        }

        return view('payments.index', compact('payments'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $validated = $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'invoice_id' => 'nullable|exists:invoices,id',
            'payment_date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'payment_mode' => 'required|string',
            'reference_number' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        return DB::transaction(function() use ($user, $validated) {
            $paymentNum = 'PAY-' . rand(10000, 99999);
            $payment = Payment::create([
                'user_id' => $user->id,
                'customer_id' => $validated['customer_id'] ?? null,
                'invoice_id' => $validated['invoice_id'] ?? null,
                'payment_number' => $paymentNum,
                'payment_date' => $validated['payment_date'],
                'amount' => $validated['amount'],
                'payment_mode' => $validated['payment_mode'],
                'reference_number' => $validated['reference_number'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);

            // Update invoice status if linked
            if (!empty($validated['invoice_id'])) {
                $invoice = Invoice::where('user_id', $user->id)->find($validated['invoice_id']);
                if ($invoice) {
                    $newPaid = $invoice->paid_amount + $validated['amount'];
                    $status = ($newPaid >= $invoice->grand_total) ? 'paid' : 'partial';
                    $invoice->update([
                        'paid_amount' => $newPaid,
                        'status' => $status
                    ]);
                }
            }

            // Update customer balance if linked
            if (!empty($validated['customer_id'])) {
                $customer = Customer::where('user_id', $user->id)->find($validated['customer_id']);
                if ($customer) {
                    $customer->decrement('balance', $validated['amount']);
                }
            }

            return response()->json([
                'success' => true,
                'payment' => $payment->load(['customer', 'invoice'])
            ]);
        });
    }

    public function destroy($id)
    {
        $user = Auth::user();
        $payment = Payment::where('user_id', $user->id)->findOrFail($id);

        DB::transaction(function() use ($payment, $user) {
            if ($payment->invoice_id) {
                $invoice = Invoice::where('user_id', $user->id)->find($payment->invoice_id);
                if ($invoice) {
                    $newPaid = max(0, $invoice->paid_amount - $payment->amount);
                    $status = ($newPaid >= $invoice->grand_total && $invoice->grand_total > 0) ? 'paid' : (($newPaid > 0) ? 'partial' : 'unpaid');
                    $invoice->update(['paid_amount' => $newPaid, 'status' => $status]);
                }
            }
            if ($payment->customer_id) {
                $customer = Customer::where('user_id', $user->id)->find($payment->customer_id);
                if ($customer) {
                    $customer->increment('balance', $payment->amount);
                }
            }
            $payment->delete();
        });

        return response()->json(['success' => true, 'message' => 'Payment deleted successfully.']);
    }
}
