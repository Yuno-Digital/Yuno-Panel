<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiKey;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ApiKeyController extends Controller
{
    public function application(): View
    {
        return $this->index(ApiKey::TYPE_APPLICATION);
    }

    public function client(): View
    {
        return $this->index(ApiKey::TYPE_CLIENT);
    }

    public function storeApplication(Request $request): RedirectResponse
    {
        return $this->store($request, ApiKey::TYPE_APPLICATION, 'admin.api.application.index');
    }

    public function storeClient(Request $request): RedirectResponse
    {
        return $this->store($request, ApiKey::TYPE_CLIENT, 'admin.api.client.index');
    }

    public function destroy(ApiKey $apiKey): RedirectResponse
    {
        $type = $apiKey->key_type;
        $apiKey->delete();

        return redirect()->route("admin.api.{$type}.index")->with('status', 'API key revoked.');
    }

    /**
     * Render the list of keys of a given type for the current user.
     */
    private function index(string $type): View
    {
        $keys = ApiKey::where('key_type', $type)
            ->where('user_id', Auth::id())
            ->latest()
            ->get();

        return view('admin.api.index', [
            'type' => $type,
            'keys' => $keys,
            'title' => $type === ApiKey::TYPE_APPLICATION ? 'Application Keys' : 'Client Keys',
            'createRoute' => "admin.api.{$type}.store",
        ]);
    }

    /**
     * Generate a new key and flash its one-time plaintext secret.
     */
    private function store(Request $request, string $type, string $redirectRoute): RedirectResponse
    {
        $data = $request->validate([
            'memo' => ['nullable', 'string', 'max:255'],
        ]);

        [, $plaintext] = ApiKey::generate($request->user(), $type, $data['memo'] ?? null);

        return redirect()->route($redirectRoute)
            ->with('status', 'API key created. Copy it now — it will not be shown again.')
            ->with('new_key', $plaintext);
    }
}
