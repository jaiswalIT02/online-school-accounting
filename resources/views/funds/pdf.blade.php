@php
    $title = 'Fund Records';
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
        }
        h2 {
            text-align: center;
            margin-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #000;
            padding: 4px 6px;
        }
        th {
            background-color: #f0f0f0;
        }
        .text-right {
            text-align: right;
        }
        .summary-table {
            margin-bottom: 10px;
        }
        .summary-table th, .summary-table td {
            border: none;
            padding: 2px 4px;
        }
    </style>
</head>
<body>
    <h2>{{ $title }}</h2>

    <table class="summary-table">
        <tr>
            <th>Total Records:</th>
            <td>{{ number_format($totalCount ?? 0) }}</td>
            <th>Total Amount:</th>
            <td>₹{{ number_format($totalAmount ?? 0, 2) }}</td>
            <th>Average Amount:</th>
            <td>₹{{ number_format($averageAmount ?? 0, 2) }}</td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 12%;">Date</th>
                <th style="width: 25%;">Component Name</th>
                <th style="width: 18%;">Component Code</th>
                <th style="width: 15%;">Amount</th>
                <th style="width: 25%;">Remark</th>
            </tr>
        </thead>
        <tbody>
            @forelse($funds as $index => $fund)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $fund->fund_date }}</td>
                    <td>{{ $fund->component_name ?? '-' }}</td>
                    <td>{{ $fund->component_code ?? '-' }}</td>
                    <td class="text-right">₹{{ number_format($fund->amount ?? 0, 2) }}</td>
                    <td>{{ $fund->remark ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center;">No fund records found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>

