<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'shop_id', 'customer_id', 'total_bill', 'discount', 'final_bill',
        'customer_name', 'customer_phone', 'customer_info',
        'payment_method', 'payment_status', 'amount_paid', 'change_amount', 'status',
    ];

    protected $casts = [
        'total_bill'    => 'decimal:2',
        'discount'      => 'decimal:2',
        'final_bill'    => 'decimal:2',
        'amount_paid'   => 'decimal:2',
        'change_amount' => 'decimal:2',
    ];

    // ── Relationships ──────────────────────────────────────────

    public function shop()
    {
        return $this->belongsTo(User::class, 'shop_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function saleDetails()
    {
        return $this->hasMany(SaleDetail::class);
    }

    public function claims()
    {
        return $this->hasMany(Claim::class);
    }

    // ── Helpers ────────────────────────────────────────────────

    public function getTotalCostAttribute(): float
    {
        return $this->saleDetails->sum(fn($d) => ($d->unit_cost ?? 0) * $d->quantity);
    }

    public function getProfitAttribute(): float
    {
        return $this->final_bill - $this->total_cost;
    }
}
