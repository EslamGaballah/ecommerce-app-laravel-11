<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PageController extends Controller
{
    /** The number of days customers have to request a return, shown in the return policy. */
    private const RETURN_DAYS = 14;

    /** The date the policies were last changed. Update it when you edit lang/{en,ar}/pages.php. */
    private const POLICIES_UPDATED_AT = '2026-11-06';

    private const POLICIES = ['privacy', 'terms', 'shipping', 'returns'];

    /**
     * The page name comes from the route definition, never from the visitor.
     */
    public function show(Request $request): View
    {
        $name = $request->route()->defaults['page'];

        return view($name === 'faq' ? 'front.pages.faq' : 'front.pages.show', [
            'page' => __("pages.{$name}", [
                'site' => Setting::get('site_name'),
                'days' => self::RETURN_DAYS,
            ]),
            'updatedAt' => in_array($name, self::POLICIES, true) ? self::POLICIES_UPDATED_AT : null,
        ]);
    }
}
