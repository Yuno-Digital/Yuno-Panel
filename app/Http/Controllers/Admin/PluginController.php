<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plugin;
use App\Support\PluginManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

        $available = collect(PluginManager::available())->pluck('id')->all();
        if (! in_array($data['id'], $available, true)) {
            return redirect()->route('admin.plugins.index')->with('error', __('That plugin is not available.'));
        }

        if (! PluginManager::install($data['id'])) {
            return redirect()->route('admin.plugins.index')->with('error', __('Could not install the plugin.'));
        }

        return redirect()->route('admin.plugins.index')->with('status', __('Plugin installed — enable it below.'));
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
