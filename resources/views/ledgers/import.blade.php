@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Import from Receipt & Payment - {{ $ledger->name }}</h4>
        <a class="btn btn-outline-secondary" href="{{ route('ledgers.show', $ledger) }}">Back</a>
    </div>

    <div class="card">
        <div class="card-body">
            @if (session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <form method="POST" action="{{ route('ledgers.processImport', $ledger) }}">
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
                    <small class="form-text text-muted">Select the Receipt & Payment account to import entries from. Only entries matching the ledger name will be imported.</small>
                </div>

                <div class="alert alert-info">
                    <strong>Note:</strong> This will import entries from the selected Receipt & Payment account where the particular name matches "{{ $ledger->name }}".
                    Receipt entries will be imported as Debit, and Payment entries will be imported as Credit.
                </div>

                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary">Import Entries</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
