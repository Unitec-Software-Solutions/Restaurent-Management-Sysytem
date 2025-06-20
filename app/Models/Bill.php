<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Auth;

class Bill extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'bill_number',
        'branch_id',
        'organization_id',
        'customer_name',
        'customer_phone',
        'subtotal',
        'tax_amount',
        'service_charge',
        'discount_amount',
        'total_amount',
        'payment_method',
        'payment_status',
        'notes',
        'generated_by',
        'generated_at'
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'service_charge' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'generated_at' => 'datetime'
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($bill) {
            if (!$bill->bill_number) {
                $bill->bill_number = 'BILL-' . str_pad(
                    Bill::where('organization_id', $bill->organization_id)->count() + 1,
                    6,
                    '0',
                    STR_PAD_LEFT
                );
            }
            
            if (!$bill->generated_at) {
                $bill->generated_at = now();
            }
        });
    }

    // Relationships
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function generatedBy()
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    // Static method to create bill from order
    public static function createFromOrder(Order $order)
    {
        return static::create([
            'order_id' => $order->id,
            'branch_id' => $order->branch_id,
            'organization_id' => $order->branch->organization_id,
            'customer_name' => $order->customer_name,
            'customer_phone' => $order->customer_phone,
            'subtotal' => $order->subtotal,
            'tax_amount' => $order->tax,
            'service_charge' => $order->service_charge,
            'discount_amount' => $order->discount,
            'total_amount' => $order->total,
            'generated_by' => Auth::id()
        ]);
    }

    // Helper methods
    public function isPaid()
    {
        return $this->payment_status === 'paid';
    }

    public function isPending()
    {
        return $this->payment_status === 'pending';
    }

    public function markAsPaid($paymentMethod = null)
    {
        $this->update([
            'payment_status' => 'paid',
            'payment_method' => $paymentMethod,
            'paid_at' => now()
        ]);
    }
}
