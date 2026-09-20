<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StockRegister;
use App\Models\Worker;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

class StockRegisterController extends Controller
{
    public function index(Request $request)
    {
        $userId = Auth::id();
        $workers = Worker::where('user_id', $userId)->orderBy('name', 'asc')->get();

        // Auto-seed sample notebook entries if none exist for user
        if (StockRegister::where('user_id', $userId)->count() === 0) {
            $this->seedSampleEntries($userId);
        }

        $query = StockRegister::with('worker')->where('user_id', $userId);

        if ($request->has('worker_id') && !empty($request->worker_id)) {
            $query->where('worker_id', $request->worker_id);
        }

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('item_name', 'like', "%{$search}%")
                  ->orWhere('details', 'like', "%{$search}%")
                  ->orWhere('conversion_notes', 'like', "%{$search}%")
                  ->orWhereHas('worker', function($wq) use ($search) {
                      $wq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->has('type') && in_array($request->type, ['in', 'out'])) {
            $query->where('type', $request->type);
        }

        if ($request->has('from_date') && !empty($request->from_date)) {
            $query->whereDate('entry_date', '>=', $request->from_date);
        }

        if ($request->has('to_date') && !empty($request->to_date)) {
            $query->whereDate('entry_date', '<=', $request->to_date);
        }

        // Retrieve chronologically to calculate running balances accurately
        $allEntries = $query->orderBy('entry_date', 'asc')->orderBy('id', 'asc')->get();

        // Calculate running balance per item chronologically (In = Material Given to Worker, Out = Material Received from Worker)
        $runningBalances = [];
        foreach ($allEntries as $entry) {
            $itemName = $entry->item_name;
            if (!isset($runningBalances[$itemName])) {
                $runningBalances[$itemName] = 0;
            }

            if ($entry->type === 'in') {
                $runningBalances[$itemName] += (float)$entry->qty_in;
            } else {
                $runningBalances[$itemName] -= (float)$entry->qty_out;
            }
            $entry->calc_balance = abs($runningBalances[$itemName]);
        }

        $totalQtyIn = $allEntries->sum('qty_in');
        $totalQtyOut = $allEntries->sum('qty_out');
        $currentBalance = abs($totalQtyIn - $totalQtyOut);
        $totalCones = $allEntries->where('type', 'in')->sum(function($e) {
            return is_numeric($e->details) ? (float)$e->details : (float)preg_replace('/[^0-9.]/', '', $e->details ?? '0');
        });

        $distinctItems = StockRegister::where('user_id', $userId)->distinct()->pluck('item_name');

        // Reorder descending: latest created entry / newest entry date at top
        $sortedEntries = $allEntries->sortBy([
            ['entry_date', 'desc'],
            ['id', 'desc']
        ])->values();

        // Paginate 15 entries per page
        $perPage = 15;
        $currentPage = Paginator::resolveCurrentPage() ?: 1;
        $currentPageItems = $sortedEntries->slice(($currentPage - 1) * $perPage, $perPage)->values();

        $entries = new LengthAwarePaginator(
            $currentPageItems,
            $sortedEntries->count(),
            $perPage,
            $currentPage,
            [
                'path' => Paginator::resolveCurrentPath(),
                'query' => $request->query(),
            ]
        );

        if ($request->has('export') || $request->wantsJson()) {
            if ($request->has('export')) {
                $exportType = strtolower($request->export);
                
                if ($exportType === 'csv' || $exportType === 'excel') {
                    $timestamp = Carbon::now()->format('d-m-Y_H-i');
                    $filename = "Stock_Register_{$timestamp}.csv";

                    return response()->streamDownload(function() use ($sortedEntries, $totalQtyIn, $totalQtyOut, $currentBalance, $totalCones) {
                        $file = fopen('php://output', 'w');
                        fputs($file, "\xEF\xBB\xBF"); // UTF-8 BOM for MS Excel

                        fputcsv($file, ['Date', 'Worker Name', 'Item / Quality', 'Cone Count (Details)', 'Material Given to Worker (kg)', 'Material Received from Worker (kg)', 'Balance with Worker (kg)', 'Conversion / Notes']);

                        foreach ($sortedEntries as $e) {
                            fputcsv($file, [
                                Carbon::parse($e->entry_date)->format('d/m/Y'),
                                $e->worker->name ?? 'All Workers',
                                $e->item_name,
                                $e->details ?? '-',
                                $e->qty_in > 0 ? number_format($e->qty_in, 3) : '-',
                                $e->qty_out > 0 ? number_format($e->qty_out, 3) : '-',
                                number_format(abs($e->calc_balance ?? 0), 3),
                                $e->conversion_notes ?? '-',
                            ]);
                        }

                        fputcsv($file, [
                            'ALL-TIME OVERALL TOTALS',
                            '',
                            '',
                            number_format($totalCones) . ' Cones',
                            number_format($totalQtyIn, 3) . ' kg',
                            number_format($totalQtyOut, 3) . ' kg',
                            number_format(abs($currentBalance), 3) . ' kg',
                            ''
                        ]);

                        fclose($file);
                    }, $filename, [
                        'Content-Type' => 'text/csv; charset=UTF-8',
                        'Cache-Control' => 'no-cache, no-store, must-revalidate',
                        'Pragma' => 'no-cache',
                        'Expires' => '0',
                    ]);
                }

                $exportEntries = $sortedEntries->map(function($e) {
                    return [
                        'date' => Carbon::parse($e->entry_date)->format('d/m/Y'),
                        'worker_name' => $e->worker->name ?? 'All Workers',
                        'item_name' => $e->item_name,
                        'details' => $e->details ?? '-',
                        'qty_in' => $e->qty_in > 0 ? number_format($e->qty_in, 3) : '-',
                        'qty_out' => $e->qty_out > 0 ? number_format($e->qty_out, 3) : '-',
                        'calc_balance' => number_format(abs($e->calc_balance ?? 0), 3),
                        'notes' => $e->conversion_notes ?? '-',
                    ];
                })->values();

                return response()->json([
                    'success' => true,
                    'entries' => $exportEntries,
                    'totals' => [
                        'total_given' => number_format($totalQtyIn, 3),
                        'total_received' => number_format($totalQtyOut, 3),
                        'balance' => number_format(abs($currentBalance), 3),
                        'total_cones' => number_format($totalCones),
                    ]
                ]);
            }

            return response()->json([
                'success' => true,
                'entries' => $entries,
                'totals' => [
                    'qty_in' => number_format($totalQtyIn, 3, '.', ''),
                    'qty_out' => number_format($totalQtyOut, 3, '.', ''),
                    'balance' => number_format($currentBalance, 3, '.', ''),
                    'total_cones' => $totalCones,
                    'count' => $sortedEntries->count(),
                ]
            ]);
        }

        return view('stock_register.index', compact('entries', 'totalQtyIn', 'totalQtyOut', 'currentBalance', 'totalCones', 'distinctItems', 'workers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'worker_id' => 'nullable|exists:workers,id',
            'entry_date' => 'required|date',
            'item_name' => 'required|string|max:255',
            'type' => 'required|in:in,out',
            'quantity' => 'required|numeric|min:0.001',
            'details' => 'nullable|numeric|min:0',
            'conversion_notes' => 'nullable|string|max:255',
        ]);

        $userId = Auth::id();
        $qty = (float)$validated['quantity'];

        $entry = StockRegister::create([
            'user_id' => $userId,
            'worker_id' => $validated['worker_id'] ?? null,
            'entry_date' => $validated['entry_date'],
            'item_name' => trim($validated['item_name']),
            'type' => $validated['type'],
            'details' => isset($validated['details']) && $validated['details'] !== '' ? $validated['details'] : null,
            'qty_in' => $validated['type'] === 'in' ? $qty : 0,
            'qty_out' => $validated['type'] === 'out' ? $qty : 0,
            'conversion_notes' => $validated['conversion_notes'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Stock register entry added successfully!',
            'entry' => $entry
        ]);
    }

    public function update(Request $request, $id)
    {
        $userId = Auth::id();
        $entry = StockRegister::where('user_id', $userId)->findOrFail($id);

        $validated = $request->validate([
            'worker_id' => 'nullable|exists:workers,id',
            'entry_date' => 'required|date',
            'item_name' => 'required|string|max:255',
            'type' => 'required|in:in,out',
            'quantity' => 'required|numeric|min:0.001',
            'details' => 'nullable|numeric|min:0',
            'conversion_notes' => 'nullable|string|max:255',
        ]);

        $qty = (float)$validated['quantity'];

        $entry->update([
            'worker_id' => $validated['worker_id'] ?? null,
            'entry_date' => $validated['entry_date'],
            'item_name' => trim($validated['item_name']),
            'type' => $validated['type'],
            'details' => $validated['details'] ?? null,
            'qty_in' => $validated['type'] === 'in' ? $qty : 0,
            'qty_out' => $validated['type'] === 'out' ? $qty : 0,
            'conversion_notes' => $validated['conversion_notes'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Stock entry updated successfully.',
            'entry' => $entry
        ]);
    }

    public function destroy($id)
    {
        $userId = Auth::id();
        $entry = StockRegister::where('user_id', $userId)->findOrFail($id);
        $entry->delete();

        return response()->json([
            'success' => true,
            'message' => 'Stock entry deleted successfully.'
        ]);
    }

    private function seedSampleEntries($userId)
    {
        $samples = [
            ['entry_date' => '2026-03-02', 'item_name' => '30 PC', 'type' => 'out', 'details' => '440', 'qty_in' => 0, 'qty_out' => 60.000, 'conversion_notes' => '30 PC 1 kg Per 14 mts'],
            ['entry_date' => '2026-04-04', 'item_name' => '30 PC', 'type' => 'in',  'details' => null,  'qty_in' => 23.000, 'qty_out' => 0, 'conversion_notes' => '30 PC 1 kg Per 14 mts'],
            ['entry_date' => '2026-04-10', 'item_name' => '30 PC', 'type' => 'out', 'details' => '470', 'qty_in' => 0, 'qty_out' => 64.000, 'conversion_notes' => '30 PC 1 kg Per 14 mts'],
            ['entry_date' => '2026-04-10', 'item_name' => '30 PC', 'type' => 'in',  'details' => null,  'qty_in' => 27.550, 'qty_out' => 0, 'conversion_notes' => '30 PC 1 kg Per 14 mts'],
            ['entry_date' => '2026-05-09', 'item_name' => '30 PC', 'type' => 'out', 'details' => '470', 'qty_in' => 0, 'qty_out' => 64.000, 'conversion_notes' => '50 PC 1 kg Per 24 mts'],
            ['entry_date' => '2026-05-10', 'item_name' => '30 PC', 'type' => 'in',  'details' => null,  'qty_in' => 35.600, 'qty_out' => 0, 'conversion_notes' => '30 PC 1 kg Per 14 mts'],
            ['entry_date' => '2026-05-22', 'item_name' => '30 PC', 'type' => 'in',  'details' => null,  'qty_in' => 41.400, 'qty_out' => 0, 'conversion_notes' => '50 PC 1 kg Per 24 mts'],
            ['entry_date' => '2026-06-12', 'item_name' => '30 PC', 'type' => 'out', 'details' => '440', 'qty_in' => 0, 'qty_out' => 60.000, 'conversion_notes' => '30 PC 1 kg Per 14 mts'],
            ['entry_date' => '2026-06-13', 'item_name' => '30 PC', 'type' => 'in',  'details' => null,  'qty_in' => 33.900, 'qty_out' => 0, 'conversion_notes' => '30 PC 1 kg Per 14 mts'],
            ['entry_date' => '2026-06-20', 'item_name' => '30 PC', 'type' => 'in',  'details' => null,  'qty_in' => 24.500, 'qty_out' => 0, 'conversion_notes' => '30 PC 1 kg Per 14 mts'],
            ['entry_date' => '2026-07-18', 'item_name' => '30 PC', 'type' => 'in',  'details' => null,  'qty_in' => 35.300, 'qty_out' => 0, 'conversion_notes' => '30 PC 1 kg Per 14 mts'],
        ];

        foreach ($samples as $s) {
            StockRegister::create(array_merge($s, ['user_id' => $userId]));
        }
    }
}
