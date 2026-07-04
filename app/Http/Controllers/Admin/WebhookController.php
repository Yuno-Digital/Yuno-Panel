<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Webhook;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class WebhookController extends Controller
{
    public function index(): View
    {
        return view('admin.webhooks.index', ['webhooks' => Webhook::latest()->get()]);
    }

    public function create(): View
    {
        return view('admin.webhooks.create', ['webhook' => new Webhook(['is_active' => true])]);
    }

    public function store(Request $request): RedirectResponse
    {
        Webhook::create($this->validated($request));

        return redirect()->route('admin.webhooks.index')->with('status', 'Webhook created.');
    }

    public function edit(Webhook $webhook): View
    {
        return view('admin.webhooks.edit', compact('webhook'));
    }

    public function update(Request $request, Webhook $webhook): RedirectResponse
    {
        $webhook->update($this->validated($request));

        return redirect()->route('admin.webhooks.index')->with('status', 'Webhook updated.');
    }

    public function destroy(Webhook $webhook): RedirectResponse
    {
        $webhook->delete();

        return redirect()->route('admin.webhooks.index')->with('status', 'Webhook deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'url' => ['required', 'url', 'max:2048'],
            'events' => ['nullable', 'array'],
            'events.*' => [Rule::in(array_keys(Webhook::EVENTS))],
            'is_active' => ['nullable', 'boolean'],
        ]);

        return [
            'name' => $data['name'],
            'url' => $data['url'],
            'events' => array_values($data['events'] ?? []),
            'is_active' => $request->boolean('is_active'),
        ];
    }
}
