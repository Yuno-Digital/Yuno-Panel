<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\ApiKey;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use PragmaRX\Google2FAQRCode\Google2FA;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();

        // When 2FA setup has been started but not confirmed, render the QR code
        // so the user can scan it with their authenticator app.
        $twoFactorQr = null;
        if ($user->two_factor_secret && ! $user->two_factor_confirmed_at) {
            $google2fa = app(Google2FA::class);
            $twoFactorQr = $google2fa->getQRCodeInline(
                config('app.name'),
                $user->email,
                $user->two_factor_secret,
            );
        }

        return view('profile.edit', [
            'user' => $user,
            'apiKeys' => $user->apiKeys()
                ->where('key_type', ApiKey::TYPE_CLIENT)
                ->latest()
                ->get(),
            'twoFactorQr' => $twoFactorQr,
        ]);
    }

    /**
     * Update the user's UI theme preference (light / dark / system).
     */
    public function updateTheme(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'theme' => ['required', 'in:light,dark,system'],
        ]);

        $request->user()->update(['theme' => $data['theme']]);

        return Redirect::route('profile.edit')->with('status', 'theme-updated');
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
