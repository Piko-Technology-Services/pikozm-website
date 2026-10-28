<?php

/*
|--------------------------------------------------------------------------
| ZamPayroll subscriptions (/zampayroll-subscriptions)
|--------------------------------------------------------------------------
|
| Plans, prices and links for the ZamPayroll subscription checkout.
| Prices are in Zambian Kwacha (ZMW). Annual prices give 2 months free.
|
*/

return [

    'name' => 'ZamPayroll',

    'site_url' => 'https://zampayroll.com',
    'login_url' => 'https://zampayroll.com/login',
    'register_url' => 'https://zampayroll.com/register',
    'contact_url' => 'https://zampayroll.com#demo',
    'logo_url' => env('ZAMPAYROLL_LOGO_URL', 'https://zampayroll.com/assets/images/logo.png'),

    // Payment success / failure notifications go here.
    'admin_email' => env('ZAMPAYROLL_ADMIN_EMAIL', 'info@zampayroll.com'),

    // DPO service type for ZamPayroll subscriptions: 117535 – Web Hosting & Management
    'dpo_service_type' => env('DPO_ZAMPAYROLL_SERVICE_TYPE', '117535'),

    'currency' => 'ZMW',

    // How long the browser waits for a mobile money approval before offering to retry.
    'mobile_money_timeout_seconds' => 180,

    /*
    | Mobile networks offered for in-page mobile money. "dpo_name" must match
    | the paymentname DPO returns from GetMobilePaymentOptions (case-insensitive).
    | "prefixes" are the local number prefixes used to auto-detect the network.
    */
    'mobile_networks' => [
        'airtel' => ['label' => 'Airtel Money', 'dpo_name' => 'Airtel', 'prefixes' => ['097', '077', '057']],
        'mtn' => ['label' => 'MTN MoMo', 'dpo_name' => 'MTN', 'prefixes' => ['096', '076', '056']],
        'zamtel' => ['label' => 'Zamtel Kwacha', 'dpo_name' => 'Zamtel', 'prefixes' => ['095', '075', '055']],
    ],
    'mobile_country' => 'zambia',

    'plans' => [
        'free' => [
            'name' => 'Free Experience',
            'audience' => 'For startups and small businesses',
            'employees' => 'Up to 10 active employees',
            'min_employees' => 0,
            'max_employees' => 10,
            'monthly' => 0,
            'annual' => null,
            'price_note' => 'K0 for 2 months',
            'summary' => 'Explore ZamPayroll with up to 10 active employees before moving to a paid subscription.',
            'features' => [
                'Access to core HR and payroll capabilities',
                'Employee records and payroll management',
                'Employee self-service',
                'Limited AI assistant usage',
                'Standard support',
            ],
            'footnote' => 'A paid subscription is required from month three to continue using the platform.',
            'cta' => ['label' => 'Start free', 'url' => 'https://zampayroll.com/register'],
        ],
        'tikula' => [
            'name' => 'Tikula',
            'tag' => 'Growing Businesses',
            'employees' => '11–50 active employees',
            'min_employees' => 11,
            'max_employees' => 50,
            'monthly' => 799,
            'annual' => 7990,
            'summary' => 'Built for ambitious businesses ready to professionalise their HR operations and simplify payroll.',
            'detail' => 'Your complete ZamPayroll experience, including payroll, employee management, attendance, leave, overtime, loans, reporting, AI assistance and support.',
        ],
        'medium-enterprise' => [
            'name' => 'Medium Enterprise',
            'tag' => 'Popular Choice',
            'popular' => true,
            'employees' => '51–150 active employees',
            'min_employees' => 51,
            'max_employees' => 150,
            'monthly' => 1499,
            'annual' => 14990,
            'summary' => 'Designed for established organisations managing larger teams and increasingly complex workforce operations.',
            'detail' => 'The same complete platform, with every core feature and support service included—priced to reflect your workforce size.',
        ],
        'corporate' => [
            'name' => 'Corporate',
            'tag' => 'Advanced Scale',
            'employees' => '151–300 active employees',
            'min_employees' => 151,
            'max_employees' => 300,
            'monthly' => 2499,
            'annual' => 24990,
            'summary' => 'For organisations seeking a centralised, intelligent approach to workforce administration and payroll at scale.',
            'detail' => "Full access to ZamPayroll's capabilities and support, helping your organisation maintain consistency and visibility across its workforce.",
        ],
        'enterprise' => [
            'name' => 'Enterprise',
            'employees' => '301+ active employees',
            'min_employees' => 301,
            'max_employees' => null,
            'monthly' => null,
            'annual' => null,
            'price_note' => 'Tailored Pricing',
            'summary' => 'For large employers, multi-site operations and organisations with specialised deployment requirements.',
            'features' => [
                'All ZamPayroll features and support',
                'Workforce-scale deployment planning',
                'Implementation and migration assessment',
                'Tailored commercial arrangements',
                'Optional infrastructure and integration assessment',
            ],
            'footnote' => 'Pricing is determined through consultation based on workforce size and deployment requirements.',
            'cta' => ['label' => 'Talk to our team', 'url' => 'https://zampayroll.com#demo'],
        ],
    ],

];
