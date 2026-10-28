<?php

/*
|--------------------------------------------------------------------------
| DPO Pay (3G Direct Pay) configuration
|--------------------------------------------------------------------------
|
| Ported from payments/includes/config.php. Values now live in .env so the
| TEST token can be swapped for the LIVE token without touching code.
|
| redirect_url / back_url default to this application's own routes
| (/payments/success and /payments/cancel). Only set them in .env if you
| need DPO to call a different absolute URL.
|
*/

return [

    'company_token' => env('DPO_COMPANY_TOKEN'),

    // Same endpoints for test and live; the company token decides the mode.
    'api_endpoint' => env('DPO_API_ENDPOINT', 'https://secure.3gdirectpay.com/API/v6/'),
    'payment_url' => env('DPO_PAYMENT_URL', 'https://secure.3gdirectpay.com/payv3.php?ID='),

    'currency' => env('DPO_CURRENCY', 'ZMW'),

    // Service type used for free-form "Paying for something else?" payments.
    'default_service_type' => env('DPO_DEFAULT_SERVICE_TYPE', '85325'),

    'redirect_url' => env('DPO_REDIRECT_URL'),
    'back_url' => env('DPO_BACK_URL'),

    'timeout' => 30,

    // Sent with every DPO API call. Avoid the default "GuzzleHttp" agent, which DPO's firewall may block.
    'user_agent' => env('DPO_USER_AGENT', 'PikoPay/1.0 (+https://pikozm.com)'),

];
