@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Stock Ledgers</h4>
        <div class="d-flex gap-2">
            <a class="btn btn-outline-secondary" href="{{ route('stocks.index') }}">Stocks</a>
            <form method="POST" action="{{ route('stock_ledgers.create_all_from_items') }}" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-info" onclick="return confirm('Create stock ledgers for items that do not have a ledger yet?')">
                    <i class="fas fa-plus"></i> Create Ledgers for New Items Only
                </button>
            </form>
            <a class="btn btn-primary" href="{{ route('stock_ledgers.create') }}">Create Stock Ledger</a>
        </div>
    </div>

    @if (session('status'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Search bar --}}
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('stock_ledgers.index') }}" class="d-flex gap-2 align-items-end">
                <div class="flex-grow-1">
                    <label class="form-label small">Search</label>
                    <input type="text"
                           class="form-control"
                           name="search"
                           placeholder="Search by ledger name, item name, item code, or description..."
                           value="{{ request('search') }}">
                </div>
                <div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Search
                    </button>
                </div>
                @if(request('search'))
                    <div>
                        <a href="{{ route('stock_ledgers.index') }}" class="btn btn-outline-secondary">Clear</a>
                    </div>
                @endif
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Ledger Name</th>
                            <th>Item</th>
                            <th>Date From</th>
                            <th>Date To</th>
                            <th class="text-end">Opening Balance</th>
                            <th>Description</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($stockLedgers as $ledger)
                            <tr>
                                <td>{{ $ledger->ledger_name }}</td>
                                <td>{{ $ledger->item->name ?? '-' }}</td>
                                <td>{{ $ledger->date_from->format('d/m/Y') }}</td>
                                <td>{{ $ledger->date_to->format('d/m/Y') }}</td>
                                <td class="text-end">{{ number_format($ledger->opening_balance, 2) }} {{ $ledger->opening_type }}</td>
                                <td>{{ Str::limit($ledger->description ?? '', 30) }}</td>
                                <td class="text-end">
                                    <a class="btn btn-sm btn-outline-primary" href="{{ route('stock_ledgers.show', $ledger) }}">View</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">No stock ledgers yet. Create one to get started.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-3">
        {{ $stockLedgers->links() }}
    </div>
</div>
@endsection
