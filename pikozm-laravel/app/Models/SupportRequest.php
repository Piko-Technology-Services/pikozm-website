<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * "Apply for Digital Support" requests from NGOs and social projects.
 */
class SupportRequest extends Model
{
    public const ORGANIZATION_TYPES = [
        'NGO' => 'NGO / Non-Profit',
        'Community' => 'Community Project',
        'Faith' => 'Faith-based Organization',
        'Government' => 'Government Initiative',
        'Other' => 'Other',
    ];

    protected $fillable = [
        'organization_name', 'contact_person', 'email', 'phone', 'country',
        'organization_type', 'support_needs', 'website', 'message', 'status',
    ];
}
