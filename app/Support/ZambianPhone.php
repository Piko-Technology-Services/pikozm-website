<?php

namespace App\Support;

/**
 * Helpers for Zambian mobile numbers (mobile money).
 */
class ZambianPhone
{
    /**
     * Normalise "0977 123 456", "+260977123456" or "977123456" to "260977123456".
     * Returns null when the input is not a valid 9-digit Zambian mobile number.
     */
    public static function normalize(?string $input): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $input);

        if (str_starts_with($digits, '260')) {
            $digits = substr($digits, 3);
        } elseif (str_starts_with($digits, '0')) {
            $digits = substr($digits, 1);
        }

        return preg_match('/^[79]\d{8}$|^5\d{8}$/', $digits) ? '260'.$digits : null;
    }

    /** Local format for display: 097 712 3456 */
    public static function display(string $e164): string
    {
        $local = '0'.substr($e164, 3);

        return substr($local, 0, 3).' '.substr($local, 3, 3).' '.substr($local, 6);
    }

    /** Network key from config('zampayroll.mobile_networks'), or null if unknown. */
    public static function detectNetwork(string $e164): ?string
    {
        $prefix = '0'.substr($e164, 3, 2);

        foreach (config('zampayroll.mobile_networks') as $key => $network) {
            if (in_array($prefix, $network['prefixes'], true)) {
                return $key;
            }
        }

        return null;
    }
}
