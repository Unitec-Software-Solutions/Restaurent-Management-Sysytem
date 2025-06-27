<?php

namespace App\Services;

use App\Models\ItemMaster;
use App\Models\ItemTransaction;
use App\Models\Branch;
use Illuminate\Support\Facades\Auth;

class IngredientStockService
{
    /**
     * Get current stock level for an ingredient in HQ branch
     */
    public static function getHQStock($ingredientId)
    {
        $hqBranch = Branch::where('organization_id', Auth::user()->organization_id)
            ->where('is_head_office', true)
            ->first();

        if (!$hqBranch) {
            return 0;
        }

        return ItemTransaction::stockOnHand($ingredientId, $hqBranch->id);
    }

    /**
     * Get stock levels for multiple ingredients in HQ branch
     */
    public static function getMultipleHQStock(array $ingredientIds)
    {
        $hqBranch = Branch::where('organization_id', Auth::user()->organization_id)
            ->where('is_head_office', true)
            ->first();

        if (!$hqBranch) {
            return array_fill_keys($ingredientIds, 0);
        }

        $stocks = [];
        foreach ($ingredientIds as $ingredientId) {
            $stocks[$ingredientId] = ItemTransaction::stockOnHand($ingredientId, $hqBranch->id);
        }

        return $stocks;
    }

    /**
     * Check if sufficient stock exists for ingredient requirements
     */
    public static function validateIngredientRequirements(array $requirements)
    {
        $ingredientIds = array_keys($requirements);
        $stocks = self::getMultipleHQStock($ingredientIds);

        $shortages = [];

        foreach ($requirements as $ingredientId => $requiredQuantity) {
            $availableStock = $stocks[$ingredientId] ?? 0;

            if ($availableStock < $requiredQuantity) {
                $ingredient = ItemMaster::find($ingredientId);
                $shortages[] = [
                    'ingredient_id' => $ingredientId,
                    'ingredient_name' => $ingredient->name,
                    'required' => $requiredQuantity,
                    'available' => $availableStock,
                    'shortage' => $requiredQuantity - $availableStock,
                    'unit' => $ingredient->unit_of_measurement
                ];
            }
        }

        return [
            'has_shortages' => !empty($shortages),
            'shortages' => $shortages,
            'can_proceed' => empty($shortages)
        ];
    }

    /**
     * Get HQ branch for organization
     */
    public static function getHQBranch($organizationId = null)
    {
        $orgId = $organizationId ?: Auth::user()->organization_id;

        return Branch::where('organization_id', $orgId)
            ->where('is_head_office', true)
            ->first();
    }
}
