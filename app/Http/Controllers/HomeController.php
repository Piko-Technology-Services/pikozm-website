<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        return view('pages.home', [
            'services' => config('piko.services'),
            'partnerLogos' => config('piko.partner_logos'),
            'contactServices' => ContactMessage::SERVICES,
        ]);
    }
}
