<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock Ledger - {{ $stock_ledger->ledger_name }} - Print</title>
    <style>
        @media print {
            @page { size: A4 portrait; margin: 15mm; }
            body { margin: 0; padding: 0; }
            .no-print { display: none !important; }
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Times New Roman', serif; font-size: 11pt; line-height: 1.4; padding: 20px; background: #fff; }
        .print-actions { text-align: center; margin-bottom: 15px; padding: 10px; background: #f5f5f5; }
        .print-actions button { padding: 8px 20px; margin: 0 5px; font-size: 14px; cursor: pointer; background: #007bff; color: white; border: none; border-radius: 4px; }
        .print-actions button:hover { background: #0056b3; }
        .ledger-header { margin-bottom: 15px; }
        .ledger-title { font-size: 14pt; font-weight: bold; color: #333; }
        .ledger-meta { font-size: 10pt; color: #555; margin-top: 5px; }
        .stock-table { width: 100%; border-collapse: collapse; border: 1px solid #333; margin-top: 10px; }
        .stock-table th, .stock-table td { border: 1px solid #333; padding: 6px 8px; text-align: left; font-size: 10pt; }
        .stock-table th { background: #f0f0f0; font-weight: bold; }
        .stock-table .text-end { text-align: right; }
        .stock-table .text-center { text-align: center; }
        .stock-table tfoot th { background: #e8e8e8; }
        .mt-1 { margin-top: 0.25rem; }
    </style>
</head>
<body>
    <div class="no-print print-actions">
        <button type="button" onclick="window.print()">Print</button>
        <button type="button" onclick="window.close()">Close</button>
    </div>

    <div class="ledger-header">
        <div class="ledger-title">Stock Ledger: {{ $stock_ledger->ledger_name }}</div>
        <div class="ledger-meta">
            Item: {{ $stock_ledger->item->name ?? '-' }} &nbsp;|&nbsp;
            Period: {{ $stock_ledger->date_from->format('d/m/Y') }} to {{ $stock_ledger->date_to->format('d/m/Y') }} &nbsp;|&nbsp;
            Opening Balance: {{ number_format($stock_ledger->opening_balance, 2) }} {{ $stock_ledger->item->unit ?? 'kg' }}
        </div>
        @if($stock_ledger->description)
            <div class="ledger-meta mt-1">{{ $stock_ledger->description }}</div>
        @endif
    </div>

    <table class="stock-table">
        <thead>
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
                <td class="fw-semibold" style="font-weight: bold;">Opening Balance</td>
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
                    <td colspan="6" class="text-center">No stock entries in this period.</td>
                </tr>
            @endforelse
        </tbody>
        @if($stocks->isNotEmpty())
        <tfoot>
            <tr>
                <th colspan="3" class="text-end">Total</th>
                <th class="text-end">{{ number_format($totalReceipt ?? 0, 2) }}</th>
                <th class="text-end">{{ number_format($totalIssued ?? 0, 2) }}</th>
                <th class="text-end">{{ number_format($closingBalance ?? 0, 2) }} {{ $stock_ledger->item->unit ?? 'kg' }}</th>
            </tr>
        </tfoot>
        @endif
    </table>
</body>
</html>
