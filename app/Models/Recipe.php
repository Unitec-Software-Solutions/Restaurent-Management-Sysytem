<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Recipe extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'production_item_id',
        'recipe_name',
        'description',
        'instructions',
        'yield_quantity',
        'preparation_time',
        'cooking_time',
        'total_time',
        'difficulty_level',
        'notes',
        'is_active',
        'created_by',
        'updated_by',
    ];

    public function details()
    {
        return $this->hasMany(RecipeDetail::class, 'recipe_id');
    }

    public function productionItem()
    {
        return $this->belongsTo(ItemMaster::class, 'production_item_id');
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
}
