<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * A DPO Pay checkout. Replaces the save_order()/update_order()/get_order()
 * helpers that wrote to payments/data/orders.json.
 */
class Order extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_TOKEN_CREATED = 'token_created';
    public const STATUS_PAID = 'paid';
    public const STATUS_UNPAID = 'unpaid';
    public const STATUS_FAILED = 'failed';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'company_ref', 'product_id', 'product_name', 'description', 'amount', 'currency',
        'service_type', 'customer_name', 'customer_email', 'customer_phone', 'notes',
        'status', 'dpo_token', 'dpo_trans_ref', 'error_message', 'verify_message', 'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'verified_at' => 'datetime',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /** Same format as before: PIKO-XXXXXXXX */
    public static function generateCompanyRef(): string
    {
        do {
            $ref = 'PIKO-'.strtoupper(bin2hex(random_bytes(4)));
        } while (static::where('company_ref', $ref)->exists());

        return $ref;
    }

    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID;
    }

    public function markTokenCreated(string $token, ?string $transRef): void
    {
        $this->update([
            'status' => self::STATUS_TOKEN_CREATED,
            'dpo_token' => $token,
            'dpo_trans_ref' => $transRef,
        ]);
    }

    public function markFailed(string $message): void
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'error_message' => Str::limit($message, 1000),
        ]);
    }
}
