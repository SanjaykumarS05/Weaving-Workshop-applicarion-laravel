<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Payment;
use App\Models\Setting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BillingController extends Controller
{
    public function create()
    {
        return view('billing.create');
    }

    public function getNextInvoiceNumber()
    {
        $user = Auth::user();
        $setting = Setting::where('user_id', $user->id)->first();
        $prefix = $setting->invoice_prefix ?? 'GST';
        $startVal = $setting->invoice_start_value ?? 1;

        $lastInvoice = Invoice::where('user_id', $user->id)
            ->where('invoice_number', 'like', "{$prefix}%")
            ->orderBy('id', 'desc')
            ->first();

        if ($lastInvoice) {
            $numPart = preg_replace('/[^0-9]/', '', substr($lastInvoice->invoice_number, strlen($prefix)));
            $nextNum = max((int)$numPart + 1, $startVal);
        } else {
            $nextNum = $startVal;
        }

        $formattedNumber = sprintf("%s-%04d", $prefix, $nextNum);
        return response()->json(['success' => true, 'invoice_number' => $formattedNumber]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $setting = Setting::where('user_id', $user->id)->first();
        $profileStateCode = $setting->profile_state_code ?? '';

        $validated = $request->validate([
            'customer_name' => 'required|string',
            'customer_phone' => 'nullable|string',
            'customer_email' => 'nullable|email',
            'customer_gstin' => 'nullable|string',
            'customer_address' => 'nullable|string',
            'customer_state' => 'nullable|string',
            'customer_state_code' => 'nullable|string',
            'supply_type' => 'required|string', // intra, inter, none
            'invoice_date' => 'required|date',
            'e_way_bill_no' => 'nullable|string',
            'delivery_note' => 'nullable|string',
            'reference_no_date' => 'nullable|string',
            'other_references' => 'nullable|string',
            'buyers_order_no' => 'nullable|string',
            'buyers_order_date' => 'nullable|date',
            'dispatch_doc_no' => 'nullable|string',
            'delivery_note_date' => 'nullable|date',
            'dispatched_through' => 'nullable|string',
            'destination' => 'nullable|string',
            'terms_of_delivery' => 'nullable|string',
            'discount_amount' => 'nullable|numeric|min:0',
            'paid_amount' => 'nullable|numeric|min:0',
            'payment_mode' => 'nullable|string',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_name' => 'required|string',
            'items.*.hsn_code' => 'nullable|string',
            'items.*.unit' => 'nullable|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.rate' => 'required|numeric|min:0',
            'items.*.tax_rate' => 'required|numeric|min:0',
            'items.*.product_id' => 'nullable|integer',
        ]);

        return DB::transaction(function() use ($user, $setting, $validated, $profileStateCode) {
            // Check or create customer
            $customer = null;
            if (!empty($validated['customer_name'])) {
                $customer = Customer::where('user_id', $user->id)
                    ->where('name', trim($validated['customer_name']))
                    ->first();
                if (!$customer) {
                    $customer = Customer::create([
                        'user_id' => $user->id,
                        'name' => trim($validated['customer_name']),
                        'phone' => $validated['customer_phone'] ?? null,
                        'email' => $validated['customer_email'] ?? null,
                        'gstin' => $validated['customer_gstin'] ?? null,
                        'address' => $validated['customer_address'] ?? null,
                        'state' => $validated['customer_state'] ?? null,
                        'state_code' => $validated['customer_state_code'] ?? null,
                    ]);
                } else {
                    // Update details
                    $customer->update([
                        'phone' => $validated['customer_phone'] ?? $customer->phone,
                        'email' => $validated['customer_email'] ?? $customer->email,
                        'gstin' => $validated['customer_gstin'] ?? $customer->gstin,
                        'address' => $validated['customer_address'] ?? $customer->address,
                        'state' => $validated['customer_state'] ?? $customer->state,
                        'state_code' => $validated['customer_state_code'] ?? $customer->state_code,
                    ]);
                }
            }

            // Generate invoice number
            $prefix = $setting->invoice_prefix ?? 'GST';
            $startVal = $setting->invoice_start_value ?? 1;

            $lastInvoice = Invoice::where('user_id', $user->id)
                ->where('invoice_number', 'like', "{$prefix}%")
                ->orderBy('id', 'desc')
                ->first();

            if ($lastInvoice) {
                $numPart = preg_replace('/[^0-9]/', '', substr($lastInvoice->invoice_number, strlen($prefix)));
                $nextNum = max((int)$numPart + 1, $startVal);
            } else {
                $nextNum = $startVal;
            }
            $invoiceNumber = sprintf("%s-%04d", $prefix, $nextNum);

            // Calculate Item Totals & Tax
            $supplyType = $validated['supply_type']; // intra, inter, none
            $subtotal = 0;
            $taxableTotal = 0;
            $totalCgst = 0;
            $totalSgst = 0;
            $totalIgst = 0;

            $itemsData = [];

            foreach ($validated['items'] as $item) {
                $qty = floatval($item['quantity']);
                $rate = floatval($item['rate']);
                $taxRate = floatval($item['tax_rate']);
                $lineTaxable = $qty * $rate;

                $cgst = 0;
                $sgst = 0;
                $igst = 0;

                if ($supplyType === 'intra') {
                    $halfRate = $taxRate / 2;
                    $cgst = round($lineTaxable * ($halfRate / 100), 2);
                    $sgst = round($lineTaxable * ($halfRate / 100), 2);
                } elseif ($supplyType === 'inter') {
                    $igst = round($lineTaxable * ($taxRate / 100), 2);
                }

                $lineTotal = $lineTaxable + $cgst + $sgst + $igst;

                $subtotal += $lineTaxable;
                $taxableTotal += $lineTaxable;
                $totalCgst += $cgst;
                $totalSgst += $sgst;
                $totalIgst += $igst;

                $itemsData[] = [
                    'product_id' => $item['product_id'] ?? null,
                    'product_name' => $item['product_name'],
                    'hsn_code' => $item['hsn_code'] ?? '',
                    'unit' => $item['unit'] ?? 'Kgs',
                    'quantity' => $qty,
                    'rate' => $rate,
                    'tax_rate' => $taxRate,
                    'taxable_amount' => $lineTaxable,
                    'cgst_amount' => $cgst,
                    'sgst_amount' => $sgst,
                    'igst_amount' => $igst,
                    'total_amount' => $lineTotal,
                ];

                // Decrement stock if product exists
                if (!empty($item['product_id'])) {
                    $product = Product::where('user_id', $user->id)->find($item['product_id']);
                    if ($product) {
                        $product->decrement('stock', $qty);
                    }
                }
            }

            $discount = floatval($validated['discount_amount'] ?? 0);
            $rawGrandTotal = max(0, ($subtotal + $totalCgst + $totalSgst + $totalIgst) - $discount);
            $roundedGrandTotal = round($rawGrandTotal);
            $roundOff = round($roundedGrandTotal - $rawGrandTotal, 2);

            $paidAmount = floatval($validated['paid_amount'] ?? 0);
            $status = 'unpaid';
            if ($paidAmount >= $roundedGrandTotal && $roundedGrandTotal > 0) {
                $status = 'paid';
            } elseif ($paidAmount > 0) {
                $status = 'partial';
            }

            $invoice = Invoice::create([
                'user_id' => $user->id,
                'invoice_number' => $invoiceNumber,
                'invoice_date' => $validated['invoice_date'],
                'customer_id' => $customer ? $customer->id : null,
                'customer_name' => $validated['customer_name'],
                'customer_phone' => $validated['customer_phone'] ?? null,
                'customer_address' => $validated['customer_address'] ?? null,
                'customer_gstin' => $validated['customer_gstin'] ?? null,
                'customer_state' => $validated['customer_state'] ?? null,
                'customer_state_code' => $validated['customer_state_code'] ?? null,
                'supply_type' => $supplyType,
                'e_way_bill_no' => $validated['e_way_bill_no'] ?? null,
                'delivery_note' => $validated['delivery_note'] ?? null,
                'reference_no_date' => $validated['reference_no_date'] ?? null,
                'other_references' => $validated['other_references'] ?? null,
                'buyers_order_no' => $validated['buyers_order_no'] ?? null,
                'buyers_order_date' => $validated['buyers_order_date'] ?? null,
                'dispatch_doc_no' => $validated['dispatch_doc_no'] ?? null,
                'delivery_note_date' => $validated['delivery_note_date'] ?? null,
                'dispatched_through' => $validated['dispatched_through'] ?? null,
                'destination' => $validated['destination'] ?? null,
                'terms_of_delivery' => $validated['terms_of_delivery'] ?? null,
                'subtotal' => $subtotal,
                'taxable_amount' => $taxableTotal,
                'cgst_amount' => $totalCgst,
                'sgst_amount' => $totalSgst,
                'igst_amount' => $totalIgst,
                'discount_amount' => $discount,
                'round_off' => $roundOff,
                'grand_total' => $roundedGrandTotal,
                'paid_amount' => $paidAmount,
                'status' => $status,
                'payment_mode' => $validated['payment_mode'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($itemsData as $it) {
                $invoice->items()->create($it);
            }

            // Record payment if paid amount > 0
            if ($paidAmount > 0) {
                Payment::create([
                    'user_id' => $user->id,
                    'customer_id' => $customer ? $customer->id : null,
                    'invoice_id' => $invoice->id,
                    'payment_number' => 'PAY-' . rand(10000, 99999),
                    'payment_date' => $validated['invoice_date'],
                    'amount' => $paidAmount,
                    'payment_mode' => $validated['payment_mode'] ?? 'cash',
                    'notes' => "Payment for invoice {$invoiceNumber}",
                ]);
            }

            // Update customer balance (due amount added to balance)
            if ($customer) {
                $dueAmount = max(0, $roundedGrandTotal - $paidAmount);
                $customer->increment('balance', $dueAmount);
            }

            return response()->json([
                'success' => true,
                'invoice' => $invoice->load('items'),
                'print_url' => route('invoices.print', $invoice->id)
            ]);
        });
    }
}
