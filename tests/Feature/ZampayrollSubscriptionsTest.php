<?php

namespace Tests\Feature;

use App\Mail\ZampayrollPaymentUpdate;
use App\Models\ZampayrollSubscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ZampayrollSubscriptionsTest extends TestCase
{
    use RefreshDatabase;

    private array $verifyCodes = [];

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
    }

    /** Fake DPO. verifyToken answers with the queued codes, one per call. */
    private function fakeDpo(array $verifyCodes = ['000'], string $chargeCode = '130', array $networks = ['Airtel', 'MTN', 'Zamtel']): void
    {
        $this->verifyCodes = $verifyCodes;
        $tokenNo = 0;

        Http::fake(function (Request $request) use (&$tokenNo, $chargeCode, $networks) {
            $body = $request->body();

            if (str_contains($body, '<Request>createToken')) {
                $tokenNo++;

                return Http::response("<?xml version=\"1.0\"?><API3G><Result>000</Result><TransToken>TOKEN-{$tokenNo}</TransToken><TransRef>R{$tokenNo}</TransRef></API3G>");
            }
            if (str_contains($body, 'GetMobilePaymentOptions')) {
                $opts = collect($networks)->map(fn ($n) => "<mobileoption><country>zambia</country><paymentname>{$n}</paymentname><celluarprefix>260</celluarprefix></mobileoption>")->implode('');

                return Http::response("<?xml version=\"1.0\"?><API3G><Code>000</Code><paymentoptions>{$opts}</paymentoptions></API3G>");
            }
            if (str_contains($body, 'ChargeTokenMobile')) {
                return Http::response("<?xml version=\"1.0\"?><API3G><StatusCode>{$chargeCode}</StatusCode><ResultExplanation>New invoice</ResultExplanation><instructions>Dial *115# if no prompt</instructions></API3G>");
            }
            $code = array_shift($this->verifyCodes) ?? '900';

            return Http::response("<?xml version=\"1.0\"?><API3G><Result>{$code}</Result><ResultExplanation>Result {$code}</ResultExplanation></API3G>");
        });
    }

    private function customer(array $overrides = []): array
    {
        return array_merge([
            'company_name' => 'Kafue Agro Ltd',
            'contact_name' => 'Bupe Katongo',
            'email' => 'hr@kafueagro.co.zm',
            'phone' => '0962000000',
            'employees' => 30,
            'billing' => 'monthly',
        ], $overrides);
    }

    public function test_payments_page_shows_steps_and_plans_only(): void
    {
        $this->get('/zampayroll-subscriptions')
            ->assertOk()
            ->assertSee('Pay for your ZamPayroll subscription')
            ->assertSeeInOrder(['Choose your plan', 'Enter your details', 'Choose how to pay', 'Get confirmed'])
            ->assertSee('K799')->assertSee('K7,990')
            ->assertSee('K1,499')->assertSee('K2,499')
            ->assertSee('Start free')->assertSee('Talk to our team')
            ->assertDontSee('One powerful platform');
    }

    public function test_checkout_only_for_payable_plans(): void
    {
        $this->get('/zampayroll-subscriptions/checkout/tikula?billing=annual')->assertOk()->assertSee('K7,990.00');
        $this->get('/zampayroll-subscriptions/checkout/free')->assertNotFound();
        $this->get('/zampayroll-subscriptions/checkout/enterprise')->assertNotFound();
    }

    public function test_card_payment_redirects_to_dpo_with_web_hosting_service_type(): void
    {
        $this->fakeDpo();

        $this->post('/zampayroll-subscriptions/checkout/tikula', $this->customer(['payment_method' => 'card']))
            ->assertRedirect(config('dpo.payment_url').'TOKEN-1');

        Http::assertSent(fn (Request $r) => str_contains($r->body(), '<ServiceType>117535</ServiceType>')
            && str_contains($r->body(), '<PaymentAmount>799.00</PaymentAmount>')
            && str_contains($r->body(), route('zampayroll.payment.complete'))
            && str_contains($r->body(), '<customerEmail>hr@kafueagro.co.zm</customerEmail>')
            && str_contains($r->body(), '<customerCountry>ZM</customerCountry>')
            && str_contains($r->body(), '<customerPhone>962000000</customerPhone>')
            && str_contains($r->body(), '<customerDialCode>ZM</customerDialCode>'));

        $this->assertSame(ZampayrollSubscription::STATUS_AWAITING, ZampayrollSubscription::sole()->status);
    }

    public function test_card_return_marks_paid_and_emails_admin(): void
    {
        $this->fakeDpo(['000']);
        $this->post('/zampayroll-subscriptions/checkout/medium-enterprise', $this->customer(['payment_method' => 'card', 'employees' => 90, 'billing' => 'annual']));

        $this->get('/zampayroll-subscriptions/payment/complete?TransactionToken=TOKEN-1')
            ->assertOk()->assertSee('Payment received');

        $s = ZampayrollSubscription::sole();
        $this->assertTrue($s->isPaid());
        $this->assertSame('14990.00', $s->amount);
        Mail::assertSent(ZampayrollPaymentUpdate::class, fn ($m) => $m->hasTo(config('zampayroll.admin_email')) && $m->event === 'paid');
    }

    public function test_card_cancel_emails_admin(): void
    {
        $this->fakeDpo();
        $this->post('/zampayroll-subscriptions/checkout/tikula', $this->customer(['payment_method' => 'card']));

        $this->get('/zampayroll-subscriptions/payment/cancelled?TransToken=TOKEN-1')->assertOk()->assertSee('Payment cancelled');

        $this->assertSame(ZampayrollSubscription::STATUS_CANCELLED, ZampayrollSubscription::sole()->status);
        Mail::assertSent(ZampayrollPaymentUpdate::class, fn ($m) => $m->event === 'cancelled');
    }

    public function test_headcount_above_plan_band_is_rejected_with_a_suggestion(): void
    {
        $this->post('/zampayroll-subscriptions/checkout/tikula', $this->customer(['payment_method' => 'card', 'employees' => 120]))
            ->assertSessionHasErrors(['employees' => 'Tikula covers up to 50 active employees. For 120 employees, choose Medium Enterprise.']);
    }

    public function test_manual_payment_goes_to_zampayroll_login_and_notifies_admin(): void
    {
        $this->post('/zampayroll-subscriptions/checkout/corporate', $this->customer(['payment_method' => 'manual', 'employees' => 200]))
            ->assertRedirect('https://zampayroll.com/login');

        $this->assertSame(ZampayrollSubscription::STATUS_MANUAL, ZampayrollSubscription::sole()->status);
        Mail::assertSent(ZampayrollPaymentUpdate::class, fn ($m) => $m->event === 'manual');
    }

    public function test_mobile_money_prompt_then_paid_on_poll(): void
    {
        $this->fakeDpo(['900', '000']);

        $start = $this->postJson('/zampayroll-subscriptions/mobile-money', $this->customer([
            'plan' => 'tikula', 'network' => 'mtn', 'mobile_number' => '096 123 4567',
        ]))->assertOk()->assertJsonPath('state', 'awaiting')->assertJsonPath('instructions', 'Dial *115# if no prompt');

        Http::assertSent(fn (Request $r) => str_contains($r->body(), 'ChargeTokenMobile')
            && str_contains($r->body(), '<PhoneNumber>260961234567</PhoneNumber>')
            && str_contains($r->body(), '<MNO>MTN</MNO>')
            && str_contains($r->body(), '<MNOcountry>zambia</MNOcountry>'));

        $statusUrl = $start->json('status_url');
        $this->getJson($statusUrl)->assertJsonPath('state', 'awaiting');
        $this->getJson($statusUrl)->assertJsonPath('state', 'paid')->assertJsonPath('receipt.amount', 'K799.00');
        $this->getJson($statusUrl)->assertJsonPath('state', 'paid');

        Mail::assertSent(ZampayrollPaymentUpdate::class, 1);
    }

    public function test_mobile_money_decline_then_retry_uses_a_new_token(): void
    {
        $this->fakeDpo(['901', '000']);

        $ref = $this->postJson('/zampayroll-subscriptions/mobile-money', $this->customer([
            'plan' => 'tikula', 'network' => 'airtel', 'mobile_number' => '0971234567',
        ]))->json('reference');

        $this->getJson("/zampayroll-subscriptions/mobile-money/{$ref}")->assertJsonPath('state', 'failed');
        Mail::assertSent(ZampayrollPaymentUpdate::class, fn ($m) => $m->event === 'failed');

        $this->postJson('/zampayroll-subscriptions/mobile-money', $this->customer([
            'plan' => 'tikula', 'network' => 'airtel', 'mobile_number' => '0971234567', 'reference' => $ref,
        ]))->assertJsonPath('attempt', 2)->assertJsonPath('reference', $ref);

        $s = ZampayrollSubscription::sole();
        $this->assertSame('TOKEN-2', $s->dpo_token);

        $this->getJson("/zampayroll-subscriptions/mobile-money/{$ref}")->assertJsonPath('state', 'paid');
        Mail::assertSent(ZampayrollPaymentUpdate::class, fn ($m) => $m->event === 'paid');
    }

    public function test_unavailable_network_fails_gracefully(): void
    {
        $this->fakeDpo(networks: ['Airtel', 'MTN']);

        $this->postJson('/zampayroll-subscriptions/mobile-money', $this->customer([
            'plan' => 'tikula', 'network' => 'zamtel', 'mobile_number' => '0951234567',
        ]))->assertStatus(422)->assertJsonPath('state', 'failed')
            ->assertJsonPath('message', 'Zamtel Kwacha isn’t available for this payment right now. Choose another network or pay by card.');
    }

    public function test_invalid_mobile_number_is_rejected(): void
    {
        $this->postJson('/zampayroll-subscriptions/mobile-money', $this->customer([
            'plan' => 'tikula', 'network' => 'mtn', 'mobile_number' => '12345',
        ]))->assertStatus(422)->assertJsonValidationErrors('mobile_number');
    }

    public function test_status_of_someone_elses_payment_is_hidden(): void
    {
        $s = ZampayrollSubscription::create([
            'reference' => 'ZPR-OTHER', 'plan' => 'tikula', 'plan_name' => 'Tikula', 'billing_cycle' => 'monthly',
            'amount' => 799, 'company_name' => 'X', 'contact_name' => 'Y', 'email' => 'x@example.com', 'phone' => '1',
            'payment_method' => 'mobile_money', 'status' => 'awaiting_payment',
        ]);

        $this->getJson("/zampayroll-subscriptions/mobile-money/{$s->reference}")->assertNotFound();
    }

    public function test_dpo_firewall_block_is_explained_to_admin_not_customer(): void
    {
        Http::fake(['*' => Http::response('<html><body>403 Forbidden</body></html>', 403, ['Server' => 'cloudflare'])]);

        $this->post('/zampayroll-subscriptions/checkout/tikula', $this->customer(['payment_method' => 'card']))
            ->assertStatus(502)
            ->assertSee('Please try again in a few minutes')
            ->assertDontSee('artisan');

        $this->assertStringContainsString('HTTP 403', ZampayrollSubscription::sole()->last_message);
        Mail::assertSent(ZampayrollPaymentUpdate::class, fn ($m) => $m->event === 'failed');
    }

    public function test_dpo_requests_use_the_app_user_agent(): void
    {
        $this->fakeDpo();

        $this->post('/zampayroll-subscriptions/checkout/tikula', $this->customer(['payment_method' => 'card']));

        Http::assertSent(fn (Request $r) => $r->header('User-Agent')[0] === 'PikoPay/1.0 (+https://pikozm.com)');
    }

    public function test_callback_base_url_overrides_local_return_urls(): void
    {
        config(['dpo.callback_base_url' => 'https://pikozm.com']);
        $this->fakeDpo();

        $this->post('/zampayroll-subscriptions/checkout/tikula', $this->customer(['payment_method' => 'card', 'company_name' => 'Kafue “Agro” Ltd – Ndola']));

        Http::assertSent(fn (Request $r) => str_contains($r->body(), '<RedirectURL>https://pikozm.com/zampayroll-subscriptions/payment/complete</RedirectURL>')
            && str_contains($r->body(), '<BackURL>https://pikozm.com/zampayroll-subscriptions/payment/cancelled</BackURL>')
            && str_contains($r->body(), '(Monthly) - Kafue Agro Ltd Ndola</ServiceDescription>'));
    }

    public function test_unrecognised_phone_is_not_sent_to_dpo(): void
    {
        $this->fakeDpo();

        $this->post('/zampayroll-subscriptions/checkout/tikula', $this->customer(['payment_method' => 'card', 'phone' => '+44 20 7946 0000']));

        Http::assertSent(fn (Request $r) => str_contains($r->body(), '<customerCountry>ZM</customerCountry>')
            && ! str_contains($r->body(), '<customerPhone>')
            && ! str_contains($r->body(), '<customerDialCode>'));
    }
}
