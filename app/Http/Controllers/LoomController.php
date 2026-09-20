<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Loom;
use App\Models\Worker;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

class LoomController extends Controller
{
    public function index(Request $request)
    {
        $userId = Auth::id();
        $workers = Worker::where('user_id', $userId)->orderBy('name', 'asc')->get();

        $query = Loom::with('worker')->where('user_id', $userId);

        if ($request->has('worker_id') && !empty($request->worker_id)) {
            $query->where('worker_id', $request->worker_id);
        }

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('details', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%")
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

        // Retrieve chronologically to calculate running balance accurately
        $allEntries = $query->orderBy('entry_date', 'asc')->orderBy('id', 'asc')->get();

        $runningBalance = 0;
        foreach ($allEntries as $entry) {
            if ($entry->type === 'in') {
                $runningBalance += (float)$entry->qty_in;
            } else {
                $runningBalance -= (float)$entry->qty_out;
            }
            $entry->calc_balance = abs($runningBalance);
        }

        $totalQtyIn = $allEntries->sum('qty_in');
        $totalQtyOut = $allEntries->sum('qty_out');
        $currentBalance = abs($totalQtyIn - $totalQtyOut);

        // Reorder descending: latest entry date & newest record at top
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
                    $filename = "Heavy_Looms_{$timestamp}.csv";

                    return response()->streamDownload(function() use ($sortedEntries, $totalQtyIn, $totalQtyOut, $currentBalance) {
                        $file = fopen('php://output', 'w');
                        fputs($file, "\xEF\xBB\xBF");

                        fputcsv($file, ['Date', 'Worker Name', 'Warp / Production Details', 'Given to Loom (m)', 'Cloth Received from Worker (m)', 'Remaining Warp Balance (m)', 'Notes / Remarks']);

                        foreach ($sortedEntries as $e) {
                            fputcsv($file, [
                                Carbon::parse($e->entry_date)->format('d/m/Y'),
                                $e->worker->name ?? 'All Workers',
                                $e->details ?? '-',
                                $e->qty_in > 0 ? number_format($e->qty_in, 3) : '-',
                                $e->qty_out > 0 ? number_format($e->qty_out, 3) : '-',
                                number_format(abs($e->calc_balance ?? 0), 3),
                                $e->notes ?? '-',
                            ]);
                        }

                        fputcsv($file, [
                            'ALL-TIME OVERALL TOTALS',
                            '',
                            '',
                            number_format($totalQtyIn, 3) . ' m',
                            number_format($totalQtyOut, 3) . ' m',
                            number_format(abs($currentBalance), 3) . ' m',
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
                        'details' => $e->details ?? '-',
                        'qty_in' => $e->qty_in > 0 ? number_format($e->qty_in, 3) : '-',
                        'qty_out' => $e->qty_out > 0 ? number_format($e->qty_out, 3) : '-',
                        'calc_balance' => number_format(abs($e->calc_balance ?? 0), 3),
                        'notes' => $e->notes ?? '-',
                    ];
                })->values();

                return response()->json([
                    'success' => true,
                    'entries' => $exportEntries,
                    'totals' => [
                        'total_given' => number_format($totalQtyIn, 3),
                        'total_received' => number_format($totalQtyOut, 3),
                        'balance' => number_format(abs($currentBalance), 3),
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
                    'count' => $sortedEntries->count(),
                ]
            ]);
        }

        return view('looms.index', compact('entries', 'totalQtyIn', 'totalQtyOut', 'currentBalance', 'workers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'worker_id' => 'nullable|exists:workers,id',
            'entry_date' => 'required|date',
            'type' => 'required|in:in,out',
            'quantity' => 'required|numeric|min:0.001',
            'details' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:255',
        ]);

        $userId = Auth::id();
        $qty = (float)$validated['quantity'];

        $entry = Loom::create([
            'user_id' => $userId,
            'worker_id' => $validated['worker_id'] ?? null,
            'entry_date' => $validated['entry_date'],
            'type' => $validated['type'],
            'details' => $validated['details'] ?? null,
            'qty_in' => $validated['type'] === 'in' ? $qty : 0,
            'qty_out' => $validated['type'] === 'out' ? $qty : 0,
            'notes' => $validated['notes'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Loom register entry added successfully!',
            'entry' => $entry
        ]);
    }

    public function update(Request $request, $id)
    {
        $userId = Auth::id();
        $entry = Loom::where('user_id', $userId)->findOrFail($id);

        $validated = $request->validate([
            'worker_id' => 'nullable|exists:workers,id',
            'entry_date' => 'required|date',
            'type' => 'required|in:in,out',
            'quantity' => 'required|numeric|min:0.001',
            'details' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:255',
        ]);

        $qty = (float)$validated['quantity'];

        $entry->update([
            'worker_id' => $validated['worker_id'] ?? null,
            'entry_date' => $validated['entry_date'],
            'type' => $validated['type'],
            'details' => $validated['details'] ?? null,
            'qty_in' => $validated['type'] === 'in' ? $qty : 0,
            'qty_out' => $validated['type'] === 'out' ? $qty : 0,
            'notes' => $validated['notes'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Loom entry updated successfully.',
            'entry' => $entry
        ]);
    }

    public function destroy($id)
    {
        $userId = Auth::id();
        $entry = Loom::where('user_id', $userId)->findOrFail($id);
        $entry->delete();

        return response()->json([
            'success' => true,
            'message' => 'Loom entry deleted successfully.'
        ]);
    }
}
