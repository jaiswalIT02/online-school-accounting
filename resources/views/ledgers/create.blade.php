@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Create Ledger</h4>
        <a class="btn btn-outline-secondary" href="{{ route('ledgers.index') }}">Back</a>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('ledgers.store') }}">
                @csrf
                @php($ledger = new \App\Models\Ledger())
                <div class="mb-3">
                    <label class="form-label" for="name">Ledger Name</label>
                    <select
                        class="form-select @error('name') is-invalid @enderror"
                        id="name"
                        name="name"
                        required
                    >
                        <option value="">-- Select Component --</option>
                        @foreach ($articles ?? [] as $article)
                            <option value="{{ $article->name }}" @selected(old('name', $ledger->name ?? '') == $article->name)>
                                {{ $article->name }} ({{ $article->acode }})
                            </option>
                        @endforeach
                    </select>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="opening_balance">Opening Balance</label>
                        <input
                            class="form-control @error('opening_balance') is-invalid @enderror"
                            id="opening_balance"
                            name="opening_balance"
                            type="number"
                            step="0.01"
                            min="0"
                            value="{{ old('opening_balance', $ledger->opening_balance ?? '0.00') }}"
                        >
                        @error('opening_balance')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="opening_balance_type">Opening Type</label>
                        <select
                            class="form-select @error('opening_balance_type') is-invalid @enderror"
                            id="opening_balance_type"
                            name="opening_balance_type"
                            required
                        >
                            @php($selectedType = old('opening_balance_type', $ledger->opening_balance_type ?? 'Dr'))
                            <option value="Dr" @selected($selectedType === 'Dr')>Dr</option>
                            <option value="Cr" @selected($selectedType === 'Cr')>Cr</option>
                        </select>
                        @error('opening_balance_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="description">Description</label>
                    <textarea
                        class="form-control @error('description') is-invalid @enderror"
                        id="description"
                        name="description"
                        rows="3"
                    >{{ old('description', $ledger->description ?? '') }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="d-flex justify-content-end">
                    <button class="btn btn-primary" type="submit">Save Ledger</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
