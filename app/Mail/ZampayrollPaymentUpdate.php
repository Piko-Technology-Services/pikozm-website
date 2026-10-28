<?php

namespace App\Mail;

use App\Models\ZampayrollSubscription;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Admin notification for a ZamPayroll subscription payment outcome.
 */
class ZampayrollPaymentUpdate extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public ZampayrollSubscription $subscription, public string $event)
    {
    }

    public function envelope(): Envelope
    {
        $s = $this->subscription;

        $subject = match ($this->event) {
            ZampayrollSubscription::STATUS_PAID => "✅ ZamPayroll payment received – {$s->company_name} ({$s->plan_name}, {$s->formattedAmount()})",
            ZampayrollSubscription::STATUS_CANCELLED => "⚠️ ZamPayroll payment cancelled – {$s->company_name} ({$s->plan_name})",
            ZampayrollSubscription::STATUS_MANUAL => "📝 ZamPayroll manual payment requested – {$s->company_name} ({$s->plan_name})",
            default => "❌ ZamPayroll payment failed – {$s->company_name} ({$s->plan_name})",
        };

        return new Envelope(subject: $subject, replyTo: [$s->email]);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.zampayroll-payment-update');
    }
}
