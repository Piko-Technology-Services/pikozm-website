<?php

namespace Tests\Feature;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public static function pages(): array
    {
        return [
            'home' => ['/', 'Let’s Build Smarter Businesses Together'],
            'digital impact' => ['/digital-impact', 'Piko Digital Impact Programme'],
            'talent' => ['/talent', 'Join Our Team'],
            'promo' => ['/promo', 'Tikula Biiné'],
            'payments' => ['/payments', 'ZP Software Subscription'],
            'pikopay' => ['/pikopay', 'PikoPay - Secure Payments'],
            'checkout' => ['/payments/checkout/logo-design', 'ZMW 500.00'],
        ];
    }

    #[DataProvider('pages')]
    public function test_public_pages_render(string $uri, string $expected): void
    {
        $this->get($uri)->assertOk()->assertSee($expected, false);
    }

    public function test_unknown_product_is_404(): void
    {
        $this->get('/payments/checkout/does-not-exist')->assertNotFound();
    }

    public static function legacyUrls(): array
    {
        return [
            ['/index.html', '/'],
            ['/digital-impact.html', '/digital-impact'],
            ['/talent.html', '/talent'],
            ['/register.html', '/promo'],
            ['/payments.html', '/pikopay'],
            ['/payments/index.php', '/payments'],
            ['/payments/checkout.php?product=logo-design', '/payments/checkout/logo-design'],
            ['/payments/payment-success.php?TransID=ABC', '/payments/success?TransID=ABC'],
            ['/payments/payment-cancel.php', '/payments/cancel'],
        ];
    }

    #[DataProvider('legacyUrls')]
    public function test_legacy_static_urls_redirect_permanently(string $old, string $new): void
    {
        $this->get($old)->assertStatus(301)->assertRedirect($new);
    }
}
