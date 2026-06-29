<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Egg;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
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
            'tags' => ['nullable', 'string'],
            'features' => ['nullable', 'string'],
            'docker_images' => ['required', 'string'],
            'file_denylist' => ['nullable', 'string'],
            'update_url' => ['nullable', 'url', 'max:255'],
            'startup' => ['required', 'string'],
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

        $images = $this->parseDockerImages($data['docker_images']);

        return [
            'name' => $data['name'],
            'author' => $data['author'] ?? null,
            'description' => $data['description'] ?? null,
            'tags' => $this->parseList($data['tags'] ?? ''),
            'features' => $this->parseList($data['features'] ?? ''),
            'docker_images' => $images,
            'docker_image' => array_values($images)[0] ?? '',
            'file_denylist' => $this->parseList($data['file_denylist'] ?? ''),
            'update_url' => $data['update_url'] ?? null,
            'startup' => $data['startup'],
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
     * Parse "Display Name|image:tag" lines into a name => image map. Lines with
     * no pipe use the image as its own display name.
     *
     * @return array<string, string>
     */
    private function parseDockerImages(string $value): array
    {
        $images = [];
        foreach (preg_split('/[\r\n]+/', $value) as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            if (str_contains($line, '|')) {
                [$name, $image] = array_map('trim', explode('|', $line, 2));
            } else {
                $name = $image = $line;
            }
            $images[$name] = $image;
        }

        return $images;
    }
}
