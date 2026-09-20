<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Borrow;
use App\Models\Worker;
use Illuminate\Support\Facades\Auth;

class BorrowController extends Controller
{
    public function index(Request $request)
    {
        $userId = Auth::id();
        $workers = Worker::where('user_id', $userId)->orderBy('name', 'asc')->get();

        $query = Borrow::with('worker')->where('user_id', $userId);

        if ($request->has('worker_id') && !empty($request->worker_id)) {
            $query->where('worker_id', $request->worker_id);
        }

        if ($request->has('type') && in_array($request->type, ['given', 'returned'])) {
            $query->where('type', $request->type);
        }

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('notes', 'like', "%{$search}%")
                  ->orWhereHas('worker', function($wq) use ($search) {
                      $wq->where('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->has('from_date') && !empty($request->from_date)) {
            $query->whereDate('entry_date', '>=', $request->from_date);
        }

        if ($request->has('to_date') && !empty($request->to_date)) {
            $query->whereDate('entry_date', '<=', $request->to_date);
        }

        // Summary calculations
        $allUserBorrows = Borrow::where('user_id', $userId)->get();
        $totalGiven = $allUserBorrows->where('type', 'given')->sum('amount');
        $totalReturned = $allUserBorrows->where('type', 'returned')->sum('amount');
        $netBalance = $totalGiven - $totalReturned;

        // Count active workers with net balance > 0
        $workerBalances = [];
        foreach ($allUserBorrows as $b) {
            $wId = $b->worker_id;
            if (!isset($workerBalances[$wId])) $workerBalances[$wId] = 0;
            if ($b->type === 'given') {
                $workerBalances[$wId] += $b->amount;
            } else {
                $workerBalances[$wId] -= $b->amount;
            }
        }
        $activeBorrowersCount = count(array_filter($workerBalances, fn($bal) => $bal > 0));

        // Sorting
        $query->orderBy('entry_date', 'desc')->orderBy('id', 'desc');

        // Export JSON handler
        if ($request->has('export') && $request->export == '1') {
            $exportEntries = $query->get()->map(function($b) use ($workerBalances) {
                return [
                    'id' => $b->id,
                    'date' => \Carbon\Carbon::parse($b->entry_date)->format('d/m/Y'),
                    'worker_name' => $b->worker->name ?? 'Unknown',
                    'worker_phone' => $b->worker->phone ?? '-',
                    'qty_in' => $b->type === 'given' ? number_format($b->amount, 2) : '-', // Given to Worker
                    'qty_out' => $b->type === 'returned' ? number_format($b->amount, 2) : '-', // Returned by Worker
                    'calc_balance' => number_format(abs($workerBalances[$b->worker_id] ?? 0), 2),
                    'notes' => $b->notes ?? '-',
                ];
            });

            return response()->json([
                'success' => true,
                'entries' => $exportEntries,
                'totals' => [
                    'total_given' => number_format($totalGiven, 2),
                    'total_returned' => number_format($totalReturned, 2),
                    'balance' => number_format($netBalance, 2),
                    'active_borrowers' => $activeBorrowersCount,
                ]
            ]);
        }

        $borrows = $query->paginate(25)->withQueryString();

        return view('borrows.index', compact(
            'borrows',
            'workers',
            'totalGiven',
            'totalReturned',
            'netBalance',
            'activeBorrowersCount',
            'workerBalances'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'worker_id' => 'required|exists:workers,id',
            'entry_date' => 'required|date',
            'type' => 'required|in:given,returned',
            'amount' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string|max:500',
        ]);

        $validated['user_id'] = Auth::id();
        $borrow = Borrow::create($validated);

        return response()->json(['success' => true, 'borrow' => $borrow, 'message' => 'Borrow entry added successfully!']);
    }

    public function update(Request $request, $id)
    {
        $borrow = Borrow::where('user_id', Auth::id())->findOrFail($id);

        $validated = $request->validate([
            'worker_id' => 'required|exists:workers,id',
            'entry_date' => 'required|date',
            'type' => 'required|in:given,returned',
            'amount' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string|max:500',
        ]);

        $borrow->update($validated);

        return response()->json(['success' => true, 'borrow' => $borrow, 'message' => 'Borrow entry updated successfully!']);
    }

    public function destroy($id)
    {
        $borrow = Borrow::where('user_id', Auth::id())->findOrFail($id);
        $borrow->delete();

        return response()->json(['success' => true, 'message' => 'Borrow entry deleted successfully!']);
    }
}
