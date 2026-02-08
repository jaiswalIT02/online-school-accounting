@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Extracted Data from PDF</h4>
        <a class="btn btn-outline-secondary" href="{{ route('pdf_extract.index') }}">Upload Another</a>
    </div>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    @if (isset($metadata) && (!empty($metadata['order_id']) || !empty($metadata['generated_on'])))
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Payment Advice Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    @if (!empty($metadata['order_id']))
                        <div class="col-md-6 mb-2">
                            <strong>Order ID:</strong> {{ $metadata['order_id'] }}
                        </div>
                    @endif
                    @if (!empty($metadata['generated_on']))
                        <div class="col-md-6 mb-2">
                            <strong>PPA Date (Generated On):</strong> {{ $metadata['generated_on'] }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Extracted Beneficiaries ({{ count($extractedData) }})</h5>
        </div>
        <div class="card-body">
            @if (count($extractedData) > 0)
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>SI No</th>
                                <th>Txn ID</th>
                                <th>Name</th>
                                <th>Name in PFMS</th>
                                <th>Account No</th>
                                <th>IFSC</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($extractedData as $index => $data)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $data['txn_id'] ?? '-' }}</td>
                                    <td>{{ $data['name'] ?? '-' }}</td>
                                    <td>{{ $data['name_in_pfms'] ?? '-' }}</td>
                                    <td>{{ $data['account_no'] ?? '-' }}</td>
                                    <td>{{ $data['ifsc'] ?? '-' }}</td>
                                    <td class="text-end">{{ number_format($data['amount'] ?? 0, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="6" class="text-end">Total Amount:</th>
                                <th class="text-end">{{ number_format(collect($extractedData)->sum('amount'), 2) }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <form method="POST" action="{{ route('pdf_extract.save') }}" class="mt-3">
                    @csrf
                    <input type="hidden" name="extracted_data" value="{{ json_encode($extractedData) }}">
                    
                    <div class="mb-3">
                        <label class="form-label" for="receipt_payment_account_id">Select Receipt & Payment Account to Save</label>
                        <select
                            class="form-select @error('receipt_payment_account_id') is-invalid @enderror"
                            id="receipt_payment_account_id"
                            name="receipt_payment_account_id"
                            required
                        >
                            <option value="">-- Select Account --</option>
                            @foreach ($accounts ?? [] as $account)
                                <option value="{{ $account->id }}" @selected(($selectedAccountId ?? old('receipt_payment_account_id')) == $account->id)>
                                    {{ $account->header_title ?? $account->name }} - {{ $account->period_from->format('d M Y') }} to {{ $account->period_to->format('d M Y') }}
                                </option>
                            @endforeach
                        </select>
                        @error('receipt_payment_account_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-success">Save to Account</button>
                    </div>
                </form>
            @else
                <div class="alert alert-warning">
                    No vendor data could be extracted from the PDF. Please check if the PDF contains selectable text.
                </div>
            @endif
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Raw PDF Text (for debugging)</h5>
        </div>
        <div class="card-body">
            <pre class="bg-light p-3 rounded" style="max-height: 400px; overflow-y: auto; font-size: 11px;">{{ $rawText }}</pre>
        </div>
    </div>
</div>
@endsection
