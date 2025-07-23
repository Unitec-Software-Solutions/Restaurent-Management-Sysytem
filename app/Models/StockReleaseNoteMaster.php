<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockReleaseNoteMaster extends Model
{
    use SoftDeletes;

    protected $table = 'stock_release_note_master';

    protected $fillable = [
        'srn_number',
        'branch_id',
        'organization_id',
        'released_by_user_id',
        'released_at',
        'received_by_user_id',
        'received_at',
        'verified_by_user_id',
        'verified_at',
        'release_date',
        'release_type',
        'total_amount',
        'status',
        'notes',
        'is_active',
        'created_by',
        'document_id',
        'document_type',
    ];

    // Relationships
    public function items()
    {
        return $this->hasMany(StockReleaseNoteItem::class, 'srn_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function releasedBy()
    {
        return $this->belongsTo(User::class, 'released_by_user_id');
    }

    public function receivedBy()
    {
        return $this->belongsTo(User::class, 'received_by_user_id');
    }

    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by_user_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Polymorphic document relation (manual, not Eloquent morph)
    public function document()
    {
        // You can resolve the related model manually using document_type and document_id
        // Example: return app($this->document_type)::find($this->document_id);
        return null;
    }
}
