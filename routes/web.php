<?php

use App\Http\Controllers\ContactController;
use App\Http\Controllers\DigitalImpact\DonationController;
use App\Http\Controllers\DigitalImpact\PartnershipController;
use App\Http\Controllers\DigitalImpact\SupportRequestController;
use App\Http\Controllers\DigitalImpact\VolunteerController;
use App\Http\Controllers\DigitalImpactController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LegacyRedirectController;
use App\Http\Controllers\LencoWebhookController;
use App\Http\Controllers\Payments\CatalogController;
use App\Http\Controllers\Payments\CheckoutController;
use App\Http\Controllers\Payments\DpoCallbackController;
use App\Http\Controllers\PikoPayController;
use App\Http\Controllers\PromoController;
use App\Http\Controllers\TalentController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public pages
|--------------------------------------------------------------------------
*/

Route::get('/', HomeController::class)->name('home');
Route::post('/contact', [ContactController::class, 'store'])->middleware('throttle:forms')->name('contact.store');

Route::get('/talent', [TalentController::class, 'create'])->name('talent.create');
Route::post('/talent', [TalentController::class, 'store'])->middleware('throttle:forms')->name('talent.store');

Route::get('/promo', [PromoController::class, 'index'])->name('promo.index');
Route::post('/promo', [PromoController::class, 'store'])->middleware('throttle:forms')->name('promo.store');

/*
|--------------------------------------------------------------------------
| Piko Digital Impact Programme (PDIP)
|--------------------------------------------------------------------------
| These JSON endpoints replace app.pikozm.com/api/digital-impact/* and
| app.pikozm.com/api/support/*.
*/

Route::get('/digital-impact', DigitalImpactController::class)->name('digital-impact');

Route::prefix('digital-impact')->name('digital-impact.')->middleware('throttle:forms')->group(function () {
    Route::post('/volunteers', [VolunteerController::class, 'store'])->name('volunteers.store');
    Route::post('/partners', [PartnershipController::class, 'store'])->name('partners.store');
    Route::post('/support-requests', [SupportRequestController::class, 'store'])->name('support-requests.store');
    Route::post('/donations', [DonationController::class, 'initiate'])->name('donations.initiate');
    Route::post('/donations/verify', [DonationController::class, 'verify'])->name('donations.verify');
});

/*
|--------------------------------------------------------------------------
| Payments – DPO Pay catalog checkout
|--------------------------------------------------------------------------
*/

Route::prefix('payments')->name('payments.')->group(function () {
    Route::get('/', CatalogController::class)->name('index');
    Route::get('/checkout/{product}', [CheckoutController::class, 'product'])->name('checkout.product');
    Route::get('/checkout', [CheckoutController::class, 'restart'])->name('checkout.restart');
    Route::post('/checkout', [CheckoutController::class, 'custom'])->name('checkout.custom');
    Route::post('/checkout/process', [CheckoutController::class, 'process'])->middleware('throttle:forms')->name('process');
    Route::get('/success', [DpoCallbackController::class, 'success'])->name('success');
    Route::get('/cancel', [DpoCallbackController::class, 'cancel'])->name('cancel');
});

/*
|--------------------------------------------------------------------------
| PikoPay – Lenco free-form payments
|--------------------------------------------------------------------------
*/

Route::get('/pikopay', [PikoPayController::class, 'index'])->name('pikopay.index');
Route::post('/pikopay/initiate', [PikoPayController::class, 'initiate'])->middleware('throttle:forms')->name('pikopay.initiate');
Route::post('/pikopay/verify', [PikoPayController::class, 'verify'])->middleware('throttle:forms')->name('pikopay.verify');

Route::post('/webhooks/lenco', LencoWebhookController::class)->name('webhooks.lenco');

/*
|--------------------------------------------------------------------------
| Legacy URLs from the static site – keep old links, bookmarks, social
| shares and the DPO-registered callback URLs working.
|--------------------------------------------------------------------------
*/

$legacyUrls = [
    'index.html' => 'home',
    'digital-impact.html' => 'digital-impact',
    'talent.html' => 'talent.create',
    'register.html' => 'promo.index',
    'register' => 'promo.index',
    'payments.html' => 'pikopay.index',
    'payments/index.php' => 'payments.index',
    'payments/checkout.php' => 'payments.checkout.product',
    // DPO may still have these registered as RedirectURL / BackURL.
    'payments/payment-success.php' => 'payments.success',
    'payments/payment-cancel.php' => 'payments.cancel',
];

foreach ($legacyUrls as $uri => $target) {
    Route::get($uri, LegacyRedirectController::class)->defaults('to', $target);
}
