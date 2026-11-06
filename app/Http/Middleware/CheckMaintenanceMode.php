<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * The switch in the site settings. Unlike "php artisan down", staff keep working, so they can
 * prepare the store and turn the switch off again from the same page.
 */
class CheckMaintenanceMode
{
    /** Staff must be able to sign in, and to change the language of the login page. */
    private const ALWAYS_OPEN = ['login', 'logout', 'lang/*', 'forgot-password', 'reset-password', 'reset-password/*', 'api/login'];

    public function handle(Request $request, Closure $next): Response
    {
        if (! Setting::get('maintenance_mode') || $request->is(self::ALWAYS_OPEN)) {
            return $next($request);
        }

        $user = $request->is('api/*') ? $request->user('sanctum') : $request->user();

        if ($user?->can('access-dashboard')) {
            return $next($request);
        }

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json(['message' => __('app.maintenance_title')], 503);
        }

        return response()->view('maintenance', [], 503)->header('Retry-After', '3600');
    }
}
