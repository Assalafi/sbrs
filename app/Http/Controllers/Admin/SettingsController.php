<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = Setting::getAllGrouped();
        return view('admin.settings.index', compact('settings'));
    }

    public function edit(string $group)
    {
        $settings = Setting::where('group', $group)->get();
        return view('admin.settings.edit', compact('settings', 'group'));
    }

    public function update(Request $request, string $group)
    {
        $settings = Setting::where('group', $group)->get();

        foreach ($settings as $setting) {
            $key = $setting->key;

            if ($setting->type === 'image' || $setting->type === 'file') {
                if ($request->hasFile($key)) {
                    $file = $request->file($key);
                    $oldValue = $setting->value;
                    if ($oldValue && Storage::disk('public')->exists($oldValue)) {
                        Storage::disk('public')->delete($oldValue);
                    }
                    $path = $file->store('settings', 'public');
                    $setting->update(['value' => $path]);
                }
            } elseif ($setting->type === 'boolean') {
                $setting->update(['value' => $request->has($key) ? '1' : '0']);
            } else {
                if ($request->has($key)) {
                    $setting->update(['value' => $request->input($key)]);
                }
            }
        }

        Setting::clearCache();

        return redirect()->route('admin.settings.index')
            ->with('success', ucfirst($group) . ' settings updated successfully.');
    }

    public function clearCache()
    {
        Setting::clearCache();
        return back()->with('success', 'Cache cleared successfully.');
    }
}
