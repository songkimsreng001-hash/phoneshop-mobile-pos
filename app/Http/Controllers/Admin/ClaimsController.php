<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ShopAdmin;
use App\Models\User;

class ClaimsController extends Controller
{
    public function index($shop_id)
    {
        $rec = Auth::guard('admin')->user();
        $isAuthorized = $rec->canAccessShop((int) $shop_id);

        if (!$isAuthorized) {
            return redirect()->route('admin.shops')
                         ->with('error' , 'Unauthorized access or shop does not exist.');
        }
        $shop = User::where('id', $shop_id)->first();

        return view('admin.layouts.claims', ['rec' => $rec, 'shop_id' => $shop_id, 'shop_name' => $shop->name]);
    }
}
