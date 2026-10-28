<?php

namespace App\Http\Controllers;

use App\Models\Donation;
use App\Models\PartnershipRequest;
use App\Models\SupportRequest;
use App\Models\VolunteerApplication;
use Illuminate\View\View;

/**
 * Piko Digital Impact Programme (PDIP) landing page. The page's four forms
 * post to the controllers in App\Http\Controllers\DigitalImpact.
 */
class DigitalImpactController extends Controller
{
    public function __invoke(): View
    {
        return view('digital-impact.index', [
            'focusAreas' => Donation::FOCUS_AREAS,
            'currencies' => config('lenco.currencies.donations'),
            'availability' => VolunteerApplication::AVAILABILITY,
            'partnershipTypes' => PartnershipRequest::PARTNERSHIP_TYPES,
            'partnerOrgTypes' => PartnershipRequest::ORGANIZATION_TYPES,
            'supportOrgTypes' => SupportRequest::ORGANIZATION_TYPES,
        ]);
    }
}
