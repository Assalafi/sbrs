<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Services\RemitaSplitService;

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
        $remitaSplitSettings = $group === 'remita' ? RemitaSplitService::settings() : [];
        return view('admin.settings.edit', compact('settings', 'group', 'remitaSplitSettings'));
    }

    public function update(Request $request, string $group)
    {
        $settings = Setting::where('group', $group)->get();

        if ($group === 'remita' && $request->has('remita_beneficiaries')) {
            $split = RemitaSplitService::normaliseSettings([
                'enabled' => $request->boolean('remita_split_enabled'),
                'beneficiaries' => $request->input('remita_beneficiaries', []),
            ]);
            if ($split === null) return back()->withInput()->with('error', 'Check the split settings: use three accounts whose percentages total exactly 100%.');
            Setting::set('remita_split_settings', json_encode($split, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        }

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
