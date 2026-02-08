@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Import from Receipt & Payment - {{ $cashbook->name }}</h4>
        <a class="btn btn-outline-secondary" href="{{ route('cashbooks.show', $cashbook) }}">Back</a>
    </div>

    <div class="card">
        <div class="card-body">
            @if (session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <form method="POST" action="{{ route('cashbooks.processImport', $cashbook) }}">
                @csrf
                
                <div class="mb-3">
                    <label class="form-label" for="receipt_payment_account_id">Select Receipt & Payment Account</label>
                    <select
                        class="form-select @error('receipt_payment_account_id') is-invalid @enderror"
                        id="receipt_payment_account_id"
                        name="receipt_payment_account_id"
                        required
                    >
                        <option value="">-- Select Account --</option>
                        @foreach ($accounts ?? [] as $account)
                            <option value="{{ $account->id }}">
                                {{ $account->header_title ?? $account->name }} - {{ $account->period_from->format('d M Y') }} to {{ $account->period_to->format('d M Y') }}
                            </option>
                        @endforeach
                    </select>
                    @error('receipt_payment_account_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">Select the Receipt & Payment account to import entries from.</small>
                </div>

                <div class="alert alert-info">
                    <strong>Note:</strong> This will import all entries from the selected Receipt & Payment account. 
                    Entries will be created as cashbook entries with amounts in the bank column.
                </div>

                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary">Import Entries</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
