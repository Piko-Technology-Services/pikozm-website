<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use App\Support\WhatsApp;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Home page "Let's Connect" form: store the enquiry, then hand off to WhatsApp
 * exactly like the old client-side script did.
 */
class ContactController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190'],
            'message' => ['required', 'string', 'max:5000'],
            'services' => ['nullable', 'array'],
            'services.*' => [Rule::in(ContactMessage::SERVICES)],
        ]);

        $contact = ContactMessage::create($data);

        return redirect()->away(WhatsApp::link(config('piko.whatsapp.contact'), $contact->toWhatsAppText()));
    }
}
