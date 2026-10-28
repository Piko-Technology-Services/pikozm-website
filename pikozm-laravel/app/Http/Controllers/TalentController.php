<?php

namespace App\Http\Controllers;

use App\Models\TalentApplication;
use App\Support\WhatsApp;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TalentController extends Controller
{
    public function create(): View
    {
        return view('pages.talent', ['skills' => TalentApplication::SKILLS]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'full_name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:40'],
            'email' => ['nullable', 'email', 'max:190'],
            'skill' => ['required', Rule::in(TalentApplication::SKILLS)],
            'message' => ['nullable', 'string', 'max:5000'],
        ]);

        $application = TalentApplication::create($data);

        return redirect()->away(WhatsApp::link(config('piko.whatsapp.talent'), $application->toWhatsAppText()));
    }
}
