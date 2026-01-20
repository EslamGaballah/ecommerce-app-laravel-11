<?php

namespace App\Http\Middleware;


use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class SetAppLang
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        // $avilable_Locales = config('app.avilable_locales');
        $available_Locales =array_keys(config('app.available_locales'));

        $locale = config('app.locale');

        // set lang for Api
        if ($request->hasHeader("Accept-Language")) 
            {
                $headerlang = ($request->header("Accept-Language"));

                if (in_array($headerlang, $available_Locales)) 
                    {
                        App::setLocale($headerlang);
                    }
            }

        //  set lang for web
        if ($request->hasSession() && session()->has('lang')) 
            {
                $sessionLang = session('lang');
                if (in_array($sessionLang, $available_Locales))
                    {
                        App::setLocale( $sessionLang);
                    }

            }
 
        return $next($request);
    }
}
