@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h4 class="mb-1">{{$account->name }}</h4>
            {{-- @if ($account->header_subtitle)
                <div class="text-muted">{{ $account->header_subtitle }}</div>
            @endif --}}
            <div class="text-muted">
                For the period {{ $account->period_from->format('d M Y') }}
                to {{ $account->period_to->format('d M Y') }}
            </div>
        </div>
        <div class="d-flex gap-2">
            <a class="btn btn-outline-secondary" href="{{ route('receipt_payments.index') }}">Back</a>
            <a class="btn btn-outline-secondary" href="{{ route('receipt_payments.edit', $account) }}">Edit</a>
            <a class="btn btn-success" href="{{ route('receipt_payments.print', $account) }}" target="_blank">
                <i class="bi bi-printer"></i> Print
            </a>
            <a class="btn btn-outline-primary" href="{{ route('pdf_extract.index', ['account_id' => $account->id]) }}">
                Extract from PDF
            </a>
            
            <a class="btn btn-primary" href="{{ route('receipt_payment_entries.create', $account) }}?type=both">Add Entry</a>
            {{-- <a class="btn btn-primary" href="{{ route('receipt_payment_entries.create', $account) }}?type=receipt">Add Receipt</a>
            <a class="btn btn-primary" href="{{ route('receipt_payment_entries.create', $account) }}?type=payment">Add Payment</a> --}}
        </div>
    </div>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    @php
        // Always show closing balance on payment side
        $closingValue = abs($closingBalance);
        // If closing balance is positive (surplus), it goes to payment side
        // If closing balance is negative (deficit), it also goes to payment side (as a positive value)
        $receiptGrandTotal = $receiptTotal;
        $paymentGrandTotal = $paymentTotal + $closingValue;
    @endphp

    <style>
        .receipt-payment-row {
            display: flex;
            align-items: stretch;
        }
        .receipt-payment-card {
            display: flex;
            flex-direction: column;
            height: 100%;
        }
        .receipt-payment-card .card-body {
            display: flex;
            flex-direction: column;
            padding: 0 !important;
            flex: 1;
            min-height: 600px;
        }
        .receipt-payment-card .table-responsive {
            display: flex;
            flex-direction: column;
            flex: 1;
            overflow: hidden;
        }
        .receipt-payment-card table {
            display: flex;
            flex-direction: column;
            height: 100%;
            margin: 0;
        }
        .receipt-payment-card thead {
            flex-shrink: 0;
            display: table;
            width: 100%;
            table-layout: fixed;
        }
        .receipt-payment-card tbody {
            flex: 1;
            overflow-y: auto;
            display: block;
            min-height: 0;
        }
        .receipt-payment-card tbody tr {
            display: table;
            width: 100%;
            table-layout: fixed;
        }
        .receipt-payment-card tfoot {
            flex-shrink: 0;
            display: table;
            width: 100%;
            table-layout: fixed;
            background-color: #f8f9fa;
        }
        /* Bulk checkbox: larger size, indigo border, no label text */
        .bulk-cb-wrap {
            cursor: pointer;
        }
        .bulk-cb {
            width: 22px !important;
            height: 22px !important;
            min-width: 22px !important;
            min-height: 22px !important;
            border: 2px solid #4f46e5 !important;
            border-radius: 4px;
            cursor: pointer;
        }
        .bulk-cb:checked {
            background-color: #4f46e5;
            border-color: #4f46e5;
        }
    </style>

    <div class="row g-3 receipt-payment-row">
        <div class="col-lg-6">
            <div class="card receipt-payment-card">
                <div class="card-header bg-light fw-semibold d-flex justify-content-between align-items-center">
                    <span>Receipt</span>
                    <div class="d-flex gap-2 receipt-bulk-actions" style="visibility: hidden;">
                        <button type="button" class="btn btn-sm btn-outline-secondary receipt-bulk-edit">Edit</button>
                        <button type="button" class="btn btn-sm btn-outline-danger receipt-bulk-delete">Delete</button>
                    </div>
                </div>
                <div class="card-body">
                    <form id="form-receipt-bulk-delete" method="POST" action="{{ route('receipt_payment_entries.bulk_destroy') }}" class="d-none">
                        @csrf
                    </form>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 60%">Particulars</th>
                                    <th style="width: 20%">A. Code</th>
                                    <th style="width: 18%" class="text-end">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="fw-semibold" style="width: 61%">Opening Balance</td>
                                    <td style="width: 20%">-</td>
                                    <td class="text-end" style="width: 18%">-</td>
                                </tr>
                                @forelse ($receipts as $entry)
                                    <tr>
                                        <td style="width: 60%">
                                            <div class="fw-semibold">
                                                {{ $entry->current_particular_name }}
                                            </div>
                                            @if ($entry->remarks)
                                                <div class="text-muted small">
                                                    {{ $entry->remarks }}
                                                    @if ($entry->beneficiary_id && $entry->beneficiary)
                                                        <span class="ms-2 fw-semibold">- Vendor: {{ $entry->beneficiary->name }}</span>
                                                    @endif
                                                </div>
                                            @elseif ($entry->beneficiary_id && $entry->beneficiary)
                                                <div class="text-muted small">
                                                    <span class="fw-semibold">Vendor: {{ $entry->beneficiary->name }}</span>
                                                </div>
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
                                            <div class="mt-1 d-flex align-items-center gap-2 flex-wrap">
                                                <a class="btn btn-sm btn-outline-secondary" href="{{ route('receipt_payment_entries.edit', $entry) }}">Edit</a>
                                                <form class="d-inline" method="POST" action="{{ route('receipt_payment_entries.destroy', $entry) }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this entry?')">
                                                        Delete
                                                    </button>
                                                </form>
                                                <label class="mb-0 ms-1 d-inline-flex align-items-center bulk-cb-wrap">
                                                    <input type="checkbox" class="form-check-input receipt-row-cb bulk-cb" name="receipt_ids[]" value="{{ $entry->id }}">
                                                </label>
                                            </div>
                                        </td>
                                        <td style="width: 20%">{{ $entry->current_acode ?? '-' }}</td>
                                        <td class="text-end" style="width: 18%">{{ number_format($entry->amount, 2) }}</td>
                                    </tr>
                                @empty
                                @endforelse
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="2">Total Receipts</th>
                                    <th class="text-end">{{ number_format($receiptGrandTotal, 2) }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card receipt-payment-card">
                <div class="card-header bg-light fw-semibold d-flex justify-content-between align-items-center">
                    <span>Payment</span>
                    <div class="d-flex gap-2 payment-bulk-actions" style="visibility: hidden;">
                        <button type="button" class="btn btn-sm btn-outline-secondary payment-bulk-edit">Edit</button>
                        <button type="button" class="btn btn-sm btn-outline-danger payment-bulk-delete">Delete</button>
                    </div>
                </div>
                <div class="card-body">
                    <form id="form-payment-bulk-delete" method="POST" action="{{ route('receipt_payment_entries.bulk_destroy') }}" class="d-none">
                        @csrf
                    </form>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 60%">Particulars</th>
                                    <th style="width: 20%">A. Code</th>
                                    <th style="width: 18%" class="text-end">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="fw-semibold" style="width: 60%">.</td>
                                    <td style="width: 20%"></td>
                                    <td class="text-end" style="width: 18%"></td>
                                </tr>
                                @forelse ($payments as $entry)
                                    <tr>
                                        <td style="width: 60%">
                                            <div class="fw-semibold">
                                                {{ $entry->current_particular_name }}
                                            </div>
                                            @if ($entry->remarks)
                                                <div class="text-muted small">
                                                    {{ $entry->remarks }}
                                                    @if ($entry->beneficiary_id && $entry->beneficiary)
                                                        <span class="ms-2 fw-semibold">- Vendor: {{ $entry->beneficiary->name }}</span>
                                                    @endif
                                                </div>
                                            @elseif ($entry->beneficiary_id && $entry->beneficiary)
                                                <div class="text-muted small">
                                                    <span class="fw-semibold">Vendor: {{ $entry->beneficiary->name }}</span>
                                                </div>
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
                                            <div class="mt-1 d-flex align-items-center gap-2 flex-wrap">
                                                <a class="btn btn-sm btn-outline-secondary" href="{{ route('receipt_payment_entries.edit', $entry) }}">Edit</a>
                                                <form class="d-inline" method="POST" action="{{ route('receipt_payment_entries.destroy', $entry) }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this entry?')">
                                                        Delete
                                                    </button>
                                                </form>
                                                <label class="mb-0 ms-1 d-inline-flex align-items-center bulk-cb-wrap">
                                                    <input type="checkbox" class="form-check-input payment-row-cb bulk-cb" name="payment_ids[]" value="{{ $entry->id }}">
                                                </label>
                                            </div>
                                        </td>
                                        <td style="width: 20%">{{ $entry->current_acode ?? '-' }}</td>
                                        <td class="text-end" style="width: 18%">{{ number_format($entry->amount, 2) }}</td>
                                    </tr>
                                @empty
                                @endforelse
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th class="fw-semibold" style="width: 60%">Closing Balance</th>
                                    <th style="width: 20%"></th>
                                    <th class="text-end" style="width: 18%">{{ number_format($closingValue, 2) }}</th>
                                </tr>
                                <tr>
                                    <th colspan="2">Total Payments</th>
                                    <th class="text-end">{{ number_format($paymentGrandTotal, 2) }}</th>
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
    // --- Bulk actions: Receipt ---
    const receiptCbs = document.querySelectorAll('.receipt-row-cb');
    const receiptBulkActions = document.querySelector('.receipt-bulk-actions');
    const formReceiptDelete = document.getElementById('form-receipt-bulk-delete');
    const receiptBulkEditBtn = document.querySelector('.receipt-bulk-edit');
    const receiptBulkDeleteBtn = document.querySelector('.receipt-bulk-delete');

    function updateReceiptBulkVisibility() {
        const any = Array.from(receiptCbs).some(function(cb) { return cb.checked; });
        if (receiptBulkActions) receiptBulkActions.style.visibility = any ? 'visible' : 'hidden';
    }
    receiptCbs.forEach(function(cb) { cb.addEventListener('change', updateReceiptBulkVisibility); });
    updateReceiptBulkVisibility();

    if (receiptBulkDeleteBtn && formReceiptDelete) {
        receiptBulkDeleteBtn.addEventListener('click', function() {
            const ids = Array.from(receiptCbs).filter(function(cb) { return cb.checked; }).map(function(cb) { return cb.value; });
            if (ids.length === 0) return;
            if (!confirm('Delete ' + ids.length + ' selected entr' + (ids.length === 1 ? 'y' : 'ies') + '?')) return;
            while (formReceiptDelete.lastChild) formReceiptDelete.removeChild(formReceiptDelete.lastChild);
            var tok = document.createElement('input');
            tok.type = 'hidden'; tok.name = '_token'; tok.value = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            formReceiptDelete.appendChild(tok);
            ids.forEach(function(id) {
                var inp = document.createElement('input');
                inp.type = 'hidden'; inp.name = 'ids[]'; inp.value = id;
                formReceiptDelete.appendChild(inp);
            });
            formReceiptDelete.submit();
        });
    }
    if (receiptBulkEditBtn) {
        receiptBulkEditBtn.addEventListener('click', function() {
            const ids = Array.from(receiptCbs).filter(function(cb) { return cb.checked; }).map(function(cb) { return cb.value; });
            if (ids.length === 0) return;
            var q = ids.map(function(id) { return 'ids[]=' + encodeURIComponent(id); }).join('&');
            window.location.href = '{{ route("receipt_payment_entries.bulk_edit") }}?' + q;
        });
    }

    // --- Bulk actions: Payment ---
    const paymentCbs = document.querySelectorAll('.payment-row-cb');
    const paymentBulkActions = document.querySelector('.payment-bulk-actions');
    const formPaymentDelete = document.getElementById('form-payment-bulk-delete');
    const paymentBulkEditBtn = document.querySelector('.payment-bulk-edit');
    const paymentBulkDeleteBtn = document.querySelector('.payment-bulk-delete');

    function updatePaymentBulkVisibility() {
        const any = Array.from(paymentCbs).some(function(cb) { return cb.checked; });
        if (paymentBulkActions) paymentBulkActions.style.visibility = any ? 'visible' : 'hidden';
    }
    paymentCbs.forEach(function(cb) { cb.addEventListener('change', updatePaymentBulkVisibility); });
    updatePaymentBulkVisibility();

    if (paymentBulkDeleteBtn && formPaymentDelete) {
        paymentBulkDeleteBtn.addEventListener('click', function() {
            const ids = Array.from(paymentCbs).filter(function(cb) { return cb.checked; }).map(function(cb) { return cb.value; });
            if (ids.length === 0) return;
            if (!confirm('Delete ' + ids.length + ' selected entr' + (ids.length === 1 ? 'y' : 'ies') + '?')) return;
            formPaymentDelete.innerHTML = '';
            var tok = document.createElement('input');
            tok.type = 'hidden'; tok.name = '_token'; tok.value = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            formPaymentDelete.appendChild(tok);
            ids.forEach(function(id) {
                var inp = document.createElement('input');
                inp.type = 'hidden'; inp.name = 'ids[]'; inp.value = id;
                formPaymentDelete.appendChild(inp);
            });
            formPaymentDelete.submit();
        });
    }
    if (paymentBulkEditBtn) {
        paymentBulkEditBtn.addEventListener('click', function() {
            const ids = Array.from(paymentCbs).filter(function(cb) { return cb.checked; }).map(function(cb) { return cb.value; });
            if (ids.length === 0) return;
            var q = ids.map(function(id) { return 'ids[]=' + encodeURIComponent(id); }).join('&');
            window.location.href = '{{ route("receipt_payment_entries.bulk_edit") }}?' + q;
        });
    }

    // Ensure both cards have equal heights and totals align
    const receiptCard = document.querySelector('.col-lg-6:first-child .receipt-payment-card');
    const paymentCard = document.querySelector('.col-lg-6:last-child .receipt-payment-card');
    
    if (receiptCard && paymentCard) {
        function syncHeights() {
            const receiptHeight = receiptCard.offsetHeight;
            const paymentHeight = paymentCard.offsetHeight;
            const maxHeight = Math.max(receiptHeight, paymentHeight, 600);
            receiptCard.style.minHeight = maxHeight + 'px';
            paymentCard.style.minHeight = maxHeight + 'px';
        }
        syncHeights();
        window.addEventListener('resize', syncHeights);
        const observer = new MutationObserver(syncHeights);
        observer.observe(receiptCard, { childList: true, subtree: true });
        observer.observe(paymentCard, { childList: true, subtree: true });
    }
});
</script>


@endsection
