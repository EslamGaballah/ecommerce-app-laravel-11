<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Cart;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $cookieId = $request->cookie('cart_id');

        $request->authenticate();
        $request->session()->regenerate();

        if($cookieId) {
            Cart::where('cookie_id', $cookieId)
                ->update([
                    'user_id' => auth()->id()
                ]);
        }
        
        // return redirect()->intended(route('dashboard', absolute: false));
        if ($request->user()->can('access-dashboard')) {
            return redirect()->intended(route('dashboard.home'));
        }

        // intended back to last bage after login else ->(route('home'));
        return redirect()->intended(route('home'));

    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
