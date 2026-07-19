<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id', 'product_id', 'quantity', 'unit_price',
        'unit_cost', 'discount', 'subtotal', 'warranty_months',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'unit_cost'  => 'decimal:2',
        'discount'   => 'decimal:2',
        'subtotal'   => 'decimal:2',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Profit per line item
    public function getProfitAttribute(): float
    {
        if ($this->unit_cost) {
            return ($this->unit_price - $this->unit_cost) * $this->quantity - $this->discount;
        }
        return 0;
    }
}
