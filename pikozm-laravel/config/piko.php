<?php

/*
|--------------------------------------------------------------------------
| Piko Technology Services – company details
|--------------------------------------------------------------------------
|
| Single place for contact details, WhatsApp numbers and social links that
| were previously copy-pasted into every static HTML page.
|
*/

return [

    'name' => 'Piko Technology Services',

    'email' => 'info@pikozm.com',

    'location' => 'Ndola, Zambia',

    // Displayed phone numbers (E.164 without "+" is used for tel:/wa.me links)
    'phones' => [
        ['display' => '+260 773 550 095', 'e164' => '260773550095'],
        ['display' => '+260 962 893 773', 'e164' => '260962893773'],
    ],

    // Which number receives each kind of WhatsApp lead
    'whatsapp' => [
        'contact' => '260962893773', // "Let's Connect" form on the home page
        'talent' => '260773550095',  // Talent / Join Our Team form
        'promo' => '260773550095',   // Promo package registration
    ],

    // Leave a value null to hide that icon in the footer.
    'social' => [
        'facebook' => 'https://www.facebook.com/share/1D4N2rzSRZ/?mibextid=wwXIfr',
        'whatsapp' => 'https://wa.me/260962893773',
        'instagram' => null,
        'youtube' => null,
    ],

    // Legal documents served from /public
    'legal' => [
        'privacy' => 'docs/privacy_policy.pdf',
        'terms' => 'docs/terms_and_conditions.pdf',
        'refunds' => 'docs/refund_and_cancellation-policy.pdf',
    ],

    // Home page "Our Services" grid (two columns, in display order).
    'services' => [
        ['title' => 'Business Consultancy', 'image' => 'images/portfolio/business-consultancy.jpg', 'description' => 'Professional advisory for business operations, market entry, digital transformation, and compliance.'],
        ['title' => 'Large Format Printing', 'image' => 'images/portfolio/large-format-printing.jpg', 'description' => 'Banners, flyers, roll-ups, posters, company ID cards, and premium business cards with quality finish.'],
        ['title' => 'Computer Science Training', 'image' => 'images/portfolio/computer-training.jpg', 'description' => 'Hands-on coding bootcamps, short courses, and mentorship in web, software, and systems development.'],
        ['title' => 'Bespoke Software Development', 'image' => 'images/portfolio/bespoke-software-dev.jpg', 'description' => 'Custom software tailored to your workflows – CRMs, ERPs, inventory systems, portals, and more.'],
        ['title' => 'Graphics & Motion Design', 'image' => 'images/portfolio/graphics-design.jpg', 'description' => 'Creative visual assets, animations, explainer videos, and motion branding for digital impact.'],
        ['title' => 'Web Development & Design', 'image' => 'images/portfolio/web-design.jpg', 'description' => 'Stunning, responsive websites with hosting, emails, and SEO to establish your online presence.'],
        ['title' => 'Accounting Software Solutions', 'image' => 'images/portfolio/accounting-software.jpg', 'description' => 'Installation and training for QuickBooks, Sage, Pastel, and custom-built accounting tools.'],
        ['title' => 'Business Development', 'image' => 'images/portfolio/business-dev.jpg', 'description' => 'Strategic planning, company registration, branding, and growth solutions for SMEs and startups.'],
    ],

    // Logos in the scrolling strip on the home page (repeated to fill the slider).
    'partner_logos' => [
        ['src' => 'images/services/erp.png', 'alt' => 'ERP', 'height' => 60],
        ['src' => 'images/services/payroll.png', 'alt' => 'Payroll', 'height' => 50],
        ['src' => 'images/services/lenco.svg', 'alt' => 'Lenco', 'height' => 50],
        ['src' => 'images/services/PCI-DSS.svg', 'alt' => 'PCI DSS', 'height' => 60],
    ],

    'adsense_client' => env('ADSENSE_CLIENT', 'ca-pub-1802075932902182'),

];
