<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bundle extends Model
{
    protected $fillable = ['name_ar', 'name_en', 'slug', 'description', 'is_active', 'distinct_products', 'starts_at', 'ends_at'];

    protected $casts = [
        'is_active' => 'boolean',
        'distinct_products' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function getNameAttribute(): string
    {
        return app()->getLocale() === 'ar' ? $this->name_ar : $this->name_en;
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class);
    }

    public function tiers(): HasMany
    {
        return $this->hasMany(BundleTier::class)->orderBy('min_quantity');
    }

    /** Switched on, and today falls inside its dates if it has any. */
    public function scopeRunning(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where(fn ($query) => $query->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn ($query) => $query->whereNull('ends_at')->orWhere('ends_at', '>=', now()));
    }

    /** The biggest tier this quantity reaches. */
    public function tierFor(int $quantity): ?BundleTier
    {
        return $this->tiers->filter(fn ($tier) => $tier->min_quantity <= $quantity)->last();
    }

    /** The next tier above this quantity. */
    public function nextTierAfter(int $quantity): ?BundleTier
    {
        return $this->tiers->first(fn ($tier) => $tier->min_quantity > $quantity);
    }
}
