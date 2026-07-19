<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'price'        => 'decimal:2',
        'cost_price'   => 'decimal:2',
        'isDeleted'    => 'boolean',
    ];

    // ── Relationships ──────────────────────────────────────────

    public function shop()
    {
        return $this->belongsTo(User::class, 'shop_id');
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function saleDetails()
    {
        return $this->hasMany(SaleDetail::class);
    }

    public function purchaseDetails()
    {
        return $this->hasMany(PurchaseDetail::class);
    }

    public function stocks()
    {
        return $this->hasMany(Stock::class);
    }

    public function claims()
    {
        return $this->hasMany(Claim::class);
    }

    // ── Helpers ────────────────────────────────────────────────

    public function isLowStock(): bool
    {
        return $this->qty <= $this->reorder_level;
    }

    public function getMarginAttribute(): float
    {
        if ($this->cost_price && $this->cost_price > 0) {
            return round((($this->price - $this->cost_price) / $this->price) * 100, 2);
        }
        return 0;
    }
}
