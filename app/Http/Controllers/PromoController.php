<?php

namespace App\Http\Controllers;

use App\Models\PromoPackage;
use App\Models\PromoRegistration;
use App\Support\WhatsApp;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Business Booster promo packages (formerly register.html).
 */
class PromoController extends Controller
{
    public function index(): View
    {
        return view('pages.promo', ['packages' => PromoPackage::active()->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'promo_package_id' => ['required', Rule::exists('promo_packages', 'id')->where('is_active', true)],
            'full_name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:40'],
            'email' => ['nullable', 'email', 'max:190'],
            'business_name' => ['nullable', 'string', 'max:190'],
            'message' => ['nullable', 'string', 'max:5000'],
        ]);

        $registration = PromoRegistration::create($data)->load('package');

        return redirect()->away(WhatsApp::link(config('piko.whatsapp.promo'), $registration->toWhatsAppText()));
    }
}
