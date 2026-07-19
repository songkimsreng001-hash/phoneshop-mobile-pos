<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Purchase extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'reference_no', 'shop_id', 'supplier_id', 'purchase_date',
        'status', 'payment_status', 'subtotal', 'discount', 'tax',
        'shipping_cost', 'grand_total', 'amount_paid', 'amount_due', 'notes', 'created_by',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'subtotal'      => 'decimal:2',
        'discount'      => 'decimal:2',
        'tax'           => 'decimal:2',
        'grand_total'   => 'decimal:2',
        'amount_paid'   => 'decimal:2',
        'amount_due'    => 'decimal:2',
    ];

    public function shop()
    {
        return $this->belongsTo(User::class, 'shop_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function details()
    {
        return $this->hasMany(PurchaseDetail::class);
    }

    public function stocks()
    {
        return $this->morphMany(Stock::class, 'reference');
    }

    public function createdBy()
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }

    // Auto-generate reference number
    public static function generateReferenceNo(): string
    {
        $year  = now()->year;
        $count = self::whereYear('created_at', $year)->count() + 1;
        return 'PO-' . $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }
}
