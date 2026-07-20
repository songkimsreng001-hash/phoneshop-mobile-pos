<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $guarded = [];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',   // FIX: passwords now properly hashed
        ];
    }

    public function admins()
    {
        return $this->belongsToMany(Admin::class, 'shop_admins', 'shop_id', 'admin_id');
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'shop_id', 'id');
    }

    public function canAccessShop(int $shopId): bool
    {
        return (int) $this->id === $shopId;
    }
}
