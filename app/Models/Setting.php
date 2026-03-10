<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    use HasUuid;

    protected $fillable = [
        'group',
        'key',
        'value',
        'type',
        'label',
        'description',
        'is_public',
    ];

    protected $casts = [
        'is_public' => 'boolean',
    ];

    public static function get(string $key, $default = null)
    {
        return Cache::remember("setting.{$key}", 3600, function () use ($key, $default) {
            $setting = self::where('key', $key)->first();
            return $setting ? $setting->value : $default;
        });
    }

    public static function set(string $key, $value): bool
    {
        $setting = self::where('key', $key)->first();

        if ($setting) {
            $setting->update(['value' => $value]);
        } else {
            self::create([
                'key' => $key,
                'value' => $value,
            ]);
        }

        Cache::forget("setting.{$key}");
        Cache::forget("settings.all");

        return true;
    }

    public static function getByGroup(string $group)
    {
        return Cache::remember("settings.group.{$group}", 3600, function () use ($group) {
            return self::where('group', $group)->get();
        });
    }

    public static function getAllGrouped()
    {
        return Cache::remember("settings.all.grouped", 3600, function () {
            return self::all()->groupBy('group');
        });
    }

    public static function clearCache(): void
    {
        Cache::flush();
    }

    public static function getImageUrl(string $key): ?string
    {
        $value = self::get($key);
        if ($value) {
            return asset('storage/' . $value);
        }
        return null;
    }
}
