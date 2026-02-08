@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Fund Management</h4>
        <div>
            <a href="{{ route('funds.import') }}" class="btn btn-success me-2">
                <i class="fas fa-file-upload"></i> Import Excel
            </a>
            <a href="{{ route('funds.view_all') }}" class="btn btn-outline-primary">View All Records</a>
        </div>
    </div>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <!-- Create/Edit Form -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">
                @if(isset($editFund))
                    Edit Fund Record
                @else
                    Add New Fund Record
                @endif
            </h5>
        </div>
        <div class="card-body">
            <form id="fundForm" method="POST" action="{{ isset($editFund) ? route('funds.update', $editFund) : route('funds.store') }}">
                @csrf
                @if(isset($editFund))
                    @method('PUT')
                @endif

                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label" for="fund_date">Date</label>
                        <input
                            type="text"
                            class="form-control @error('fund_date') is-invalid @enderror"
                            id="fund_date"
                            name="fund_date"
                            placeholder="dd/mm/yyyy"
                            maxlength="10"
                            value="{{ old('fund_date', isset($editFund) ? $editFund->fund_date : '') }}"
                        >
                        @error('fund_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Enter date as dd/mm/yyyy (e.g., 19/01/2026)</small>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label" for="component_name">Component Name</label>
                        <select
                            class="form-select @error('component_name') is-invalid @enderror"
                            id="component_name"
                            name="component_name"
                        >
                            <option value="">-- Select Component --</option>
                            @foreach ($articles ?? [] as $article)
                                <option value="{{ $article->name }}" 
                                    data-code="{{ $article->acode }}"
                                    @selected(old('component_name', $editFund->component_name ?? '') == $article->name)>
                                    {{ $article->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('component_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="form-label" for="component_code">Component Code</label>
                        <input
                            type="text"
                            class="form-control @error('component_code') is-invalid @enderror"
                            id="component_code"
                            name="component_code"
                            value="{{ old('component_code', $editFund->component_code ?? '') }}"
                            readonly
                        >
                        @error('component_code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="form-label" for="amount">Amount</label>
                        <input
                            type="number"
                            step="0.01"
                            min="0"
                            class="form-control @error('amount') is-invalid @enderror"
                            id="amount"
                            name="amount"
                            value="{{ old('amount', $editFund->amount ?? '') }}"
                        >
                        @error('amount')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label class="form-label" for="remark">Remark</label>
                        <textarea
                            class="form-control @error('remark') is-invalid @enderror"
                            id="remark"
                            name="remark"
                            rows="3"
                        >{{ old('remark', $editFund->remark ?? '') }}</textarea>
                        @error('remark')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    @if(isset($editFund))
                        <a href="{{ route('funds.index', request()->except('edit')) }}" class="btn btn-secondary">Cancel</a>
                    @endif
                    <button type="submit" class="btn btn-primary">
                        {{ isset($editFund) ? 'Update' : 'Add' }} Fund Record
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Card -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title">Total Records</h5>
                    <h3 class="mb-0">{{ number_format($totalCount ?? 0) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title">Total Amount</h5>
                    <h3 class="mb-0">₹{{ number_format($totalAmount ?? 0, 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title">Average Amount</h5>
                    <h3 class="mb-0">₹{{ number_format(($totalCount > 0) ? ($totalAmount / $totalCount) : 0, 2) }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Search and Filter Section -->
    <div class="card mb-3 border-primary">
        <div class="card-header bg-primary text-white">
            <h6 class="mb-0">
                <i class="fas fa-filter me-2"></i>Search & Filter
            </h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('funds.index') }}" id="searchForm">
                @if(request('edit'))
                    <input type="hidden" name="edit" value="{{ request('edit') }}">
                @endif
                <div class="row g-3 align-items-end">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-muted">
                            <i class="fas fa-search me-1"></i>Search
                        </label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="fas fa-search text-muted"></i>
                            </span>
                            <input
                                type="text"
                                name="search"
                                class="form-control border-start-0"
                                placeholder="Search by component name, code, or remark..."
                                value="{{ request('search') }}"
                            >
                            @if(request()->has('search') && request('search') !== '')
                                <button type="button" class="btn btn-outline-danger border-start-0" onclick="clearSearch()" title="Clear search">
                                    <i class="fas fa-times"></i>
                                </button>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold text-muted">
                            <i class="fas fa-sort me-1"></i>Sort Order
                        </label>
                        <select name="sort_order" class="form-select form-select-lg" onchange="document.getElementById('searchForm').submit()">
                            <option value="recent" {{ request('sort_order', 'recent') === 'recent' ? 'selected' : '' }}>
                                Recent First (Newest)
                            </option>
                            <option value="old" {{ request('sort_order') === 'old' ? 'selected' : '' }}>
                                Old First (Oldest)
                            </option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100 btn-lg">
                            <i class="fas fa-search me-1"></i>Search
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Funds List -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-list me-2"></i>Fund Records ({{ $funds->total() }})
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 5%">SL No</th>
                            <th style="width: 12%">Date</th>
                            <th style="width: 22%">Component Name</th>
                            <th style="width: 13%">Component Code</th>
                            <th style="width: 13%" class="text-end">Amount</th>
                            <th style="width: 22%">Remark</th>
                            <th style="width: 8%" class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($funds as $index => $fund)
                            <tr>
                                <td>{{ ($funds->currentPage() - 1) * $funds->perPage() + $index + 1 }}</td>
                                <td>{{ $fund->fund_date }}</td>
                                <td>
                                    <div class="fw-semibold">{{ $fund->component_name ?? '-' }}</div>
                                </td>
                                <td>{{ $fund->component_code ?? '-' }}</td>
                                <td class="text-end fw-semibold">₹{{ number_format($fund->amount ?? 0, 2) }}</td>
                                <td>{{ $fund->remark ?? '-' }}</td>
                                <td class="text-center">
                                    <div class="d-flex gap-1 justify-content-center flex-wrap">
                                        <button type="button" class="btn btn-sm btn-success add-from-row"
                                            data-date="{{ $fund->fund_date }}"
                                            data-component-name="{{ e($fund->component_name) }}"
                                            data-component-code="{{ e($fund->component_code) }}"
                                            data-amount="{{ $fund->amount }}"
                                            data-remark="{{ e($fund->remark ?? '') }}"
                                        >Add</button>
                                        <a href="{{ route('funds.index', array_merge(request()->except('edit'), ['edit' => $fund->id])) }}" class="btn btn-sm btn-primary">Edit</a>
                                        <form method="POST" action="{{ route('funds.destroy', $fund) }}" class="d-inline" onsubmit="return confirm('Delete this fund record?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">No fund records found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($funds->count() > 0)
                        <tfoot class="table-light">
                            <tr>
                                <th colspan="4" class="text-end">Total:</th>
                                <th class="text-end">₹{{ number_format($funds->sum('amount'), 2) }}</th>
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

<script>
function clearSearch() {
    const url = new URL(window.location.href);
    url.searchParams.delete('search');
    window.location.href = url.toString();
}

document.addEventListener('DOMContentLoaded', function() {
    const dateInput = document.getElementById('fund_date');
    const componentNameSelect = document.getElementById('component_name');
    const componentCodeInput = document.getElementById('component_code');
    const fundForm = document.getElementById('fundForm');
    const formCard = document.querySelector('.card.mb-4');
    const storeUrl = '{{ route("funds.store") }}';

    // Auto-format date: keep exactly 8 digits (ddmmyyyy) then format as dd/mm/yyyy so editing month doesn't lose year
    if (dateInput) {
        dateInput.addEventListener('input', function(e) {
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
        dateInput.addEventListener('keydown', function(e) {
            if (e.target.value.replace(/\D/g, '').length >= 8 && /\d/.test(e.key) && !e.ctrlKey && !e.metaKey) {
                e.preventDefault();
            }
        });
    }

    // Auto-populate component_code when component_name is selected
    if (componentNameSelect && componentCodeInput) {
        componentNameSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const code = selectedOption.getAttribute('data-code');
            componentCodeInput.value = code || '';
        });
    }

    // Add from row: copy row data into form and switch to Add mode
    document.querySelectorAll('.add-from-row').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const date = (this.getAttribute('data-date') || '').trim();
            const componentName = (this.getAttribute('data-component-name') || '').trim();
            const componentCode = (this.getAttribute('data-component-code') || '').trim();
            const amount = this.getAttribute('data-amount') || '';
            const remark = (this.getAttribute('data-remark') || '').trim();

            if (dateInput) dateInput.value = date;
            if (componentCodeInput) componentCodeInput.value = componentCode;
            document.getElementById('amount').value = amount;
            document.getElementById('remark').value = remark;

            if (componentNameSelect) {
                let found = false;
                for (let i = 0; i < componentNameSelect.options.length; i++) {
                    if (componentNameSelect.options[i].value === componentName) {
                        componentNameSelect.selectedIndex = i;
                        if (componentCodeInput) componentCodeInput.value = componentNameSelect.options[i].getAttribute('data-code') || componentCode;
                        found = true;
                        break;
                    }
                }
                if (!found && componentName) {
                    const opt = document.createElement('option');
                    opt.value = componentName;
                    opt.setAttribute('data-code', componentCode);
                    opt.textContent = componentName;
                    componentNameSelect.appendChild(opt);
                    componentNameSelect.selectedIndex = componentNameSelect.options.length - 1;
                    if (componentCodeInput) componentCodeInput.value = componentCode;
                }
            }

            fundForm.action = storeUrl;
            const methodInput = fundForm.querySelector('input[name="_method"]');
            if (methodInput) methodInput.remove();
            if (formCard) formCard.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    });

    @if(request('edit'))
        if (formCard) formCard.scrollIntoView({ behavior: 'smooth', block: 'start' });
    @endif
});
</script>
@endsection
