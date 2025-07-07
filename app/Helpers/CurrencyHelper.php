<?php

namespace App\Helpers;

class CurrencyHelper
{
    /**
     * Get currency symbol by currency code
     */
    public static function getSymbol(string $currencyCode): string
    {
        $currencies = [
            'LKR' => 'Rs.',
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            'INR' => '₹',
            'AUD' => 'A$',
            'CAD' => 'C$',
            'SGD' => 'S$',
            'MYR' => 'RM',
            'THB' => '฿',
            'PKR' => '₨',
            'BDT' => '৳',
            'JPY' => '¥',
            'CNY' => '¥',
            'KRW' => '₩',
        ];

        return $currencies[strtoupper($currencyCode)] ?? $currencyCode;
    }

    /**
     * Get currency name by currency code
     */
    public static function getName(string $currencyCode): string
    {
        $currencies = [
            'LKR' => 'Sri Lankan Rupee',
            'USD' => 'US Dollar',
            'EUR' => 'Euro',
            'GBP' => 'British Pound',
            'INR' => 'Indian Rupee',
            'AUD' => 'Australian Dollar',
            'CAD' => 'Canadian Dollar',
            'SGD' => 'Singapore Dollar',
            'MYR' => 'Malaysian Ringgit',
            'THB' => 'Thai Baht',
            'PKR' => 'Pakistani Rupee',
            'BDT' => 'Bangladeshi Taka',
            'JPY' => 'Japanese Yen',
            'CNY' => 'Chinese Yuan',
            'KRW' => 'South Korean Won',
        ];

        return $currencies[strtoupper($currencyCode)] ?? $currencyCode;
    }

    /**
     * Format currency amount
     */
    public static function format(float $amount, string $currencyCode): string
    {
        $symbol = self::getSymbol($currencyCode);
        
        // For currencies that typically don't use decimals
        $noDecimalCurrencies = ['JPY', 'KRW'];
        
        if (in_array(strtoupper($currencyCode), $noDecimalCurrencies)) {
            return $symbol . number_format($amount, 0);
        }
        
        return $symbol . number_format($amount, 2);
    }

    /**
     * Get all supported currencies
     */
    public static function getAllCurrencies(): array
    {
        return [
            'LKR' => 'LKR - Sri Lankan Rupee',
            'USD' => 'USD - US Dollar',
            'EUR' => 'EUR - Euro',
            'GBP' => 'GBP - British Pound',
            'INR' => 'INR - Indian Rupee',
            'AUD' => 'AUD - Australian Dollar',
            'CAD' => 'CAD - Canadian Dollar',
            'SGD' => 'SGD - Singapore Dollar',
            'MYR' => 'MYR - Malaysian Ringgit',
            'THB' => 'THB - Thai Baht',
            'PKR' => 'PKR - Pakistani Rupee',
            'BDT' => 'BDT - Bangladeshi Taka',
            'JPY' => 'JPY - Japanese Yen',
            'CNY' => 'CNY - Chinese Yuan',
            'KRW' => 'KRW - South Korean Won',
        ];
    }
}
