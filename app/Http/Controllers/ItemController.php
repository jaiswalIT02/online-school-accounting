<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    public function index(Request $request)
    {
        $query = Item::query();

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('item_code', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $items = $query->orderBy('name')->paginate(15)->withQueryString();
        $editingItem = null;

        if ($request->has('edit')) {
            $editingItem = Item::find($request->edit);
        }

        return view('items.index', compact('items', 'editingItem'));
    }

    public function show(Item $item)
    {
        return view('items.show', compact('item'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'item_code' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'unit' => ['nullable', 'string', 'max:50'],
            'status' => ['required', 'string', 'in:active,inactive'],
        ]);

        // Convert string fields to uppercase
        if (isset($data['name'])) $data['name'] = strtoupper($data['name']);
        if (isset($data['item_code'])) $data['item_code'] = strtoupper($data['item_code']);
        if (isset($data['description'])) $data['description'] = strtoupper($data['description']);
        if (isset($data['unit'])) $data['unit'] = strtoupper($data['unit']);

        Item::create($data);

        return redirect()
            ->route('items.index')
            ->with('status', 'Item created.');
    }

    public function update(Request $request, Item $item)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'item_code' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'unit' => ['nullable', 'string', 'max:50'],
            'status' => ['required', 'string', 'in:active,inactive'],
        ]);

        // Convert string fields to uppercase
        if (isset($data['name'])) $data['name'] = strtoupper($data['name']);
        if (isset($data['item_code'])) $data['item_code'] = strtoupper($data['item_code']);
        if (isset($data['description'])) $data['description'] = strtoupper($data['description']);
        if (isset($data['unit'])) $data['unit'] = strtoupper($data['unit']);

        $item->update($data);

        return redirect()
            ->route('items.index')
            ->with('status', 'Item updated.');
    }

    public function destroy(Item $item)
    {
        $item->delete();

        return redirect()
            ->route('items.index')
            ->with('status', 'Item deleted.');
    }
}
