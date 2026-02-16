<?php

return [
    'username' => env('ARAMEX_USERNAME'),
    'password' => env('ARAMEX_PASSWORD'),
    'account_number' => env('ARAMEX_ACCOUNT_NUMBER'),
    'account_pin' => env('ARAMEX_ACCOUNT_PIN'),
    'account_entity' => env('ARAMEX_ACCOUNT_ENTITY'),
    'account_country_code' => env('ARAMEX_ACCOUNT_COUNTRY_CODE'),
    'version' => env('ARAMEX_VERSION', 'v1.0'),
];
