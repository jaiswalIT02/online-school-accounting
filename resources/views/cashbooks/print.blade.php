<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $cashbook->name }} - Print</title>
    <style>
        @media print {
            @page {
                size: A4 landscape;
                margin: 10mm;
            }
            body {
                margin: 0;
                padding: 0;
            }
            .no-print {
                display: none !important;
            }
            .page-break {
                page-break-after: always;
                break-after: page;
            }
            .page-break:last-child {
                page-break-after: auto;
                break-after: auto;
            }
            
            /* Reset flexbox to normal table display for printing */
            .page-section {
                display: block !important;
            }
            
            .page-section table {
                display: table !important;
                width: 100% !important;
            }
            
            /* Make sure thead and tfoot repeat on every printed page */
            .cashbook-table thead {
                display: table-header-group !important;
            }
            
            .page-section table thead,
            .page-section table tbody,
            .page-section table tfoot {
                display: revert !important;
            }
            
            .cashbook-table tbody {
                display: table-row-group !important;
            }
            
            .cashbook-table tfoot {
                display: table-footer-group !important;
            }
            
            .page-section table thead tr,
            .page-section table tbody tr,
            .page-section table tfoot tr {
                display: table-row !important;
            }
            
            /* Fixed row height for printing */
            .cashbook-table tbody tr {
                height: 55px !important;
                page-break-inside: avoid !important;
            }
            
            .cashbook-table td,
            .cashbook-table th {
                height: 55px !important;
            }
            
            /* Force two-column layout in print: receipts left, payments right */
            .two-page-spread {
                display: flex !important;
                flex-direction: row !important;
                width: 100% !important;
                gap: 10px !important;
                page-break-inside: avoid !important;
            }
            .page-section {
                display: block !important;
                width: 49% !important;
                max-width: 49% !important;
                flex: 0 1 49% !important;
                overflow: visible !important;
            }
            .page-section table {
                display: table !important;
                width: 100% !important;
                table-layout: fixed !important;
            }
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Times New Roman', serif;
            font-size: 10pt;
            line-height: 1.3;
            padding: 4px 15px;
            background: #fff;
        }
        
        .print-actions {
            text-align: center;
            margin-bottom: 15px;
            padding: 10px;
            background: #f5f5f5;
        }
        
        .print-actions button {
            padding: 8px 20px;
            margin: 0 5px;
            font-size: 14px;
            cursor: pointer;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
        }
        
        .print-actions button:hover {
            background: #0056b3;
        }
        
        .cashbook-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1px;
        }
        
        .cashbook-title {
            font-size: 16pt;
            font-weight: bold;
            text-align: center;
            flex: 1;
            color: #0066cc;
        }
        
        .page-number {
            font-size: 11pt;
            color: #000;
            width: 50px;
            text-align: right;
        }
        
        .period-info {
            text-align: center;
            font-size: 12pt;
            font-weight: bold;
            margin-bottom: 5px;
            color: #000;
        }
        
        .two-page-spread {
            display: flex;
            gap: 15px;
            width: 100%;
            align-items: stretch;
            margin-bottom: 20px;
        }
        
        .page-section {
            flex: 1;
            border: 2px solid #0066cc;
            display: flex;
            flex-direction: column;
        }
        
        .page-section .section-header {
            flex-shrink: 0;
        }
        
        .page-section table {
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        
        .page-section table thead,
        .page-section table tbody {
            display: block;
        }
        
        .page-section table tfoot {
            display: block;
            margin-top: auto;
        }
        
        .page-section table thead tr,
        .page-section table tbody tr,
        .page-section table tfoot tr {
            display: table;
            width: 100%;
            table-layout: fixed;
        }
        
        .section-header {
            background: #fff;
            padding: 8px;
            text-align: center;
            font-weight: bold;
            font-size: 12pt;
            color: #0066cc;
            border-bottom: 2px solid #0066cc;
        }
        
        .cashbook-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9pt;
        }
        
        .cashbook-table th {
            border: 1px solid #0066cc;
            padding: 6px 4px;
            text-align: center;
            font-weight: bold;
            font-size: 9pt;
            color: #0066cc;
            background: #fff;
        }
        
        .cashbook-table th.particulars {
            text-align: left;
            padding-left: 6px;
        }
        
        .cashbook-table th.voucher-col,
        .cashbook-table th.folio-col {
            position: relative;
            white-space: nowrap;
            height: 120px;
            vertical-align: bottom;
            padding: 0;
        }
        
        .cashbook-table th.voucher-col .rotated-text,
        .cashbook-table th.folio-col .rotated-text {
            position: absolute;
            bottom: 50%;
            left: 50%;
            transform: translate(-50%, 50%) rotate(-90deg);
            transform-origin: center;
            white-space: nowrap;
            text-align: center;
            font-weight: bold;
            font-size: 9pt;
            color: #0066cc;
        }
        
        .cashbook-table td {
            border: 1px solid #0066cc;
            padding: 3px 2px;
            vertical-align: top;
            font-size: 9pt;
        }
        
        /* .cashbook-table tbody tr {
            height: 55px;
        } */
        .cashbook-table td.date-col {
            text-align: center;
            width: 12%;
        }
        
        .cashbook-table td.voucher-col {
            text-align: center;
            width: 8%;
        }
        
        .cashbook-table td.particulars-col {
            text-align: left;
            padding-left: 6px;
            width: 40%;
        }
        
        .cashbook-table td.amount-col {
            text-align: right;
            padding-right: 4px;
            width: 10%;
        }
        
        .cashbook-table td.folio-col {
            text-align: center;
            width: 5%;
        }
        
        .cashbook-table td.bank-col {
            text-align: right;
            padding-right: 4px;
            width: 11%;
        }
        
        .cashbook-table td.total-col {
            text-align: right;
            padding-right: 4px;
            width: 14%;
        }
        
        .amount-split {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .amount-split .rs {
            flex: 1;
            text-align: right;
            padding-right: 3px;
        }
        
        .amount-split .p {
            width: 25px;
            text-align: center;
            font-size: 8pt;
            color: #666;
        }
        
        .particulars-text {
            font-weight: 500;
        }
        
        .particulars-note {
            font-size: 8pt;
            color: #555;
            font-style: italic;
            margin-top: 2px;
        }
        
        .opening-balance-row,
        .closing-balance-row {
            background: #f9f9f9;
            font-weight: bold;
        }
        
        /* Prevent rows from breaking across pages */
        .cashbook-table tbody tr {
            page-break-inside: avoid;
            break-inside: avoid;
        }
        
        .cashbook-table tfoot {
            background: #fff;
            font-weight: bold;
        }
        
        .cashbook-table tfoot th {
            padding: 6px 4px;
            border-top: 2px solid #0066cc;
        }
        
        .cashbook-table tfoot td {
            padding: 6px 4px;
            border-top: 2px solid #0066cc;
            text-align: right;
        }
    </style>
</head>
<body>
    <!-- <div class="no-print print-actions">
        <button onclick="window.print()">Print</button>
        <button onclick="window.close()">Close</button>
    </div> -->

    @php
        $maxPages = max(count($receiptPages), count($paymentPages));
    @endphp
    <div class="cashbook-header">
               
                <div class="cashbook-title">CASH BOOK</div>
               
            </div>

            <div class="period-info">
                {{ strtoupper($cashbook->period_month) }} {{ $cashbook->period_year }}
            </div>

    @for ($pageIndex = 0; $pageIndex < $maxPages; $pageIndex++)
        @php
            $receiptPage = isset($receiptPages[$pageIndex]) ? $receiptPages[$pageIndex] : null;
            $paymentPage = isset($paymentPages[$pageIndex]) ? $paymentPages[$pageIndex] : null;
            $currentPageNumber = $pageIndex + 1;
            $isLastPage = $currentPageNumber === $maxPages;
        @endphp

        <div class="{{ $isLastPage ? '' : 'page-break' }}">
            {{-- HEADER --}}
            <!-- <div class="cashbook-header">
                <div class="page-number">{{ $currentPageNumber }}</div>
                <div class="cashbook-title">CASH BOOK</div>
                <div class="page-number">{{ $currentPageNumber }}</div>
            </div>

            <div class="period-info">
                {{ strtoupper($cashbook->period_month) }} {{ $cashbook->period_year }}
            </div> -->

            <div class="two-page-spread">
                {{-- RECEIPTS SECTION (LEFT) --}}
                <div class="page-section page-section-receipts">
                    <!-- <div class="section-header">RECEIPTS</div> -->
                    <table class="cashbook-table">
                        <thead>
                            <tr>
                                <th style="width: 12%">Date & Month</th>
                                <th class="voucher-col" style="width: 8%"><div class="rotated-text">Voucher No</div></th>
                                <th class="particulars" style="width: 40%">PARTICULARS</th>
                                <th style="width: 10%">
                                    <div>Cash Amount</div>
                                    <div style="display: flex; justify-content: space-around; font-size: 8pt; font-weight: normal; margin-top: 2px;">
                                        <span>Rs.</span>
                                        <span>P.</span>
                                    </div>
                                </th>
                                <th class="folio-col" style="width: 5%"><div class="rotated-text">L. Folio</div></th>
                                <th style="width: 11%">
                                    <div>Bank</div>
                                    <div style="display: flex; justify-content: space-around; font-size: 8pt; font-weight: normal; margin-top: 2px;">
                                        <span>Rs.</span>
                                        <span>P.</span>
                                    </div> 
                                </th>
                                <th style="width: 14%">
                                    <div>Total Amount</div>
                                    <div style="display: flex; justify-content: space-around; font-size: 8pt; font-weight: normal; margin-top: 2px;">
                                        <span>Rs.</span>
                                        <span>P.</span>
                                    </div>
                                </th>
                            </tr>
                            {{-- OPENING BALANCE BOX --}}
                                <tr class="opening-balance-row">
                                    <td class="date-col">
                                    @if($pageIndex == 0)
                                    {{ $firstDate->format('d/m/y') }}
                                    @else
                                    {{ $receiptPage['entries']->first()->entry_date->format('d/m/y') }}
                                    @endif
                                    </td>
                                    <td class="voucher-col"></td>
                                    <td class="particulars-col">
                                        <span class="particulars-text">Opening Balance</span>
                                    </td>
                                    <td class="amount-col"></td>
                                    <td class="folio-col"></td>
                                    <td class="bank-col">
                                    @if ($receiptPage && ($receiptPage['opening']['bank'] ?? 0))
                                        <div class="amount-split">
                                            <span class="rs">{{ number_format($receiptPage['opening']['bank'], 2, '.', ',') }}</span>
                                            {{-- <span class="p">00</span> --}}
                                        </div>
                                    @endif
                                    </td>
                                    <td class="total-col">
                                    @if ($receiptPage && isset($receiptPage['opening']['total']))
                                        <div class="amount-split">
                                            <span class="rs">{{ number_format($receiptPage['opening']['total'], 2, '.', ',') }}</span>
                                            {{-- <span class="p">00</span> --}}
                                        </div>
                                    @endif
                                    </td>
                                </tr>
                        </thead>
                        <tbody>
                            @if ($receiptPage)
                               
                                
                                {{-- TRANSACTION ENTRIES --}}
                                @foreach ($receiptPage['entries'] as $entry)
                                    <tr>
                                        <td class="date-col">{{ $entry->entry_date->format('d/m/y') }}</td>
                                        <td class="voucher-col">{{ $entry->voucher_no ?? '' }}</td>
                                        <td class="particulars-col">
                                            <span class="particulars-text">{{ $entry->particulars }}</span>
                                            @if ($entry->narration)
                                                <div class="particulars-note">{{ $entry->narration }}</div>
                                            @endif
                                            @if ($entry->tax_amount || $entry->tax_for || $entry->tax_remark)
                                                <div class="particulars-note" style="margin-top: 3px; font-weight: bold;">
                                                    @if ($entry->tax_for)
                                                        <strong>{{ strtoupper($entry->tax_for) }}</strong>
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
                                                            <strong>Tax:</strong> {{ number_format($entry->tax_amount, 2) }}
                                                            @if ($entry->tax_remark), {{ $entry->tax_remark }}@endif
                                                        @else
                                                            @if ($entry->tax_remark){{ $entry->tax_remark }}@endif
                                                        @endif
                                                    @endif
                                                </div>
                                            @endif
                                        </td>
                                        <td class="amount-col"></td>
                                        <td class="folio-col">{{ $entry->folio_no ?? '' }}</td>
                                        <td class="bank-col">
                                            @if ($entry->bank_amount > 0)
                                                <div class="amount-split">
                                                    <span class="rs">{{ number_format($entry->bank_amount, 2, '.', ',') }}</span>
                                                    {{-- <span class="p">00</span> --}}
                                                </div>
                                            @endif
                                        </td>
                                        <td class="total-col">
                                            @if ($entry->bank_amount > 0)
                                                <div class="amount-split">
                                                    <span class="rs">{{ number_format($entry->bank_amount, 2, '.', ',') }}</span>
                                                    {{-- <span class="p">00</span> --}}
                                                </div>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                        {{-- TOTAL BOX - ALWAYS AT BOTTOM --}}
                            
                        <tfoot>
                            <tr class="closing-balance-row">
                                <td class="date-col"></td>
                                <td class="voucher-col"></td>
                                <td class="particulars-col">
                                    <span class="particulars-text">-</span>
                                </td>
                                <td class="amount-col"></td>
                                <td class="folio-col"></td>
                                <td class="bank-col"></td>
                                <td class="total-col"></td>
                            </tr>
                            {{-- CLOSING BALANCE BOX - ALWAYS AT BOTTOM --}}
                            <tr class="closing-balance-row">
                                <td class="date-col"></td>
                                <td class="voucher-col"></td>
                                <td class="particulars-col">
                                    <span class="particulars-text">-</span>
                                </td>
                                <td class="amount-col"></td>
                                <td class="folio-col"></td>
                                <td class="bank-col"></td>
                                <td class="total-col"></td>
                            </tr>
                            {{-- TOTAL BOX - cumulative sum from page 1 through this page --}}
                            <tr>
                                <th colspan="3" style="text-align: left; padding-left: 6px;">Total</th>
                                <td class="amount-col"></td>
                                <td class="folio-col"></td>
                                <td class="bank-col">
                                    @if ($receiptPage && ($receiptPage['closing']['bank'] ?? 0))
                                        <div class="amount-split">
                                            <span class="rs">{{ number_format($receiptPage['closing']['bank'], 2, '.', ',') }}</span>
                                            {{-- <span class="p">00</span> --}}
                                        </div>
                                    @endif
                                </td>
                                <td class="total-col">
                                    @if ($receiptPage && ($receiptPage['closing']['total'] ?? 0))
                                        <div class="amount-split">
                                            <span class="rs">{{ number_format($receiptPage['closing']['total'], 2, '.', ',') }}</span>
                                            {{-- <span class="p">00</span> --}}
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                {{-- PAYMENTS SECTION (RIGHT) --}}
                <div class="page-section page-section-payments">
                    <!-- <div class="section-header">PAYMENTS</div> -->
                    <table class="cashbook-table">
                        <thead>
                            <tr>
                                <th style="width: 12%">Date & Month</th>
                                <th class="voucher-col" style="width: 8%"><div class="rotated-text">Voucher No</div></th>
                                <th class="particulars" style="width: 40%">PARTICULARS</th>
                                <th style="width: 10%">
                                    <div>Cash Amount</div>
                                    <div style="display: flex; justify-content: space-around; font-size: 8pt; font-weight: normal; margin-top: 2px;">
                                        <span>Rs.</span>
                                        <span>P.</span>
                                    </div>
                                </th>
                                <th class="folio-col" style="width: 5%"><div class="rotated-text">L. Folio</div></th>
                                <th style="width: 11%">
                                    <div>Bank</div>
                                    <div style="display: flex; justify-content: space-around; font-size: 8pt; font-weight: normal; margin-top: 2px;">
                                        <span>Rs.</span>
                                        <span>P.</span>
                                    </div>
                                </th>
                                <th style="width: 14%">
                                    <div>Total Amount</div>
                                    <div style="display: flex; justify-content: space-around; font-size: 8pt; font-weight: normal; margin-top: 2px;">
                                        <span>Rs.</span>
                                        <span>P.</span>
                                    </div>
                                </th>
                            </tr>
                            {{-- OPENING BALANCE BOX --}}
                                <tr class="opening-balance-row">
                                    <td class="date-col"></td>
                                    <td class="voucher-col"></td>
                                    <td class="particulars-col">
                                        <span class="particulars-text">-</span>
                                    </td>
                                    <td class="amount-col"></td>
                                    <td class="folio-col"></td>
                                    <td class="bank-col"></td>
                                    <td class="total-col"></td>
                                </tr>
                        </thead>
                        <tbody>
                            @if ($paymentPage)
                                
                                
                                {{-- TRANSACTION ENTRIES --}}
                                @foreach ($paymentPage['entries'] as $entry)
                                    <tr>
                                        <td class="date-col">{{ $entry->entry_date->format('d/m/y') }}</td>
                                        <td class="voucher-col">{{ $entry->voucher_no ?? '' }}</td>
                                        <td class="particulars-col">
                                            <span class="particulars-text">{{ $entry->particulars }}</span>
                                            @if ($entry->narration)
                                                <div class="particulars-note">{{ $entry->narration }}</div>
                                            @endif
                                            @if ($entry->tax_amount || $entry->tax_for || $entry->tax_remark)
                                                <div class="particulars-note" style="margin-top: 3px; font-weight: bold;">
                                                    @if ($entry->tax_for)
                                                        <strong>{{ strtoupper($entry->tax_for) }}</strong>
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
                                                            <strong>Tax:</strong> {{ number_format($entry->tax_amount, 2) }}
                                                            @if ($entry->tax_remark), {{ $entry->tax_remark }}@endif
                                                        @else
                                                            @if ($entry->tax_remark){{ $entry->tax_remark }}@endif
                                                        @endif
                                                    @endif
                                                </div>
                                            @endif
                                        </td>
                                        <td class="amount-col"></td>
                                        <td class="folio-col">{{ $entry->folio_no ?? '' }}</td>
                                        <td class="bank-col">
                                            @if ($entry->bank_amount > 0)
                                                <div class="amount-split">
                                                    <span class="rs">{{ number_format($entry->bank_amount, 2, '.', ',') }}</span>
                                                    {{-- <span class="p">00</span> --}}
                                                </div>
                                            @endif
                                        </td>
                                        <td class="total-col">
                                            @if ($entry->bank_amount > 0)
                                                <div class="amount-split">
                                                    <span class="rs">{{ number_format($entry->bank_amount, 2, '.', ',') }}</span>
                                                    {{-- <span class="p">00</span> --}}
                                                </div>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                        <tfoot>

                        <!-- extra total box added here -->
                        <!-- <tr>
                                <th colspan="3" style="text-align: left; padding-left: 6px;">Total</th>
                                <td class="amount-col"></td>
                                <td class="folio-col"></td>
                                <td class="bank-col"></td>
                                <td class="total-col"></td>
                            </tr> -->
                            {{-- CLOSING BALANCE BOX - ALWAYS AT BOTTOM --}}
                            <tr>
                                <td class="date-col" style="text-align: left; padding-left: 6px;">Total Payment</td>
                                <td class="voucher-col"></td>
                                <td class="particular-col"></td>
                                <td class="amount-col"></td>
                                <td class="folio-col"></td>
                                <td class="bank-col">
                                    @if ($paymentPage && ($paymentPage['opening']['bank'] ?? 0))
                                        <div class="amount-split">
                                            <span class="rs">{{ number_format($paymentPage['opening']['bank'], 2, '.', ',') }}</span>
                                            {{-- <span class="p">00</span> --}}
                                        </div>
                                    @endif
                                </td>
                                <td class="total-col">
                                    @if ($paymentPage && ($paymentPage['opening']['total'] ?? 0))
                                        <div class="amount-split">
                                            <span class="rs">{{ number_format($paymentPage['opening']['total'], 2, '.', ',') }}</span>
                                            {{-- <span class="p">00</span> --}}
                                        </div>
                                    @endif
                                </td>
                            </tr>
                            <tr class="closing-balance-row">
                                <td class="date-col">
                                @if($pageIndex == $maxPages-1)
                                {{ $lastDate->format('d/m/y') }}
                                @else
                                {{ $paymentPage['entries']->last()->entry_date->format('d/m/y') }}
                                @endif
                                </td>
                                <td class="voucher-col"></td>
                                <td class="particulars-col">
                                    <span class="particulars-text">Closing Balance</span>
                                </td>
                                <td class="amount-col"></td>
                                <td class="folio-col"></td>
                                <td class="bank-col">
                                @if ($paymentPage && ($paymentPage['closing']['bank'] ?? 0))
                                        <div class="amount-split">
                                            <span class="rs">{{ number_format($paymentPage['closing']['bank'], 2, '.', ',') }}</span>
                                            {{-- <span class="p">00</span> --}}
                                        </div>
                                    @endif
                                </td>
                                <td class="total-col">
                                @if ($paymentPage && ($paymentPage['closing']['total'] ?? 0))
                                    <div class="amount-split">
                                        <span class="rs">{{ number_format($paymentPage['closing']['total'], 2, '.', ',') }}</span>
                                        {{-- <span class="p">00</span> --}}
                                    </div>
                                @endif
                                </td>
                            </tr>
                            {{-- TOTAL BOX - cumulative sum from page 1 through this page --}}
                            <tr>
                                <th colspan="3" style="text-align: left; padding-left: 6px;">Total</th>
                                <td class="amount-col"></td>
                                <td class="folio-col"></td>
                                <td class="bank-col">
                                    @if ($paymentPage && ($paymentPage['opening']['bank'] ?? 0))
                                        <div class="amount-split">
                                            <span class="rs">{{ number_format($paymentPage['opening']['bank'] + $paymentPage['closing']['bank'], 2, '.', ',') }}</span>
                                            {{-- <span class="p">00</span> --}}
                                        </div>
                                    @endif
                                </td>
                                <td class="total-col">
                                    @if ($paymentPage && ($paymentPage['opening']['total'] ?? 0))
                                        <div class="amount-split">
                                            <span class="rs">{{ number_format($paymentPage['opening']['total'] + $paymentPage['closing']['total'], 2, '.', ',') }}</span>
                                            {{-- <span class="p">00</span> --}}
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    @endfor
    
    {{-- Height for table rows/cells is defined only in @media print (lines 64-73) --}}
</body>
</html>
