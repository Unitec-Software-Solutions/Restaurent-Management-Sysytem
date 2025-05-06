<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'description',
        'is_public',
    ];

    protected $casts = [
        'is_public' => 'boolean',
    ];

    public static function getValue(string $key, $default = null)
    {
        $setting = static::where('key', $key)->first();
        if (!$setting) {
            return $default;
        }

        return match ($setting->type) {
            'boolean' => (bool) $setting->value,
            'integer' => (int) $setting->value,
            'json' => json_decode($setting->value, true),
            default => $setting->value,
        };
    }

    public static function setValue(string $key, $value): void
    {
        $setting = static::where('key', $key)->first();
        if (!$setting) {
            return;
        }

        $setting->value = match ($setting->type) {
            'boolean' => (string) (bool) $value,
            'integer' => (string) (int) $value,
            'json' => json_encode($value),
            default => (string) $value,
        };
        $setting->save();
    }
} 