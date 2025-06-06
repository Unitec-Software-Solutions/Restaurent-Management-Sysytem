<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierPaymentDetail extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'supp_payments_details';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'payment_master_id',
        'method_type',
        'amount',
        'reference_number',
        'value_date',
        'cheque_number',
        'bank_name',
        'cheque_date',
        'transaction_id',
        'bank_reference',
        'installment_number',
        'due_date',
        'metadata'
    ];

    protected $casts = [
    'value_date' => 'date',
    'cheque_date' => 'date',
    'due_date' => 'date',
    ];

    /**
     * Get the payment master record associated with this detail.
     */
    public function paymentMaster(): BelongsTo
    {
        return $this->belongsTo(SupplierPaymentMaster::class, 'payment_master_id');
    }
}