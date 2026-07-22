<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PermissionController extends Controller
{
    /**
     * List all permissions, grouped by their `group` column.
     */
    public function index()
    {
        $permissions = Permission::orderBy('group')->orderBy('display_name')->get();

        return response()->json(
            $permissions->groupBy(fn ($p) => $p->group ?? 'general')->map->values()
        );
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'         => 'required|string|max:255|unique:permissions,name',
            'display_name' => 'required|string|max:255',
            'group'        => 'nullable|string|max:100',
            'description'  => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $permission = Permission::create([
            'name'         => strtolower(str_replace(' ', '_', $request->name)),
            'display_name' => $request->display_name,
            'group'        => $request->group,
            'description'  => $request->description,
        ]);

        return response()->json($permission, 201);
    }

    public function update(Request $request, string $id)
    {
        $permission = Permission::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'display_name' => 'required|string|max:255',
            'group'        => 'nullable|string|max:100',
            'description'  => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $permission->update($request->only(['display_name', 'group', 'description']));

        return response()->json($permission);
    }

    public function destroy(string $id)
    {
        $permission = Permission::findOrFail($id);

        if ($permission->roles()->exists()) {
            return response()->json(['message' => 'This permission is still assigned to one or more roles.'], 422);
        }

        $permission->delete();

        return response()->json(['message' => 'Permission deleted.']);
    }
}
