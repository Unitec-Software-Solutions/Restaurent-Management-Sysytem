<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationProvider extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'credentials',
        'is_active',
    ];

    protected $casts = [
        'credentials' => 'array',
        'is_active' => 'boolean',
    ];

    public function setCredentialsAttribute($value)
    {
        $this->attributes['credentials'] = json_encode($value);
    }
} 