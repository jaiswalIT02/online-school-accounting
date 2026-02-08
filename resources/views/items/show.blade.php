@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Item Details</h4>
        <a class="btn btn-outline-secondary" href="{{ route('items.index') }}">Back to Items</a>
    </div>

    <div class="card">
        <div class="card-body">
            <dl class="row mb-0">
                <dt class="col-sm-3">Item Name</dt>
                <dd class="col-sm-9">{{ $item->name }}</dd>

                <dt class="col-sm-3">Item Code</dt>
                <dd class="col-sm-9">{{ $item->item_code ?? '-' }}</dd>

                <dt class="col-sm-3">Description</dt>
                <dd class="col-sm-9">{{ $item->description ?? '-' }}</dd>

                <dt class="col-sm-3">Unit</dt>
                <dd class="col-sm-9">{{ $item->unit ?? '-' }}</dd>

                <dt class="col-sm-3">Status</dt>
                <dd class="col-sm-9">
                    <span class="badge {{ $item->status === 'active' ? 'bg-success' : 'bg-secondary' }}">
                        {{ ucfirst($item->status) }}
                    </span>
                </dd>
            </dl>
            <hr>
            <a class="btn btn-primary" href="{{ route('items.index', ['edit' => $item->id]) }}">Edit Item</a>
        </div>
    </div>
</div>
@endsection
