<?php

/**
 * Centralized Currency Configuration
 * 
 * Single source of truth for all currencies used across the application.
 * To add a new currency, simply add a new entry to this array.
 * All dropdowns, validation rules, symbols, and invoice PDFs will auto-update.
 */
return [
    'INR' => [
        'name' => 'Indian Rupee',
        'symbol' => '₹',
        'flag' => '🇮🇳',
        'region' => 'IN',
        'currency_name' => 'Rupees',
        'cents_name' => 'Paise',
    ],
    'USD' => [
        'name' => 'US Dollar',
        'symbol' => '$',
        'flag' => '🇺🇸',
        'region' => 'US',
        'currency_name' => 'Dollars',
        'cents_name' => 'Cents',
    ],
    'GBP' => [
        'name' => 'British Pound',
        'symbol' => '£',
        'flag' => '🇬🇧',
        'region' => 'UK',
        'currency_name' => 'Pounds',
        'cents_name' => 'Pence',
    ],
    'EUR' => [
        'name' => 'Euro',
        'symbol' => '€',
        'flag' => '🇪🇺',
        'region' => 'EU',
        'currency_name' => 'Euros',
        'cents_name' => 'Cents',
    ],
    'CAD' => [
        'name' => 'Canadian Dollar',
        'symbol' => 'C$',
        'flag' => '🇨🇦',
        'region' => 'CA',
        'currency_name' => 'Dollars',
        'cents_name' => 'Cents',
    ],
    'AUD' => [
        'name' => 'Australian Dollar',
        'symbol' => 'A$',
        'flag' => '🇦🇺',
        'region' => 'AU',
        'currency_name' => 'Dollars',
        'cents_name' => 'Cents',
    ],
    'AED' => [
        'name' => 'UAE Dirham',
        'symbol' => 'د.إ',
        'flag' => '🇦🇪',
        'region' => 'AE',
        'currency_name' => 'Dirhams',
        'cents_name' => 'Fils',
    ],
];
