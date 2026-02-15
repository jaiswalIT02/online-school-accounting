<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ledger A/C. of {{ $ledger->name }} - Print</title>
    <style>
        @media print {
            @page {
                size: A4 portrait;
                margin: 15mm;
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
        
        .ledger-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 10px;
        }
        
        .ledger-title {
            font-size: 14pt;
            font-weight: bold;
            color: #0066cc;
        }
        
        .ledger-title .label {
            color: #0066cc;
        }
        
        .ledger-title .account-name {
            color: #000;
            font-style: italic;
            margin-left: 5px;
        }
        
        .page-number {
            font-size: 11pt;
            color: #000;
            margin-top: 2px;
        }
        
        .ledger-table {
            width: 100%;
            border-collapse: collapse;
            border: 2px solid #0066cc;
            margin-top: 5px;
        }
        
        .ledger-table thead {
            background: #fff;
        }
        
        .ledger-table th {
            border: 1px solid #0066cc;
            padding: 8px 6px;
            text-align: center;
            font-weight: bold;
            font-size: 10pt;
            color: #0066cc;
            background: #fff;
        }
        
        .ledger-table th.particulars {
            text-align: left;
            padding-left: 8px;
        }
        
        .ledger-table td {
            border: 1px solid #0066cc;
            padding: 6px 4px;
            vertical-align: top;
            font-size: 10pt;
        }
        
        .ledger-table td.date-col {
            text-align: center;
            width: 12%;
        }
        
        .ledger-table td.particulars-col {
            text-align: left;
            padding-left: 8px;
            width: 30%;
        }
        
        .ledger-table td.folio-col {
            text-align: center;
            width: 8%;
        }
        
        .ledger-table td.amount-col {
            text-align: right;
            padding-right: 8px;
            width: 12%;
        }
        
        .ledger-table td.balance-type-col {
            text-align: center;
            width: 6%;
        }
        
        .ledger-table td.balance-col {
            text-align: right;
            padding-right: 8px;
            width: 12%;
        }
        
        .amount-split {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .amount-split .rs {
            flex: 1;
            text-align: right;
            padding-right: 4px;
        }
        
        .amount-split .p {
            width: 30px;
            text-align: center;
            font-size: 9pt;
            color: #666;
        }
        
        .particulars-text {
            font-weight: 500;
        }
        
        .particulars-note {
            font-size: 9pt;
            color: #555;
            font-style: italic;
            margin-top: 2px;
        }
        
        .opening-balance-row {
            background: #f9f9f9;
        }
        
        .ledger-table tbody tr {
            page-break-inside: avoid;
        }
        
        .footer-border {
            border-top: 2px solid #0066cc;
            margin-top: 10px;
            padding-top: 5px;
        }
        
        .decorative-border {
            border-top: 1px solid #9b59b6;
            margin-top: 15px;
            padding-top: 5px;
            background: repeating-linear-gradient(
                90deg,
                transparent,
                transparent 10px,
                #9b59b6 10px,
                #9b59b6 12px
            );
            height: 3px;
        }
    </style>
</head>
<body>
    <div class="no-print print-actions">
        <button onclick="window.print()">Print</button>
        <button onclick="window.close()">Close</button>
    </div>

    <div class="ledger-header">
        <div class="ledger-title">
            <span class="label">LEDGER A/C. of</span>
            <span class="account-name">{{ $ledger->name }}</span>
        </div>
        {{-- <div class="page-number">11</div> --}}
    </div>

    @php
        $pageNumber = 1;
    @endphp
    @foreach($pages as $index => $items)
    <table class="ledger-table">
        <thead>
            <tr>
                <th style="width: 12%">Month & Date</th>
                <th class="particulars" style="width: 30%">PARTICULARS</th>
                <th style="width: 8%">Folio No.</th>
                <th style="width: 12%">
                    <div>DEBIT</div>
                    <div style="display: flex; justify-content: space-around; font-size: 9pt; font-weight: normal; margin-top: 2px;">
                        <span>Rs.</span>
                        <span>P.</span>
                    </div>
                </th>
                <th style="width: 12%">
                    <div>CREDIT</div>
                    <div style="display: flex; justify-content: space-around; font-size: 9pt; font-weight: normal; margin-top: 2px;">
                        <span>Rs.</span>
                        <span>P.</span>
                    </div>
                </th>
                <th style="width: 6%">Cr. or Dr.</th>
                <th style="width: 12%">
                    <div>BALANCE</div>
                    <div style="display: flex; justify-content: space-around; font-size: 9pt; font-weight: normal; margin-top: 2px;">
                        <span>Rs.</span>
                        <span>P.</span>
                    </div>
                </th>
            </tr>
        </thead>
        <tbody> 
            
                
                <tr style="background: #f0f0f0; font-weight: bold;">
                    <td class="date-col"  style="text-align: center; padding-right: 8px;">{{ $items['opening_date']->format('d/m/y')}}</td>
                    <td class="date-col" style="text-align: left; padding-right: 8px;">Opening Balance</td>
                    <td class="date-col" style="text-align: right; padding-right: 8px;"></td>
                    <td class="date-col" style="text-align: right; padding-right: 8px;"></td>
                    <td class="balance-col">
                        {{-- <div class="amount-split">
                            <span class="rs">{{ number_format($items['opening_balance'] ?? 0, 2, '.', ',') }}</span>
                        </div> --}}
                    </td>
                    <td>
                    <div style="text-align: center; font-size: 9pt; margin-top: 2px;">
                        {{-- {{ $closingBalanceType ?? 'Dr' }} --}}
                    </div>

                    </td>
                    <td class="amount-col">
                        <div class="amount-split">
                            <span class="rs">{{ number_format($items['opening_balance'] ?? 0, 2, '.', ',') }}</span>
                            {{-- <span class="p">00</span> --}}
                        </div>
                    </td>
                </tr>
               
                @forelse ($items['entries'] as $row)
                
                    @if ($row['is_opening'] ?? false)
                        <tr class="opening-balance-row">
                            <td class="date-col">-</td>
                            <td class="particulars-col">
                                <span class="particulars-text">Opening Balance</span>
                            </td>
                            <td class="folio-col">-</td>
                            <td class="amount-col">
                                <div class="amount-split">
                                    <span class="rs">-</span>
                                    {{-- <span class="p">00</span> --}}
                                </div>
                            </td>
                            <td class="amount-col">
                                <div class="amount-split">
                                    <span class="rs">-</span>
                                    {{-- <span class="p">00</span> --}}
                                </div>
                            </td>
                            <td class="balance-type-col">{{ $row['balance_type'] }}</td>
                            <td class="balance-col">
                                <div class="amount-split">
                                    <span class="rs">{{ number_format($row['balance'], 2, '.', ',') }}</span>
                                    {{-- <span class="p">00</span> --}}
                                </div>
                            </td>
                        </tr>
                    @else
                     
                        @php($entry = $row['entry'])
                    

                        <tr>
                            <td class="date-col">{{ $entry?->entry_date?->format('d/m/y') }}</td>
                            <td class="particulars-col">
                                <span class="particulars-text">{{ $entry->particulars }}</span>
                                @if ($entry->narration)
                                    <div class="particulars-note">{{ $entry->narration }}</div>
                                @endif
                            </td>
                            <td class="folio-col">{{ $entry->folio_no ?? '' }}</td>
                            <td class="amount-col">
                                @if ($entry->debit > 0)
                                    <div class="amount-split">
                                        <span class="rs">{{ number_format($entry->debit, 2, '.', ',') }}</span>
                                        {{-- <span class="p">00</span> --}}
                                    </div>
                                @else
                                    <div class="amount-split">
                                        <span class="rs">-</span>
                                        {{-- <span class="p">00</span> --}}
                                    </div>
                                @endif
                            </td>
                            <td class="amount-col">
                                @if ($entry->credit > 0)
                                    <div class="amount-split">
                                        <span class="rs">{{ number_format($entry->credit, 2, '.', ',') }}</span>
                                        {{-- <span class="p">00</span> --}}
                                    </div>
                                @else
                                    <div class="amount-split">
                                        <span class="rs">-</span>
                                        {{-- <span class="p">00</span> --}}
                                    </div>
                                @endif
                            </td>
                            <td class="balance-type-col">{{ $row['balance_type'] }}</td>
                            <td class="balance-col">
                                <div class="amount-split">
                                     <span class="rs">{{ number_format($entry->balance, 2, '.', ',') }}</span>
                                    {{-- <span class="rs">00</span> --}}
                                    {{-- <span class="p">00</span> --}}
                                </div>
                            </td>
                        </tr>
                    @endif
                @empty
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 20px;">No entries yet.</td>
                    </tr>
                @endforelse
                
                <tr style="background: #f9f9f9; font-weight: bold;">
                    <td class="date-col" colspan="3" style="text-align: right; padding-right: 8px;">Total</td>
                    <td class="amount-col">
                        <div class="amount-split">
                            <span class="rs">{{ number_format($items['total_debit'] ?? 0, 2, '.', ',') }}</span>
                            {{-- <span class="p">00</span> --}}
                        </div>
                    </td>
                    <td class="amount-col">
                        <div class="amount-split">
                            <span class="rs">{{ number_format($items['total_credit'] ?? 0, 2, '.', ',') }}</span>
                            {{-- <span class="p">00</span> --}}
                        </div>
                    </td>
                    <td class="balance-type-col">{{ $closingBalanceType ?? 'Dr' }}</td>
                    <td class="balance-col">
                        <div class="amount-split">
                            <span class="rs">{{ number_format($items['closing_balance'] ?? 0, 2, '.', ',') }}</span>
                            {{-- <span class="rs">00</span> --}}
                            {{-- <span class="p">00</span> --}}
                        </div>
                    </td>
                </tr>
                <tr style="background: #f0f0f0; font-weight: bold;">
                    <td class="date-col" style="text-align: center; padding-right: 8px;">{{ $items['closing_date']->format('d/m/y')}}</td>
                    <td class="date-col" style="text-align: left; padding-right: 8px;">Closing Balance</td>
                    <td class="date-col" style="text-align: right; padding-right: 8px;"></td>
                    <td class="date-col" style="text-align: right; padding-right: 8px;"></td>
                    
                    <td class="balance-col">
                        {{-- <div class="amount-split">
                            <span class="rs">{{ number_format($items['closing_balance'] ?? 0, 2, '.', ',') }}</span>
                        </div> --}}
                    </td>
                    <td>
                    <div style="text-align: center; font-size: 9pt; margin-top: 2px;">
                        {{-- {{ $closingBalanceType ?? 'Dr' }} --}}
                    </div>

                    </td>
                    <td class="amount-col">
                        <div class="amount-split">
                            <span class="rs">{{ number_format($items['closing_balance'] ?? 0, 2, '.', ',') }}</span>
                        </div>
                    </td>
                </tr>
                @if ($index < $pages->count() - 1)
                    <tr style="page-break-after: always;">
                        <td colspan="8"></td>
                    </tr>
                @endif
                
           
            
            {{-- <tr style="background: #f9f9f9; font-weight: bold;">
                <td class="date-col" colspan="3" style="text-align: right; padding-right: 8px;">Total</td>
                <td class="amount-col">
                    <div class="amount-split">
                        <span class="rs">{{ number_format($totalDebit ?? 0, 0, '.', ',') }}</span>
                        <span class="p">00</span>
                    </div>
                </td>
                <td class="amount-col">
                    <div class="amount-split">
                        <span class="rs">{{ number_format($totalCredit ?? 0, 0, '.', ',') }}</span>
                        <span class="p">00</span>
                    </div>
                </td>
                <td class="balance-type-col">{{ $closingBalanceType ?? 'Dr' }}</td>
                <td class="balance-col">
                    <div class="amount-split">
                        <!-- <span class="rs">{{ number_format($closingBalance ?? 0, 0, '.', ',') }}</span> -->
                        <span class="rs">00</span>
                        <span class="p">00</span>
                    </div>
                </td>
            </tr>
            <tr style="background: #f0f0f0; font-weight: bold;">
                <td class="date-col" colspan="4" style="text-align: right; padding-right: 8px;">Closing Balance</td>
                
                <td class="balance-col">
                    <div class="amount-split">
                        <!-- <span class="rs">{{ number_format($closingBalance ?? 0, 0, '.', ',') }}</span> -->
                        <span class="p">00</span>
                    </div>
                </td>
                <td>
                <div style="text-align: center; font-size: 9pt; margin-top: 2px;">{{ $closingBalanceType ?? 'Dr' }}</div>

                </td>
                <td class="amount-col">
                    <div class="amount-split">
                        <span class="rs">{{ number_format($totalCredit ?? 0, 0, '.', ',') }}</span>
                        <span class="p">00</span>
                    </div>
                </td>
            </tr> --}}
        </tbody>
    </table>
     @endforeach

    <div class="decorative-border"></div>

    <script>
        // Auto print when page loads (optional - commented out)
        // window.onload = function() {
        //     window.print();
        // }
    </script>
</body>
</html>
