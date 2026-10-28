<?php

namespace App\Support;

class WhatsApp
{
    /** Build a wa.me click-to-chat link with a pre-filled message. */
    public static function link(string $number, string $text): string
    {
        return 'https://wa.me/'.preg_replace('/\D+/', '', $number).'?text='.rawurlencode($text);
    }
}
