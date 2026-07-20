<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class RoleController extends Controller
{
    public function index()
    {
        $rec = Auth::guard('superadmin')->user();
        $roles = Role::with('permissions')->get();
        $permissions = Permission::orderBy('group')->orderBy('display_name')->get();
        $admins = Admin::all();

        return view('superadmin.layouts.roles', compact('rec', 'roles', 'permissions', 'admins'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:roles,name',
            'display_name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first());
        }

        $role = Role::create([
            'name' => strtolower(str_replace(' ', '_', $request->name)),
            'display_name' => $request->display_name,
            'description' => $request->description,
            'is_system' => false,
        ]);

        if ($request->filled('permissions')) {
            $role->permissions()->sync($request->permissions);
        }

        return redirect()->back()->with('success', 'Role created successfully.');
    }

    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);
        $validator = Validator::make($request->all(), [
            'display_name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first());
        }

        $role->display_name = $request->display_name;
        $role->description = $request->description;
        $role->save();

        $role->permissions()->sync($request->permissions ?? []);

        return redirect()->back()->with('success', 'Role updated successfully.');
    }

    public function assignToAdmin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'admin_id' => 'required|exists:admins,id',
            'role_id' => 'required|exists:roles,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first());
        }

        $admin = Admin::findOrFail($request->admin_id);
        $role = Role::findOrFail($request->role_id);

        $admin->roles()->syncWithoutDetaching([$role->id]);

        return redirect()->back()->with('success', 'Role assigned successfully.');
    }
}
