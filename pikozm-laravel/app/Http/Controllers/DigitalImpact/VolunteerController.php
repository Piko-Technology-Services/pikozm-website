<?php

namespace App\Http\Controllers\DigitalImpact;

use App\Http\Controllers\Controller;
use App\Models\VolunteerApplication;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * "Be Part of the Team" – previously POSTed to app.pikozm.com/api/digital-impact/volunteer.
 */
class VolunteerController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190'],
            'phone' => ['required', 'string', 'max:40'],
            'country' => ['required', 'string', 'max:80'],
            'skills' => ['required', 'string', 'max:255'],
            'availability' => ['required', Rule::in(array_keys(VolunteerApplication::AVAILABILITY))],
            'message' => ['nullable', 'string', 'max:5000'],
            'nrc' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'resume' => ['required', 'file', 'mimes:pdf,doc,docx', 'max:5120'],
        ], [], [
            'nrc' => 'passport / national ID',
            'resume' => 'resume / CV',
        ]);

        // Stored on the private "local" disk (storage/app/private), never publicly reachable.
        $data['id_document_path'] = $request->file('nrc')->store('volunteers/id-documents');
        $data['resume_path'] = $request->file('resume')->store('volunteers/resumes');
        unset($data['nrc'], $data['resume']);

        VolunteerApplication::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Thank you for volunteering! Our team will contact you shortly.',
        ], 201);
    }
}
