@extends('layouts.app')

@section('content')
<div class="container">
    <h4 class="mb-4">Vendors</h4>

    @if (session('status'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Add/Edit Form --}}
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">{{ $editingBeneficiary ? 'Edit Vendor' : 'Add New Vendor' }}</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ $editingBeneficiary ? route('beneficiaries.update', $editingBeneficiary) : route('beneficiaries.store') }}">
                @csrf
                @if($editingBeneficiary)
                    @method('PUT')
                @endif

                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label" for="name">Vendor Name</label>
                        <input
                            class="form-control @error('name') is-invalid @enderror"
                            id="name"
                            name="name"
                            type="text"
                            value="{{ old('name', $editingBeneficiary?->name) }}"
                            required
                        >
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-2 mb-3">
                        <label class="form-label" for="acode">Component Code</label>
                        <input
                            class="form-control @error('acode') is-invalid @enderror"
                            id="acode"
                            name="acode"
                            type="text"
                            value="{{ old('acode', $editingBeneficiary?->acode) }}"
                        >
                        @error('acode')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-2 mb-3">
                        <label class="form-label" for="vendor_code">Vendor Code</label>
                        <input
                            class="form-control @error('vendor_code') is-invalid @enderror"
                            id="vendor_code"
                            name="vendor_code"
                            type="text"
                            value="{{ old('vendor_code', $editingBeneficiary?->vendor_code) }}"
                        >
                        @error('vendor_code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- <div class="col-md-2 mb-3">
                        <label class="form-label" for="salary">Salary</label>
                        <input
                            class="form-control @error('salary') is-invalid @enderror"
                            id="salary"
                            name="salary"
                            type="number"
                            step="0.01"
                            min="0"
                            value="{{ old('salary', $editingBeneficiary?->salary) }}"
                            required
                        >
                        @error('salary')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div> -->

                    <div class="col-md-2 mb-3">
                        <label class="form-label" for="status">Status</label>
                        <select
                            class="form-select @error('status') is-invalid @enderror"
                            id="status"
                            name="status"
                            required
                        >
                            @php($status = old('status', $editingBeneficiary?->status ?? 1))
                            <option value="1" @selected($status == 1)>Active</option>
                            <option value="0" @selected($status == 0)>Inactive</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-1 mb-3 d-flex align-items-end">
                        <button class="btn btn-primary w-100" type="submit">
                            {{ $editingBeneficiary ? 'Update' : 'Add' }}
                        </button>
                    </div>
                </div>

                @if($editingBeneficiary)
                    <div class="mt-2">
                        <a href="{{ route('beneficiaries.index', request()->only('search')) }}" class="btn btn-sm btn-outline-secondary">Cancel</a>
                    </div>
                @endif
            </form>
        </div>
    </div>

    {{-- Search Bar --}}
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('beneficiaries.index') }}" class="d-flex gap-2">
                <input type="text" 
                       class="form-control" 
                       name="search" 
                       placeholder="Search by vendor name, component code, or vendor code..." 
                       value="{{ request('search') }}">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Search
                </button>
                @if(request('search'))
                    <a href="{{ route('beneficiaries.index') }}" class="btn btn-outline-secondary">
                        Clear
                    </a>
                @endif
            </form>
        </div>
    </div>

    {{-- Beneficiaries List --}}
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Vendor Name</th>
                            <th>Component Code</th>
                            <th>Vendor Code</th>
                            <!-- <th>Salary</th> -->
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($beneficiaries as $beneficiary)
                            <tr>
                                <td>{{ $beneficiary->name }}</td>
                                <td>{{ $beneficiary->acode ?? '-' }}</td>
                                <td>{{ $beneficiary->vendor_code ?? '-' }}</td>
                                <!-- <td>{{ number_format($beneficiary->salary, 2) }}</td> -->
                                <td>
                                    <span class="badge {{ $beneficiary->status ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $beneficiary->status ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <a class="btn btn-sm btn-outline-primary" href="{{ route('beneficiaries.index', array_merge(request()->only('search'), ['edit' => $beneficiary->id])) }}">
                                        Edit
                                    </a>
                                    <!-- <form class="d-inline" method="POST" action="{{ route('beneficiaries.destroy', $beneficiary) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this vendor?')">
                                            Delete
                                        </button>
                                    </form> -->
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">No vendors yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-3">
        {{ $beneficiaries->links() }}
    </div>
</div>
@endsection
