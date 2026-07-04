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
        return view('admin.plugins.index', ['plugins' => PluginManager::all()]);
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
