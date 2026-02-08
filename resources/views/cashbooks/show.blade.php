@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h4 class="mb-1">{{ $cashbook->name }}</h4>
            <div class="text-muted">For the month of {{ $cashbook->period_month }} {{ $cashbook->period_year }}</div>
        </div>
        <div class="d-flex gap-2">
            <a class="btn btn-outline-secondary" href="{{ route('cashbooks.index') }}">Back</a>
            <a class="btn btn-outline-secondary" href="{{ route('cashbooks.edit', $cashbook) }}">Edit</a>
            <a class="btn btn-outline-primary" href="{{ route('cashbooks.print', $cashbook) }}" target="_blank">Print</a>
            <form method="POST" action="{{ route('cashbooks.create_all_months') }}" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-success" onclick="return confirm('This will create 12 cashbooks from April 2025 to March 2026 (financial year). Continue?')">Create 12 Months Cashbooks</button>
            </form>
            <!-- <a class="btn btn-success" href="{{ route('cashbooks.import', $cashbook) }}" onclick="return confirm('Import entries from Receipt & Payment? This will create cashbook entries for matching transactions in the same month.')">Import from R&P</a>
            <a class="btn btn-primary" href="{{ route('cashbook_entries.create', $cashbook) }}?type=both">Add Entry</a> -->
        </div>
    </div>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    @php
        // Receipt totals (including opening balance)
        $receiptTotalCash = $opening['cash'] + $receiptTotals['cash'];
        $receiptTotalBank = $opening['bank'] + $receiptTotals['bank'];
        $receiptTotal = $receiptTotalCash + $receiptTotalBank;
    @endphp

    <style>
        .cashbook-row {
            display: flex;
            align-items: stretch;
        }
        .cashbook-card {
            display: flex;
            flex-direction: column;
            height: 100%;
        }
        .cashbook-card .card-body {
            display: flex;
            flex-direction: column;
            padding: 0 !important;
            flex: 1;
            min-height: 600px;
        }
        .cashbook-card .table-responsive {
            display: flex;
            flex-direction: column;
            flex: 1;
            overflow: hidden;
        }
        .cashbook-card table {
            display: flex;
            flex-direction: column;
            height: 100%;
            margin: 0;
        }
        .cashbook-card thead {
            flex-shrink: 0;
            display: table;
            width: 100%;
            table-layout: fixed;
        }
        .cashbook-card tbody {
            flex: 1;
            overflow-y: auto;
            display: block;
            min-height: 0;
        }
        .cashbook-card tbody tr {
            display: table;
            width: 100%;
            table-layout: fixed;
        }
        .cashbook-card tfoot {
            flex-shrink: 0;
            display: table;
            width: 100%;
            table-layout: fixed;
            background-color: #f8f9fa;
        }
    </style>

    <div class="row g-3 cashbook-row">
        <div class="col-lg-6">
            <div class="card cashbook-card">
                <div class="card-header bg-light fw-semibold">Receipts</div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 14%">Date</th>
                                    <th style="width: 50%">Particulars</th>
                                    <th style="width: 8%" class="text-end">Cash</th>
                                    <th style="width: 14%" class="text-end">Bank</th>
                                    <th style="width: 14%" class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td style="width: 14%">
                                        @php
                                            // Get first day of the cashbook's month
                                            $monthMap = [
                                                'january' => 1, 'jan' => 1, '1' => 1, '01' => 1,
                                                'february' => 2, 'feb' => 2, '2' => 2, '02' => 2,
                                                'march' => 3, 'mar' => 3, '3' => 3, '03' => 3,
                                                'april' => 4, 'apr' => 4, '4' => 4, '04' => 4,
                                                'may' => 5, '5' => 5, '05' => 5,
                                                'june' => 6, 'jun' => 6, '6' => 6, '06' => 6,
                                                'july' => 7, 'jul' => 7, '7' => 7, '07' => 7,
                                                'august' => 8, 'aug' => 8, '8' => 8, '08' => 8,
                                                'september' => 9, 'sep' => 9, 'sept' => 9, '9' => 9, '09' => 9,
                                                'october' => 10, 'oct' => 10, '10' => 10,
                                                'november' => 11, 'nov' => 11, '11' => 11,
                                                'december' => 12, 'dec' => 12, '12' => 12,
                                            ];
                                            $monthNumber = $monthMap[strtolower(trim($cashbook->period_month))] ?? 1;
                                            $firstDayOfMonth = \Carbon\Carbon::create($cashbook->period_year, $monthNumber, 1);
                                        @endphp
                                        {{ $firstDayOfMonth->format('d/m/Y') }}
                                    </td>
                                    <td style="width: 50%" class="fw-semibold">Opening Balance</td>
                                    <td style="width: 8%" class="text-end">{{ number_format($opening['cash'], 2) }}</td>
                                    <td style="width: 14%" class="text-end">{{ number_format($opening['bank'], 2) }}</td>
                                    <td style="width: 14%" class="text-end">{{ number_format($opening['total'], 2) }}</td>
                                </tr>
                                @forelse ($receipts as $entry)
                                    <tr>
                                        <td style="width: 14%">{{ $entry->entry_date->format('d/m/Y') }}</td>
                                        <td style="width: 50%">
                                            <div class="fw-semibold">{{ $entry->particulars }}</div>
                                            @if ($entry->narration)
                                                <div class="text-muted small">{{ $entry->narration }}</div>
                                            @endif
                                            @if ($entry->tax_amount || $entry->tax_for || $entry->tax_remark)
                                                <div class="mt-1 small fw-bold">
                                                    @if ($entry->tax_for)
                                                        {{ strtoupper($entry->tax_for) }}
                                                        @php
                                                            $bracketContent = [];
                                                            if ($entry->tax_amount) {
                                                                $bracketContent[] = 'Tax: ' . number_format($entry->tax_amount, 2);
                                                            }
                                                            if ($entry->tax_remark) {
                                                                $bracketContent[] = $entry->tax_remark;
                                                            }
                                                        @endphp
                                                        @if (!empty($bracketContent))
                                                            ({{ implode(', ', $bracketContent) }})
                                                        @endif
                                                    @else
                                                        @if ($entry->tax_amount)
                                                            Tax: {{ number_format($entry->tax_amount, 2) }}
                                                            @if ($entry->tax_remark)
                                                                , {{ $entry->tax_remark }}
                                                            @endif
                                                        @else
                                                            @if ($entry->tax_remark)
                                                                {{ $entry->tax_remark }}
                                                            @endif
                                                        @endif
                                                    @endif
                                                </div>
                                            @endif
                                            {{-- Edit and Delete buttons hidden --}}
                                        </td>
                                        <td style="width: 8%" class="text-end">{{ number_format($entry->cash_amount, 2) }}</td>
                                        <td style="width: 14%"  class="text-end">{{ number_format($entry->bank_amount, 2) }}</td>
                                        <td style="width: 14%" class="text-end">{{ number_format($entry->cash_amount + $entry->bank_amount, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">No receipts yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th style="width: 14%" colspan="2">-</th>
                                    <th style="width: 50%" colspan="2">Total</th>
                                    <th style="width: 8%" class="text-end">{{ number_format($receiptTotalCash, 2) }}</th>
                                    <th style="width: 14%" class="text-end">{{ number_format($receiptTotalBank, 2) }}</th>
                                    <th style="width: 14%" class="text-end">{{ number_format($receiptTotal, 2) }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card cashbook-card">
                <div class="card-header bg-light fw-semibold">Payments</div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 14%">Date</th>
                                    <th>Particulars</th>
                                    <th style="width: 8%" class="text-end">Cash</th>
                                    <th style="width: 14%" class="text-end">Bank</th>
                                    <th style="width: 14%" class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                            <tr>
                                    <td style="width: 14%">   .</td>
                                    <td style="width: 50%" class="fw-semibold">     </td>
                                       
                                </tr>
                                @forelse ($payments as $entry)
                                    <tr>
                                        <td style="width: 14%">{{ $entry->entry_date->format('d/m/Y') }}</td>
                                        <td style="width: 50%">
                                            <div class="fw-semibold">{{ $entry->particulars }}</div>
                                            @if ($entry->narration)
                                                <div class="text-muted small">{{ $entry->narration }}</div>
                                            @endif
                                            @if ($entry->tax_amount || $entry->tax_for || $entry->tax_remark)
                                                <div class="mt-1 small fw-bold">
                                                    @if ($entry->tax_for)
                                                        {{ strtoupper($entry->tax_for) }}
                                                        @php
                                                            $bracketContent = [];
                                                            if ($entry->tax_amount) {
                                                                $bracketContent[] = 'Tax: ' . number_format($entry->tax_amount, 2);
                                                            }
                                                            if ($entry->tax_remark) {
                                                                $bracketContent[] = $entry->tax_remark;
                                                            }
                                                        @endphp
                                                        @if (!empty($bracketContent))
                                                            ({{ implode(', ', $bracketContent) }})
                                                        @endif
                                                    @else
                                                        @if ($entry->tax_amount)
                                                            Tax: {{ number_format($entry->tax_amount, 2) }}
                                                            @if ($entry->tax_remark)
                                                                , {{ $entry->tax_remark }}
                                                            @endif
                                                        @else
                                                            @if ($entry->tax_remark)
                                                                {{ $entry->tax_remark }}
                                                            @endif
                                                        @endif
                                                    @endif
                                                </div>
                                            @endif
                                            {{-- Edit and Delete buttons hidden --}}
                                        </td>
                                        <td style="width: 8%" class="text-end">{{ number_format($entry->cash_amount, 2) }}</td>
                                        <td style="width: 14%" class="text-end">{{ number_format($entry->bank_amount, 2) }}</td>
                                        <td style="width: 14%" class="text-end">{{ number_format($entry->cash_amount + $entry->bank_amount, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">No payments yet.</td>
                                    </tr>
                                @endforelse
                                {{-- First Total Row: Show Payment Total --}}
                                <tr>
                                    <th style="width: 14%" colspan="2">-</th>
                                    <th style="width: 50%" colspan="2">Total</th>
                                    <th style="width: 8%" class="text-end">{{ number_format($paymentTotals['cash'], 2) }}</th>
                                    <th style="width: 14%" class="text-end">{{ number_format($paymentTotals['bank'], 2) }}</th>
                                    <th style="width: 14%" class="text-end">{{ number_format($paymentTotals['total'], 2) }}</th>
                                </tr>
                                {{-- Closing Balance: Show Difference (Receipts - Payments) --}}
                                <tr>
                                    <td style="width: 14%">-</td>
                                    <td style="width: 50%" class="fw-semibold">Closing Balance</td>
                                    <td style="width: 8%" class="text-end">{{ number_format($receiptTotals['cash'] - $paymentTotals['cash'], 2) }}</td>
                                    <td style="width: 14%" class="text-end">{{ number_format($receiptTotals['bank'] - $paymentTotals['bank'], 2) }}</td>
                                    <td style="width: 14%" class="text-end">{{ number_format($receiptTotals['total'] - $paymentTotals['total'], 2) }}</td>
                                </tr>
                            </tbody>
                            <tfoot class="table-light">
                                {{-- Last Total Row: Show Receipt Total --}}
                                <tr>
                                    <th style="width: 14%" colspan="2">-</th>
                                    <th style="width: 50%" colspan="2">Total</th>
                                    <th style="width: 8%" class="text-end">{{ number_format($receiptTotals['cash'], 2) }}</th>
                                    <th style="width: 14%" class="text-end">{{ number_format($receiptTotals['bank'], 2) }}</th>
                                    <th style="width: 14%" class="text-end">{{ number_format($receiptTotals['total'], 2) }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Ensure both cards have equal heights and totals align
    const receiptsCard = document.querySelector('.col-lg-6:first-child .cashbook-card');
    const paymentsCard = document.querySelector('.col-lg-6:last-child .cashbook-card');
    
    if (receiptsCard && paymentsCard) {
        // Function to sync heights
        function syncHeights() {
            const receiptsHeight = receiptsCard.offsetHeight;
            const paymentsHeight = paymentsCard.offsetHeight;
            const maxHeight = Math.max(receiptsHeight, paymentsHeight, 600);
            
            receiptsCard.style.minHeight = maxHeight + 'px';
            paymentsCard.style.minHeight = maxHeight + 'px';
        }
        
        // Sync on load and resize
        syncHeights();
        window.addEventListener('resize', syncHeights);
        
        // Use MutationObserver to watch for content changes
        const observer = new MutationObserver(syncHeights);
        observer.observe(receiptsCard, { childList: true, subtree: true });
        observer.observe(paymentsCard, { childList: true, subtree: true });
    }
});
</script>
@endsection
