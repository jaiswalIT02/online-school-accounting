@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Accounts</h4>
        <div class="d-flex gap-2">
            <!-- <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#createAllLedgersModal">
                <i class="bi bi-book"></i> Create All Sessions from Activities
            </button> -->
            <a class="btn btn-primary" href="{{ route('accounts.create') }}">Create Account</a>
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
                            <th>Account&nbsp;Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($accounts as $account)
                        <tr>
                            <td>{{ $account->name }}</td>
                            <td>
                                {{ $account->status === 1 ? 'Active' : ($account->status === 0 ? 'Inactive' : '-') }}
                            </td>
                            <td class="text-end">
                                <a class="btn btn-sm btn-outline-primary" href="{{ route('accounts.show', $account) }}">View</a>
                                <a class="btn btn-sm btn-outline-secondary" href="{{ route('accounts.edit', $account) }}">Edit</a>
                                <form class="d-inline" method="POST" action="{{ route('accounts.destroy', $account) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this ledger?')">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted">No account yet.</td>
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

<!-- Create All Ledgers from Activities Modal -->
<div class="modal fade" id="createAllLedgersModal" tabindex="-1" aria-labelledby="createAllLedgersModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createAllLedgersModalLabel">Create All Ledgers from Activities</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('ledgers.create_all_from_activities') }}">
                @csrf
                <div class="modal-body">
                    <p class="text-muted">This will create ledgers for all active components. Existing ledgers will be skipped.</p>
                    @if (session('ledger_error'))
                    <div class="alert alert-danger">{{ session('ledger_error') }}</div>
                    @endif
                    @if (session('ledger_success'))
                    <div class="alert alert-success">{{ session('ledger_success') }}</div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create All Ledgers</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection