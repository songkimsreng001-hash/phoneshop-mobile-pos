<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'email', 'phone', 'address', 'date_of_birth',
        'total_purchase_amount', 'total_purchase_count', 'loyalty_points', 'is_active', 'notes',
    ];

    protected $casts = [
        'is_active'             => 'boolean',
        'date_of_birth'         => 'date',
        'total_purchase_amount' => 'decimal:2',
        'loyalty_points'        => 'decimal:2',
    ];

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    // Recalculate totals from actual invoices
    public function recalculateTotals(): void
    {
        $this->update([
            'total_purchase_amount' => $this->invoices()->where('payment_status', 'paid')->sum('final_bill'),
            'total_purchase_count'  => $this->invoices()->where('status', 'completed')->count(),
        ]);
    }
}
