@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Cashbooks</h4>
        <div class="d-flex gap-2">
            <form method="POST" action="{{ route('cashbooks.create_all_months') }}" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-success" onclick="return confirm('This will create 12 cashbooks from April 2025 to March 2026 (financial year). Continue?')">Create 12 Months Cashbooks</button>
            </form>
            <a class="btn btn-primary" href="{{ route('cashbooks.create') }}">Create Cashbook</a>
        </div>
    </div>

    @if (session('status'))
    <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    @if (session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Period</th>
                            <th>Opening Cash</th>
                            <th>Opening Bank</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($cashbooks as $cashbook)
                        <tr>
                            <td>{{ $cashbook->name }}</td>
                            <td>{{ $cashbook->period_month }} {{ $cashbook->period_year }}</td>
                            <td>{{ number_format($cashbook->opening_cash, 2) }}</td>
                            <td>{{ number_format($cashbook->opening_bank, 2) }}</td>
                            <td class="text-end">
                                <a class="btn btn-sm btn-outline-primary" href="{{ route('cashbooks.show', $cashbook) }}">View</a>
                                <a class="btn btn-sm btn-outline-secondary" href="{{ route('cashbooks.edit', $cashbook) }}">Edit</a>
                                <form class="d-inline" method="POST" action="{{ route('cashbooks.destroy', $cashbook) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this cashbook?')">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted">No cashbooks yet.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-3">
        {{ $cashbooks->links() }}
    </div>
</div>
@endsection