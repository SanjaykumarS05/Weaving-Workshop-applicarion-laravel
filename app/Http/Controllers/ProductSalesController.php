<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InvoiceItem;
use App\Models\Invoice;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProductSalesController extends Controller
{
    public function index(Request $request)
    {
        $userId = Auth::id();

        $query = InvoiceItem::whereHas('invoice', function($q) use ($userId, $request) {
            $q->where('user_id', $userId);
            if ($request->has('from_date') && !empty($request->from_date)) {
                $q->whereDate('invoice_date', '>=', $request->from_date);
            }
            if ($request->has('to_date') && !empty($request->to_date)) {
                $q->whereDate('invoice_date', '<=', $request->to_date);
            }
        });

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where('product_name', 'like', "%{$search}%");
        }

        $sales = $query->select(
            'product_name',
            'unit',
            'tax_rate',
            DB::raw('SUM(quantity) as total_qty'),
            DB::raw('SUM(taxable_amount) as total_taxable'),
            DB::raw('SUM(cgst_amount + sgst_amount + igst_amount) as total_tax'),
            DB::raw('SUM(total_amount) as total_sales')
        )
        ->groupBy('product_name', 'unit', 'tax_rate')
        ->orderBy('total_sales', 'desc')
        ->get();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'sales' => $sales]);
        }

        return view('product_sales.index', compact('sales'));
    }
}
