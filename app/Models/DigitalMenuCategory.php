<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DigitalMenuCategory extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function digitalMenus()
    {
        return $this->hasMany(DigitalMenu::class);
    }
} 