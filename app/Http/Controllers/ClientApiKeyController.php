<?php

namespace App\Http\Controllers;

use App\Models\ApiKey;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ClientApiKeyController extends Controller
{
    /**
     * Create a client API key for the current user.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'memo' => ['nullable', 'string', 'max:255'],
        ]);

        [, $plaintext] = ApiKey::generate($request->user(), ApiKey::TYPE_CLIENT, $data['memo'] ?? null);

        return redirect()->route('profile.edit')
            ->with('status', 'api-key-created')
            ->with('new_key', $plaintext);
    }

    /**
     * Revoke one of the current user's client API keys.
     */
    public function destroy(Request $request, ApiKey $apiKey): RedirectResponse
    {
        abort_unless(
            $apiKey->key_type === ApiKey::TYPE_CLIENT && $apiKey->user_id === $request->user()->id,
            403,
        );

        $apiKey->delete();

        return redirect()->route('profile.edit')->with('status', 'api-key-revoked');
    }
}
