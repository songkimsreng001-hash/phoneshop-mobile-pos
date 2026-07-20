@extends('superadmin.layouts.main')

@section('page_title', 'Roles & Permissions')

@section('content')
<div class="toolbar py-5 py-lg-5" id="kt_toolbar">
    <div class="container-xxl d-flex flex-stack flex-wrap">
        <div class="page-title d-flex flex-column me-3">
            <h1 class="d-flex text-dark fw-bolder my-1 fs-3">Roles & Permissions</h1>
            <ul class="breadcrumb breadcrumb-dot fw-bold text-gray-600 fs-7 my-1">
                <li class="breadcrumb-item text-gray-600"><a href="{{ url('/super-admin/dashboard') }}" class="text-gray-600 text-hover-primary">Dashboard</a></li>
                <li class="breadcrumb-item text-gray-600">Roles</li>
            </ul>
        </div>
    </div>
</div>

<div id="kt_content_container" class="d-flex flex-column-fluid align-items-start container-xxl">
    <div class="content flex-row-fluid" id="kt_content">
        <div class="row g-5">
            <div class="col-xl-7">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Roles</h3>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Display Name</th>
                                    <th>Permissions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($roles as $role)
                                    <tr>
                                        <td>{{ $role->name }}</td>
                                        <td>{{ $role->display_name }}</td>
                                        <td>{{ $role->permissions->pluck('display_name')->join(', ') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-xl-5">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Create / Update Role</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('superadmin.roles.store') }}">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">Role Name</label>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Display Name</label>
                                <input type="text" name="display_name" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control"></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Permissions</label>
                                <select name="permissions[]" class="form-select" multiple>
                                    @foreach($permissions as $permission)
                                        <option value="{{ $permission->id }}">{{ $permission->display_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <button class="btn btn-primary">Save Role</button>
                        </form>
                    </div>
                </div>

                <div class="card mt-5">
                    <div class="card-header">
                        <h3 class="card-title">Assign Role to Admin</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('superadmin.roles.assign') }}">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">Admin</label>
                                <select name="admin_id" class="form-select" required>
                                    @foreach($admins as $admin)
                                        <option value="{{ $admin->id }}">{{ $admin->name }} ({{ $admin->email }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Role</label>
                                <select name="role_id" class="form-select" required>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->id }}">{{ $role->display_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <button class="btn btn-success">Assign</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
