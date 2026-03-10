<?php

use App\Models\Setting;

if (!function_exists('setting')) {
    function setting(string $key, $default = null)
    {
        return Setting::get($key, $default);
    }
}

if (!function_exists('setting_image')) {
    function setting_image(string $key): ?string
    {
        return Setting::getImageUrl($key);
    }
}

if (!function_exists('settings_by_group')) {
    function settings_by_group(string $group)
    {
        return Setting::getByGroup($group);
    }
}
