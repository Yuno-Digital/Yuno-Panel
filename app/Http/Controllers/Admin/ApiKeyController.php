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
    /**
     * List the current admin's application keys.
     */
    public function application(): View
    {
        $keys = ApiKey::where('key_type', ApiKey::TYPE_APPLICATION)
            ->where('user_id', Auth::id())
            ->latest()
            ->get();

        return view('admin.api.index', [
            'keys' => $keys,
            'title' => 'Application Keys',
        ]);
    }

    /**
     * Generate a new application key and flash its one-time plaintext secret.
     */
    public function storeApplication(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'memo' => ['nullable', 'string', 'max:255'],
        ]);

        [, $plaintext] = ApiKey::generate($request->user(), ApiKey::TYPE_APPLICATION, $data['memo'] ?? null);

        return redirect()->route('admin.api.application.index')
            ->with('status', 'API key created. Copy it now — it will not be shown again.')
            ->with('new_key', $plaintext);
    }

    /**
     * Revoke an application key.
     */
    public function destroy(ApiKey $apiKey): RedirectResponse
    {
        abort_unless($apiKey->key_type === ApiKey::TYPE_APPLICATION, 404);

        $apiKey->delete();

        return redirect()->route('admin.api.application.index')->with('status', 'API key revoked.');
    }
}
