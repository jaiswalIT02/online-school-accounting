@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Edit Receipt &amp; Payment Account</h4>
        <a class="btn btn-outline-secondary" href="{{ route('receipt_payments.show', $account) }}">Back</a>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('receipt_payments.update', $account) }}">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label class="form-label" for="name">Name</label>
                    <input
                        class="form-control @error('name') is-invalid @enderror"
                        id="name"
                        name="name"
                        type="text"
                        value="{{ old('name', $account->name) }}"
                        required
                    >
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label" for="header_title">Header Title</label>
                    <input
                        class="form-control @error('header_title') is-invalid @enderror"
                        id="header_title"
                        name="header_title"
                        type="text"
                        value="{{ old('header_title', $account->header_title) }}"
                        placeholder="Receipt and Payment Account of ..."
                    >
                    @error('header_title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label" for="header_subtitle">Header Subtitle</label>
                    <input
                        class="form-control @error('header_subtitle') is-invalid @enderror"
                        id="header_subtitle"
                        name="header_subtitle"
                        type="text"
                        value="{{ old('header_subtitle', $account->header_subtitle) }}"
                        placeholder="For the month from ... to ..."
                    >
                    @error('header_subtitle')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="period_from">Period From</label>
                        <input
                            class="form-control @error('period_from') is-invalid @enderror"
                            id="period_from"
                            name="period_from"
                            type="date"
                            value="{{ old('period_from', $account->period_from->format('Y-m-d')) }}"
                            required
                        >
                        @error('period_from')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="period_to">Period To</label>
                        <input
                            class="form-control @error('period_to') is-invalid @enderror"
                            id="period_to"
                            name="period_to"
                            type="date"
                            value="{{ old('period_to', $account->period_to->format('Y-m-d')) }}"
                            required
                        >
                        @error('period_to')
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
                    >{{ old('description', $account->description) }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex justify-content-end">
                    <button class="btn btn-primary" type="submit">Update Account</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
