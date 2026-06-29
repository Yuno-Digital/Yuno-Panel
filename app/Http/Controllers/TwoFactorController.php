<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use PragmaRX\Google2FAQRCode\Google2FA;

class TwoFactorController extends Controller
{
    public function __construct(private readonly Google2FA $google2fa)
    {
    }

    /**
     * Begin setup: generate a (still unconfirmed) secret so the user can scan
     * the QR code shown on the profile page.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->user()->forceFill([
            'two_factor_secret' => $this->google2fa->generateSecretKey(),
            'two_factor_confirmed_at' => null,
            'two_factor_recovery_codes' => null,
        ])->save();

        return redirect()->route('profile.edit')->with('status', 'two-factor-pending');
    }

    /**
     * Confirm setup by verifying a code from the authenticator app. On success
     * the feature becomes active and recovery codes are generated.
     */
    public function confirm(Request $request): RedirectResponse
    {
        $request->validate(['code' => ['required', 'string']]);
        $user = $request->user();

        if (! $user->two_factor_secret
            || ! $this->google2fa->verifyKey($user->two_factor_secret, $request->string('code')->toString())) {
            return back()->withErrors(['code' => __('The provided code is invalid.')]);
        }

        $user->forceFill([
            'two_factor_confirmed_at' => now(),
            'two_factor_recovery_codes' => self::recoveryCodes(),
        ])->save();

        return redirect()->route('profile.edit')->with('status', 'two-factor-confirmed');
    }

    /**
     * Disable two-factor authentication.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->user()->forceFill([
            'two_factor_secret' => null,
            'two_factor_confirmed_at' => null,
            'two_factor_recovery_codes' => null,
        ])->save();

        return redirect()->route('profile.edit')->with('status', 'two-factor-disabled');
    }

    /**
     * Generate a fresh set of one-time recovery codes.
     *
     * @return array<int, string>
     */
    public static function recoveryCodes(): array
    {
        return collect(range(1, 8))
            ->map(fn () => Str::random(10).'-'.Str::random(10))
            ->all();
    }
}
