<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Stock;
use Illuminate\Http\Request;

class StockController extends Controller
{
    public function index(Request $request)
    {
        $query = Stock::query()->with('item');

        if ($request->filled('item_id')) {
            $query->where('item_id', $request->item_id);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }
        if ($request->filled('stock_type')) {
            $query->where('stock_type', $request->stock_type);
        }

        $stocks = $query->orderBy('date')->orderBy('id')->paginate(15)->withQueryString();
        $items = Item::orderBy('name')->get();
        $editingStock = null;

        if ($request->has('edit')) {
            $editingStock = Stock::with('item')->find($request->edit);
        }

        return view('stocks.index', compact('stocks', 'items', 'editingStock'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'date' => ['required', 'string', 'regex:/^\d{2}\/\d{2}\/\d{4}$/'],
            'item_id' => ['required', 'exists:items,id'],
            'number' => ['required', 'numeric', 'min:0'],
            'stock_type' => ['required', 'in:Receipt,Issued'],
            'stock_amount' => ['nullable', 'numeric', 'min:0'],
            'stock_unit' => ['nullable', 'string', 'max:50'],
        ]);

        $data['date'] = \Carbon\Carbon::createFromFormat('d/m/Y', $data['date'])->format('Y-m-d');
        $data['stock_balance'] = 0; // backend only
        // Convert string fields to uppercase
        if (isset($data['stock_type'])) $data['stock_type'] = strtoupper($data['stock_type']);
        if (isset($data['stock_unit']) && $data['stock_unit'] !== '') {
            $data['stock_unit'] = strtoupper($data['stock_unit']);
        } else {
            $data['stock_unit'] = null;
        }

        Stock::create($data);

        return redirect()
            ->route('stocks.index')
            ->with('status', 'Stock entry created.');
    }

    public function update(Request $request, Stock $stock)
    {
        $data = $request->validate([
            'date' => ['required', 'string', 'regex:/^\d{2}\/\d{2}\/\d{4}$/'],
            'item_id' => ['required', 'exists:items,id'],
            'number' => ['required', 'numeric', 'min:0'],
            'stock_type' => ['required', 'in:Receipt,Issued'],
            'stock_amount' => ['nullable', 'numeric', 'min:0'],
            'stock_unit' => ['nullable', 'string', 'max:50'],
        ]);

        $data['date'] = \Carbon\Carbon::createFromFormat('d/m/Y', $data['date'])->format('Y-m-d');
        // Convert string fields to uppercase
        if (isset($data['stock_type'])) $data['stock_type'] = strtoupper($data['stock_type']);
        if (isset($data['stock_unit']) && $data['stock_unit'] !== '') {
            $data['stock_unit'] = strtoupper($data['stock_unit']);
        } else {
            $data['stock_unit'] = null;
        }
        // stock_balance not updated - backend only

        $stock->update($data);

        return redirect()
            ->route('stocks.index')
            ->with('status', 'Stock entry updated.');
    }

    public function destroy(Stock $stock)
    {
        $stock->delete();

        return redirect()
            ->route('stocks.index')
            ->with('status', 'Stock entry deleted.');
    }
}
