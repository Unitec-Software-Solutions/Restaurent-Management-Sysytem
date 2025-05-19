<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\MenuCategory;
use Illuminate\Database\Eloquent\Model;

class TimeSlot extends Model
{
    use HasFactory;

    // Specify the attributes that are mass assignable
    protected $fillable = ['start_time', 'end_time', 'day_of_week'];

    // Define relationships (if applicable)
    public function menuCategories()
    {
        return $this->hasMany(MenuCategory::class);
    }
}