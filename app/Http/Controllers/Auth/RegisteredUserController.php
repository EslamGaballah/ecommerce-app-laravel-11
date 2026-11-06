<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Interfaces\CartRepositoryInterface;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
        public function create(): View
    {
        abort_unless(Setting::get('registration_enabled'), 404);


        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
        public function store(Request $request): RedirectResponse
    {
        abort_unless(Setting::get('registration_enabled'), 404);


        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = DB::transaction(function () use ($request) {

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $userRole = Role::firstOrCreate(['name' => 'user']);

        $user->roles()->attach($userRole->id);
        return $user;
    });
        
        event(new Registered($user));

        Auth::login($user);

        if ($cookieId = $request->cookie('cart_id')) {
            app(CartRepositoryInterface::class)->mergeGuestCart($cookieId, $user->id);
        }

        return redirect(route('home', absolute: false));
    }
}
