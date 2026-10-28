<?php

namespace Tests\Feature;

use App\Models\Order;
use Database\Seeders\ProductSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class DpoCheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ProductSeeder::class);
    }

    private function fakeDpo(string $createResult = '000', string $verifyResult = '000'): void
    {
        Http::fake(function (Request $request) use ($createResult, $verifyResult) {
            if (str_contains($request->body(), 'createToken')) {
                return Http::response("<?xml version=\"1.0\"?><API3G><Result>{$createResult}</Result><ResultExplanation>Created</ResultExplanation><TransToken>TOKEN-123</TransToken><TransRef>R1</TransRef></API3G>");
            }

            return Http::response("<?xml version=\"1.0\"?><API3G><Result>{$verifyResult}</Result><ResultExplanation>Transaction Paid</ResultExplanation></API3G>");
        });
    }

    private function customer(): array
    {
        return ['name' => 'Bupe', 'email' => 'bupe@example.com', 'phone' => '0962000000'];
    }

    public function test_catalog_checkout_creates_order_and_redirects_to_dpo(): void
    {
        $this->fakeDpo();

        $this->post('/payments/checkout/process', ['mode' => 'catalog', 'product' => 'logo-design'] + $this->customer())
            ->assertRedirect(config('dpo.payment_url').'TOKEN-123');

        $order = Order::sole();
        $this->assertSame(Order::STATUS_TOKEN_CREATED, $order->status);
        $this->assertSame('500.00', $order->amount);
        $this->assertStringStartsWith('PIKO-', $order->company_ref);
    }

    public function test_fixed_price_cannot_be_overridden_by_posted_amount(): void
    {
        $this->fakeDpo();

        $this->post('/payments/checkout/process', ['mode' => 'catalog', 'product' => 'logo-design', 'amount' => 1] + $this->customer());

        $this->assertSame('500.00', Order::sole()->amount);
        Http::assertSent(fn (Request $r) => str_contains($r->body(), '<PaymentAmount>500.00</PaymentAmount>'));
    }

    public function test_custom_payment_escapes_xml(): void
    {
        $this->fakeDpo();

        $this->post('/payments/checkout/process', [
            'mode' => 'custom', 'item_name' => 'Invoice <42> & co', 'amount' => '1250',
        ] + $this->customer())->assertRedirect();

        Http::assertSent(fn (Request $r) => str_contains($r->body(), 'Invoice &lt;42&gt; &amp; co'));
        $this->assertSame(config('dpo.default_service_type'), Order::sole()->service_type);
    }

    public function test_dpo_error_marks_order_failed(): void
    {
        $this->fakeDpo(createResult: '801');

        $this->post('/payments/checkout/process', ['mode' => 'catalog', 'product' => 'logo-design'] + $this->customer())
            ->assertStatus(502)
            ->assertSee('Payment could not be started');

        $this->assertSame(Order::STATUS_FAILED, Order::sole()->status);
    }

    public function test_success_callback_verifies_and_marks_paid(): void
    {
        $this->fakeDpo();
        $this->post('/payments/checkout/process', ['mode' => 'catalog', 'product' => 'logo-design'] + $this->customer());

        $this->get('/payments/success?TransactionToken=TOKEN-123')
            ->assertOk()
            ->assertSee('Payment successful');

        $this->assertSame(Order::STATUS_PAID, Order::sole()->status);
    }

    public function test_unpaid_callback_marks_unpaid(): void
    {
        $this->fakeDpo(verifyResult: '901');
        $this->post('/payments/checkout/process', ['mode' => 'catalog', 'product' => 'logo-design'] + $this->customer());

        $this->get('/payments/success?TransID=TOKEN-123')->assertSee('Payment not completed');

        $this->assertSame(Order::STATUS_UNPAID, Order::sole()->status);
    }

    public function test_cancel_marks_order_cancelled(): void
    {
        $this->fakeDpo();
        $this->post('/payments/checkout/process', ['mode' => 'catalog', 'product' => 'logo-design'] + $this->customer());

        $this->get('/payments/cancel?TransToken=TOKEN-123')->assertOk()->assertSee('Payment cancelled');

        $this->assertSame(Order::STATUS_CANCELLED, Order::sole()->status);
    }
}
