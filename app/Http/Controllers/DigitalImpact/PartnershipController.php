<?php

namespace App\Http\Controllers\DigitalImpact;

use App\Http\Controllers\Controller;
use App\Models\PartnershipRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * "Become a Partner" – previously POSTed to app.pikozm.com/api/digital-impact/partner.
 */
class PartnershipController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'organization_name' => ['required', 'string', 'max:190'],
            'contact_person' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190'],
            'phone' => ['required', 'string', 'max:40'],
            'country' => ['required', 'string', 'max:80'],
            'partnership_type' => ['required', Rule::in(array_keys(PartnershipRequest::PARTNERSHIP_TYPES))],
            'organization_type' => ['required', Rule::in(array_keys(PartnershipRequest::ORGANIZATION_TYPES))],
            'message' => ['nullable', 'string', 'max:5000'],
        ]);

        PartnershipRequest::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Your partnership request has been received.',
        ], 201);
    }
}
