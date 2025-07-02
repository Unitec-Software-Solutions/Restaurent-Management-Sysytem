<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MenuCategory extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'branch_id',
        'organization_id', 
        'name',
        'unicode_name',
        'description',
        'image_url',
        'sort_order',
        'display_order', 
        'is_active',
        'is_inactive', 
        'is_featured',
        'settings',
        'availability_schedule',
        'notes'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_inactive' => 'boolean',
        'is_featured' => 'boolean',
        'sort_order' => 'integer',
        'display_order' => 'integer',
        'settings' => 'array',
        'availability_schedule' => 'array'
    ];

    protected $attributes = [
        'is_active' => true,
        'is_inactive' => false,
        'is_featured' => false,
        'sort_order' => 1
    ];

    /**
     * Boot the model for Laravel + PostgreSQL + Tailwind CSS
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($category) {
            // Set unicode_name if not provided
            if (empty($category->unicode_name)) {
                $category->unicode_name = $category->name;
            }
            
            // Set organization_id from branch if not provided
            if (empty($category->organization_id) && !empty($category->branch_id)) {
                $branch = \App\Models\Branch::find($category->branch_id);
                if ($branch) {
                    $category->organization_id = $branch->organization_id;
                }
            }
            
            // Set sort_order if not provided
            if (empty($category->sort_order)) {
                $maxOrder = static::where('branch_id', $category->branch_id)->max('sort_order') ?? 0;
                $category->sort_order = $maxOrder + 1;
            }
        });
    }

    // Relationships
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function menuItems(): HasMany
    {
        return $this->hasMany(MenuItem::class, 'menu_category_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeForBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }
}