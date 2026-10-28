<?php

/*
|--------------------------------------------------------------------------
| Lenco configuration
|--------------------------------------------------------------------------
|
| Used by PikoPay (/pikopay) and Digital Impact donations. Previously these
| payments were initiated through the external app.pikozm.com API; they now
| run inside this application.
|
| public_key  – handed to the Lenco inline widget in the browser.
| secret_key  – server-side only; used to verify a collection by reference
|               and to validate webhook signatures.
|
*/

return [

    'public_key' => env('LENCO_PUBLIC_KEY'),

    'secret_key' => env('LENCO_SECRET_KEY'),

    'api_base' => rtrim(env('LENCO_API_BASE', 'https://api.lenco.co/access/v2'), '/'),

    'channels' => ['card', 'mobile-money'],

    // Currencies offered in the PikoPay and donation forms.
    'currencies' => [
        'pikopay' => ['ZMW', 'USD'],
        'donations' => ['USD', 'EUR', 'GBP', 'ZMW', 'EGP'],
    ],

];
