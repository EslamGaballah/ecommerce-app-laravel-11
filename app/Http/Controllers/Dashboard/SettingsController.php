<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SettingsController extends Controller
{
    /** Unchecked boxes are not sent by the browser, so these are read one by one. */
    private const SWITCHES = [
        'payment_cash_enabled',
        'payment_card_enabled',
        'show_stock',
        'registration_enabled',
        'product_ratings_visible',
        'product_reviews_enabled',
        'blog_comments_enabled',
        'maintenance_mode',
        'show_social_header',
        'show_social_footer',
        'dark_mode_enabled',
    ];

    public function edit()
    {
        return view('dashboard.settings.edit', ['settings' => Setting::allValues()]);
    }

    public function update(Request $request)
    {
        $values = $request->validate([
            'site_name' => ['required', 'string', 'max:100'],
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
            'phone' => ['nullable', 'string', 'max:30'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'working_hours' => ['nullable', 'string', 'max:255'],
            'whatsapp' => ['nullable', 'regex:/^[0-9]{8,15}$/'],
            'tax' => ['required', 'numeric', 'min:0', 'max:999999'],
            'site_description' => ['nullable', 'string', 'max:300'],
            'announcement' => ['nullable', 'string', 'max:150'],
            'currency' => ['required', Rule::in(Setting::CURRENCIES)],
            'low_stock_threshold' => ['required', 'integer', 'min:0', 'max:100000'],
            'products_per_page' => ['required', 'integer', 'min:3', 'max:60'],
            'min_order_amount' => ['nullable', 'numeric', 'min:0', 'max:9999999'],
            'free_shipping_min' => ['nullable', 'numeric', 'min:0', 'max:9999999'],
            'cod_max_items' => ['nullable', 'integer', 'min:1', 'max:100000'],
            'cod_max_amount' => ['nullable', 'numeric', 'gt:0', 'max:9999999'],
            'maintenance_message' => ['nullable', 'string', 'max:500'],
            'default_theme' => ['required', Rule::in(['light', 'dark', 'system'])],
            'stagnant_days' => ['required', 'integer', 'min:1', 'max:3650'],
            ...collect(array_keys(Setting::SOCIAL_NETWORKS))
                ->mapWithKeys(fn ($network) => [$network => ['nullable', 'url:http,https', 'max:255']])
                ->all(),
        ]);

        foreach (self::SWITCHES as $switch) {
            $values[$switch] = $request->boolean($switch) ? '1' : '0';
        }

        // Test card payments don't count in production, because they are never offered there.
        $canUseCard = $values['payment_card_enabled'] === '1' && ! app()->isProduction();

        if ($values['payment_cash_enabled'] === '0' && ! $canUseCard) {
            throw ValidationException::withMessages(['payment_cash_enabled' => __('app.payment_method_required')]);
        }

        if ($request->hasFile('logo')) {
            Storage::disk('public')->delete(Setting::get('logo') ?? '');
            $values['logo'] = $request->file('logo')->store('settings', 'public');
        } else {
            unset($values['logo']);
        }

        Setting::saveValues($values);

        return back()->with('success', __('app.settings_saved'));
    }
}
