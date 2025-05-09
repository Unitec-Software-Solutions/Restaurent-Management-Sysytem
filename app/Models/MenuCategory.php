<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class MenuCategory extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'is_inactive',
        'display_order',
        'is_active',
    ];

    protected $casts = [
        'is_inactive' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function menuItems()
    {
        return $this->hasMany(MenuItem::class);
    }

    // Scope for active categories
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Scope for ordered categories
    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order');
    }

    // Scope for seasonal categories that should be active now
    public function scopeSeasonal($query)
    {
        $now = Carbon::now();
        
        return $query->where(function($q) use ($now) {
            // Avurudu (April)
            $q->when($now->month === 4, function($q) {
                $q->orWhere('name', 'Avurudu Special');
            })
            // Christmas (December)
            ->when($now->month === 12, function($q) {
                $q->orWhere('name', 'Christmas Feast');
            })
            // Mango Season (May-July)
            ->when($now->month >= 5 && $now->month <= 7, function($q) {
                $q->orWhere('name', 'Mango Season');
            })
            // Monsoon Season (May-September for SW monsoon, Oct-Jan for NE monsoon)
            ->when(($now->month >= 5 && $now->month <= 9) || 
                  ($now->month >= 10 || $now->month <= 1), function($q) {
                $q->orWhere('name', 'Monsoon Warmers');
            });
        });
    }

    // Scope for current meal period categories
    public function scopeCurrentMealPeriod($query)
    {
        $hour = Carbon::now()->hour;
        
        return $query->where(function($q) use ($hour) {
            // Breakfast (6am-11am)
            $q->when($hour >= 6 && $hour < 11, function($q) {
                $q->orWhere('name', 'Breakfast');
            })
            // Brunch (11am-2pm)
            ->when($hour >= 11 && $hour < 14, function($q) {
                $q->orWhere('name', 'Brunch');
            })
            // Lunch (11am-3pm)
            ->when($hour >= 11 && $hour < 15, function($q) {
                $q->orWhere('name', 'Lunch');
            })
            // Dinner (6pm-11pm)
            ->when($hour >= 18 || $hour < 23, function($q) {
                $q->orWhere('name', 'Dinner');
            });
        });
    }
}