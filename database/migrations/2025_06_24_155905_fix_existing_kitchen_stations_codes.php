<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\KitchenStation;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Fix any existing kitchen stations that don't have codes
        KitchenStation::whereNull('code')->orWhere('code', '')->each(function ($station) {
            $typePrefix = match($station->type) {
                'cooking' => 'COOK',
                'prep' => 'PREP',
                'beverage' => 'BEV',
                'dessert' => 'DESS',
                'grill' => 'GRILL',
                'fry' => 'FRY',
                'bar' => 'BAR',
                default => 'MAIN'
            };
            
            $station->update([
                'code' => $typePrefix . '-' . str_pad($station->branch_id, 2, '0', STR_PAD_LEFT) . '-' . str_pad($station->id, 3, '0', STR_PAD_LEFT)
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Could revert codes if needed
    }
};
