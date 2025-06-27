<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Recipe extends Model
{
    use HasFactory;

    protected $table = 'production_recipes';

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

    protected $casts = [
        'yield_quantity' => 'decimal:2',
        'preparation_time' => 'integer',
        'cooking_time' => 'integer',
        'total_time' => 'integer',
        'is_active' => 'boolean',
    ];

    // Relationships
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

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForProductionItem($query, $itemId)
    {
        return $query->where('production_item_id', $itemId);
    }

    // Helper methods
    public function getTotalIngredientCost()
    {
        return $this->details->sum(function ($detail) {
            return $detail->quantity_required * ($detail->rawMaterialItem->buying_price ?? 0);
        });
    }

    public function getCostPerUnit()
    {
        return $this->yield_quantity > 0 ? $this->getTotalIngredientCost() / $this->yield_quantity : 0;
    }

    /**
     * Calculate ingredient requirements for a specific production quantity
     */
    public function calculateIngredientsForQuantity($productionQuantity)
    {
        $multiplier = $productionQuantity / $this->yield_quantity;

        return $this->details->map(function ($detail) use ($multiplier) {
            return [
                'ingredient_item_id' => $detail->raw_material_item_id,
                'ingredient' => $detail->rawMaterialItem,
                'planned_quantity' => $detail->quantity_required * $multiplier,
                'unit_of_measurement' => $detail->unit_of_measurement,
                'preparation_notes' => $detail->preparation_notes,
                'is_manually_added' => false,
            ];
        });
    }

    /**
     * Get formatted total time
     */
    public function getFormattedTotalTime()
    {
        if ($this->total_time < 60) {
            return $this->total_time . ' min';
        }

        $hours = floor($this->total_time / 60);
        $minutes = $this->total_time % 60;

        return $hours . 'h ' . ($minutes > 0 ? $minutes . 'm' : '');
    }

    /**
     * Check if recipe can be used for production
     */
    public function canBeUsedForProduction()
    {
        return $this->is_active &&
               $this->details->count() > 0 &&
               $this->yield_quantity > 0;
    }
}
