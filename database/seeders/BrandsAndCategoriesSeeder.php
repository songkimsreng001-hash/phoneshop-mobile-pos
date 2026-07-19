<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Brand;
use App\Models\Category;

class BrandsAndCategoriesSeeder extends Seeder
{
    public function run(): void
    {
        // ── Brands ─────────────────────────────────────────────────────
        $brands = [
            ['name' => 'Apple',     'slug' => 'apple',     'country_of_origin' => 'USA'],
            ['name' => 'Samsung',   'slug' => 'samsung',   'country_of_origin' => 'South Korea'],
            ['name' => 'Xiaomi',    'slug' => 'xiaomi',    'country_of_origin' => 'China'],
            ['name' => 'Oppo',      'slug' => 'oppo',      'country_of_origin' => 'China'],
            ['name' => 'Vivo',      'slug' => 'vivo',      'country_of_origin' => 'China'],
            ['name' => 'Huawei',    'slug' => 'huawei',    'country_of_origin' => 'China'],
            ['name' => 'OnePlus',   'slug' => 'oneplus',   'country_of_origin' => 'China'],
            ['name' => 'Google',    'slug' => 'google',    'country_of_origin' => 'USA'],
            ['name' => 'Nokia',     'slug' => 'nokia',     'country_of_origin' => 'Finland'],
            ['name' => 'Realme',    'slug' => 'realme',    'country_of_origin' => 'China'],
        ];

        foreach ($brands as $brand) {
            Brand::firstOrCreate(['slug' => $brand['slug']], $brand);
        }

        // ── Parent Categories ──────────────────────────────────────────
        $parentCategories = [
            ['name' => 'Smartphones',   'slug' => 'smartphones'],
            ['name' => 'Accessories',   'slug' => 'accessories'],
            ['name' => 'Spare Parts',   'slug' => 'spare-parts'],
            ['name' => 'Tablets',       'slug' => 'tablets'],
            ['name' => 'Wearables',     'slug' => 'wearables'],
        ];

        $parents = [];
        foreach ($parentCategories as $cat) {
            $parents[$cat['slug']] = Category::firstOrCreate(['slug' => $cat['slug']], $cat);
        }

        // ── Sub-categories ─────────────────────────────────────────────
        $subCategories = [
            // Accessories
            ['name' => 'Chargers & Cables',  'slug' => 'chargers-cables',  'parent' => 'accessories'],
            ['name' => 'Phone Cases',        'slug' => 'phone-cases',      'parent' => 'accessories'],
            ['name' => 'Screen Protectors',  'slug' => 'screen-protectors','parent' => 'accessories'],
            ['name' => 'Earphones',          'slug' => 'earphones',        'parent' => 'accessories'],
            ['name' => 'Power Banks',        'slug' => 'power-banks',      'parent' => 'accessories'],
            ['name' => 'Memory Cards',       'slug' => 'memory-cards',     'parent' => 'accessories'],

            // Spare Parts
            ['name' => 'Screens & Displays', 'slug' => 'screens-displays', 'parent' => 'spare-parts'],
            ['name' => 'Batteries',          'slug' => 'batteries',        'parent' => 'spare-parts'],
            ['name' => 'Back Covers',        'slug' => 'back-covers',      'parent' => 'spare-parts'],
            ['name' => 'Charging Ports',     'slug' => 'charging-ports',   'parent' => 'spare-parts'],

            // Wearables
            ['name' => 'Smart Watches',      'slug' => 'smart-watches',    'parent' => 'wearables'],
            ['name' => 'Wireless Earbuds',   'slug' => 'wireless-earbuds', 'parent' => 'wearables'],
        ];

        foreach ($subCategories as $sub) {
            $parentId = $parents[$sub['parent']]->id ?? null;
            Category::firstOrCreate(
                ['slug' => $sub['slug']],
                ['name' => $sub['name'], 'slug' => $sub['slug'], 'parent_id' => $parentId]
            );
        }

        $this->command->info('Brands and categories seeded successfully.');
    }
}
