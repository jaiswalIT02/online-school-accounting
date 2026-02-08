@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Extract Data from PDF</h4>
        <a class="btn btn-outline-secondary" href="{{ route('receipt_payments.index') }}">Back</a>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('pdf_extract.extract') }}" enctype="multipart/form-data">
                @csrf
                
                <div class="mb-3">
                    <label class="form-label" for="pdf_file">Upload PDF File</label>
                    <input
                        type="file"
                        class="form-control @error('pdf_file') is-invalid @enderror"
                        id="pdf_file"
                        name="pdf_file"
                        accept=".pdf"
                        required
                    >
                    <small class="form-text text-muted">Maximum file size: 10MB. PDF must contain selectable text.</small>
                    @error('pdf_file')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label" for="receipt_payment_account_id">Select Receipt & Payment Account (Optional)</label>
                    <select
                        class="form-select @error('receipt_payment_account_id') is-invalid @enderror"
                        id="receipt_payment_account_id"
                        name="receipt_payment_account_id"
                    >
                        <option value="">-- Select Account (Optional) --</option>
                        @foreach ($accounts ?? [] as $account)
                            <option value="{{ $account->id }}" @selected(($selectedAccountId ?? old('receipt_payment_account_id')) == $account->id)>
                                {{ $account->header_title ?? $account->name }} - {{ $account->period_from->format('d M Y') }} to {{ $account->period_to->format('d M Y') }}
                            </option>
                        @endforeach
                    </select>
                    <small class="form-text text-muted">If selected, extracted data will be automatically saved to this account.</small>
                    @error('receipt_payment_account_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="alert alert-info">
                    <strong>Note:</strong> This tool extracts vendor data from bank advice PDFs. It looks for:
                    <ul class="mb-0">
                        <li>Vendor names</li>
                        <li>Account numbers</li>
                        <li>IFSC codes</li>
                        <li>Amounts</li>
                    </ul>
                </div>

                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary">Extract Data</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
