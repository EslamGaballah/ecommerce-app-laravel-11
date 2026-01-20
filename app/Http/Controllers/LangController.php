<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class LangController extends Controller
{
    public function change($lang) 
    {
        // if (in_array($lang, config('app.avilable_locales'))) 
                    if (array_key_exists($lang, config('app.available_locales'))) {

            // {
                session()->put('lang', $lang);
            }

        return back();
    }
}
