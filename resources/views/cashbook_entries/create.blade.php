@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Add Entry - {{ $cashbook->name }}</h4>
        <a class="btn btn-outline-secondary" href="{{ route('cashbooks.show', $cashbook) }}">Back</a>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('cashbook_entries.store', $cashbook) }}">
                @csrf
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="entry_date">Date</label>
                        <input
                            class="form-control @error('entry_date') is-invalid @enderror"
                            id="entry_date"
                            name="entry_date"
                            type="text"
                            placeholder="dd/mm/yy"
                            maxlength="8"
                            value="{{ old('entry_date') }}"
                            required
                        >
                        @error('entry_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Enter date as dd/mm/yy (e.g., 25/12/23)</small>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="particulars">Particulars</label>
                    <input
                        class="form-control @error('particulars') is-invalid @enderror"
                        id="particulars"
                        name="particulars"
                        type="text"
                        value="{{ old('particulars') }}"
                        required
                    >
                    @error('particulars')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label" for="type">Type</label>
                        <select
                            class="form-select @error('type') is-invalid @enderror"
                            id="type"
                            name="type"
                            required
                        >
                            @php($selectedType = old('type', $type ?? 'both'))
                            <option value="both" @selected($selectedType === 'both')>Receipt and Payment</option>
                            <option value="receipt" @selected($selectedType === 'receipt')>Receipt</option>
                            <option value="payment" @selected($selectedType === 'payment')>Payment</option>
                        </select>
                        @error('type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label" for="cash_amount">Cash Amount</label>
                        <input
                            class="form-control @error('cash_amount') is-invalid @enderror"
                            id="cash_amount"
                            name="cash_amount"
                            type="number"
                            step="0.01"
                            min="0"
                            value="{{ old('cash_amount') }}"
                        >
                        @error('cash_amount')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label" for="bank_amount">Bank Amount</label>
                        <input
                            class="form-control @error('bank_amount') is-invalid @enderror"
                            id="bank_amount"
                            name="bank_amount"
                            type="number"
                            step="0.01"
                            min="0"
                            value="{{ old('bank_amount') }}"
                        >
                        @error('bank_amount')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="narration">Narration</label>
                    <textarea
                        class="form-control @error('narration') is-invalid @enderror"
                        id="narration"
                        name="narration"
                        rows="3"
                    >{{ old('narration') }}</textarea>
                    @error('narration')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text">Enter cash or bank amount.</div>
                </div>

                <div class="d-flex justify-content-end">
                    <button class="btn btn-primary" type="submit">Save Entry</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const dateInput = document.getElementById('entry_date');
    
    if (dateInput) {
        // Auto-format date input with slashes
        dateInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, ''); // Remove all non-digits
            
            // Add slashes automatically
            if (value.length >= 2) {
                value = value.substring(0, 2) + '/' + value.substring(2);
            }
            if (value.length >= 5) {
                value = value.substring(0, 5) + '/' + value.substring(5, 7);
            }
            
            e.target.value = value;
        });
        
        // Prevent typing beyond 8 characters (dd/mm/yy)
        dateInput.addEventListener('keydown', function(e) {
            if (e.target.value.length >= 8 && e.key !== 'Backspace' && e.key !== 'Delete' && !e.ctrlKey) {
                e.preventDefault();
            }
        });
    }
});
</script>
@endsection
