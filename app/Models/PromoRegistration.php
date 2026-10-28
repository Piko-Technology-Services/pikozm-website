<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PromoRegistration extends Model
{
    protected $fillable = ['promo_package_id', 'full_name', 'phone', 'email', 'business_name', 'message'];

    public function package(): BelongsTo
    {
        return $this->belongsTo(PromoPackage::class, 'promo_package_id');
    }

    public function toWhatsAppText(): string
    {
        return "Hello Piko Tech 👋,\nI'm registering for the *{$this->package->label()}* promo:\n\n"
            ."👤 *Name:* {$this->full_name}\n"
            ."📱 *Phone:* {$this->phone}\n"
            .'📧 *Email:* '.($this->email ?: 'N/A')."\n"
            .'🏢 *Business:* '.($this->business_name ?: 'N/A')."\n"
            .'📝 *Message:* '.($this->message ?: 'N/A');
    }
}
