@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h4 class="mb-1">Stock Ledger: {{ $stock_ledger->ledger_name }}</h4>
            <div class="text-muted">
                Item: {{ $stock_ledger->item->name ?? '-' }} &nbsp;|&nbsp;
                Period: {{ $stock_ledger->date_from->format('d/m/Y') }} to {{ $stock_ledger->date_to->format('d/m/Y') }} &nbsp;|&nbsp;
                Opening Balance: {{ number_format($stock_ledger->opening_balance, 2) }} {{ $stock_ledger->opening_type }}
            </div>
            @if($stock_ledger->description)
                <div class="text-muted small mt-1">{{ $stock_ledger->description }}</div>
            @endif
        </div>
        <div class="d-flex gap-2">
            <a class="btn btn-outline-secondary" href="{{ route('stock_ledgers.index') }}">Back to Stock Ledgers</a>
            <a class="btn btn-success" href="{{ route('stock_ledgers.print', $stock_ledger) }}" target="_blank">
                <i class="fas fa-print"></i> Print
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-light fw-semibold">Stock Register</div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 10%">Date</th>
                            <th style="width: 18%">Name of Components</th>
                            <th style="width: 6%" class="text-center">NO</th>
                            <th style="width: 14%" class="text-end">Receipt / Unit</th>
                            <th style="width: 14%" class="text-end">Issued / Unit</th>
                            <th style="width: 12%" class="text-end">Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>{{ $stock_ledger->date_from->format('d/m/Y') }}</td>
                            <td class="fw-semibold">Opening Balance</td>
                            <td class="text-center">-</td>
                            <td class="text-end">-</td>
                            <td class="text-end">-</td>
                            <td class="text-end">{{ number_format($stock_ledger->opening_balance, 2) }} {{ $stock_ledger->item->unit ?? 'kg' }}</td>
                        </tr>
                        @forelse ($rows as $row)
                            @php($stock = $row['stock'])
                            <tr>
                                <td>{{ $stock->date->format('d/m/Y') }}</td>
                                <td>{{ $stock_ledger->item->name ?? '-' }}</td>
                                <td class="text-center">{{ $stock->number > 0 ? number_format($stock->number, 2) : '-' }}</td>
                                <td class="text-end">
                                    @if ($stock->stock_type === 'Receipt')
                                        {{ number_format($stock->stock_amount ?? $stock->number, 2) }} {{ $stock_ledger->item->unit ?? '' }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if ($stock->stock_type === 'Issued')
                                        {{ number_format($stock->stock_amount ?? $stock->number, 2) }} {{ $stock_ledger->item->unit ?? '' }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="text-end">{{ number_format($row['running_balance'], 2) }} {{ $stock_ledger->item->unit ?? 'kg' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-3">No stock entries in this period.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($stocks->isNotEmpty())
                    <tfoot class="table-light">
                        <tr>
                            <th colspan="3" class="text-end">Total</th>
                            <th class="text-end">{{ number_format($totalReceipt ?? 0, 2) }}</th>
                            <th class="text-end">{{ number_format($totalIssued ?? 0, 2) }}</th>
                            <th class="text-end">{{ number_format($closingBalance ?? 0, 2) }} {{ $stock_ledger->item->unit ?? 'kg' }}</th>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
