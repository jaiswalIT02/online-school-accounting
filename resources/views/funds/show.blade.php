@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Fund Records - View All</h4>
        <a href="{{ route('funds.index') }}" class="btn btn-outline-secondary">Back to Manage Funds</a>
    </div>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <!-- Filters Section -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Filter Options</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('funds.view_all') }}" class="row g-3" id="filterForm">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Search</label>
                    <input 
                        type="text" 
                        class="form-control" 
                        name="search" 
                        value="{{ request('search') }}" 
                        placeholder="Component name, Component code, Remark..."
                    >
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Component Name</label>
                    <select class="form-select" name="component_name">
                        <option value="">All Components</option>
                        @foreach ($uniqueComponents ?? [] as $componentName)
                            <option value="{{ $componentName }}" @selected(request('component_name') == $componentName)>
                                {{ $componentName }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Date From</label>
                    <input 
                        type="text" 
                        class="form-control date-input" 
                        name="date_from" 
                        id="date_from"
                        value="{{ request('date_from') }}"
                        placeholder="dd/mm/yyyy"
                        pattern="\d{2}/\d{2}/\d{4}"
                    >
                    <small class="form-text text-muted">Format: dd/mm/yyyy</small>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Date To</label>
                    <input 
                        type="text" 
                        class="form-control date-input" 
                        name="date_to" 
                        id="date_to"
                        value="{{ request('date_to') }}"
                        placeholder="dd/mm/yyyy"
                        pattern="\d{2}/\d{2}/\d{4}"
                    >
                    <small class="form-text text-muted">Format: dd/mm/yyyy</small>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Month</label>
                    <select class="form-select" name="month_only">
                        <option value="">All Months</option>
                        @php
                            $months = [
                                '01' => 'January',
                                '02' => 'February',
                                '03' => 'March',
                                '04' => 'April',
                                '05' => 'May',
                                '06' => 'June',
                                '07' => 'July',
                                '08' => 'August',
                                '09' => 'September',
                                '10' => 'October',
                                '11' => 'November',
                                '12' => 'December',
                            ];
                            $selectedMonthOnly = str_pad(request('month_only', ''), 2, '0', STR_PAD_LEFT);
                        @endphp
                        @foreach ($months as $value => $label)
                            <option value="{{ $value }}" {{ $selectedMonthOnly === $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Amount From</label>
                    <input 
                        type="number" 
                        step="0.01" 
                        class="form-control" 
                        name="amount_from" 
                        value="{{ request('amount_from') }}" 
                        placeholder="Min"
                    >
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Amount To</label>
                    <input 
                        type="number" 
                        step="0.01" 
                        class="form-control" 
                        name="amount_to" 
                        value="{{ request('amount_to') }}" 
                        placeholder="Max"
                    >
                </div>
                <div class="col-md-2 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                    <a href="{{ route('funds.view_all') }}" class="btn btn-outline-secondary w-100">Clear</a>
                </div>
            </form>
            @if (request()->hasAny(['search', 'component_name', 'component_code', 'month_only', 'date_from', 'date_to', 'amount_from', 'amount_to']))
                <div class="mt-3">
                    <a href="{{ route('funds.view_all') }}" class="btn btn-sm btn-outline-secondary">Clear All Filters</a>
                    <span class="ms-2 text-muted">
                        Showing {{ $funds->count() }} record(s)
                    </span>
                </div>
            @endif
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center border-primary">
                <div class="card-body">
                    <h6 class="card-title text-muted mb-2">Total Records</h6>
                    <h3 class="mb-0 text-primary">{{ number_format($totalCount ?? 0) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center border-success">
                <div class="card-body">
                    <h6 class="card-title text-muted mb-2">Total Amount</h6>
                    <h3 class="mb-0 text-success">₹{{ number_format($totalAmount ?? 0, 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center border-info">
                <div class="card-body">
                    <h6 class="card-title text-muted mb-2">Average Amount</h6>
                    <h3 class="mb-0 text-info">₹{{ number_format($averageAmount ?? 0, 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center border-warning">
                <div class="card-body">
                    <h6 class="card-title text-muted mb-2">Filtered Records</h6>
                    <h3 class="mb-0 text-warning">{{ number_format($totalCount ?? 0) }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Funds Table -->
    <div class="card">
        <div class="card-header bg-light">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Fund Records ({{ $funds->total() }})</h5>
                <div>
                    <button onclick="window.print()" class="btn btn-sm btn-outline-primary">Print</button>
                    <button onclick="exportTableToCSV()" class="btn btn-sm btn-outline-success">Export CSV</button>
                    <a href="{{ route('funds.export.excel', request()->query()) }}" class="btn btn-sm btn-outline-success">
                        <i class="fas fa-file-excel"></i> Export Excel
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0" id="fundsTable">
                    <thead class="table-dark">
                        <tr>
                            <th style="width: 5%">#</th>
                            <th style="width: 12%">Date</th>
                            <th style="width: 20%">Component Name</th>
                            <th style="width: 15%">Component Code</th>
                            <th style="width: 15%" class="text-end">Amount</th>
                            <th style="width: 25%">Remark</th>
                            <th style="width: 8%" class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($funds as $index => $fund)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $fund->fund_date }}</td>
                                <td>
                                    <div class="fw-semibold">{{ $fund->component_name ?? '-' }}</div>
                                </td>
                                <td>{{ $fund->component_code ?? '-' }}</td>
                                <td class="text-end fw-semibold">₹{{ number_format($fund->amount ?? 0, 2) }}</td>
                                <td>{{ $fund->remark ?? '-' }}</td>
                                <td class="text-center">
                                    <div class="d-flex gap-1 justify-content-center">
                                        <a href="{{ route('funds.index', ['edit' => $fund->id]) }}" class="btn btn-sm btn-primary" title="Edit">Edit</a>
                                        <form method="POST" action="{{ route('funds.destroy', $fund) }}" class="d-inline" onsubmit="return confirm('Delete this fund record?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="Delete">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-5">
                                    <p class="mt-2 mb-0">No fund records found.</p>
                                    @if (request()->hasAny(['search', 'component_name', 'component_code', 'month_only', 'date_from', 'date_to', 'amount_from', 'amount_to']))
                                        <a href="{{ route('funds.view_all') }}" class="btn btn-sm btn-outline-primary mt-2">Clear Filters</a>
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($funds->count() > 0)
                        <tfoot class="table-light">
                            <tr>
                                <th colspan="3" class="text-end">Total:</th>
                                <th class="text-end">₹{{ number_format($totalAmount ?? 0, 2) }}</th>
                                <th colspan="2"></th>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
        @if($funds->hasPages())
            <div class="card-footer">
                {{ $funds->links() }}
            </div>
        @endif
    </div>
</div>

<style>
    @media print {
        .card-header, .btn, .d-flex.justify-content-between, nav, .container > .d-flex {
            display: none !important;
        }
        .card {
            border: none !important;
            box-shadow: none !important;
        }
        table {
            font-size: 12px;
        }
    }
</style>

<script>
    // Auto-format date inputs to dd/mm/yyyy
    document.addEventListener('DOMContentLoaded', function() {
        const dateInputs = document.querySelectorAll('.date-input');
        
        dateInputs.forEach(function(input) {
            // Format existing value if it's in YYYY-MM-DD format (from old date inputs)
            if (input.value && /^\d{4}-\d{2}-\d{2}$/.test(input.value)) {
                const parts = input.value.split('-');
                input.value = parts[2] + '/' + parts[1] + '/' + parts[0]; // Convert YYYY-MM-DD to dd/mm/yyyy
            }
            
            // Auto-format as user types
            input.addEventListener('input', function(e) {
                let raw = e.target.value.replace(/\D/g, '');
                raw = raw.substring(0, 8); // max 8 digits
                let formatted = '';
                if (raw.length <= 2) {
                    formatted = raw;
                } else if (raw.length <= 4) {
                    formatted = raw.substring(0, 2) + '/' + raw.substring(2);
                } else {
                    formatted = raw.substring(0, 2) + '/' + raw.substring(2, 4) + '/' + raw.substring(4);
                }
                e.target.value = formatted;
            });
            
            // Prevent typing beyond 8 digits
            input.addEventListener('keydown', function(e) {
                if (e.target.value.replace(/\D/g, '').length >= 8 && /\d/.test(e.key) && !e.ctrlKey && !e.metaKey) {
                    e.preventDefault();
                }
            });
        });
    });

    function exportTableToCSV() {
        const table = document.getElementById('fundsTable');
        const rows = table.querySelectorAll('tr');
        let csv = [];

        // Get headers
        const headers = [];
        table.querySelectorAll('thead th').forEach(th => {
            if (th.textContent.trim() !== 'Actions') {
                headers.push(th.textContent.trim());
            }
        });
        csv.push(headers.join(','));

        // Get data rows (skip footer)
        rows.forEach((row, index) => {
            if (row.closest('thead') || row.closest('tfoot')) return;
            
            const cols = row.querySelectorAll('td');
            if (cols.length === 0) return;
            
            let rowData = [];
            cols.forEach((col, colIndex) => {
                // Skip actions column (last column)
                if (colIndex < cols.length - 1) {
                    let text = col.textContent.trim().replace(/"/g, '""');
                    rowData.push('"' + text + '"');
                }
            });
            csv.push(rowData.join(','));
        });

        // Add total row
        const footer = table.querySelector('tfoot tr');
        if (footer) {
            let footerData = [];
            footer.querySelectorAll('th').forEach((th, index) => {
                if (index < 5) {
                    let text = th.textContent.trim().replace(/"/g, '""');
                    footerData.push('"' + text + '"');
                }
            });
            csv.push(footerData.join(','));
        }

        // Download CSV
        const csvContent = csv.join('\n');
        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        const url = URL.createObjectURL(blob);
        link.setAttribute('href', url);
        link.setAttribute('download', 'funds_' + new Date().toISOString().split('T')[0] + '.csv');
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
</script>
@endsection
