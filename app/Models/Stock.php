<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id', 'shop_id', 'quantity', 'type',
        'reference_type', 'reference_id', 'notes', 'created_by',
    ];

    // Stock movement types
    const TYPE_PURCHASE   = 'purchase';
    const TYPE_SALE       = 'sale';
    const TYPE_ADJUSTMENT = 'adjustment';
    const TYPE_RETURN     = 'return';
    const TYPE_TRANSFER   = 'transfer';

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function shop()
    {
        return $this->belongsTo(User::class, 'shop_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }

    // Polymorphic reference (purchase / invoice / etc.)
    public function reference()
    {
        return $this->morphTo(__FUNCTION__, 'reference_type', 'reference_id');
    }
}
