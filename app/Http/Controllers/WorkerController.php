<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Worker;
use Illuminate\Support\Facades\Auth;

class WorkerController extends Controller
{
    public function index(Request $request)
    {
        $userId = Auth::id();
        $query = Worker::where('user_id', $userId);

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%");
            });
        }

        $workers = $query->orderBy('name', 'asc')->get();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'workers' => $workers]);
        }

        return view('workers.index', compact('workers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
        ]);

        $validated['user_id'] = Auth::id();
        $worker = Worker::create($validated);

        return response()->json(['success' => true, 'worker' => $worker, 'message' => 'Worker added successfully!']);
    }

    public function update(Request $request, $id)
    {
        $worker = Worker::where('user_id', Auth::id())->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
        ]);

        $worker->update($validated);

        return response()->json(['success' => true, 'worker' => $worker, 'message' => 'Worker updated successfully!']);
    }

    public function destroy($id)
    {
        $worker = Worker::where('user_id', Auth::id())->findOrFail($id);
        $worker->delete();

        return response()->json(['success' => true, 'message' => 'Worker deleted successfully!']);
    }
}
