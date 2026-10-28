<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A "Business Booster" promo package shown on /promo.
 */
class PromoPackage extends Model
{
    protected $fillable = ['slug', 'name', 'price', 'tagline', 'features', 'is_featured', 'is_active', 'sort_order'];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'features' => 'array',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(PromoRegistration::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    /** "K1,499.00" */
    public function formattedPrice(): string
    {
        return 'K'.number_format((float) $this->price, 2);
    }

    public function label(): string
    {
        return "{$this->name} – {$this->formattedPrice()}";
    }
}
