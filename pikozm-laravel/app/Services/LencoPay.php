<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Server side of the Lenco inline checkout used by PikoPay and donations.
 *
 * The browser opens the Lenco widget with our public key + a reference we
 * generate. Afterwards we confirm the outcome server-side, either by asking
 * Lenco for the collection status or via the signed webhook.
 */
class LencoPay
{
    public function __construct(private readonly array $config)
    {
    }

    public function publicKey(): ?string
    {
        return $this->config['public_key'] ?? null;
    }

    public function isConfigured(): bool
    {
        return filled($this->config['public_key'] ?? null);
    }

    public function channels(): array
    {
        return $this->config['channels'] ?? ['card', 'mobile-money'];
    }

    /**
     * Look up a collection by our reference.
     *
     * @return array{status: string, data: array}|null  status is one of
     *         pending | successful | failed | pay-offline, or null on error.
     */
    public function collectionStatus(string $reference): ?array
    {
        if (blank($this->config['secret_key'] ?? null)) {
            Log::warning('Lenco secret key missing; cannot verify collection', ['reference' => $reference]);

            return null;
        }

        try {
            $response = Http::withToken($this->config['secret_key'])
                ->acceptJson()
                ->timeout(20)
                ->get($this->config['api_base'].'/collections/status/'.rawurlencode($reference));
        } catch (ConnectionException $e) {
            Log::warning('Lenco connection failed', ['reference' => $reference, 'error' => $e->getMessage()]);

            return null;
        }

        if (! $response->successful()) {
            Log::warning('Lenco status lookup failed', ['reference' => $reference, 'http' => $response->status()]);

            return null;
        }

        $data = (array) $response->json('data', []);

        return ['status' => (string) ($data['status'] ?? 'pending'), 'data' => $data];
    }

    /**
     * Validate the X-Lenco-Signature header of a webhook call.
     * Signature = HMAC-SHA512(raw body, key = SHA256(secret key)).
     */
    public function hasValidSignature(string $rawBody, ?string $signature): bool
    {
        if (blank($signature) || blank($this->config['secret_key'] ?? null)) {
            return false;
        }

        $hashKey = hash('sha256', $this->config['secret_key']);

        return hash_equals(hash_hmac('sha512', $rawBody, $hashKey), $signature);
    }
}
