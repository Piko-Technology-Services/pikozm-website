<?php

namespace Database\Seeders;

use App\Models\PromoPackage;
use Illuminate\Database\Seeder;

/**
 * "Business Booster" packages from register.html.
 */
class PromoPackageSeeder extends Seeder
{
    public function run(): void
    {
        $packages = [
            [
                'slug' => 'digital-biine-starter',
                'name' => 'Digital Biiné Starter',
                'price' => 479.99,
                'tagline' => 'Perfect for Startups & Fresh Ideas',
                'features' => ['Logo Illustration', '2 Square-size Posters', 'Business Card Design', 'Letterhead Design'],
            ],
            [
                'slug' => 'tikula-biine',
                'name' => 'Tikula Biiné',
                'price' => 849.98,
                'tagline' => 'Great for Growing Businesses',
                'features' => ['Logo Illustration', '2 Square-size Posters', '50 Printed Business Cards', 'Letterhead Design', 'Invoice/Quotation Template', 'Company Profile'],
                'is_featured' => true,
            ],
            [
                'slug' => 'ama-biine-yama-bosele',
                'name' => 'Ama Biiné yama Bosele',
                'price' => 1499.00,
                'tagline' => 'Built for Established Enterprises',
                'features' => ['Full Logo Design (Primary + Variants)', '2 Square-size Posters', '50 Printed Business Cards', 'Printed Letterhead Design', 'Invoice & Quotation Templates', 'Company Profile', 'Website (Domain & Emails)'],
            ],
        ];

        foreach ($packages as $order => $package) {
            PromoPackage::updateOrCreate(
                ['slug' => $package['slug']],
                $package + ['is_featured' => false, 'is_active' => true, 'sort_order' => $order],
            );
        }
    }
}
