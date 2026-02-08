@extends('layouts.app')

@section('content')
<div class="container">
    <h4 class="mb-4">Components</h4>

    @if (session('status'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Add/Edit Form --}}
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">{{ $editingArticle ? 'Edit Component' : 'Add New Component' }}</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ $editingArticle ? route('articles.update', $editingArticle) : route('articles.store') }}">
                @csrf
                @if($editingArticle)
                    @method('PUT')
                @endif

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label" for="name">Component</label>
                        <input
                            class="form-control @error('name') is-invalid @enderror"
                            id="name"
                            name="name"
                            type="text"
                            value="{{ old('name', $editingArticle?->name) }}"
                            required
                        >
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label" for="acode">Component Code</label>
                        <input
                            class="form-control @error('acode') is-invalid @enderror"
                            id="acode"
                            name="acode"
                            type="text"
                            value="{{ old('acode', $editingArticle?->acode) }}"
                            required
                        >
                        @error('acode')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label" for="status">Status</label>
                        <select
                            class="form-select @error('status') is-invalid @enderror"
                            id="status"
                            name="status"
                            required
                        >
                            @php($status = old('status', $editingArticle?->status ?? 1))
                            <option value="1" @selected($status == 1)>Active</option>
                            <option value="0" @selected($status == 0)>Inactive</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-1 mb-3 d-flex align-items-end">
                        <button class="btn btn-primary w-100" type="submit">
                            {{ $editingArticle ? 'Update' : 'Add' }}
                        </button>
                    </div>
                </div>

                @if($editingArticle)
                    <div class="mt-2">
                        <a href="{{ route('articles.index') }}" class="btn btn-sm btn-outline-secondary">Cancel</a>
                    </div>
                @endif
            </form>
        </div>
    </div>

    {{-- Search Bar --}}
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('articles.index') }}" class="d-flex gap-2">
                <input type="text" 
                       class="form-control" 
                       name="search" 
                       placeholder="Search by component name or component code..." 
                       value="{{ request('search') }}">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Search
                </button>
                @if(request('search'))
                    <a href="{{ route('articles.index') }}" class="btn btn-outline-secondary">
                        Clear
                    </a>
                @endif
            </form>
        </div>
    </div>

    {{-- Articles List --}}
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Component</th>
                            <th>Component Code</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($articles as $article)
                            <tr>
                                <td>{{ $article->name }}</td>
                                <td>{{ $article->acode }}</td>
                                <td>
                                    <span class="badge {{ $article->status ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $article->status ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <a class="btn btn-sm btn-outline-primary" href="{{ route('articles.index', ['edit' => $article->id]) }}">
                                        Edit
                                    </a>
                                    <!-- <form class="d-inline" method="POST" action="{{ route('articles.destroy', $article) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this article?')">
                                            Delete
                                        </button>
                                    </form> -->
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">No components yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-3">
        {{ $articles->links() }}
    </div>
</div>
@endsection
