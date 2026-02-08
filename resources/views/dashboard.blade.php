@extends('layouts.app')

@section('content')
<div class="container">
    <div class="mb-4">
        <h2 class="mb-0">Dashboard</h2>
        <p class="text-muted">Welcome to Tally Management System</p>
    </div>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <!-- Quick Stats -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center border-primary">
                <div class="card-body">
                    <h5 class="card-title text-muted mb-2">Ledgers</h5>
                    <h3 class="mb-0 text-primary">{{ \App\Models\Ledger::count() }}</h3>
                    <a href="{{ route('ledgers.index') }}" class="btn btn-sm btn-outline-primary mt-2">View All</a>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center border-success">
                <div class="card-body">
                    <h5 class="card-title text-muted mb-2">Cashbooks</h5>
                    <h3 class="mb-0 text-success">{{ \App\Models\Cashbook::count() }}</h3>
                    <a href="{{ route('cashbooks.index') }}" class="btn btn-sm btn-outline-success mt-2">View All</a>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center border-info">
                <div class="card-body">
                    <h5 class="card-title text-muted mb-2">Receipt & Payment</h5>
                    <h3 class="mb-0 text-info">{{ \App\Models\ReceiptPaymentAccount::count() }}</h3>
                    <a href="{{ route('receipt_payments.index') }}" class="btn btn-sm btn-outline-info mt-2">View All</a>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center border-warning">
                <div class="card-body">
                    <h5 class="card-title text-muted mb-2">Funds</h5>
                    <h3 class="mb-0 text-warning">{{ \App\Models\Fund::count() }}</h3>
                    <a href="{{ route('funds.index') }}" class="btn btn-sm btn-outline-warning mt-2">View All</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Links -->
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <a href="{{ route('ledgers.create') }}" class="list-group-item list-group-item-action">
                            Create New Ledger
                        </a>
                        <a href="{{ route('cashbooks.create') }}" class="list-group-item list-group-item-action">
                            Create New Cashbook
                        </a>
                        <a href="{{ route('receipt_payments.create') }}" class="list-group-item list-group-item-action">
                            Create Receipt & Payment Account
                        </a>
                        <a href="{{ route('funds.index') }}" class="list-group-item list-group-item-action">
                            Add Fund Record
                        </a>
                        <a href="{{ route('articles.index') }}" class="list-group-item list-group-item-action">
                            Manage Components
                        </a>
                        <a href="{{ route('beneficiaries.index') }}" class="list-group-item list-group-item-action">
                            Manage Vendors
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">Recent Activity</h5>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between">
                                <span>Total Ledger Entries</span>
                                <strong>{{ \App\Models\LedgerEntry::count() }}</strong>
                            </div>
                        </div>
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between">
                                <span>Total Cashbook Entries</span>
                                <strong>{{ \App\Models\CashbookEntry::count() }}</strong>
                            </div>
                        </div>
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between">
                                <span>Total Receipt & Payment Entries</span>
                                <strong>{{ \App\Models\ReceiptPaymentEntry::count() }}</strong>
                            </div>
                        </div>
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between">
                                <span>Total Components</span>
                                <strong>{{ \App\Models\Article::count() }}</strong>
                            </div>
                        </div>
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between">
                                <span>Total Vendors</span>
                                <strong>{{ \App\Models\Beneficiary::count() }}</strong>
                            </div>
                        </div>
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between">
                                <span>Total Funds Amount</span>
                                <strong>₹{{ number_format(\App\Models\Fund::sum('amount'), 2) }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
