<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Diamond Brand Code
    |--------------------------------------------------------------------------
    |
    | The brand code used in barcode number generation.
    | Format: YY + BRAND_CODE + LOT_NO (e.g., 25100000001)
    |
    */
    'brand_code' => env('DIAMOND_BRAND_CODE', '100'),

    /*
    |--------------------------------------------------------------------------
    | Daily Margin Rate
    |--------------------------------------------------------------------------
    |
    | The daily compound margin rate used for duration price calculation.
    | Formula: duration_price = purchase_price × (1 + rate)^days
    | Default: 5% per day (0.05)
    |
    */
    'daily_margin_rate' => env('DIAMOND_MARGIN_RATE', 0.05),

    /*
    |--------------------------------------------------------------------------
    | Cache Duration (seconds)
    |--------------------------------------------------------------------------
    |
    | How long to cache static data like stone types, shapes, colors, etc.
    | Default: 86400 seconds (24 hours)
    |
    */
    'cache_duration' => [
        'admins' => 3600,        // 1 hour
        'static_data' => 86400,  // 24 hours
    ],

    /*
    |--------------------------------------------------------------------------
    | Jewellery – Platinum Rate (USD per gram, 950 purity)
    |--------------------------------------------------------------------------
    |
    | When set, this value overrides the database setting and locks
    | the field as read-only in the pricing matrix form.
    | Set to null to allow manual entry via the UI / database setting.
    |
    */
    'jewellery_platinum_rate' => env('JEWELLERY_PLATINUM_RATE'),

];
