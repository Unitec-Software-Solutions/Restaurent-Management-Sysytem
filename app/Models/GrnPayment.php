<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GrnPayment extends Model
{
    use SoftDeletes, HasFactory;

    protected $table = 'grn_payments';
    protected $primaryKey = 'payment_id';

    protected $fillable = [
        'grn_id',
        'amount',
        'payment_date',
        'payment_method',
        'reference_number',
        'notes',
        'paid_by_user_id',
        'organization_id'
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    public function grn()
    {
        return $this->belongsTo(GrnMaster::class, 'grn_id');
    }

    public function paidByUser()
    {
        return $this->belongsTo(User::class, 'paid_by_user_id');
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id');
    }
}
