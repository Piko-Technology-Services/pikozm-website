<?php

namespace Tests\Feature;

use App\Models\ContactMessage;
use App\Models\PromoPackage;
use App\Models\TalentApplication;
use Database\Seeders\PromoPackageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeadFormsTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_form_is_saved_and_handed_to_whatsapp(): void
    {
        $response = $this->post('/contact', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'message' => 'I need a website',
            'services' => ['Web Development'],
        ]);

        $response->assertRedirectContains('https://wa.me/'.config('piko.whatsapp.contact'));
        $this->assertDatabaseHas('contact_messages', ['email' => 'test@example.com']);
        $this->assertSame(['Web Development'], ContactMessage::first()->services);
    }

    public function test_contact_form_rejects_unknown_services(): void
    {
        $this->post('/contact', [
            'name' => 'Test', 'email' => 'test@example.com', 'message' => 'Hi', 'services' => ['Hacking'],
        ])->assertSessionHasErrors('services.0');
    }

    public function test_talent_registration(): void
    {
        $this->post('/talent', [
            'full_name' => 'Jo Banda', 'phone' => '0970000000', 'skill' => 'Graphic Design',
        ])->assertRedirectContains('https://wa.me/'.config('piko.whatsapp.talent'));

        $this->assertSame(1, TalentApplication::count());
    }

    public function test_promo_registration(): void
    {
        $this->seed(PromoPackageSeeder::class);
        $package = PromoPackage::where('slug', 'tikula-biine')->first();

        $this->post('/promo', [
            'promo_package_id' => $package->id, 'full_name' => 'Jo', 'phone' => '097',
        ])->assertRedirectContains(rawurlencode('Tikula Biiné'));

        $this->assertDatabaseHas('promo_registrations', ['promo_package_id' => $package->id]);
    }
}
