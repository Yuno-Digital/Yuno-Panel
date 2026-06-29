<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingController extends Controller
{
    /**
     * Settings the panel exposes, with their defaults.
     */
    private const DEFAULTS = [
        'panel_name' => 'Yuno Panel',
        'support_url' => '',
        'allow_registration' => '1',
    ];

    public function index(): View
    {
        $settings = [];
        foreach (self::DEFAULTS as $key => $default) {
            $settings[$key] = Setting::get($key, $default);
        }

        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'panel_name' => ['required', 'string', 'max:255'],
            'support_url' => ['nullable', 'url', 'max:255'],
            'allow_registration' => ['nullable', 'boolean'],
        ]);

        Setting::set('panel_name', $data['panel_name']);
        Setting::set('support_url', $data['support_url'] ?? '');
        Setting::set('allow_registration', $request->boolean('allow_registration') ? '1' : '0');

        return redirect()->route('admin.settings.index')->with('status', 'Settings saved.');
    }
}
