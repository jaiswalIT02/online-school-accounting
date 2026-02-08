@extends('layouts.app')

@section('content')
<div class="container">
    <h4 class="mb-4">Stocks</h4>

    @if (session('status'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Add/Edit Form --}}
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">{{ $editingStock ? 'Edit Stock Entry' : 'Add New Stock Entry' }}</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ $editingStock ? route('stocks.update', $editingStock) : route('stocks.store') }}">
                @csrf
                @if($editingStock)
                    @method('PUT')
                @endif

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label" for="date">Date</label>
                        <input type="text" class="form-control @error('date') is-invalid @enderror" id="date" name="date" value="{{ old('date', $editingStock?->date?->format('d/m/Y') ?? now()->format('d/m/Y')) }}" placeholder="dd/mm/yyyy" maxlength="10" required>
                        @error('date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label" for="item_id">Stock Item</label>
                        <select class="form-select @error('item_id') is-invalid @enderror" id="item_id" name="item_id" required>
                            <option value="">Select Item</option>
                            @foreach ($items as $item)
                                <option value="{{ $item->id }}" @selected(old('item_id', $editingStock?->item_id) == $item->id)>{{ $item->name }}</option>
                            @endforeach
                        </select>
                        @error('item_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label" for="stock_type">Stock Type</label>
                        <select class="form-select @error('stock_type') is-invalid @enderror" id="stock_type" name="stock_type" required>
                            <option value="">Select</option>
                            <option value="Receipt" @selected(old('stock_type', $editingStock?->stock_type) === 'Receipt')>Receipt</option>
                            <option value="Issued" @selected(old('stock_type', $editingStock?->stock_type) === 'Issued')>Issued</option>
                        </select>
                        @error('stock_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label class="form-label" for="stock_amount">Stock Quantity</label>
                        <input type="number" step="0.01" min="0" class="form-control @error('stock_amount') is-invalid @enderror" id="stock_amount" name="stock_amount" value="{{ old('stock_amount', $editingStock?->stock_amount ?? 0) }}">
                        @error('stock_amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label" for="number">Number</label>
                        <input type="number" step="0.01" min="0" class="form-control @error('number') is-invalid @enderror" id="number" name="number" value="{{ old('number', $editingStock?->number ?? 0) }}" required>
                        @error('number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-1 mb-3 d-flex align-items-end">
                        <button class="btn btn-primary w-100" type="submit">{{ $editingStock ? 'Update' : 'Add' }}</button>
                    </div>
                </div>

                @if($editingStock)
                    <div class="mt-2">
                        <a href="{{ route('stocks.index', request()->only(['item_id','date_from','date_to','stock_type'])) }}" class="btn btn-sm btn-outline-secondary">Cancel</a>
                    </div>
                @endif
            </form>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('stocks.index') }}" class="row g-2 align-items-end">
                <div class="col-md-2">
                    <label class="form-label small">Item</label>
                    <select name="item_id" class="form-select">
                        <option value="">All Items</option>
                        @foreach ($items as $item)
                            <option value="{{ $item->id }}" @selected(request('item_id') == $item->id)>{{ $item->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Date From</label>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Date To</label>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Stock Type</label>
                    <select name="stock_type" class="form-select">
                        <option value="">All</option>
                        <option value="Receipt" @selected(request('stock_type') === 'Receipt')>Receipt</option>
                        <option value="Issued" @selected(request('stock_type') === 'Issued')>Issued</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search"></i> Filter</button>
                </div>
                @if(request()->hasAny(['item_id', 'date_from', 'date_to', 'stock_type']))
                    <div class="col-md-1">
                        <a href="{{ route('stocks.index') }}" class="btn btn-outline-secondary w-100">Clear</a>
                    </div>
                @endif
            </form>
        </div>
    </div>

    {{-- Stock Register table --}}
    <div class="card">
        <div class="card-header bg-light fw-semibold">Stock Register</div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 10%">Date</th>
                            <th style="width: 18%">Name of Components</th>
                            <th style="width: 6%" class="text-center">NO</th>
                            <th style="width: 14%" class="text-end">Receipt / Unit</th>
                            <th style="width: 14%" class="text-end">Issued / Unit</th>
                            <!-- <th style="width: 12%" class="text-end">Balance</th> -->
                            <th style="width: 14%" class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($stocks as $stock)
                            <tr>
                                <td>{{ $stock->date->format('d/m/Y') }}</td>
                                <td>{{ $stock->item->name ?? '-' }}</td>
                                <td class="text-center">{{ $stock->number > 0 ? number_format($stock->number, 2) : '-' }}</td>
                                <td class="text-end">
                                    @if ($stock->stock_type === 'Receipt')
                                        {{ number_format($stock->stock_amount ?? $stock->number, 2) }} {{ $stock->item->unit ?? '' }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if ($stock->stock_type === 'Issued')
                                        {{ number_format($stock->stock_amount ?? $stock->number, 2) }} {{ $stock->item->unit ?? '' }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <!-- <td class="text-end">{{ number_format($stock->stock_balance ?? 0, 2) }} {{ $stock->stock_unit ?? '' }}</td> -->
                                <td class="text-end">
                                    <a class="btn btn-sm btn-outline-primary" href="{{ route('stocks.index', array_merge(request()->only(['item_id','date_from','date_to','stock_type']), ['edit' => $stock->id])) }}">Edit</a>
                                    <form class="d-inline" method="POST" action="{{ route('stocks.destroy', $stock) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this stock entry?')">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">No stock entries yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($stocks->isNotEmpty())
                    @php
                        $totalReceipt = $stocks->where('stock_type', 'Receipt')->sum(fn($s) => $s->stock_amount ?? $s->number);
                        $totalIssued = $stocks->where('stock_type', 'Issued')->sum(fn($s) => $s->stock_amount ?? $s->number);
                    @endphp
                    <tfoot class="table-light">
                        <!-- <tr>
                            <th colspan="3" class="text-end">Total</th>
                            <th class="text-end">{{ number_format($totalReceipt, 2) }}</th>
                            <th class="text-end">{{ number_format($totalIssued, 2) }}</th>
                            <th class="text-end">{{ number_format($stocks->sum('stock_balance'), 2) }}</th>
                            <th></th>
                        </tr> -->
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>

    <div class="mt-3">
        {{ $stocks->links() }}
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var dateEl = document.getElementById('date');
    if (!dateEl) return;
    dateEl.addEventListener('input', function(e) {
        var v = e.target.value.replace(/\D/g, '');
        if (v.length >= 2) v = v.slice(0, 2) + '/' + v.slice(2);
        if (v.length >= 5) v = v.slice(0, 5) + '/' + v.slice(5, 9);
        e.target.value = v;
    });
});
</script>
@endsection
