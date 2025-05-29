<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DigitalMenu extends Model
{
    protected $table = 'item_master';

    // ... existing code ...

    public function category()
    {
        return $this->belongsTo(DigitalMenuCategory::class);
    }

    // ... existing code ...
} 