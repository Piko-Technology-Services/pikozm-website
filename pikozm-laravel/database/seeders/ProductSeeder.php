<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

/**
 * Catalog ported from payments/includes/products.php.
 *
 * Pricing sourced from Piko quotation PTS-QUO-279 (18/09/2026) and
 * client-confirmed subscription pricing. All amounts in ZMW.
 *
 * While on DPO TEST credentials only service types 54841 (Test Product)
 * and 85325 (Test Service) are valid – replace with LIVE codes later.
 */
class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            // ---------------- Subscriptions ----------------
            [
                'slug' => 'zp-software-subscription',
                'name' => 'ZP Software Subscription',
                'description' => 'Licence and ongoing access to the ZP business software platform, including updates and support.',
                'price' => 7500.00,
                'service_type' => '54841',
                'category' => Product::CATEGORY_SUBSCRIPTION,
                'billing_cycle' => 'year',
            ],
            [
                'slug' => 'website-hosting',
                'name' => 'Website Business Hosting',
                'description' => 'Annual business web hosting, including custom domain and professional business email accounts.',
                'price' => 750.00,
                'service_type' => '85325',
                'category' => Product::CATEGORY_SUBSCRIPTION,
                'billing_cycle' => 'year',
            ],

            // ---------------- One-off services ----------------
            [
                'slug' => 'custom-software-development',
                'name' => 'Custom Software Development',
                'description' => 'Bespoke software built to your workflows — CRM, ERP, inventory, or internal business systems.',
                'price' => 5000.00,
                'service_type' => '85325',
                'category' => Product::CATEGORY_ONE_TIME,
            ],
            [
                'slug' => 'company-website',
                'name' => 'Modern Company Website (UI/UX Design & Development)',
                'description' => 'Full design and development of a responsive company website, including custom domain setup and Google SEO configuration.',
                'price' => 5500.00,
                'service_type' => '85325',
                'category' => Product::CATEGORY_ONE_TIME,
                'note' => 'Projects commence on receipt of a 75% down payment of the agreed total.',
            ],
            [
                'slug' => 'company-profile-print',
                'name' => 'Company Profile, Letterhead & Business Card Design',
                'description' => 'Design and printing of your company profile, branded letterhead, and business cards.',
                'price' => 1000.00,
                'service_type' => '54841',
                'category' => Product::CATEGORY_ONE_TIME,
            ],
            [
                'slug' => 'logo-design',
                'name' => 'Logo Design',
                'description' => 'Original logo design with revisions, delivered in full print and digital formats.',
                'price' => 500.00,
                'service_type' => '54841',
                'category' => Product::CATEGORY_ONE_TIME,
            ],
        ];

        foreach ($products as $order => $product) {
            Product::updateOrCreate(
                ['slug' => $product['slug']],
                $product + ['pricing_type' => Product::PRICING_FIXED, 'sort_order' => $order, 'is_active' => true],
            );
        }
    }
}
