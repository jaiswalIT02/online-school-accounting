@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Edit Entry - {{ $receipt_payment->name }}</h4>
        <a class="btn btn-outline-secondary" href="{{ route('receipt_payments.show', $receipt_payment) }}">Back</a>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('receipt_payment_entries.update', $entry) }}">
                @csrf
                @method('PUT')
                @php
                    // Get current reference for component (article)
                    $currentArticleRef = '';
                    if ($entry->article_id && $entry->article) {
                        $currentArticleRef = $entry->article->name . '||' . $entry->article->acode;
                    }
                    
                    // Get current reference for vendor (beneficiary)
                    $currentBeneficiaryRef = '';
                    if ($entry->beneficiary_id) {
                        // Load beneficiary relationship if not already loaded
                        if (!$entry->relationLoaded('beneficiary')) {
                            $entry->load('beneficiary');
                        }
                        // Use beneficiary's name and acode directly
                        if ($entry->beneficiary) {
                            $currentBeneficiaryRef = $entry->beneficiary->name . '||' . ($entry->beneficiary->acode ?? '');
                        }
                    }
                @endphp
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label" for="type">Type</label>
                        <select
                            class="form-select @error('type') is-invalid @enderror"
                            id="type"
                            name="type"
                            required
                        >
                            @php
                                // Check if there's a corresponding entry using pair_id
                                $hasCorresponding = $entry->pair_id || $receipt_payment->entries()
                                    ->where('pair_id', $entry->id)
                                    ->exists();
                                $defaultType = $hasCorresponding ? 'both' : $entry->type;
                                $selectedType = old('type', $defaultType);
                            @endphp
                            <option value="both" @selected($selectedType === 'both')>Payment and Receipt</option>
                            <!-- <option value="receipt" @selected($selectedType === 'receipt')>Receipt</option>
                            <option value="payment" @selected($selectedType === 'payment')>Payment</option> -->
                        </select>
                        @error('type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label" for="article_ref">Component</label>
                        <select
                            class="form-select @error('article_ref') is-invalid @enderror"
                            id="article_ref"
                            name="article_ref"
                        >
                            <option value="">Select component</option>
                            @foreach ($articles as $article)
                                @php($value = $article->name . '||' . $article->acode)
                                <option
                                    value="{{ $value }}"
                                    @selected(old('article_ref', $currentArticleRef) == $value)
                                >
                                    {{ $article->name }} ({{ $article->acode }})
                                </option>
                            @endforeach
                        </select>
                        @error('article_ref')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label" for="beneficiary_ref">Vendor</label>
                        <select
                            class="form-select @error('beneficiary_ref') is-invalid @enderror"
                            id="beneficiary_ref"
                            name="beneficiary_ref"
                        >
                            <option value="">Select vendor</option>
                            @foreach ($beneficiaries as $beneficiary)
                                @php($value = $beneficiary->name . '||' . ($beneficiary->acode ?? ''))
                                <option
                                    value="{{ $value }}"
                                    @selected(old('beneficiary_ref', $currentBeneficiaryRef) == $value)
                                >
                                    {{ $beneficiary->name }} ({{ $beneficiary->acode ?? '-' }})
                                </option>
                            @endforeach
                        </select>
                        @error('beneficiary_ref')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label" for="date">Date</label>
                        <input
                            class="form-control @error('date') is-invalid @enderror"
                            id="date"
                            name="date"
                            type="text"
                            placeholder="dd/mm/yyyy"
                            value="{{ old('date', $entry->date) }}"
                            pattern="\d{2}/\d{2}/\d{4}"
                        >
                        @error('date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Format: dd/mm/yyyy (e.g., 12/05/2025)</div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label" for="amount">Amount</label>
                        <input
                            class="form-control @error('amount') is-invalid @enderror"
                            id="amount"
                            name="amount"
                            type="number"
                            step="0.01"
                            min="0"
                            value="{{ old('amount', $entry->amount) }}"
                            required
                        >
                        @error('amount')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label" for="remarks">Remarks</label>
                        <input
                            class="form-control @error('remarks') is-invalid @enderror"
                            id="remarks"
                            name="remarks"
                            type="text"
                            value="{{ old('remarks', $entry->remarks) }}" readonly
                        >
                        @error('remarks')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Component is required. Vendor is optional.</div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label" for="tax_amount">Tax Amount</label>
                        <input
                            class="form-control @error('tax_amount') is-invalid @enderror"
                            id="tax_amount"
                            name="tax_amount"
                            type="number"
                            step="0.01"
                            min="0"
                            value="{{ old('tax_amount', $entry->tax_amount) }}"
                        >
                        @error('tax_amount')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label" for="tax_for">Tax For</label>
                        <select
                            class="form-select @error('tax_for') is-invalid @enderror"
                            id="tax_for"
                            name="tax_for"
                        >
                            <option value="">Select Tax Type</option>
                            <option value="tds" {{ old('tax_for', $entry->tax_for) == 'tds' ? 'selected' : '' }}>TDS</option>
                            <option value="pTax" {{ old('tax_for', $entry->tax_for) == 'pTax' ? 'selected' : '' }}>P Tax</option>
                        </select>
                        @error('tax_for')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label" for="tax_type">Tax Type</label>
                        <select
                            class="form-select @error('tax_type') is-invalid @enderror"
                            id="tax_type"
                            name="tax_type"
                        >
                            <!-- <option value="">Select Type</option> -->
                            <option value="cr" {{ old('tax_type', $entry->tax_type) == 'cr' ? 'selected' : '' }}>Cr</option>
                            <option value="dr" {{ old('tax_type', $entry->tax_type) == 'dr' ? 'selected' : '' }}>Dr</option>
                        </select>
                        @error('tax_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="tax_remark">Tax Remark</label>
                        <input
                            class="form-control @error('tax_remark') is-invalid @enderror"
                            id="tax_remark"
                            name="tax_remark"
                            type="text"
                            value="{{ old('tax_remark', $entry->tax_remark) }}"
                        >
                        @error('tax_remark')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="d-flex justify-content-end">
                    <button class="btn btn-primary" type="submit">Update Entry</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Auto-format date input to dd/mm/yyyy
document.getElementById('date')?.addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, ''); // Remove non-digits
    if (value.length >= 2) {
        value = value.substring(0, 2) + '/' + value.substring(2);
    }
    if (value.length >= 5) {
        value = value.substring(0, 5) + '/' + value.substring(5, 9);
    }
    e.target.value = value;
});
</script>
@endsection
