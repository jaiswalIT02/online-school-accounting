@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Create Stock Ledger</h4>
        <a class="btn btn-outline-secondary" href="{{ route('stock_ledgers.index') }}">Back</a>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('stock_ledgers.store') }}">
                @csrf

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="ledger_name">Ledger Name</label>
                        <input type="text" class="form-control @error('ledger_name') is-invalid @enderror" id="ledger_name" name="ledger_name" value="{{ old('ledger_name') }}" required>
                        @error('ledger_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="item_id">Item</label>
                        <select class="form-select @error('item_id') is-invalid @enderror" id="item_id" name="item_id" required>
                            <option value="">Select Item</option>
                            @foreach ($items as $item)
                                <option value="{{ $item->id }}" @selected(old('item_id') == $item->id)>{{ $item->name }} @if($item->item_code)({{ $item->item_code }})@endif</option>
                            @endforeach
                        </select>
                        @error('item_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label" for="date_from">Date From (Session Start)</label>
                        <input type="date" class="form-control @error('date_from') is-invalid @enderror" id="date_from" name="date_from" value="{{ old('date_from', $period['date_from']->format('Y-m-d') ?? '') }}" required>
                        @error('date_from')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label" for="date_to">Date To (Session End)</label>
                        <input type="date" class="form-control @error('date_to') is-invalid @enderror" id="date_to" name="date_to" value="{{ old('date_to', $period['date_to']->format('Y-m-d') ?? '') }}" required>
                        @error('date_to')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label" for="opening_type">Opening Type</label>
                        <select class="form-select @error('opening_type') is-invalid @enderror" id="opening_type" name="opening_type" required>
                            <option value="Dr" @selected(old('opening_type', 'Dr') === 'Dr')>Dr</option>
                            <option value="Cr" @selected(old('opening_type') === 'Cr')>Cr</option>
                        </select>
                        @error('opening_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label" for="opening_balance">Opening Balance</label>
                        <input type="number" step="0.01" min="0" class="form-control @error('opening_balance') is-invalid @enderror" id="opening_balance" name="opening_balance" value="{{ old('opening_balance', 0) }}">
                        @error('opening_balance')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-8 mb-3">
                        <label class="form-label" for="description">Description</label>
                        <input type="text" class="form-control @error('description') is-invalid @enderror" id="description" name="description" value="{{ old('description') }}">
                        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Create Stock Ledger</button>
                <a href="{{ route('stock_ledgers.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>
@endsection
