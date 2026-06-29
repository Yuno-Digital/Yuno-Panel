<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use PragmaRX\Google2FAQRCode\Google2FA;

class TwoFactorChallengeController extends Controller
{
    public function __construct(private readonly Google2FA $google2fa)
    {
    }

    /**
     * Show the two-factor challenge after a successful password login.
     */
    public function create(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has('login.2fa.id')) {
            return redirect()->route('login');
        }

        return view('auth.two-factor-challenge');
    }

    /**
     * Verify a TOTP code or a recovery code and complete the login.
     */
    public function store(Request $request): RedirectResponse
    {
        $userId = $request->session()->get('login.2fa.id');
        if (! $userId) {
            return redirect()->route('login');
        }

        $request->validate([
            'code' => ['nullable', 'string'],
            'recovery_code' => ['nullable', 'string'],
        ]);

        $user = User::findOrFail($userId);

        if ($request->filled('recovery_code')) {
            if (! $this->consumeRecoveryCode($user, $request->string('recovery_code')->toString())) {
                return back()->withErrors(['recovery_code' => __('That recovery code is invalid.')]);
            }
        } elseif ($request->filled('code')) {
            if (! $this->google2fa->verifyKey($user->two_factor_secret, $request->string('code')->toString())) {
                return back()->withErrors(['code' => __('The provided code is invalid.')]);
            }
        } else {
            return back()->withErrors(['code' => __('Enter your authentication or recovery code.')]);
        }

        $remember = (bool) $request->session()->pull('login.2fa.remember', false);
        $request->session()->forget('login.2fa.id');

        Auth::login($user, $remember);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Remove a used recovery code from the user's set, returning whether it
     * was valid.
     */
    private function consumeRecoveryCode(User $user, string $code): bool
    {
        $codes = $user->two_factor_recovery_codes ?? [];

        if (! in_array($code, $codes, true)) {
            return false;
        }

        $user->forceFill([
            'two_factor_recovery_codes' => array_values(array_diff($codes, [$code])),
        ])->save();

        return true;
    }
}
