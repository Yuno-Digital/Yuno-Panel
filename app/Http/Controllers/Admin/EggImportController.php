<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Egg;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class EggImportController extends Controller
{
    /**
     * Import an egg from an uploaded Pterodactyl/Pelican egg JSON file.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'egg_file' => ['required', 'file', 'max:2048'],
        ]);

        $json = json_decode((string) file_get_contents($request->file('egg_file')->getRealPath()), true);

        if (! is_array($json) || empty($json['name'])) {
            return back()->with('error', __('That file does not look like a valid egg export.'));
        }

        $egg = DB::transaction(fn () => $this->build($json));

        return redirect()->route('admin.eggs.edit', $egg)
            ->with('status', __('Egg ":name" imported.', ['name' => $egg->name]));
    }

    /**
     * Map an egg export array onto an Egg model and its variables.
     *
     * @param  array<string, mixed>  $json
     */
    private function build(array $json): Egg
    {
        $config = Arr::get($json, 'config', []);
        $script = Arr::get($json, 'scripts.installation', []);

        $images = $json['docker_images'] ?? [];
        if (! $images && ! empty($json['image'])) {
            $images = [$json['image'] => $json['image']];
        }

        $startup = (string) ($json['startup'] ?? '');

        $egg = Egg::create([
            'name' => $json['name'],
            'author' => $json['author'] ?? null,
            'description' => $json['description'] ?? null,
            'tags' => $json['tags'] ?? null,
            'features' => $json['features'] ?? null,
            'docker_images' => $images ?: null,
            'docker_image' => Arr::first($images) ?? '',
            'file_denylist' => $json['file_denylist'] ?? null,
            'update_url' => Arr::get($json, 'meta.update_url'),
            'startup' => $startup,
            'startup_commands' => $startup !== '' ? [$startup] : null,
            'config_files' => $this->asJsonText($config['files'] ?? null),
            'config_startup' => $this->asJsonText($config['startup'] ?? null),
            'config_logs' => $this->asJsonText($config['logs'] ?? null),
            'config_stop' => $config['stop'] ?? null,
            'script_install' => $script['script'] ?? null,
            'script_container' => $script['container'] ?? 'ghcr.io/pelican-eggs/installers:debian',
            'script_entry' => $script['entrypoint'] ?? 'bash',
        ]);

        foreach ($json['variables'] ?? [] as $variable) {
            $rules = $variable['rules'] ?? 'nullable|string';

            $egg->variables()->create([
                'name' => $variable['name'] ?? 'Variable',
                'description' => $variable['description'] ?? null,
                'env_variable' => $variable['env_variable'] ?? 'VARIABLE',
                'default_value' => $variable['default_value'] ?? null,
                'user_viewable' => (bool) ($variable['user_viewable'] ?? true),
                'user_editable' => (bool) ($variable['user_editable'] ?? true),
                'rules' => is_array($rules) ? implode('|', $rules) : (string) $rules,
            ]);
        }

        return $egg;
    }

    /**
     * Normalise a config block to a JSON string (export files vary between
     * embedding raw JSON strings and nested objects).
     */
    private function asJsonText(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_string($value)) {
            return $value;
        }

        return json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
}
