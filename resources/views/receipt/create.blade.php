@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Add Entry - Receipt</h4>
        <a class="btn btn-outline-secondary" href="">Back</a>
    </div>

    @if (session('status'))
    <div class="alert alert-success">{{ session('status') }}</div>
    @endif
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('receipt.store', $receipt_payment) }}">
                @csrf
                <div class="row">
                    <input type="hidden" value="receipt" name="type">

                    <div class="col-md-4 mb-3">
                        <label class="form-label" for="article_id">Component</label>
                        <select
                            class="form-select @error('article_ref') is-invalid @enderror"
                            id="article_ref"
                            name="article_ref">
                            <option value="">Select component</option>
                            @foreach ($articles as $article)
                            @php($value = $article->name . '||' . $article->acode)
                            <option value="{{ $value }}" @selected(old('article_ref')==$value)>
                                {{ $article->name }} ({{ $article->acode }})
                            </option>
                            @endforeach
                        </select>
                        @error('article_ref')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label" for="beneficiary_id">Vendor</label>
                        <select
                            class="form-select @error('beneficiary_ref') is-invalid @enderror"
                            id="beneficiary_ref"
                            name="beneficiary_ref">
                            <option value="">Select vendor</option>
                            @foreach ($beneficiaries as $beneficiary)
                            @php($value = $beneficiary->name . '||' . ($beneficiary->acode ?? ''))
                            <option value="{{ $value }}" @selected(old('beneficiary_ref')==$value)>
                                {{ $beneficiary->name }} ({{ $beneficiary->acode ?? '-' }})
                            </option>
                            @endforeach
                        </select>
                        @error('beneficiary_ref')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label" for="date">Date</label>
                        <input
                            class="form-control @error('date') is-invalid @enderror"
                            id="date"
                            name="date"
                            type="text"
                            placeholder="dd/mm/yyyy"
                            value="{{ old('date') }}"
                            pattern="\d{2}/\d{2}/\d{4}">
                        @error('date')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Format: dd/mm/yyyy (e.g., 12/05/2025)</div>
                    </div>
                </div>

                <div class="row">

                    <div class="col-md-4 mb-3">
                        <label class="form-label" for="amount">Amount</label>
                        <input
                            class="form-control @error('amount') is-invalid @enderror"
                            id="amount"
                            name="amount"
                            type="number"
                            step="0.01"
                            min="0"
                            value="{{ old('amount') }}"
                            required>
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
                            value="{{ old('remarks') }}">
                        @error('remarks')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Component is required. Vendor is optional.</div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label" for="tax_amount">Tax Amount</label>
                        <input
                            class="form-control @error('tax_amount') is-invalid @enderror"
                            id="tax_amount"
                            name="tax_amount"
                            type="number"
                            step="0.01"
                            min="0"
                            value="{{ old('tax_amount') }}">
                        @error('tax_amount')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">

                    <div class="col-md-3 mb-3">
                        <label class="form-label" for="tax_for">Tax For</label>
                        <select
                            class="form-select @error('tax_for') is-invalid @enderror"
                            id="tax_for"
                            name="tax_for">
                            <option value="">Select Tax Type</option>
                            <option value="tds" {{ old('tax_for') == 'tds' ? 'selected' : '' }}>TDS</option>
                            <option value="pTax" {{ old('tax_for') == 'pTax' ? 'selected' : '' }}>PTax</option>
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
                            name="tax_type">
                            <option value="">Select Type</option>
                            <option value="dr" {{ old('tax_type') == 'dr' ? 'selected' : '' }}>Dr</option>
                            <option value="cr" {{ old('tax_type') == 'cr' ? 'selected' : '' }}>Cr</option>
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
                            value="{{ old('tax_remark') }}">
                        @error('tax_remark')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="d-flex justify-content-end">
                    <button class="btn btn-primary" type="submit">Save Entry</button>
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