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
        $period = $request->get('period', 'all');

        $invoicesQuery = Invoice::where('user_id', $userId);
        $now = Carbon::now();

        // Time period filter
        if ($period === 'today') {
            $invoicesQuery->whereDate('invoice_date', Carbon::today());
        } elseif ($period === 'this_month') {
            $invoicesQuery->whereBetween('invoice_date', [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()]);
        } elseif ($period === 'last_month') {
            $lastMonth = $now->copy()->subMonth();
            $invoicesQuery->whereBetween('invoice_date', [$lastMonth->copy()->startOfMonth(), $lastMonth->copy()->endOfMonth()]);
        }

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

        $filteredInvoices = (clone $invoicesQuery)->get();

        $totalInvoices = $filteredInvoices->count();
        $totalCustomers = Customer::where('user_id', $userId)->count();
        $totalProducts = Product::where('user_id', $userId)->count();
        
        $overallValue = $filteredInvoices->sum('grand_total');
        $paidValue = $filteredInvoices->sum('paid_amount');
        $outstandingValue = $filteredInvoices->sum(function($inv) {
            return max(0, $inv->grand_total - $inv->paid_amount);
        });

        // Status counts for Pie Charts
        $paidCount = $filteredInvoices->where('status', 'paid')->count();
        $partialCount = $filteredInvoices->where('status', 'partial')->count();
        $unpaidCount = $filteredInvoices->where('status', 'unpaid')->count();

        // Specific period comparisons
        $todayValue = Invoice::where('user_id', $userId)->whereDate('invoice_date', Carbon::today())->sum('grand_total');
        $thisMonthValue = Invoice::where('user_id', $userId)->whereBetween('invoice_date', [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()])->sum('grand_total');
        $lastMonthValue = Invoice::where('user_id', $userId)->whereBetween('invoice_date', [$now->copy()->subMonth()->startOfMonth(), $now->copy()->subMonth()->endOfMonth()])->sum('grand_total');

        $recentInvoices = (clone $invoicesQuery)->orderBy('created_at', 'desc')->take(10)->get();

        return response()->json([
            'success' => true,
            'period' => $period,
            'metrics' => [
                'totalInvoices' => $totalInvoices,
                'totalCustomers' => $totalCustomers,
                'totalProducts' => $totalProducts,
                'overallValue' => number_format($overallValue, 2, '.', ''),
                'paidValue' => number_format($paidValue, 2, '.', ''),
                'outstandingValue' => number_format($outstandingValue, 2, '.', ''),
                'todayValue' => number_format($todayValue, 2, '.', ''),
                'thisMonthValue' => number_format($thisMonthValue, 2, '.', ''),
                'lastMonthValue' => number_format($lastMonthValue, 2, '.', ''),
                'paidCount' => $paidCount,
                'partialCount' => $partialCount,
                'unpaidCount' => $unpaidCount,
            ],
            'recentInvoices' => $recentInvoices
        ]);
    }
}
