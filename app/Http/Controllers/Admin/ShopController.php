<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\ShopAdmin;

class ShopController extends Controller
{
    public function index()
    {
        $rec   = Auth::guard('admin')->user();
        $shops = $rec->shops;

        return view('admin.layouts.shops', ['rec' => $rec, 'shops' => $shops]);
    }

    public function edit(Request $request)
    {
        $rec = Auth::guard('admin')->user();
        $shopId = (int) $request->input('id');

        $isAssigned = $rec->canAccessShop($shopId);
        if (! $isAssigned) {
            return redirect()->back()->with('error', 'You can only manage your assigned shop.');
        }

        $rules = [
            'id'     => 'required|exists:users,id',
            'name'   => 'required|string|max:255',
            'email'  => 'required|string|email|max:255|unique:users,email,' . $request->input('id'),
            'status' => 'required',
        ];

        $messages = [
            'id.required'     => 'The ID field is required.',
            'id.exists'       => 'The specified ID does not exist.',
            'name.required'   => 'The Name field is required.',
            'email.required'  => 'The Email field is required.',
            'email.email'     => 'The Email must be a valid email address.',
            'email.unique'    => 'The Email is already registered.',
            'status.required' => 'The Status field is required.',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first());
        }

        $admin = User::find($request->input('id'));
        $admin->name             = $request->name;
        $admin->email            = $request->email;
        $admin->blocked_by_admin = $request->status;
        $admin->save();

        return redirect()->back()->with('success', 'Shop details updated successfully!');
    }

    public function updatePassword(Request $request)
    {
        $rec = Auth::guard('admin')->user();
        $shopId = (int) $request->input('id');

        $isAssigned = $rec->canAccessShop($shopId);
        if (! $isAssigned) {
            return redirect()->back()->with('error', 'You can only manage your assigned shop.');
        }

        $rules = [
            'id'       => 'required|exists:users,id',
            'password' => 'required|string|min:8|confirmed',
        ];

        $messages = [
            'id.required'        => 'The ID field is required.',
            'id.exists'          => 'The specified ID does not exist.',
            'password.required'  => 'The New Password field is required.',
            'password.min'       => 'The New Password must be at least 8 characters.',
            'password.confirmed' => 'The New Password confirmation does not match.',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first());
        }

        $shop = User::findOrFail($request->input('id'));
        $shop->password = bcrypt($request->input('password'));
        $shop->save();

        return redirect()->back()->with('success', 'Password updated successfully!');
    }

    public function delete(Request $request)
    {
        $rec = Auth::guard('admin')->user();
        $shopId = (int) $request->input('id');

        $isAssigned = $rec->canAccessShop($shopId);
        if (! $isAssigned) {
            return redirect()->back()->with('error', 'You can only manage your assigned shop.');
        }

        $rules    = ['id' => 'required|exists:users,id'];
        $messages = [
            'id.required' => 'The ID field is required.',
            'id.exists'   => 'The specified ID does not exist.',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first());
        }

        $shop = User::find($request->input('id'));

        if ($shop) {
            $shop->delete();
            return redirect()->back()->with('success', 'Shop deleted successfully.');
        }

        return redirect()->back()->with('error', 'Shop not found.');
    }
}
