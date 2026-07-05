<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plugin;
use App\Support\PluginManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\View\View;

class PluginController extends Controller
{
    public function index(): View
    {
        return view('admin.plugins.index', [
            'plugins' => PluginManager::all(),
            'available' => PluginManager::available(),
        ]);
    }

    /**
     * One-click install a plugin from the plugins repository.
     */
    public function install(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'id' => ['required', 'string', 'regex:/^[a-z0-9._-]+$/i'],
        ]);

        $entry = collect(PluginManager::available())->firstWhere('id', $data['id']);
        if ($entry === null) {
            return redirect()->route('admin.plugins.index')->with('error', __('That plugin is not available.'));
        }

        $compat = $entry['compat'] ?? ['ok' => true, 'issues' => []];
        if (! ($compat['ok'] ?? true)) {
            return redirect()->route('admin.plugins.index')
                ->with('error', __('Incompatible plugin: :issues.', ['issues' => implode('; ', $compat['issues'] ?? [])]));
        }

        if (! PluginManager::install($data['id'])) {
            return redirect()->route('admin.plugins.index')->with('error', __('Could not install the plugin.'));
        }

        return redirect()->route('admin.plugins.index')->with('status', __('Plugin installed — enable it below.'));
    }

    /**
     * Re-download an installed plugin to the latest version from the repository.
     */
    public function update(string $plugin): RedirectResponse
    {
        if (! array_key_exists($plugin, PluginManager::discover())) {
            return redirect()->route('admin.plugins.index')->with('error', __('That plugin is not installed.'));
        }

        if (! PluginManager::update($plugin)) {
            return redirect()->route('admin.plugins.index')->with('error', __('Could not update the plugin.'));
        }

        return redirect()->route('admin.plugins.index')->with('status', __('Plugin updated — reload the panel to apply.'));
    }

    /**
     * Show a plugin's settings form (fields declared in its plugin.json).
     */
    public function settings(string $plugin): View|RedirectResponse
    {
        $meta = PluginManager::discover()[$plugin] ?? abort(404);

        if (empty($meta['settings']) && empty($meta['info'])) {
            return redirect()->route('admin.plugins.index');
        }

        // Resolve {url} in info values to the panel's base URL.
        $info = collect($meta['info'] ?? [])->map(function ($item) {
            $item['value'] = strtr((string) ($item['value'] ?? ''), ['{url}' => rtrim(url('/'), '/')]);

            return $item;
        })->all();

        return view('admin.plugins.settings', [
            'plugin' => $meta,
            'values' => PluginManager::settingsFor($plugin),
            'info' => $info,
        ]);
    }

    /**
     * Save a plugin's settings.
     */
    public function updateSettings(Request $request, string $plugin): RedirectResponse
    {
        $meta = PluginManager::discover()[$plugin] ?? abort(404);

        $keys = collect($meta['settings'] ?? [])->pluck('key')->filter()->all();
        $values = collect($request->input('settings', []))
            ->only($keys)
            ->all();

        PluginManager::saveSettings($plugin, $values);

        return redirect()->route('admin.plugins.settings', $plugin)->with('status', __('Settings saved.'));
    }

    /**
     * Uninstall a plugin (delete its folder and stored state).
     */
    public function uninstall(string $plugin): RedirectResponse
    {
        if (! PluginManager::uninstall($plugin)) {
            return redirect()->route('admin.plugins.index')->with('error', __('Could not uninstall that plugin.'));
        }

        return redirect()->route('admin.plugins.index')->with('status', __('Plugin uninstalled.'));
    }

    /**
     * Clear the plugin registry and app caches (so new plugins/routes appear).
     */
    public function clearCache(): RedirectResponse
    {
        PluginManager::clearCache();
        Artisan::call('cache:clear');
        Artisan::call('view:clear');

        return redirect()->route('admin.plugins.index')->with('status', __('Cache cleared.'));
    }

    public function toggle(Request $request, string $plugin): RedirectResponse
    {
        if (! array_key_exists($plugin, PluginManager::discover())) {
            abort(404);
        }

        $enabled = $request->boolean('enabled');
        Plugin::updateOrCreate(['id' => $plugin], ['enabled' => $enabled]);

        return redirect()->route('admin.plugins.index')->with(
            'status',
            $enabled ? __('Plugin enabled — reload the panel to apply.') : __('Plugin disabled.'),
        );
    }
}
