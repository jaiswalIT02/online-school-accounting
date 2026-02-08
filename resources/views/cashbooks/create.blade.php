@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Create Cashbook</h4>
        <a class="btn btn-outline-secondary" href="{{ route('cashbooks.index') }}">Back</a>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('cashbooks.store') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label" for="name">Name</label>
                    <input
                        class="form-control @error('name') is-invalid @enderror"
                        id="name"
                        name="name"
                        type="text"
                        value="{{ old('name', 'Cash Book') }}"
                        required
                    >
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="period_month">Month</label>
                        <select
                            class="form-select @error('period_month') is-invalid @enderror"
                            id="period_month"
                            name="period_month"
                            required
                        >
                            <option value="">Select Month</option>
                            <option value="January" {{ old('period_month') == 'January' ? 'selected' : '' }}>January</option>
                            <option value="February" {{ old('period_month') == 'February' ? 'selected' : '' }}>February</option>
                            <option value="March" {{ old('period_month') == 'March' ? 'selected' : '' }}>March</option>
                            <option value="April" {{ old('period_month') == 'April' ? 'selected' : '' }}>April</option>
                            <option value="May" {{ old('period_month') == 'May' ? 'selected' : '' }}>May</option>
                            <option value="June" {{ old('period_month') == 'June' ? 'selected' : '' }}>June</option>
                            <option value="July" {{ old('period_month') == 'July' ? 'selected' : '' }}>July</option>
                            <option value="August" {{ old('period_month') == 'August' ? 'selected' : '' }}>August</option>
                            <option value="September" {{ old('period_month') == 'September' ? 'selected' : '' }}>September</option>
                            <option value="October" {{ old('period_month') == 'October' ? 'selected' : '' }}>October</option>
                            <option value="November" {{ old('period_month') == 'November' ? 'selected' : '' }}>November</option>
                            <option value="December" {{ old('period_month') == 'December' ? 'selected' : '' }}>December</option>
                        </select>
                        @error('period_month')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="period_year">Year</label>
                        <select
                            class="form-select @error('period_year') is-invalid @enderror"
                            id="period_year"
                            name="period_year"
                            required
                        >
                            <option value="">Select Year</option>
                            @for($year = date('Y') + 5; $year >= 2000; $year--)
                                <option value="{{ $year }}" {{ old('period_year', date('Y')) == $year ? 'selected' : '' }}>{{ $year }}</option>
                            @endfor
                        </select>
                        @error('period_year')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="opening_cash">Opening Cash</label>
                        <input
                            class="form-control @error('opening_cash') is-invalid @enderror"
                            id="opening_cash"
                            name="opening_cash"
                            type="number"
                            step="0.01"
                            min="0"
                            value="{{ old('opening_cash', '0.00') }}"
                        >
                        @error('opening_cash')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="opening_bank">Opening Bank</label>
                        <input
                            class="form-control @error('opening_bank') is-invalid @enderror"
                            id="opening_bank"
                            name="opening_bank"
                            type="number"
                            step="0.01"
                            min="0"
                            value="{{ old('opening_bank', '0.00') }}"
                        >
                        @error('opening_bank')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="description">Description</label>
                    <textarea
                        class="form-control @error('description') is-invalid @enderror"
                        id="description"
                        name="description"
                        rows="3"
                    >{{ old('description') }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex justify-content-end">
                    <button class="btn btn-primary" type="submit">Save Cashbook</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
