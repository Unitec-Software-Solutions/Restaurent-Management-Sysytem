<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuItem extends Model
{
    public function menuCategories()
    {
        return $this->belongsToMany(MenuCategory::class);
    }
} 