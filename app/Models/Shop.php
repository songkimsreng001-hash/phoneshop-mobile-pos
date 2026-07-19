<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Shop extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'slug', 'phone', 'email', 'address',
        'logo', 'license_number', 'is_active', 'owner_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Auto-generate slug from name
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($shop) {
            if (empty($shop->slug)) {
                $shop->slug = Str::slug($shop->name);
            }
        });
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function admins()
    {
        return $this->belongsToMany(Admin::class, 'shop_admins', 'shop_id', 'admin_id');
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'shop_id');
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'shop_id');
    }

    public function sales()
    {
        return $this->hasMany(Sale::class, 'shop_id');
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class, 'shop_id');
    }

    public function stocks()
    {
        return $this->hasMany(Stock::class, 'shop_id');
    }
}
