@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Create Account</h4>
        <a class="btn btn-outline-secondary" href="{{ route('accounts.index') }}">Back</a>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('accounts.store') }}">
                @csrf
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="name">Account Name</label>
                        <input
                            class="form-control @error('name') is-invalid @enderror"
                            id="name"
                            name="name"
                            type="text"

                            value="{{ old('name', $account->name ?? '') }}">
                        @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="slug">Account Slug</label>
                        <input
                            class="form-control @error('slug') is-invalid @enderror"
                            id="slug"
                            name="slug"
                            type="text"
                            value="{{ old('slug', $account->slug ?? '') }}">
                        @error('slug')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="d-flex justify-content-end">
                    <button class="btn btn-primary" type="submit">Save Ledger</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection