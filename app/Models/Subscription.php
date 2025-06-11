<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    protected $fillable = ['organization_id', 'start_date', 'end_date', 'is_active'];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
}
