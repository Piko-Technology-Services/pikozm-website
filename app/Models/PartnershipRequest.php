<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartnershipRequest extends Model
{
    public const PARTNERSHIP_TYPES = [
        'CSR' => 'B2B Partnership',
        'Project' => 'Project-Based Funding',
        'Resource' => 'Infrastructure / Resource Support',
        'Other' => 'Other (General Partnership)',
    ];

    public const ORGANIZATION_TYPES = [
        'Company' => 'Company / Business',
        'NGO' => 'NGO / Non-Profit',
        'Government' => 'Government',
        'Individual' => 'Individual',
        'Other' => 'Other',
    ];

    protected $fillable = [
        'organization_name', 'contact_person', 'email', 'phone', 'country',
        'partnership_type', 'organization_type', 'message', 'status',
    ];
}
