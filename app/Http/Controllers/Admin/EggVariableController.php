<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Egg;
use App\Models\EggVariable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EggVariableController extends Controller
{
    public function store(Request $request, Egg $egg): RedirectResponse
    {
        $egg->variables()->create($this->validated($request));

        return $this->back($egg, 'Variable added.');
    }

    public function update(Request $request, Egg $egg, EggVariable $variable): RedirectResponse
    {
        abort_unless($variable->egg_id === $egg->id, 404);

        $variable->update($this->validated($request));

        return $this->back($egg, 'Variable updated.');
    }

    public function destroy(Egg $egg, EggVariable $variable): RedirectResponse
    {
        abort_unless($variable->egg_id === $egg->id, 404);

        $variable->delete();

        return $this->back($egg, 'Variable deleted.');
    }

    private function back(Egg $egg, string $message): RedirectResponse
    {
        return redirect()->route('admin.eggs.edit', $egg)->with('status', $message);
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'env_variable' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z_][A-Za-z0-9_]*$/'],
            'default_value' => ['nullable', 'string'],
            'rules' => ['required', 'string', 'max:255'],
        ]);

        $data['user_viewable'] = $request->boolean('user_viewable');
        $data['user_editable'] = $request->boolean('user_editable');

        return $data;
    }
}
