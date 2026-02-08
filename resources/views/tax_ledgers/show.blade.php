@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h4 class="mb-1">Tax Ledger A/C of {{ $article->name }}</h4>
            <div class="text-muted">
                Opening Balance: 0.00 Cr
            </div>
        </div>
        <div class="d-flex gap-2">
            <a class="btn btn-outline-secondary" href="{{ route('tax_ledgers.index') }}">Back to Tax Ledgers</a>
            <a class="btn btn-outline-primary" href="{{ route('tax_ledgers.print', $article->id) }}" target="_blank">Print</a>
        </div>
    </div>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 14%">Month &amp; Date</th>
                            <th>Particulars</th>
                            <th style="width: 10%">Folio No</th>
                            <th style="width: 12%" class="text-end">Debit Rs.</th>
                            <th style="width: 12%" class="text-end">Credit Rs.</th>
                            <th style="width: 8%" class="text-center">Dr/Cr</th>
                            <th style="width: 12%" class="text-end">Balance Rs.</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rows as $row)
                            @php($entry = $row['entry'])
                            <tr>
                                <td>{{ $entry->entry_date->format('d/m/Y') }}</td>
                                <td>
                                    <div class="fw-semibold">{{ $entry->particulars }}</div>
                                    @if ($entry->narration)
                                        <div class="text-muted small">{{ $entry->narration }}</div>
                                    @endif
                                </td>
                                <td>-</td>
                                <td class="text-end">
                                    {{ $entry->debit > 0 ? number_format($entry->debit, 2) : '-' }}
                                </td>
                                <td class="text-end">
                                    {{ $entry->credit > 0 ? number_format($entry->credit, 2) : '-' }}
                                </td>
                                <td class="text-center">{{ $row['balance_type'] }}</td>
                                <td class="text-end">{{ number_format($row['balance'], 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">No tax entries found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <th colspan="3" class="text-end">Total</th>
                            <th class="text-end">{{ number_format($totalDebit ?? 0, 2) }}</th>
                            <th class="text-end">{{ number_format($totalCredit ?? 0, 2) }}</th>
                            <th class="text-center">{{ $closingBalanceType ?? 'Cr' }}</th>
                            <th class="text-end">{{ number_format($closingBalance ?? 0, 2) }}</th>
                        </tr>
                        <tr>
                            <th colspan="6" class="text-end">Closing Balance</th>
                            <th class="text-end">{{ number_format($closingBalance ?? 0, 2) }} {{ $closingBalanceType ?? 'Cr' }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
