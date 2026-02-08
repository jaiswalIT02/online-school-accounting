@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Receipt &amp; Payment Accounts</h4>
        <div class="d-flex gap-2">
            <a class="btn btn-success" href="{{ route('pdf_extract.index') }}">
                <i class="bi bi-file-pdf"></i> Extract from PDF
            </a>
            <a class="btn btn-primary" href="{{ route('receipt_payments.create') }}">Create Account</a>
        </div>
    </div>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Period</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($accounts as $account)
                            <tr>
                                <td>{{ $account->name }}</td>
                                <td>
                                    {{ $account->period_from->format('d M Y') }}
                                    -
                                    {{ $account->period_to->format('d M Y') }}
                                </td>
                                <td class="text-end">
                                    <a class="btn btn-sm btn-outline-primary" href="{{ route('receipt_payments.show', $account) }}">View</a>
                                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('receipt_payments.edit', $account) }}">Edit</a>
                                    <form class="d-inline" method="POST" action="{{ route('receipt_payments.destroy', $account) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this account?')">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted">No accounts yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-3">
        {{ $accounts->links() }}
    </div>
</div>
@endsection
