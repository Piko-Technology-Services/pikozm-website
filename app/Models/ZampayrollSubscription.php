<?php

namespace App\Models;

use App\Mail\ZampayrollPaymentUpdate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;

/**
 * A ZamPayroll subscription purchase.
 */
class ZampayrollSubscription extends Model
{
    public const METHOD_CARD = 'card';
    public const METHOD_MOBILE = 'mobile_money';
    public const METHOD_MANUAL = 'manual';

    public const STATUS_PENDING = 'pending';
    public const STATUS_AWAITING = 'awaiting_payment';
    public const STATUS_PAID = 'paid';
    public const STATUS_FAILED = 'failed';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_MANUAL = 'manual';

    /** DPO verifyToken result codes → plain-language explanations. */
    public const DPO_RESULTS = [
        '000' => 'Payment received.',
        '900' => 'Waiting for the payment to be approved.',
        '901' => 'The payment was declined.',
        '902' => 'The payment details did not match. Please try again.',
        '903' => 'The payment window expired before it was completed.',
        '904' => 'The payment was cancelled.',
    ];

    protected $fillable = [
        'reference', 'plan', 'plan_name', 'billing_cycle', 'amount', 'currency',
        'company_name', 'contact_name', 'email', 'phone', 'employees',
        'payment_method', 'status', 'dpo_token', 'dpo_trans_ref',
        'mobile_network', 'mobile_number', 'attempts', 'last_result_code', 'last_message',
        'notified_key', 'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_at' => 'datetime',
            'attempts' => 'integer',
            'employees' => 'integer',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'reference';
    }

    public static function generateReference(): string
    {
        do {
            $reference = 'ZPR-'.strtoupper(Str::random(10));
        } while (static::where('reference', $reference)->exists());

        return $reference;
    }

    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID;
    }

    public function billingLabel(): string
    {
        return $this->billing_cycle === 'annual' ? 'Annual' : 'Monthly';
    }

    public function methodLabel(): string
    {
        return match ($this->payment_method) {
            self::METHOD_CARD => 'Card (DPO secure checkout)',
            self::METHOD_MOBILE => 'Mobile money'.($this->mobile_network ? ' – '.config("zampayroll.mobile_networks.{$this->mobile_network}.label") : ''),
            default => 'Manual payment',
        };
    }

    public function formattedAmount(): string
    {
        return 'K'.number_format((float) $this->amount, 2);
    }

    /**
     * Record a DPO verifyToken result and notify the admin once per outcome.
     */
    public function applyDpoResult(string $code, ?string $message = null): void
    {
        $status = match (true) {
            $code === '000' => self::STATUS_PAID,
            $code === '900' => self::STATUS_AWAITING,
            $code === '904' => self::STATUS_CANCELLED,
            default => self::STATUS_FAILED,
        };

        $this->update([
            'status' => $status,
            'last_result_code' => $code,
            'last_message' => Str::limit($message ?: (self::DPO_RESULTS[$code] ?? 'Payment was not completed.'), 250),
            'paid_at' => $status === self::STATUS_PAID ? ($this->paid_at ?? now()) : $this->paid_at,
        ]);

        if (in_array($status, [self::STATUS_PAID, self::STATUS_FAILED, self::STATUS_CANCELLED], true)) {
            $this->notifyAdmin($status);
        }
    }

    public function markFailed(string $message, ?string $code = null): void
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'last_result_code' => $code,
            'last_message' => Str::limit($message, 250),
        ]);

        $this->notifyAdmin(self::STATUS_FAILED);
    }

    /**
     * Email the admin about a payment outcome. Each outcome is sent once per
     * attempt, so polling and page refreshes never produce duplicate emails.
     */
    public function notifyAdmin(string $event): void
    {
        $key = "{$event}:{$this->attempts}:{$this->dpo_token}";

        if ($this->notified_key === $key) {
            return;
        }

        try {
            Mail::to(config('zampayroll.admin_email'))->send(new ZampayrollPaymentUpdate($this, $event));
            $this->forceFill(['notified_key' => $key])->save();
        } catch (Throwable $e) {
            Log::error('ZamPayroll payment email failed', ['reference' => $this->reference, 'error' => $e->getMessage()]);
        }
    }
}
