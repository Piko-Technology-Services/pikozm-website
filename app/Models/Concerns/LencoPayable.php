<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;

/**
 * Shared behaviour for records paid through the Lenco inline widget
 * (ClientPayment and Donation).
 */
trait LencoPayable
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_SUCCESSFUL = 'successful';
    public const STATUS_FAILED = 'failed';

    public static function generateReference(string $prefix): string
    {
        do {
            $reference = $prefix.'-'.now()->format('ymd').'-'.strtoupper(Str::random(8));
        } while (static::where('reference', $reference)->exists());

        return $reference;
    }

    public function initializeLencoPayable(): void
    {
        $this->mergeCasts([
            'amount' => 'decimal:2',
            'gateway_response' => 'array',
            'paid_at' => 'datetime',
        ]);
    }

    /**
     * Apply a Lenco collection status ("successful", "failed", "pending", "pay-offline").
     */
    public function applyGatewayStatus(string $gatewayStatus, array $payload = []): void
    {
        $status = match ($gatewayStatus) {
            'successful' => self::STATUS_SUCCESSFUL,
            'failed' => self::STATUS_FAILED,
            default => self::STATUS_PROCESSING,
        };

        $this->forceFill([
            'status' => $status,
            'gateway_response' => $payload ?: $this->gateway_response,
            'paid_at' => $status === self::STATUS_SUCCESSFUL ? ($this->paid_at ?? now()) : $this->paid_at,
        ])->save();
    }

    public function isSuccessful(): bool
    {
        return $this->status === self::STATUS_SUCCESSFUL;
    }
}
