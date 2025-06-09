<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GoodsTransferNote extends Model
{
    use SoftDeletes;

    protected $table = 'gtn_master';
    protected $primaryKey = 'gtn_id';

    protected $fillable = [
        'gtn_number',
        'from_branch_id',
        'to_branch_id',
        'created_by',
        'approved_by',
        'organization_id',
        'transfer_date',
        'status',
        'notes',
        'is_active',
    ];

    // Relationships

    public function fromBranch()
    {
        return $this->belongsTo(Branch::class, 'from_branch_id');
    }

    public function toBranch()
    {
        return $this->belongsTo(Branch::class, 'to_branch_id');
    }

    public function organization()
    {
        return $this->belongsTo(Organizations::class, 'organization_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(Employee::class, 'created_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(Employee::class, 'approved_by');
    }

    public function items()
    {
        return $this->hasMany(GoodsTransferItem::class, 'gtn_id');
    }
}
