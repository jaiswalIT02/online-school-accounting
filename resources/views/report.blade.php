@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Reports</h4>
    </div>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <!-- Filters Card -->
    <div class="card mb-3">
        <div class="card-header">
            <h5 class="mb-0">Filters</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('reports.index') }}" id="filterForm">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Month - Optional</label>
                        <select class="form-select" name="month" id="month">
                            <option value="">All Months</option>
                            <option value="1" {{ $month == '1' ? 'selected' : '' }}>January</option>
                            <option value="2" {{ $month == '2' ? 'selected' : '' }}>February</option>
                            <option value="3" {{ $month == '3' ? 'selected' : '' }}>March</option>
                            <option value="4" {{ $month == '4' ? 'selected' : '' }}>April</option>
                            <option value="5" {{ $month == '5' ? 'selected' : '' }}>May</option>
                            <option value="6" {{ $month == '6' ? 'selected' : '' }}>June</option>
                            <option value="7" {{ $month == '7' ? 'selected' : '' }}>July</option>
                            <option value="8" {{ $month == '8' ? 'selected' : '' }}>August</option>
                            <option value="9" {{ $month == '9' ? 'selected' : '' }}>September</option>
                            <option value="10" {{ $month == '10' ? 'selected' : '' }}>October</option>
                            <option value="11" {{ $month == '11' ? 'selected' : '' }}>November</option>
                            <option value="12" {{ $month == '12' ? 'selected' : '' }}>December</option>
                        </select>
                        <small class="form-text text-muted">Select a month (ignored if date range is set)</small>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Date From - Optional</label>
                        <input type="text" class="form-control datepicker" name="date_from" 
                               value="{{ $dateFrom ?? '' }}" placeholder="dd/mm/yyyy" maxlength="10" autocomplete="off">
                        <small class="form-text text-muted">Start date (takes priority over month). Use dd/mm/yyyy</small>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Date To - Optional</label>
                        <input type="text" class="form-control datepicker" name="date_to" 
                               value="{{ $dateTo ?? '' }}" placeholder="dd/mm/yyyy" maxlength="10" autocomplete="off">
                        <small class="form-text text-muted">End date (optional - if empty, filters for Date From only). Use dd/mm/yyyy</small>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Component (Article) - Optional</label>
                        <select class="form-select" name="article_id" id="article_id">
                            <option value="">All Components</option>
                            @foreach($articles as $article)
                                <option value="{{ $article->id }}" {{ $articleId == $article->id ? 'selected' : '' }}>
                                    {{ $article->name }} ({{ $article->acode }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <button type="submit" class="btn btn-primary me-2">Apply Filters</button>
                        <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary">Reset</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Tax Totals Section -->
    <div class="card mb-3">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">
                Tax Totals
                @if($dateFrom && $dateTo)
                    - From {{ $dateFrom }} to {{ $dateTo }}
                @elseif($month)
                    - {{ \Carbon\Carbon::create(date('Y'), $month, 1)->format('F Y') }}
                @else
                    - All Time
                @endif
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="card border-success">
                        <div class="card-body text-center">
                            <h6 class="text-muted mb-2">Total P Tax</h6>
                            <h3 class="text-success mb-0">₹ {{ number_format($taxTotals['pTax'], 2) }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-info">
                        <div class="card-body text-center">
                            <h6 class="text-muted mb-2">Total TDS</h6>
                            <h3 class="text-info mb-0">₹ {{ number_format($taxTotals['tds'], 2) }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-primary">
                        <div class="card-body text-center">
                            <h6 class="text-muted mb-2">Total Tax</h6>
                            <h3 class="text-primary mb-0">₹ {{ number_format($taxTotals['total'], 2) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Totals by Article Section -->
    <div class="card">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0">
                Payment Totals by Component
                @if($dateFrom && $dateTo)
                    - From {{ $dateFrom }} to {{ $dateTo }}
                @elseif($month)
                    - {{ \Carbon\Carbon::create(date('Y'), $month, 1)->format('F Y') }}
                @else
                    - All Time
                @endif
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Component Name</th>
                            <th>Component Code</th>
                            <th class="text-end">Total Payments</th>
                            <th class="text-center">No. of Transactions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($paymentTotals as $total)
                            <tr>
                                <td>{{ $total['article_name'] }}</td>
                                <td>{{ $total['article_acode'] }}</td>
                                <td class="text-end fw-bold">₹ {{ number_format($total['total_amount'], 2) }}</td>
                                <td class="text-center">{{ $total['count'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted">
                                    @if($articleId)
                                        No payment data found for selected component
                                        @if($dateFrom && $dateTo)
                                            from {{ $dateFrom }} to {{ $dateTo }}.
                                        @elseif($month)
                                            in {{ \Carbon\Carbon::create(date('Y'), $month, 1)->format('F Y') }}.
                                        @else
                                            for the selected period.
                                        @endif
                                    @else
                                        No payment data found
                                        @if($dateFrom && $dateTo)
                                            from {{ $dateFrom }} to {{ $dateTo }}.
                                        @elseif($month)
                                            in {{ \Carbon\Carbon::create(date('Y'), $month, 1)->format('F Y') }}.
                                        @else
                                            for the selected period.
                                        @endif
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if(count($paymentTotals) > 0)
                    <tfoot class="table-light">
                        <tr>
                            <th colspan="2" class="text-end">Grand Total:</th>
                            <th class="text-end">₹ {{ number_format(array_sum(array_column($paymentTotals, 'total_amount')), 2) }}</th>
                            <th class="text-center">{{ array_sum(array_column($paymentTotals, 'count')) }}</th>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>

    
</div>

<script>
// Date formatting: dd/mm/yyyy (e.g. 01/12/2025)
// Accepts digits only and inserts slashes after dd and mm
function formatDate(input) {
    let value = input.value.trim();
    
    // If value contains dashes, convert to slashes for consistency
    if (value.includes('-')) {
        value = value.replace(/-/g, '/');
    }
    
    // Remove all non-digits
    const digits = value.replace(/\D/g, '');
    
    if (digits.length === 0) {
        input.value = '';
        input.classList.remove('is-invalid');
        return;
    }
    
    // Limit to 8 digits (ddmmyyyy)
    const limited = digits.substring(0, 8);
    
    // Build dd/mm/yyyy
    let formatted = limited.substring(0, 2);
    if (limited.length > 2) {
        formatted += '/' + limited.substring(2, 4);
    }
    if (limited.length > 4) {
        formatted += '/' + limited.substring(4, 8);
    }
    
    input.value = formatted;
    validateDateInput(input);
}

// Validate date input format dd/mm/yyyy
function validateDateInput(input) {
    let value = input.value.trim();
    
    // Convert dashes to slashes for consistency (store as dd/mm/yyyy)
    if (value.includes('-')) {
        value = value.replace(/-/g, '/');
        input.value = value;
    }
    
    const datePattern = /^(\d{2})\/(\d{2})\/(\d{4})$/;
    
    if (value === '') {
        input.classList.remove('is-invalid');
        return true;
    }
    
    if (!datePattern.test(value)) {
        input.classList.add('is-invalid');
        return false;
    }
    
    const parts = value.split('/');
    const day = parseInt(parts[0], 10);
    const month = parseInt(parts[1], 10);
    const year = parseInt(parts[2], 10);
    
    if (day < 1 || day > 31 || month < 1 || month > 12 || year < 1900 || year > 2100) {
        input.classList.add('is-invalid');
        return false;
    }
    
    const date = new Date(year, month - 1, day);
    if (date.getDate() !== day || date.getMonth() !== month - 1 || date.getFullYear() !== year) {
        input.classList.add('is-invalid');
        return false;
    }
    
    input.classList.remove('is-invalid');
    return true;
}

// Apply date formatting to all datepicker inputs
document.addEventListener('DOMContentLoaded', function() {
    const dateInputs = document.querySelectorAll('.datepicker');
    dateInputs.forEach(input => {
        // Add input event listener for auto-formatting
        input.addEventListener('input', function() {
            formatDate(this);
        });
        
        // Validate on blur
        input.addEventListener('blur', function() {
            validateDateInput(input);
        });
        
        // Validate existing value on load
        if (input.value) {
            validateDateInput(input);
        }
        
        // Set maxlength to limit input
        input.setAttribute('maxlength', '10');
        
        // Set placeholder
        if (!input.value) {
            input.placeholder = 'dd/mm/yyyy';
        }
    });
    
    // Form validation before submit
    const filterForm = document.getElementById('filterForm');
    if (filterForm) {
        filterForm.addEventListener('submit', function(e) {
            let isValid = true;
            const invalidInputs = [];
            
            // Format and validate all date inputs before submission
            dateInputs.forEach(input => {
                const value = input.value.trim();
                
                // If there's a value, ensure it's properly formatted
                if (value !== '') {
                    // Re-format to ensure proper format
                    formatDate(input);
                    
                    // Validate the formatted value
                    if (!validateDateInput(input)) {
                        isValid = false;
                        invalidInputs.push(input.getAttribute('name') || input.name);
                    } else {
                        // Ensure the value is properly set
                        input.value = input.value.trim();
                    }
                } else {
                    // Empty dates are allowed - clear any invalid class
                    input.classList.remove('is-invalid');
                }
            });
            
            // Validate date range
            const dateFrom = document.querySelector('input[name="date_from"]').value.trim();
            const dateTo = document.querySelector('input[name="date_to"]').value.trim();
            const month = document.querySelector('select[name="month"]').value;
            
            // Date From can be used alone (will filter for that specific date)
            // Date To is optional unless Date From is provided, then it's recommended but not required
            
            // Warn if both month and date filters are used (date filter takes priority)
            if (month && (dateFrom || dateTo)) {
                if (!confirm('You have selected both Month and Date filters. Date filter will take priority. Continue?')) {
                    isValid = false;
                }
            }
            
            if (!isValid) {
                e.preventDefault();
                if (invalidInputs.length > 0) {
                    alert('Please enter valid dates in dd/mm/yyyy format (e.g., 01/01/2023) for: ' + invalidInputs.join(', '));
                }
                return false;
            }
            
            // Ensure all form values are properly set before submission
            dateInputs.forEach(input => {
                // Make sure the input is enabled and has the correct value
                input.disabled = false;
            });
        });
    }
});
</script>
@endsection
