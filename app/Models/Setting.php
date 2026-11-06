<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Once;

class Setting extends Model
{
    protected $primaryKey = 'key';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = ['key', 'value'];

    private const CACHE_KEY = 'settings';

        /** Each social network with its LineIcons name. WhatsApp has its own field, because it holds a number. */
    public const SOCIAL_NETWORKS = [
        'facebook' => 'facebook-filled',
        'instagram' => 'instagram',
        'twitter' => 'twitter-original',
        'youtube' => 'youtube',
        'tiktok' => 'tiktok',
        'linkedin' => 'linkedin-original',
        'snapchat' => 'snapchat',
        'telegram' => 'telegram',
        'pinterest' => 'pinterest',
    ];

    public const CURRENCIES = ['EGP', 'USD', 'EUR', 'GBP', 'SAR', 'AED', 'KWD', 'QAR'];

    public static function get(string $key): mixed
    {
        return static::allValues()[$key] ?? null;
    }

    /**
     * Saved values over the defaults. A field saved empty stays empty, so its section can be hidden.
     */
    public static function allValues(): array
    {
        // Prices read the currency many times per page, so each request reads the settings once.
        return once(function () {
            $saved = Cache::rememberForever(self::CACHE_KEY, fn () => static::pluck('value', 'key')->all());

            return array_merge(static::defaults(), $saved);
        });
    }

        public static function hasSocialLinks(): bool
    {
        return collect(array_keys(self::SOCIAL_NETWORKS))->push('whatsapp')->contains(fn ($network) => static::get($network));
    }

    public static function saveValues(array $values): void
    {
        foreach ($values as $key => $value) {
            static::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        Cache::forget(self::CACHE_KEY);
        Once::flush();
    }

    private static function defaults(): array
    {
        return [
            'site_name' => config('app.name'),
            'logo' => null,
            'site_description' => null,
            'announcement' => null,
            'currency' => config('app.currency', 'USD'),
            'show_stock' => '1',
            'low_stock_threshold' => '5',
            'products_per_page' => '12',
            'registration_enabled' => '1',
            'product_ratings_visible' => '1',
            'product_reviews_enabled' => '1',
            'blog_comments_enabled' => '1',
            'min_order_amount' => null,
            'free_shipping_min' => null,
            'cod_max_items' => null,
            'cod_max_amount' => null,
            'maintenance_mode' => '0',
            'maintenance_message' => null,
            'phone' => '(+20) 1091070473',
            'whatsapp' => '201091070473',
            'contact_email' => null,
            'address' => null,
            'working_hours' => null,
            'facebook' => null,
            'twitter' => null,
                        'instagram' => null,
            'youtube' => null,
            'tiktok' => null,
            'linkedin' => null,
            'snapchat' => null,
            'telegram' => null,
            'pinterest' => null,
            'show_social_header' => '1',
            'show_social_footer' => '1',
            'dark_mode_enabled' => '1',
            'default_theme' => 'light',
            'stagnant_days' => '30',
            'tax' => config('shop.flat_tax'),
            'payment_cash_enabled' => '1',
            'payment_card_enabled' => '1',
        ];
    }
}
