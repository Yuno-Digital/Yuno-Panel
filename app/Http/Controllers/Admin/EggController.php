<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Egg;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class EggController extends Controller
{
    public function index(): View
    {
        $eggs = Egg::withCount('variables')->latest()->paginate(15);

        return view('admin.eggs.index', compact('eggs'));
    }

    public function create(): View
    {
        return view('admin.eggs.create', [
            'egg' => new Egg(['script_entry' => 'bash', 'script_is_privileged' => true]),
            'eggs' => Egg::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $egg = Egg::create($this->validated($request));

        return redirect()->route('admin.eggs.edit', $egg)->with('status', 'Egg created.');
    }

    public function edit(Egg $egg): View
    {
        return view('admin.eggs.edit', [
            'egg' => $egg->load('variables'),
            'eggs' => Egg::where('id', '!=', $egg->id)->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Egg $egg): RedirectResponse
    {
        $egg->update($this->validated($request, $egg));

        return redirect()->route('admin.eggs.edit', $egg)->with('status', 'Egg updated.');
    }

    public function destroy(Egg $egg): RedirectResponse
    {
        $egg->delete();

        return redirect()->route('admin.eggs.index')->with('status', 'Egg deleted.');
    }

    /**
     * Validate and normalise the egg form into model attributes.
     *
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?Egg $egg = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'author' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'icon' => [
                'nullable', 'string', 'max:262144', // ~256 KB, enough for a small data:image
                function (string $attribute, mixed $value, \Closure $fail) {
                    if ($value && ! preg_match('#^(https?://|data:image/)#i', (string) $value)) {
                        $fail(__('The icon must be a URL or a data:image value.'));
                    }
                },
            ],
            'tags' => ['nullable', 'string'],
            'features' => ['nullable', 'string'],
            'docker_image_names' => ['required', 'array', 'min:1'],
            'docker_image_names.*' => ['nullable', 'string', 'max:255'],
            'docker_image_values' => ['required', 'array', 'min:1'],
            'docker_image_values.*' => ['nullable', 'string', 'max:255'],
            'file_denylist' => ['nullable', 'string'],
            'update_url' => ['nullable', 'url', 'max:255'],
            'startup_command_names' => ['nullable', 'array'],
            'startup_command_names.*' => ['nullable', 'string', 'max:255'],
            'startup_command_values' => ['required', 'array', 'min:1'],
            'startup_command_values.*' => ['nullable', 'string'],
            'config_from' => ['nullable', Rule::exists('eggs', 'id')],
            'config_startup' => ['nullable', 'string', 'json'],
            'config_stop' => ['nullable', 'string', 'max:255'],
            'config_files' => ['nullable', 'string', 'json'],
            'config_logs' => ['nullable', 'string', 'json'],
            'copy_script_from' => ['nullable', Rule::exists('eggs', 'id')],
            'script_container' => ['required', 'string', 'max:255'],
            'script_entry' => ['required', 'string', 'max:255'],
            'script_install' => ['nullable', 'string'],
        ]);

        $images = $this->zipDockerImages($data['docker_image_names'], $data['docker_image_values']);
        if ($images === []) {
            throw ValidationException::withMessages(['docker_image_values' => __('Add at least one Docker image.')]);
        }

        $startups = $this->zipStartupCommands($data['startup_command_names'] ?? [], $data['startup_command_values']);
        if ($startups === []) {
            throw ValidationException::withMessages(['startup_command_values' => __('Add at least one startup command.')]);
        }

        return [
            'name' => $data['name'],
            'author' => $data['author'] ?? null,
            'description' => $data['description'] ?? null,
            'icon' => $data['icon'] ?? null,
            'tags' => $this->parseList($data['tags'] ?? ''),
            'features' => $this->parseList($data['features'] ?? ''),
            'docker_images' => $images,
            'docker_image' => array_values($images)[0] ?? '',
            'file_denylist' => $this->parseList($data['file_denylist'] ?? ''),
            'update_url' => $data['update_url'] ?? null,
            'startup' => $startups[0]['command'],
            'startup_commands' => $startups,
            'config_from' => $data['config_from'] ?? null,
            'config_startup' => $data['config_startup'] ?? null,
            'config_stop' => $data['config_stop'] ?? null,
            'config_files' => $data['config_files'] ?? null,
            'config_logs' => $data['config_logs'] ?? null,
            'copy_script_from' => $data['copy_script_from'] ?? null,
            'script_container' => $data['script_container'],
            'script_entry' => $data['script_entry'],
            'script_is_privileged' => $request->boolean('script_is_privileged'),
            'script_install' => $data['script_install'] ?? null,
        ];
    }

    /**
     * Parse a newline/comma separated textarea into a clean list.
     *
     * @return array<int, string>
     */
    private function parseList(string $value): array
    {
        return collect(preg_split('/[\r\n,]+/', $value))
            ->map(fn ($v) => trim($v))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * Combine parallel name/value input arrays into a display-name => image map,
     * skipping rows with an empty image. A blank name falls back to the image.
     *
     * @param  array<int, string|null>  $names
     * @param  array<int, string|null>  $values
     * @return array<string, string>
     */
    private function zipDockerImages(array $names, array $values): array
    {
        $images = [];
        foreach ($values as $i => $image) {
            $image = trim((string) $image);
            if ($image === '') {
                continue;
            }
            $name = trim((string) ($names[$i] ?? ''));
            $images[$name !== '' ? $name : $image] = $image;
        }

        return $images;
    }

    /**
     * Combine parallel name/value input arrays into a list of startup commands,
     * preserving an optional label and skipping rows with an empty command.
     *
     * @param  array<int, string|null>  $names
     * @param  array<int, string|null>  $values
     * @return array<int, array{name: string, command: string}>
     */
    private function zipStartupCommands(array $names, array $values): array
    {
        $commands = [];
        foreach ($values as $i => $command) {
            $command = trim((string) $command);
            if ($command === '') {
                continue;
            }
            $commands[] = [
                'name' => trim((string) ($names[$i] ?? '')),
                'command' => $command,
            ];
        }

        return $commands;
    }
}
