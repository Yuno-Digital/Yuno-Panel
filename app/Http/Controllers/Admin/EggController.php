<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Egg;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EggController extends Controller
{
    public function index(): View
    {
        $eggs = Egg::latest()->paginate(15);

        return view('admin.eggs.index', compact('eggs'));
    }

    public function create(): View
    {
        return view('admin.eggs.create', ['egg' => new Egg]);
    }

    public function store(Request $request): RedirectResponse
    {
        Egg::create($this->validated($request));

        return redirect()->route('admin.eggs.index')->with('status', 'Egg created.');
    }

    public function edit(Egg $egg): View
    {
        return view('admin.eggs.edit', compact('egg'));
    }

    public function update(Request $request, Egg $egg): RedirectResponse
    {
        $egg->update($this->validated($request));

        return redirect()->route('admin.eggs.index')->with('status', 'Egg updated.');
    }

    public function destroy(Egg $egg): RedirectResponse
    {
        $egg->delete();

        return redirect()->route('admin.eggs.index')->with('status', 'Egg deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'author' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'docker_image' => ['required', 'string', 'max:255'],
            'startup' => ['required', 'string'],
        ]);
    }
}
