<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Models\Customer;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        if ($request->wantsJson()) {
            return $this->getMetrics($request);
        }
        return view('dashboard.index');
    }

    public function getMetrics(Request $request)
    {
        $userId = Auth::id();
        $invoicesQuery = Invoice::where('user_id', $userId);

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $invoicesQuery->where(function($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('grand_total', 'like', "%{$search}%");
            });
        }

        if ($request->has('from_date') && !empty($request->from_date)) {
            $invoicesQuery->whereDate('invoice_date', '>=', $request->from_date);
        }

        if ($request->has('to_date') && !empty($request->to_date)) {
            $invoicesQuery->whereDate('invoice_date', '<=', $request->to_date);
        }

        $allInvoices = Invoice::where('user_id', $userId)->get();
        $totalInvoices = $allInvoices->count();
        $totalCustomers = Customer::where('user_id', $userId)->count();
        $totalProducts = Product::where('user_id', $userId)->count();
        $overallValue = $allInvoices->sum('grand_total');

        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();
        $monthValue = Invoice::where('user_id', $userId)
            ->whereBetween('invoice_date', [$startOfMonth, $endOfMonth])
            ->sum('grand_total');

        $outstandingAmount = $allInvoices->sum(function($inv) {
            return max(0, $inv->grand_total - $inv->paid_amount);
        });

        $recentInvoices = (clone $invoicesQuery)->orderBy('created_at', 'desc')->take(10)->get();

        return response()->json([
            'success' => true,
            'metrics' => [
                'totalInvoices' => $totalInvoices,
                'totalCustomers' => $totalCustomers,
                'totalProducts' => $totalProducts,
                'overallValue' => number_format($overallValue, 2, '.', ''),
                'monthValue' => number_format($monthValue, 2, '.', ''),
                'outstandingValue' => number_format($outstandingAmount, 2, '.', ''),
            ],
            'recentInvoices' => $recentInvoices
        ]);
    }
}
