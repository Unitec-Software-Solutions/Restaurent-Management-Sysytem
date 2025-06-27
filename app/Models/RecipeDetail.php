<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RecipeDetail extends Model
{
    use HasFactory;

    protected $table = 'production_recipe_details';

    protected $fillable = [
        'recipe_id',
        'raw_material_item_id',
        'quantity_required',
        'unit_of_measurement',
        'preparation_notes'
    ];

    public function recipe()
    {
        return $this->belongsTo(Recipe::class, 'recipe_id');
    }

    public function rawMaterialItem()
    {
        return $this->belongsTo(ItemMaster::class, 'raw_material_item_id');
    }
}
