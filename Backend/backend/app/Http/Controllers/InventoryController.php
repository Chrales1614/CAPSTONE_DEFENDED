<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class InventoryController extends Controller
{
    public function index()
    {
        $inventory = Inventory::orderBy('item_name')->paginate(10);
        return response()->json($inventory);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'item_name' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'quantity' => 'required|numeric|min:0',
            'unit' => 'required|string|max:50',
            'minimum_stock' => 'required|numeric|min:0',
            'reorder_point' => 'required|numeric|min:0',
            'supplier' => 'required|string|max:255',
            'expiry_date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $inventory = Inventory::create($request->all());
        return response()->json($inventory, 201);
    }

    public function show(Inventory $inventory)
    {
        return response()->json($inventory);
    }

    public function update(Request $request, Inventory $inventory)
    {
        $validator = Validator::make($request->all(), [
            'quantity' => 'numeric|min:0',
            'minimum_stock' => 'numeric|min:0',
            'reorder_point' => 'numeric|min:0',
            'supplier' => 'string|max:255',
            'expiry_date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $inventory->update($request->all());
        return response()->json($inventory);
    }

    public function destroy(Inventory $inventory)
    {
        $inventory->delete();
        return response()->json(null, 204);
    }

    public function getLowStock()
    {
        $lowStock = Inventory::where('quantity', '<=', 'reorder_point')->get();
        return response()->json($lowStock);
    }

    public function getExpiring()
    {
        $expiring = Inventory::whereNotNull('expiry_date')
            ->where('expiry_date', '<=', now()->addMonths(3))
            ->get();
        return response()->json($expiring);
    }

    public function updateStock(Request $request, Inventory $inventory)
    {
        $validator = Validator::make($request->all(), [
            'quantity' => 'required|numeric|min:0',
            'last_restock_date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $inventory->update([
            'quantity' => $request->quantity,
            'last_restock_date' => $request->last_restock_date,
        ]);

        return response()->json($inventory);
    }
} 