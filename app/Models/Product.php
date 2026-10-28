<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * An item on the /payments catalog: either a subscription or a one-off service.
 */
class Product extends Model
{
    public const CATEGORY_SUBSCRIPTION = 'subscription';
    public const CATEGORY_ONE_TIME = 'one-time';

    public const PRICING_FIXED = 'fixed';
    public const PRICING_CUSTOM = 'custom';

    protected $fillable = [
        'slug', 'name', 'description', 'note', 'price', 'service_type',
        'pricing_type', 'category', 'billing_cycle', 'is_active', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order')->orderBy('id');
    }

    public function scopeSubscriptions(Builder $query): Builder
    {
        return $query->where('category', self::CATEGORY_SUBSCRIPTION);
    }

    public function scopeOneTime(Builder $query): Builder
    {
        return $query->where('category', self::CATEGORY_ONE_TIME);
    }

    public function isCustomPriced(): bool
    {
        return $this->pricing_type === self::PRICING_CUSTOM;
    }

    public function isSubscription(): bool
    {
        return $this->category === self::CATEGORY_SUBSCRIPTION;
    }

    /** "/ year", "/ month" or "" – mirrors cycle_label() from the old dashboard. */
    public function cycleLabel(): string
    {
        return $this->billing_cycle ? '/ '.$this->billing_cycle : '';
    }
}
