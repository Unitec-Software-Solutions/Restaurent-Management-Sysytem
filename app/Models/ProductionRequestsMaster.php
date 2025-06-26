<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductionRequestMaster extends Model
{
    use HasFactory;

    protected $table = 'production_requests_master';

    protected $fillable = [
        'organization_id',
        'branch_id',
        'request_date',
        'required_date',
        'status',
        'notes',
        'created_by_user_id',
        'approved_by_user_id',
        'approved_at',
    ];

    // Relationships
    public function items()
    {
        return $this->hasMany(ProductionRequestItem::class, 'production_request_master_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }
}
