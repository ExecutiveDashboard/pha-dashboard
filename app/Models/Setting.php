<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = ['key', 'value', 'label', 'type', 'group'];

    protected static $cache = null;

    public static function getValue(string $key, mixed $default = null): mixed
    {
        if (self::$cache === null) {
            self::$cache = Cache::remember('app_settings_dict', 3600, function () {
                return static::pluck('value', 'key')->toArray();
            });
        }
        return array_key_exists($key, self::$cache) ? self::$cache[$key] : $default;
    }

    public static function setValue(string $key, mixed $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
        self::$cache = null;
        Cache::forget('app_settings_dict');
    }
}
