@extends('admin.layouts.main')

@section('page_title', 'Categories')

@section('content')
<div class="toolbar py-5 py-lg-5" id="kt_toolbar">
    <div class="container-xxl d-flex flex-stack flex-wrap">
        <div class="page-title d-flex flex-column me-3">
            <h1 class="d-flex text-dark fw-bolder my-1 fs-3">Categories</h1>
            <ul class="breadcrumb breadcrumb-dot fw-bold text-gray-600 fs-7 my-1">
                <li class="breadcrumb-item text-gray-600"><a href="{{ url('/admin-panel/dashboard') }}" class="text-gray-600 text-hover-primary">Dashboard</a></li>
                <li class="breadcrumb-item text-gray-600">Categories</li>
            </ul>
        </div>
    </div>
</div>

<div id="kt_content_container" class="d-flex flex-column-fluid align-items-start container-xxl">
    <div class="content flex-row-fluid" id="kt_content">

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="row g-5">
            <div class="col-xl-7">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">All Categories</h3>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped align-middle">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Status</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($categories as $category)
                                    <tr>
                                        <td class="fw-bold">{{ $category->name }}</td>
                                        <td>
                                            @if($category->is_active)
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-secondary">Inactive</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <button type="button" class="btn btn-sm btn-light-primary"
                                                onclick="editCategory({{ $category->id }}, '{{ $category->name }}', {{ $category->parent_id ?? 'null' }}, '{{ $category->icon }}', @js($category->description), {{ $category->is_active ? 'true' : 'false' }})">
                                                Edit
                                            </button>
                                            <form method="POST" action="{{ route('admin.categories.destroy', $category->id) }}" class="d-inline" onsubmit="return confirm('Delete this category?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-light-danger">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                    @foreach($category->children as $child)
                                        <tr>
                                            <td class="ps-8 text-muted">&mdash; {{ $child->name }}</td>
                                            <td>
                                                @if($child->is_active)
                                                    <span class="badge bg-success">Active</span>
                                                @else
                                                    <span class="badge bg-secondary">Inactive</span>
                                                @endif
                                            </td>
                                            <td class="text-end">
                                                <button type="button" class="btn btn-sm btn-light-primary"
                                                    onclick="editCategory({{ $child->id }}, '{{ $child->name }}', {{ $child->parent_id ?? 'null' }}, '{{ $child->icon }}', @js($child->description), {{ $child->is_active ? 'true' : 'false' }})">
                                                    Edit
                                                </button>
                                                <form method="POST" action="{{ route('admin.categories.destroy', $child->id) }}" class="d-inline" onsubmit="return confirm('Delete this category?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-light-danger">Delete</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                @empty
                                    <tr><td colspan="3" class="text-center text-muted">No categories yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-xl-5">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title" id="form-title">Create Category</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('admin.categories.store') }}" id="category-form">
                            @csrf
                            <input type="hidden" name="_method" id="form-method" value="POST">
                            <div class="mb-3">
                                <label class="form-label">Name</label>
                                <input type="text" name="name" id="input-name" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Parent Category</label>
                                <select name="parent_id" id="input-parent" class="form-select">
                                    <option value="">None (top-level)</option>
                                    @foreach($parents as $parent)
                                        <option value="{{ $parent->id }}">{{ $parent->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Icon</label>
                                <input type="text" name="icon" id="input-icon" class="form-control" placeholder="e.g. fa-mobile">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea name="description" id="input-description" class="form-control"></textarea>
                            </div>
                            <div class="mb-3 form-check">
                                <input type="checkbox" name="is_active" id="input-active" class="form-check-input" value="1" checked>
                                <label class="form-check-label" for="input-active">Active</label>
                            </div>
                            <button class="btn btn-primary" type="submit">Save Category</button>
                            <button class="btn btn-light" type="button" id="cancel-edit" onclick="resetForm()" style="display:none;">Cancel</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function editCategory(id, name, parentId, icon, description, isActive) {
        document.getElementById('form-title').innerText = 'Update Category';
        document.getElementById('category-form').action = '{{ url("/admin-panel/categories") }}/' + id;
        document.getElementById('form-method').value = 'POST';
        document.getElementById('input-name').value = name;
        document.getElementById('input-parent').value = parentId ?? '';
        document.getElementById('input-icon').value = icon ?? '';
        document.getElementById('input-description').value = description ?? '';
        document.getElementById('input-active').checked = !!isActive;
        document.getElementById('cancel-edit').style.display = 'inline-block';
    }

    function resetForm() {
        document.getElementById('form-title').innerText = 'Create Category';
        document.getElementById('category-form').action = '{{ route("admin.categories.store") }}';
        document.getElementById('category-form').reset();
        document.getElementById('cancel-edit').style.display = 'none';
    }
</script>
@endsection
