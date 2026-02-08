@extends('layouts.app')

@section('content')
<div class="container">
    <h4 class="mb-4">Items</h4>

    @if (session('status'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Add/Edit Form --}}
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">{{ $editingItem ? 'Edit Item' : 'Add New Item' }}</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ $editingItem ? route('items.update', $editingItem) : route('items.store') }}">
                @csrf
                @if($editingItem)
                    @method('PUT')
                @endif

                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label" for="name">Item Name</label>
                        <input
                            class="form-control @error('name') is-invalid @enderror"
                            id="name"
                            name="name"
                            type="text"
                            value="{{ old('name', $editingItem?->name) }}"
                            required
                        >
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-2 mb-3">
                        <label class="form-label" for="item_code">Item Code</label>
                        <input
                            class="form-control @error('item_code') is-invalid @enderror"
                            id="item_code"
                            name="item_code"
                            type="text"
                            value="{{ old('item_code', $editingItem?->item_code) }}"
                        >
                        @error('item_code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-2 mb-3">
                        <label class="form-label" for="description">Description</label>
                        <input
                            class="form-control @error('description') is-invalid @enderror"
                            id="description"
                            name="description"
                            type="text"
                            value="{{ old('description', $editingItem?->description) }}"
                        >
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-2 mb-3">
                        <label class="form-label" for="unit">Unit</label>
                        <select
                            class="form-select @error('unit') is-invalid @enderror"
                            id="unit"
                            name="unit"
                        >
                            <option value="">Select Unit</option>
                            <option value="gram" @selected(old('unit', $editingItem?->unit) === 'gram')>gram</option>
                            <option value="kg" @selected(old('unit', $editingItem?->unit) === 'kg')>kg</option>
                            <option value="quintal" @selected(old('unit', $editingItem?->unit) === 'quintal')>quintal</option>
                            <option value="ton" @selected(old('unit', $editingItem?->unit) === 'ton')>ton</option>
                            <option value="piece" @selected(old('unit', $editingItem?->unit) === 'piece')>piece</option>
                            <option value="dozen" @selected(old('unit', $editingItem?->unit) === 'dozen')>dozen</option>
                            <option value="litres" @selected(old('unit', $editingItem?->unit) === 'litres')>litres</option>
                            <option value="ml" @selected(old('unit', $editingItem?->unit) === 'ml')>ml</option>
                            <option value="meter" @selected(old('unit', $editingItem?->unit) === 'meter')>meter</option>
                            <option value="packets" @selected(old('unit', $editingItem?->unit) === 'packets')>Packets</option>
                            <option value="nos" @selected(old('unit', $editingItem?->unit) === 'nos')>Nos</option>
                            <option value="cartoons" @selected(old('unit', $editingItem?->unit) === 'cartoons')>Cartoons</option>
                            <option value="bags" @selected(old('unit', $editingItem?->unit) === 'bags')>Bags</option>
                            <option value="Plates" @selected(old('unit', $editingItem?->unit) === 'Plates')>Plates</option>
                        </select>
                        @error('unit')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-2 mb-3">
                        <label class="form-label" for="status">Status</label>
                        <select
                            class="form-select @error('status') is-invalid @enderror"
                            id="status"
                            name="status"
                            required
                        >
                            @php($status = old('status', $editingItem?->status ?? 'active'))
                            <option value="active" @selected($status === 'active')>Active</option>
                            <option value="inactive" @selected($status === 'inactive')>Inactive</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-1 mb-3 d-flex align-items-end">
                        <button class="btn btn-primary w-100" type="submit">
                            {{ $editingItem ? 'Update' : 'Add' }}
                        </button>
                    </div>
                </div>

                @if($editingItem)
                    <div class="mt-2">
                        <a href="{{ route('items.index') }}" class="btn btn-sm btn-outline-secondary">Cancel</a>
                    </div>
                @endif
            </form>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('items.index') }}" class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small">Search</label>
                    <input type="text"
                           class="form-control"
                           name="search"
                           placeholder="Search by name, item code, or description..."
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All</option>
                        <option value="active" @selected(request('status') === 'active')>Active</option>
                        <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i> Filter
                    </button>
                </div>
                @if(request()->hasAny(['search', 'status']))
                    <div class="col-md-2">
                        <a href="{{ route('items.index') }}" class="btn btn-outline-secondary w-100">Clear</a>
                    </div>
                @endif
            </form>
        </div>
    </div>

    {{-- Items List --}}
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Item Name</th>
                            <th>Item Code</th>
                            <th>Description</th>
                            <th>Unit</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($items as $item)
                            <tr>
                                <td>{{ $item->name }}</td>
                                <td>{{ $item->item_code ?? '-' }}</td>
                                <td>{{ Str::limit($item->description, 40) ?? '-' }}</td>
                                <td>{{ $item->unit ?? '-' }}</td>
                                <td>
                                    <span class="badge {{ $item->status === 'active' ? 'bg-success' : 'bg-secondary' }}">
                                        {{ ucfirst($item->status) }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <a class="btn btn-sm btn-outline-info" href="{{ route('items.show', $item) }}">View</a>
                                    <a class="btn btn-sm btn-outline-primary" href="{{ route('items.index', ['edit' => $item->id]) }}">Edit</a>
                                    <form class="d-inline" method="POST" action="{{ route('items.destroy', $item) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this item?')">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">No items yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-3">
        {{ $items->links() }}
    </div>
</div>
@endsection
