<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShopAdmin extends Model
{
    use HasFactory;  // FIX: removed duplicate 'use HasFactory'

    protected $guarded = [];

    public function shop()
    {
        return $this->belongsTo(User::class, 'shop_id');
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }
}
