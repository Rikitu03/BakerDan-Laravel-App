<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserDetail;
use App\Services\GuestCartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function __construct(private GuestCartService $guestCart)
    {
    }

    public function show(Request $request)
    {
        $redirectTo = $this->guestCart->storeIntendedUrl($request, $request->query('redirect'));

        return view('auth.login', compact('redirectTo'));
    }

    public function guest(Request $request): RedirectResponse
    {
        $this->guestCart->markGuest($request);
        $redirectTo = $this->guestCart->storeIntendedUrl($request, $request->input('redirect') ?: $request->query('redirect'));

        return redirect($this->guestRedirect($redirectTo));
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required',
            'password' => 'required',
            'redirect' => 'nullable|string|max:1000',
        ]);

        $this->guestCart->storeIntendedUrl($request, $credentials['redirect'] ?? null);

        $detail = UserDetail::query()
            ->where('username', '=', $credentials['username'], 'and')
            ->first();

        if ($detail && Hash::check($credentials['password'], $detail->password)) {
            $user = $detail->user;
            
            if (!$user->is_active) {
                return back()->withErrors(['username' => 'Your account is inactive.']);
            }

            Auth::login($user);
            $request->session()->regenerate();
            $this->storePasswordHashInSession($request, $user->user_id, (string) $detail->password);

            if ($user->role === 'admin') {
                return redirect('/admin');
            }

            $mergeResult = $this->guestCart->mergeIntoUserCart($request, (int) $user->user_id);

            return redirect($this->customerRedirectAfterAuth($request, $mergeResult));
        }

        return back()->withErrors([
            'username' => 'The provided credentials do not match our records.',
        ]);
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return $this->withoutBrowserCache(redirect('/login'));
    }

    private function withoutBrowserCache(RedirectResponse $response): RedirectResponse
    {
        return $response
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', 'Fri, 01 Jan 1990 00:00:00 GMT');
    }

    private function guestRedirect(?string $target): string
    {
        if (! $target) {
            return '/customer';
        }

        $path = parse_url($target, PHP_URL_PATH);

        return match ($path) {
            '/cart' => '/customer/cart',
            '/customer', '/customer/cart', '/customer/customize' => $target,
            default => '/customer/cart',
        };
    }

    private function storePasswordHashInSession(Request $request, int $userId, string $passwordHash): void
    {
        $request->session()->put([
            'auth.password_hash_user_id' => $userId,
            'auth.password_hash' => $passwordHash,
        ]);
    }

    /**
     * @param array{all_cart_item_ids?: array<int, int>} $mergeResult
     */
    private function customerRedirectAfterAuth(Request $request, array $mergeResult): string
    {
        $target = $this->guestCart->pullIntendedUrl($request) ?: '/customer';

        if ($target === '/cart') {
            return '/customer/cart';
        }

        $parts = parse_url($target);
        $path = $parts['path'] ?? '/customer';

        if ($path !== '/customer/checkout') {
            return $target;
        }

        $cartItemIds = $mergeResult['all_cart_item_ids'] ?? [];

        if ($cartItemIds === []) {
            return '/customer/cart';
        }

        $query = [];
        if (isset($parts['query']) && is_string($parts['query'])) {
            parse_str($parts['query'], $query);
        }

        $query['items'] = implode(',', $cartItemIds);

        return $path . '?' . http_build_query($query);
    }
}
