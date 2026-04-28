<?php

namespace Database\Seeders;

use App\Models\AdminUser;
use App\Models\Currency;
use App\Models\Product;
use App\Models\ProductColor;
use App\Models\ProductImage;
use App\Models\ProductPrice;
use App\Models\ProductSize;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        [$bdt, $usd] = $this->seedCurrencies();
        $admin = $this->seedAdmins();

        $products = $this->productsData();
        foreach ($products as $data) {
            $this->createProduct($data, $admin, $bdt, $usd);
        }

        $this->command->info('✓ Seeded ' . count($products) . ' products with variants, images, and prices.');
    }

    // ─── Currencies ───────────────────────────────────────────────────────────

    private function seedCurrencies(): array
    {
        $bdt = Currency::create([
            'code'          => 'BDT',
            'symbol'        => '৳',
            'exchange_rate' => 1.000000,
            'is_default'    => true,
        ]);

        $usd = Currency::create([
            'code'          => 'USD',
            'symbol'        => '$',
            'exchange_rate' => 0.009100,
            'is_default'    => false,
        ]);

        return [$bdt, $usd];
    }

    // ─── Admin users ──────────────────────────────────────────────────────────

    private function seedAdmins(): AdminUser
    {
        $master = AdminUser::create([
            'name'          => 'Super Admin',
            'email'         => 'admin@mossim.com',
            'password_hash' => Hash::make('Admin@1234'),
            'role'          => 'master_admin',
            'is_active'     => true,
        ]);

        AdminUser::create([
            'name'          => 'Store Manager',
            'email'         => 'store@mossim.com',
            'password_hash' => Hash::make('Admin@1234'),
            'role'          => 'admin',
            'is_active'     => true,
        ]);

        AdminUser::create([
            'name'          => 'Staff Member',
            'email'         => 'staff@mossim.com',
            'password_hash' => Hash::make('Staff@1234'),
            'role'          => 'staff',
            'is_active'     => true,
        ]);

        return $master;
    }

    // ─── Product factory ──────────────────────────────────────────────────────

    private function createProduct(array $data, AdminUser $admin, Currency $bdt, Currency $usd): void
    {
        $product = Product::create([
            'product_code' => $data['code'],
            'name'         => $data['name'],
            'type'         => $data['type'],
            'description'  => $data['description'],
            'is_active'    => true,
            'created_by'   => $admin->id,
        ]);

        // Colors
        $colorModels = [];
        foreach ($data['colors'] as $colorData) {
            $colorModels[] = ProductColor::create([
                'product_id' => $product->id,
                'color_name' => $colorData['name'],
                'color_hex'  => $colorData['hex'],
            ]);
        }

        // Sizes
        $sizeModels = [];
        foreach ($data['sizes'] as $i => $label) {
            $sizeModels[] = ProductSize::create([
                'product_id' => $product->id,
                'size_label' => $label,
                'sort_order' => $i + 1,
            ]);
        }

        // Images — front + back per color
        foreach ($colorModels as $ci => $color) {
            $slug = strtolower(str_replace(' ', '-', $color->color_name));
            ProductImage::create([
                'product_id' => $product->id,
                'color_id'   => $color->id,
                'url'        => "/images/products/{$data['code']}/{$slug}-front.jpg",
                'alt_text'   => "{$product->name} — {$color->color_name}",
                'is_primary' => $ci === 0,
                'sort_order' => 1,
            ]);
            ProductImage::create([
                'product_id' => $product->id,
                'color_id'   => $color->id,
                'url'        => "/images/products/{$data['code']}/{$slug}-back.jpg",
                'alt_text'   => "{$product->name} — {$color->color_name} (Back)",
                'is_primary' => false,
                'sort_order' => 2,
            ]);
        }

        // Variants (color × size) + prices in BDT and USD
        $isFirstVariant = true;
        foreach ($colorModels as $color) {
            $colorCode = $this->colorCode($color->color_name);

            foreach ($sizeModels as $size) {
                $sku = "{$data['code']}-{$colorCode}-{$size->size_label}";

                $variant = ProductVariant::create([
                    'product_id' => $product->id,
                    'color_id'   => $color->id,
                    'size_id'    => $size->id,
                    'sku'        => $sku,
                    'is_default' => $isFirstVariant,
                    'stock_qty'  => rand(8, 60),
                    'is_active'  => true,
                    'updated_by' => $admin->id,
                    'updated_at' => now(),
                ]);

                // BDT — default price row for this variant
                ProductPrice::create([
                    'variant_id'     => $variant->id,
                    'currency_id'    => $bdt->id,
                    'actual_price'   => $data['price_bdt'],
                    'discount_type'  => $data['discount_type'] ?? null,
                    'discount_value' => $data['discount_value'] ?? 0,
                    'is_default'     => true,
                ]);

                // USD — carry percentage discounts across currencies; flat discounts are BDT-specific
                ProductPrice::create([
                    'variant_id'     => $variant->id,
                    'currency_id'    => $usd->id,
                    'actual_price'   => $data['price_usd'],
                    'discount_type'  => ($data['discount_type'] ?? null) === 'percent' ? 'percent' : null,
                    'discount_value' => ($data['discount_type'] ?? null) === 'percent' ? ($data['discount_value'] ?? 0) : 0,
                    'is_default'     => false,
                ]);

                $isFirstVariant = false;
            }
        }
    }

    /**
     * Produce a short color abbreviation for the SKU.
     * Multi-word: first char of word-1 + first two chars of word-2  →  "Sky Blue" = "SKB"
     * Single-word: first three chars                                →  "Navy"     = "NAV"
     */
    private function colorCode(string $name): string
    {
        $words = array_values(array_filter(explode(' ', trim($name))));

        if (count($words) >= 2) {
            return strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 2));
        }

        return strtoupper(substr($name, 0, 3));
    }

    // ─── Catalogue data ───────────────────────────────────────────────────────

    private function productsData(): array
    {
        return [

            // ── Men ─────────────────────────────────────────────────────────

            [
                'code'           => 'MO1001',
                'name'           => 'Classic Oxford Shirt',
                'type'           => 'man',
                'description'    => 'A timeless Oxford shirt woven from premium 100% combed cotton. Features a clean button-down collar, chest pocket, and a relaxed fit that transitions effortlessly from desk to weekend.',
                'colors'         => [
                    ['name' => 'White',      'hex' => '#FFFFFF'],
                    ['name' => 'Sky Blue',   'hex' => '#87CEEB'],
                    ['name' => 'Light Pink', 'hex' => '#FFB6C1'],
                ],
                'sizes'          => ['S', 'M', 'L', 'XL', 'XXL'],
                'price_bdt'      => 2200,
                'price_usd'      => 20,
                'discount_type'  => 'percent',
                'discount_value' => 10,
            ],

            [
                'code'           => 'MO1002',
                'name'           => 'Slim Fit Denim Jeans',
                'type'           => 'man',
                'description'    => 'Crafted from four-way stretch denim for all-day comfort, these slim fit jeans feature a tapered leg, five-pocket construction, and subtle whiskering for a lived-in look.',
                'colors'         => [
                    ['name' => 'Indigo Blue',  'hex' => '#3B4F8C'],
                    ['name' => 'Washed Black', 'hex' => '#2C2C2C'],
                ],
                'sizes'          => ['28', '30', '32', '34', '36'],
                'price_bdt'      => 3500,
                'price_usd'      => 32,
                'discount_type'  => null,
                'discount_value' => 0,
            ],

            [
                'code'           => 'MO1003',
                'name'           => 'Premium Polo Shirt',
                'type'           => 'man',
                'description'    => 'Our signature polo is knitted from piqué cotton for a refined texture. A ribbed collar, two-button placket, and structured silhouette make it a wardrobe staple.',
                'colors'         => [
                    ['name' => 'Navy',     'hex' => '#001F5B'],
                    ['name' => 'Olive',    'hex' => '#6B7B3A'],
                    ['name' => 'Burgundy', 'hex' => '#800020'],
                ],
                'sizes'          => ['S', 'M', 'L', 'XL', 'XXL'],
                'price_bdt'      => 1800,
                'price_usd'      => 16,
                'discount_type'  => 'percent',
                'discount_value' => 15,
            ],

            [
                'code'           => 'MO1010',
                'name'           => 'Cargo Chino Pants',
                'type'           => 'man',
                'description'    => 'Utility meets style in these relaxed cargo chinos. Crafted from lightweight cotton twill with multiple functional pockets and an adjustable waist for a versatile, all-day fit.',
                'colors'         => [
                    ['name' => 'Khaki',       'hex' => '#C3B091'],
                    ['name' => 'Olive Green', 'hex' => '#6B7B3A'],
                ],
                'sizes'          => ['28', '30', '32', '34', '36'],
                'price_bdt'      => 3200,
                'price_usd'      => 29,
                'discount_type'  => null,
                'discount_value' => 0,
            ],

            // ── Women ────────────────────────────────────────────────────────

            [
                'code'           => 'MO1004',
                'name'           => 'Summer Floral Dress',
                'type'           => 'women',
                'description'    => 'Light as a breeze, this midi dress is crafted from 100% viscose with an all-over floral print. Features a smocked bodice, adjustable straps, and a flowy A-line skirt — perfect for warm-weather occasions.',
                'colors'         => [
                    ['name' => 'Blush Pink', 'hex' => '#FFB7C5'],
                    ['name' => 'Ivory',      'hex' => '#FFFFF0'],
                ],
                'sizes'          => ['XS', 'S', 'M', 'L', 'XL'],
                'price_bdt'      => 4200,
                'price_usd'      => 38,
                'discount_type'  => null,
                'discount_value' => 0,
            ],

            [
                'code'           => 'MO1005',
                'name'           => 'Linen Relaxed Blouse',
                'type'           => 'women',
                'description'    => 'Effortlessly chic in 100% linen, this relaxed blouse features a V-neck, slightly dropped shoulders, and a flowy hem. Breathable and elegant — from brunch to boardroom.',
                'colors'         => [
                    ['name' => 'Cream',      'hex' => '#FFFDD0'],
                    ['name' => 'Sage Green', 'hex' => '#8CAF88'],
                    ['name' => 'Dusty Rose', 'hex' => '#DCAE96'],
                ],
                'sizes'          => ['XS', 'S', 'M', 'L', 'XL'],
                'price_bdt'      => 2800,
                'price_usd'      => 25,
                'discount_type'  => 'percent',
                'discount_value' => 10,
            ],

            [
                'code'           => 'MO1006',
                'name'           => 'Structured Tailored Blazer',
                'type'           => 'women',
                'description'    => 'A power-dressing essential. This single-breasted blazer is cut from a wool-blend with padded shoulders, a nipped waist, and welt pockets. Wear over a blouse or a tee for instant polish.',
                'colors'         => [
                    ['name' => 'Charcoal', 'hex' => '#36454F'],
                    ['name' => 'Ivory',    'hex' => '#FFFFF0'],
                ],
                'sizes'          => ['XS', 'S', 'M', 'L', 'XL'],
                'price_bdt'      => 6500,
                'price_usd'      => 59,
                'discount_type'  => null,
                'discount_value' => 0,
            ],

            [
                'code'           => 'MO1011',
                'name'           => 'Maxi Wrap Skirt',
                'type'           => 'women',
                'description'    => 'A versatile maxi skirt in fluid crêpe with a wrap front, adjustable tie waist, and a slight front slit for ease of movement. Pairs beautifully with tucked-in blouses or fitted tops.',
                'colors'         => [
                    ['name' => 'Terracotta',   'hex' => '#E2725B'],
                    ['name' => 'Forest Green', 'hex' => '#228B22'],
                ],
                'sizes'          => ['XS', 'S', 'M', 'L', 'XL'],
                'price_bdt'      => 3000,
                'price_usd'      => 27,
                'discount_type'  => 'percent',
                'discount_value' => 10,
            ],

            // ── Kids ─────────────────────────────────────────────────────────

            [
                'code'           => 'MO1007',
                'name'           => 'Kids Striped Crew Tee',
                'type'           => 'kids',
                'description'    => 'Fun, comfortable, and built to last. This crew-neck tee is knitted from soft ring-spun cotton with classic horizontal stripes and reinforced shoulder seams for active play.',
                'colors'         => [
                    ['name' => 'Red Stripe',  'hex' => '#CC0000'],
                    ['name' => 'Navy Stripe', 'hex' => '#000080'],
                ],
                'sizes'          => ['2Y', '4Y', '6Y', '8Y', '10Y'],
                'price_bdt'      => 850,
                'price_usd'      => 8,
                'discount_type'  => null,
                'discount_value' => 0,
            ],

            [
                'code'           => 'MO1008',
                'name'           => 'Kids Jogger Pants',
                'type'           => 'kids',
                'description'    => 'Cosy French-terry joggers with an elastic waistband, adjustable drawstring, ribbed ankle cuffs, and two side pockets. Ideal for school days and weekend adventures alike.',
                'colors'         => [
                    ['name' => 'Heather Gray', 'hex' => '#B6B6B4'],
                    ['name' => 'Navy',         'hex' => '#000080'],
                ],
                'sizes'          => ['2Y', '4Y', '6Y', '8Y', '10Y'],
                'price_bdt'      => 1200,
                'price_usd'      => 11,
                'discount_type'  => 'percent',
                'discount_value' => 5,
            ],

            // ── Unisex ───────────────────────────────────────────────────────

            [
                'code'           => 'MO1009',
                'name'           => 'Premium Fleece Hoodie',
                'type'           => 'unisex',
                'description'    => 'Wrapped in 380 gsm brushed fleece, this pullover hoodie features a kangaroo pocket, a jersey-lined hood, and ribbed cuffs and hem. A season-transcending layer for any gender.',
                'colors'         => [
                    ['name' => 'Black',        'hex' => '#000000'],
                    ['name' => 'Stone',        'hex' => '#928E85'],
                    ['name' => 'Forest Green', 'hex' => '#228B22'],
                ],
                'sizes'          => ['XS', 'S', 'M', 'L', 'XL', 'XXL'],
                'price_bdt'      => 3800,
                'price_usd'      => 35,
                'discount_type'  => 'percent',
                'discount_value' => 20,
            ],

            [
                'code'           => 'MO1012',
                'name'           => 'Oversized Graphic Tee',
                'type'           => 'unisex',
                'description'    => 'Our signature oversized tee in a drop-shoulder silhouette cut from 200 gsm heavyweight cotton. Features a subtle MOSSIM chest print and a boxy fit designed to be worn by everyone.',
                'colors'         => [
                    ['name' => 'White', 'hex' => '#FFFFFF'],
                    ['name' => 'Black', 'hex' => '#000000'],
                    ['name' => 'Sand',  'hex' => '#C2B280'],
                ],
                'sizes'          => ['XS', 'S', 'M', 'L', 'XL', 'XXL'],
                'price_bdt'      => 1400,
                'price_usd'      => 13,
                'discount_type'  => null,
                'discount_value' => 0,
            ],

        ];
    }
}
