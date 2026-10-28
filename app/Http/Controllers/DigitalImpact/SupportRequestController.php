<?php

namespace App\Http\Controllers\DigitalImpact;

use App\Http\Controllers\Controller;
use App\Models\SupportRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * "Apply for Digital Support" – previously POSTed to
 * app.pikozm.com/api/digital-impact/support-request.
 */
class SupportRequestController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'organization_name' => ['required', 'string', 'max:190'],
            'contact_person' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190'],
            'phone' => ['required', 'string', 'max:40'],
            'country' => ['required', 'string', 'max:80'],
            'organization_type' => ['required', Rule::in(array_keys(SupportRequest::ORGANIZATION_TYPES))],
            'support_needs' => ['required', 'string', 'max:5000'],
            'website' => ['nullable', 'url', 'max:255'],
            'message' => ['nullable', 'string', 'max:5000'],
        ]);

        SupportRequest::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Your support request has been sent.',
        ], 201);
    }
}
