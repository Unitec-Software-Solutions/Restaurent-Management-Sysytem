<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockReleaseNoteItem extends Model
{
    use SoftDeletes;

    protected $table = 'stock_release_note_items';

    protected $fillable = [
        'srn_id',
        'item_id',
        'item_code',
        'item_name',
        'release_quantity',
        'unit_of_measurement',
        'release_price',
        'line_total',
        'batch_no',
        'manufacturing_date',
        'expiry_date',
        'notes',
        'metadata',
    ];

    // Relationships
    public function stockReleaseNoteMaster()
    {
        return $this->belongsTo(StockReleaseNoteMaster::class, 'srn_id');
    }

    public function stockReleaseNote()
    {
        return $this->stockReleaseNoteMaster();
    }

    public function item()
    {
        return $this->belongsTo(ItemMaster::class, 'item_id');
    }
}
