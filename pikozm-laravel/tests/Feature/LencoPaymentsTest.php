<?php

namespace Tests\Feature;

use App\Models\ClientPayment;
use App\Models\Donation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LencoPaymentsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['lenco.public_key' => 'pub-test', 'lenco.secret_key' => 'secret-test']);
    }

    public function test_pikopay_initiate_returns_widget_data(): void
    {
        $this->postJson('/pikopay/initiate', [
            'full_name' => 'Bupe Katongo', 'email' => 'b@example.com', 'purpose' => 'Invoice 12', 'amount' => 100, 'currency' => 'ZMW',
        ])->assertCreated()
            ->assertJsonPath('data.public_key', 'pub-test')
            ->assertJsonPath('data.payment.currency', 'ZMW');

        $this->assertSame(ClientPayment::STATUS_PENDING, ClientPayment::sole()->status);
    }

    public function test_initiate_is_unavailable_without_keys(): void
    {
        config(['lenco.public_key' => null]);

        $this->postJson('/pikopay/initiate', [])->assertStatus(503);
    }

    public function test_donation_verify_uses_lenco_status(): void
    {
        Http::fake(['*/collections/status/*' => Http::response(['status' => true, 'data' => ['status' => 'successful']])]);

        $reference = $this->postJson('/digital-impact/donations', [
            'name' => 'Acme', 'email' => 'a@example.com', 'amount' => 50, 'currency' => 'USD',
        ])->assertCreated()->json('data.reference');

        $this->postJson('/digital-impact/donations/verify', ['reference' => $reference])
            ->assertJsonPath('status', Donation::STATUS_SUCCESSFUL);

        $this->assertNotNull(Donation::sole()->paid_at);
    }

    public function test_webhook_requires_valid_signature(): void
    {
        $donation = Donation::create([
            'reference' => 'PDIP-TEST', 'name' => 'A', 'email' => 'a@example.com', 'amount' => 10, 'currency' => 'USD', 'status' => 'pending',
        ]);
        $body = json_encode(['event' => 'collection.successful', 'data' => ['reference' => 'PDIP-TEST', 'status' => 'successful']]);

        $this->call('POST', '/webhooks/lenco', [], [], [], ['CONTENT_TYPE' => 'application/json', 'HTTP_X_LENCO_SIGNATURE' => 'bad'], $body)
            ->assertStatus(401);

        $signature = hash_hmac('sha512', $body, hash('sha256', 'secret-test'));
        $this->call('POST', '/webhooks/lenco', [], [], [], ['CONTENT_TYPE' => 'application/json', 'HTTP_X_LENCO_SIGNATURE' => $signature], $body)
            ->assertOk();

        $this->assertSame(Donation::STATUS_SUCCESSFUL, $donation->fresh()->status);
    }

    public function test_volunteer_application_stores_files_privately(): void
    {
        Storage::fake('local');

        $this->postJson('/digital-impact/volunteers', [
            'name' => 'Ann', 'email' => 'ann@example.com', 'phone' => '1', 'country' => 'Zambia',
            'skills' => 'Design', 'availability' => 'remote',
            'nrc' => UploadedFile::fake()->image('id.png'),
            'resume' => UploadedFile::fake()->create('cv.pdf', 20, 'application/pdf'),
        ])->assertCreated();

        $this->assertDatabaseCount('volunteer_applications', 1);
        $this->assertCount(1, Storage::disk('local')->allFiles('volunteers/resumes'));
    }

    public function test_partner_and_support_requests(): void
    {
        $this->postJson('/digital-impact/partners', [
            'organization_name' => 'Org', 'contact_person' => 'Ann', 'email' => 'a@example.com', 'phone' => '1',
            'country' => 'Zambia', 'partnership_type' => 'CSR', 'organization_type' => 'NGO',
        ])->assertCreated();

        $this->postJson('/digital-impact/support-requests', [
            'organization_name' => 'Org', 'contact_person' => 'Ann', 'email' => 'a@example.com', 'phone' => '1',
            'country' => 'Zambia', 'organization_type' => 'Faith', 'support_needs' => 'A website',
        ])->assertCreated();

        $this->assertDatabaseCount('partnership_requests', 1);
        $this->assertDatabaseCount('support_requests', 1);
    }
}
