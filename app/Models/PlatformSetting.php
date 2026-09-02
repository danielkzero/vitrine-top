<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlatformSetting extends Model
{
    protected $fillable = ['key', 'value', 'is_secret'];

    protected function casts(): array
    {
        return ['value' => 'encrypted', 'is_secret' => 'boolean'];
    }

    public static function valueFor(string $key, mixed $default = null): mixed
    {
        return static::where('key', $key)->first()?->value ?? $default;
    }

    public static function setValue(string $key, mixed $value, bool $secret = false): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value, 'is_secret' => $secret]);
    }
}
