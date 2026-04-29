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

];
