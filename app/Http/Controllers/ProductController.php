<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $userId = Auth::id();
        $query = Product::where('user_id', $userId);

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('hsn_code', 'like', "%{$search}%");
            });
        }

        $products = $query->orderBy('name', 'asc')->get();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'products' => $products]);
        }

        return view('products.index', compact('products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'hsn_code' => 'nullable|string',
            'unit' => 'required|string',
            'price' => 'required|numeric|min:0',
            'tax_rate' => 'required|numeric|min:0',
            'stock' => 'required|numeric',
        ]);

        $validated['user_id'] = Auth::id();
        $product = Product::create($validated);

        return response()->json(['success' => true, 'product' => $product]);
    }

    public function update(Request $request, $id)
    {
        $product = Product::where('user_id', Auth::id())->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'hsn_code' => 'nullable|string',
            'unit' => 'required|string',
            'price' => 'required|numeric|min:0',
            'tax_rate' => 'required|numeric|min:0',
            'stock' => 'required|numeric',
        ]);

        $product->update($validated);

        return response()->json(['success' => true, 'product' => $product]);
    }

    public function destroy($id)
    {
        $product = Product::where('user_id', Auth::id())->findOrFail($id);
        $product->delete();
        return response()->json(['success' => true, 'message' => 'Product deleted successfully.']);
    }
}
