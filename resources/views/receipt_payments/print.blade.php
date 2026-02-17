<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt and Payment Account - Print</title>
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
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Times New Roman', serif;
            font-size: 11pt;
            line-height: 1.4;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 5px;
        }
        
        .header h1 {
            font-size: 16pt;
            font-weight: bold;
            margin-bottom: 1px;
            text-transform: uppercase;
        }
        
        .header .subtitle {
            font-size: 12pt;
            margin-bottom: 5px;
        }
        
        .header .period {
            font-size: 11pt;
            font-weight: bold;
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
        
        .tables-container {
            display: flex;
            gap: 10px;
            margin-top: 5px;
            align-items: stretch;
        }
        
        .table-section {
            flex: 1;
            border: 1px solid #000;
            display: flex;
            flex-direction: column;
        }
        
        .table-section-header {
            background: #e0e0e0;
            padding: 2px;
            text-align: center;
            font-weight: bold;
            font-size: 12pt;
            border-bottom: 1px solid #000;
            flex-shrink: 0;
        }
        
        .table-section .table-wrapper {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10pt;
            table-layout: fixed;
            display: flex;
            flex-direction: column;
            height: 100%;
        }
        
        table thead {
            background: #f0f0f0;
            flex-shrink: 0;
            display: table;
            width: 100%;
            table-layout: fixed;
        }
        
        table thead tr {
            display: table-row;
        }
        
        table thead th {
            display: table-cell;
        }
        
        table tbody {
            flex: 1;
            display: block;
            min-height: 0;
        }
        
        table tbody tr {
            display: table;
            width: 100%;
            table-layout: fixed;
            page-break-inside: avoid;
        }
        
        table tbody td {
            display: table-cell;
        }
        
        table tfoot {
            flex-shrink: 0;
            display: table;
            width: 100%;
            table-layout: fixed;
            background: #f0f0f0;
            font-weight: bold;
        }
        
        table tfoot tr {
            display: table-row;
        }
        
        table tfoot th {
            display: table-cell;
            padding: 8px 4px;
        }
        
        table th {
            border: 1px solid #000;
            padding: 2px 2px;
            text-align: left;
            font-weight: bold;
            font-size: 10pt;
            box-sizing: border-box;
        }
        
        table th.text-right {
            text-align: right;
        }
        
        table td {
            border: 1px solid #000;
            padding: 2px 2px;
            vertical-align: top;
            box-sizing: border-box;
        }
        
        table td.text-right {
            text-align: right;
        }
        
        .particular-name {
            font-weight: 500;
        }
        
        .remarks {
            font-size: 9pt;
            color: #555;
            font-style: italic;
        }
        
        table tfoot th {
            padding: 8px 4px;
        }
        
        .footer {
            margin-top: 20px;
            display: flex;
            /* justify-content: space-between; */
            align-items: flex-end;
        }
        
        .signature {
            text-align: right;
            margin-left: auto;
        }
        
        .signature-line {
            border-top: 1px solid #000;
            width: 200px;
            margin-top: 10px;
            padding-top: 5px;
            
            text-align: center;
            font-size: 10pt;
        }
        .signature-line-left {
            border-top: 1px solid #000;
            width: 200px;
            margin-top: 10px;
            padding-top: 5px;
            text-align: center;
            font-size: 10pt;
        }
        
        .filter-section {
            background: #f8f9fa;
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            width: 100%;
        }
        
        .filter-section h4 {
            margin-bottom: 15px;
            font-size: 14pt;
            color: #333;
        }
        
        .filter-options {
            display: flex;
            gap: 20px;
            align-items: flex-end;
            flex-wrap: wrap;
            width: 100%;
        }
        
        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
            flex: 1;
            min-width: 150px;
        }
        
        .filter-group label {
            font-weight: 600;
            font-size: 11pt;
            color: #555;
        }
        
        .filter-group select,
        .filter-group input {
            padding: 6px 10px;
            font-size: 11pt;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        
        .filter-actions {
            display: flex;
            gap: 10px;
            flex-shrink: 0;
        }
        
        .filter-actions button {
            padding: 8px 20px;
            font-size: 12pt;
            cursor: pointer;
            border: none;
            border-radius: 4px;
        }
        
        .btn-apply {
            background: #28a745;
            color: white;
        }
        
        .btn-apply:hover {
            background: #218838;
        }
        
        .btn-clear {
            background: #6c757d;
            color: white;
        }
        
        .btn-clear:hover {
            background: #5a6268;
        }
    </style>
</head>
<body>
    <div class="no-print print-actions">
        <button onclick="window.print()">Print</button>
        <button onclick="window.close()">Close</button>
    </div>

    <!-- Filter Section -->
    @php
        // Generate month options based on account period
        $monthOptions = [];
        $startDate = \Carbon\Carbon::parse($account->period_from);
        $endDate = \Carbon\Carbon::parse($account->period_to);
        $current = $startDate->copy()->startOfMonth();
        
        while ($current->lte($endDate)) {
            $monthOptions[] = [
                'value' => $current->format('Y-m'),
                'label' => $current->format('F Y'),
            ];
            $current->addMonth();
        }
    @endphp
    <div class="no-print filter-section">
        <h4><i class="fas fa-filter"></i> Filter Options</h4>
        <form method="GET" action="{{ route('receipt_payments.print', $account) }}" id="filterForm">
            <div class="filter-options">
                <div class="filter-group">
                    <label>Filter Type</label>
                    <select name="filter_type" id="filter_type" onchange="toggleFilterOptions()">
                        <option value="">All Data</option>
                        <option value="single_month" {{ request('filter_type') == 'single_month' ? 'selected' : '' }}>Single Month</option>
                        <option value="month_range" {{ request('filter_type') == 'month_range' ? 'selected' : '' }}>Month Range</option>
                    </select>
                </div>
                
                <div class="filter-group" id="single_month_group" style="display: {{ request('filter_type') == 'single_month' ? 'flex' : 'none' }};">
                    <label>Select Month</label>
                    <select name="month" id="month">
                        <option value="">-- Select Month --</option>
                        @foreach($monthOptions as $month)
                            <option value="{{ $month['value'] }}" {{ request('month') == $month['value'] ? 'selected' : '' }}>
                                {{ $month['label'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="filter-group" id="month_range_group" style="display: {{ request('filter_type') == 'month_range' ? 'flex' : 'none' }};">
                    <label>From Month</label>
                    <select name="from_month" id="from_month">
                        <option value="">-- Select Month --</option>
                        @foreach($monthOptions as $month)
                            <option value="{{ $month['value'] }}" {{ request('from_month') == $month['value'] ? 'selected' : '' }}>
                                {{ $month['label'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="filter-group" id="to_month_group" style="display: {{ request('filter_type') == 'month_range' ? 'flex' : 'none' }};">
                    <label>To Month</label>
                    <select name="to_month" id="to_month">
                        <option value="">-- Select Month --</option>
                        @foreach($monthOptions as $month)
                            <option value="{{ $month['value'] }}" {{ request('to_month') == $month['value'] ? 'selected' : '' }}>
                                {{ $month['label'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="filter-actions">
                    <button type="submit" class="btn-apply">Apply Filter</button>
                    <a href="{{ route('receipt_payments.print', $account) }}" class="btn-clear" style="text-decoration: none; display: inline-block; padding: 8px 20px;">Clear Filter</a>
                </div>
            </div>
        </form>
    </div>

    <div class="header">
        <h1>{{ $account->name }}</h1>
        @if ($account->header_subtitle)
            {{-- <div class="subtitle">{{ $account->header_subtitle }}</div> --}}
        @endif
        <div class="period">
            @if(request('filter_type') == 'single_month' && request('month'))
                @php
                    $selectedMonth = \Carbon\Carbon::createFromFormat('Y-m', request('month'));
                @endphp
                FOR THE MONTH OF {{ strtoupper($selectedMonth->format('F-Y')) }}
            @elseif(request('filter_type') == 'month_range' && request('from_month') && request('to_month'))
                @php
                    $fromMonth = \Carbon\Carbon::createFromFormat('Y-m', request('from_month'));
                    $toMonth = \Carbon\Carbon::createFromFormat('Y-m', request('to_month'));
                @endphp
                FOR THE MONTH OF {{ strtoupper($fromMonth->format('F-Y')) }} TO {{ strtoupper($toMonth->format('F-Y')) }}
            @else
                FOR THE MONTH OF {{ strtoupper($account->period_from->format('F-Y')) }} TO {{ strtoupper($account->period_to->format('F-Y')) }}
            @endif
        </div>
    </div>

    @php
        // Always show closing balance on payment side
        $closingValue = abs($closingBalance);
        $receiptGrandTotal = $receiptTotal;
        $paymentGrandTotal = $paymentTotal + $closingValue;
    @endphp

    <div class="tables-container">
        {{-- Receipt Section --}}
        <div class="table-section">
            <div class="table-section-header">RECEIPT</div>
            <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th style="width: 63%">Particulars</th>
                        <th style="width: 20%">A. Code</th>
                        <th style="width: 17%" class="text-right">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="particular-name" style="width:63%">Opening Balance</td>
                        <td style="width:20%">-</td>
                        <td class="text-right" style="width:17%">-</td>
                    </tr>
                    @forelse ($receipts as $entry)
                        <tr>
                            <td style="width: 63%">
                                <div class="particular-name">{{ $entry->current_particular_name }}</div>
                            </td>
                            <td style="width:20%">{{ $entry->current_acode ?? '-' }}</td>
                            <td class="text-right" style="width:17%">{{ number_format($entry->amount, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" style="text-align: center; padding: 10px;">No receipts yet.</td>
                        </tr>
                    @endforelse
                    <tr>
                        <td class="particular-name" style="width:63%">-</td>
                        <td style="width:20%">-</td>
                        <td class="text-right" style="width:17%">-</td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="2">Total Receipts</th>
                        <th class="text-right">{{ number_format($receiptGrandTotal, 2) }}</th>
                    </tr>
                </tfoot>
            </table>
            </div>
        </div>

        {{-- Payment Section --}}
        <div class="table-section">
            <div class="table-section-header">PAYMENT</div>
            <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th style="width: 63%">Particulars</th>
                        <th style="width: 20%">A. Code</th>
                        <th style="width: 17%" class="text-right">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="particular-name" style="width:63%">-</td>
                        <td style="width:20%">-</td>
                        <td class="text-right" style="width:17%">-</td>
                    </tr>
                    @forelse ($payments as $entry)
                        <tr>
                            <td>
                                <div class="particular-name">{{ $entry->current_particular_name }}</div>
                            </td>
                            <td style="width:20%">{{ $entry->current_acode ?? '-' }}</td>
                            <td class="text-right" style="width:17%">{{ number_format($entry->amount, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" style="text-align: center; padding: 10px;">No payments yet.</td>
                        </tr>
                    @endforelse
                        <tr>
                            <td class="particular-name" style="width:63%">Closing Balance</td>
                            <td style="width:20%">-</td>
                            <td class="text-right" style="width:17%">{{ number_format($closingValue, 2) }}</td>
                        </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="2">Total Payments</th>
                        <th class="text-right">{{ number_format($paymentGrandTotal, 2) }}</th>
                    </tr>
                </tfoot>
            </table>
            </div>
        </div>
    </div>

    <div class="footer">
        <div></div>
        <div class="signature-line-left">
            Signature<br>
            <small style="font-size: 9pt;">Account Asstt Cum Caretaker</small>
        </div>
        <div class="signature">
            <div class="signature-line">
                Signature<br>
                <small style="font-size: 9pt;">Warden Cum Superintendent</small>
            </div>
        </div>
    </div>

    <script>
        function toggleFilterOptions() {
            const filterType = document.getElementById('filter_type').value;
            const singleMonthGroup = document.getElementById('single_month_group');
            const monthRangeGroup = document.getElementById('month_range_group');
            const toMonthGroup = document.getElementById('to_month_group');
            
            if (filterType === 'single_month') {
                singleMonthGroup.style.display = 'flex';
                monthRangeGroup.style.display = 'none';
                toMonthGroup.style.display = 'none';
            } else if (filterType === 'month_range') {
                singleMonthGroup.style.display = 'none';
                monthRangeGroup.style.display = 'flex';
                toMonthGroup.style.display = 'flex';
            } else {
                singleMonthGroup.style.display = 'none';
                monthRangeGroup.style.display = 'none';
                toMonthGroup.style.display = 'none';
            }
        }
        
        // Validate month range
        document.getElementById('filterForm')?.addEventListener('submit', function(e) {
            const filterType = document.getElementById('filter_type').value;
            if (filterType === 'month_range') {
                const fromMonth = document.getElementById('from_month').value;
                const toMonth = document.getElementById('to_month').value;
                
                if (fromMonth && toMonth && fromMonth > toMonth) {
                    e.preventDefault();
                    alert('From Month cannot be greater than To Month');
                    return false;
                }
            }
        });
        
        // Set options for to_month based on from_month
        document.getElementById('from_month')?.addEventListener('change', function() {
            const fromMonth = this.value;
            const toMonthSelect = document.getElementById('to_month');
            if (fromMonth && toMonthSelect) {
                // Disable options before from_month
                Array.from(toMonthSelect.options).forEach(function(option) {
                    if (option.value && option.value < fromMonth) {
                        option.disabled = true;
                    } else {
                        option.disabled = false;
                    }
                });
                
                // If current to_month is before from_month, reset it
                if (toMonthSelect.value && toMonthSelect.value < fromMonth) {
                    toMonthSelect.value = fromMonth;
                }
            }
        });
        
        // Auto print when page loads (optional)
        // window.onload = function() {
        //     window.print();
        // }
        
        // Sync heights for equal alignment
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize filter options visibility
            toggleFilterOptions();
            const receiptSection = document.querySelector('.tables-container .table-section:first-child');
            const paymentSection = document.querySelector('.tables-container .table-section:last-child');
            
            if (receiptSection && paymentSection) {
                function syncHeights() {
                    const receiptHeight = receiptSection.offsetHeight;
                    const paymentHeight = paymentSection.offsetHeight;
                    // Only sync heights if both sections have content, otherwise use natural height
                    const receiptRows = receiptSection.querySelectorAll('tbody tr').length;
                    const paymentRows = paymentSection.querySelectorAll('tbody tr').length;
                    
                    if (receiptRows > 0 || paymentRows > 0) {
                        // Only sync if there's actual content
                        const maxHeight = Math.max(receiptHeight, paymentHeight);
                        receiptSection.style.minHeight = maxHeight + 'px';
                        paymentSection.style.minHeight = maxHeight + 'px';
                    } else {
                        // Remove min-height if no content
                        receiptSection.style.minHeight = '';
                        paymentSection.style.minHeight = '';
                    }
                }
                
                syncHeights();
                window.addEventListener('resize', syncHeights);
            }
        });
    </script>
</body>
</html>
