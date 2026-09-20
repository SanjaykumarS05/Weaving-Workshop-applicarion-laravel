<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DeliverySheet;
use App\Models\DeliverySheetItem;
use App\Models\Invoice;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DeliverySheetController extends Controller
{
    public function index(Request $request)
    {
        $userId = Auth::id();
        $query = DeliverySheet::where('user_id', $userId)->with(['items.invoice']);

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('sheet_number', 'like', "%{$search}%")
                  ->orWhere('driver_name', 'like', "%{$search}%")
                  ->orWhere('vehicle_number', 'like', "%{$search}%");
            });
        }

        $sheets = $query->orderBy('id', 'desc')->get();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'sheets' => $sheets]);
        }

        return view('delivery_sheets.index', compact('sheets'));
    }

    public function getNextSheetNumber()
    {
        $user = Auth::user();
        $count = DeliverySheet::where('user_id', $user->id)->count();
        $nextNum = sprintf("DS-%04d", $count + 1);
        return response()->json(['success' => true, 'sheet_number' => $nextNum]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $validated = $request->validate([
            'sheet_date' => 'required|date',
            'driver_name' => 'nullable|string',
            'vehicle_number' => 'nullable|string',
            'notes' => 'nullable|string',
            'invoice_ids' => 'required|array|min:1',
            'invoice_ids.*' => 'exists:invoices,id',
        ]);

        return DB::transaction(function() use ($user, $validated) {
            $count = DeliverySheet::where('user_id', $user->id)->count();
            $sheetNumber = sprintf("DS-%04d", $count + 1);

            $sheet = DeliverySheet::create([
                'user_id' => $user->id,
                'sheet_number' => $sheetNumber,
                'sheet_date' => $validated['sheet_date'],
                'driver_name' => $validated['driver_name'] ?? null,
                'vehicle_number' => $validated['vehicle_number'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($validated['invoice_ids'] as $invId) {
                $sheet->items()->create([
                    'invoice_id' => $invId,
                    'status' => 'pending',
                ]);
            }

            return response()->json([
                'success' => true,
                'sheet' => $sheet->load('items.invoice'),
                'print_url' => route('delivery-sheets.print', $sheet->id)
            ]);
        });
    }

    public function print($id)
    {
        $user = Auth::user();
        $sheet = DeliverySheet::where('user_id', $user->id)->with(['items.invoice.items'])->findOrFail($id);
        return view('delivery_sheets.print', compact('sheet'));
    }

    public function destroy($id)
    {
        $user = Auth::user();
        $sheet = DeliverySheet::where('user_id', $user->id)->findOrFail($id);
        $sheet->delete();
        return response()->json(['success' => true, 'message' => 'Delivery sheet deleted successfully.']);
    }
}
