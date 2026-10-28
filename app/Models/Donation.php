<?php

namespace App\Models;

use App\Models\Concerns\LencoPayable;
use Illuminate\Database\Eloquent\Model;

/**
 * A Digital Impact Programme contribution, collected through Lenco.
 */
class Donation extends Model
{
    use LencoPayable;

    public const REFERENCE_PREFIX = 'PDIP';

    public const FOCUS_AREAS = [
        'general' => 'General Digital Impact Fund',
        'ngo-digital-presence' => 'NGO Digital Presence',
        'donor-systems' => 'Donor & Beneficiary Management Systems',
        'graphic-design' => 'Graphic Design & Branding',
        'training-support' => 'Training & Capacity Building',
    ];

    protected $fillable = ['reference', 'name', 'email', 'focus_area', 'amount', 'currency', 'message', 'status'];

    public function focusAreaLabel(): string
    {
        return self::FOCUS_AREAS[$this->focus_area] ?? $this->focus_area;
    }
}
