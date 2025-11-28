<?php

namespace App\Support;

class PhoneHelper
{
    public static function normalize(?string $phone, string $defaultCountry = 'IN'): ?string
    {
        if (! $phone) {
            return null;
        }

        // Keep only digits and +
        $phone = trim($phone);
        $phone = preg_replace('/[^0-9+]/', '', $phone);

        // If already starts with +, assume correct E.164
        if (str_starts_with($phone, '+')) {
            return $phone;
        }

        // India: 10-digit local mobile -> +91XXXXXXXXXX
        if ($defaultCountry === 'IN' && strlen($phone) === 10) {
            return '+91'.$phone;
        }

        // If starts with country code like 91XXXXXXXXXX, add +
        if ($defaultCountry === 'IN' && strlen($phone) === 12 && str_starts_with($phone, '91')) {
            return '+'.$phone;
        }

        // Fallback: return as-is (or null if you want strict)
        return $phone;
    }
}
