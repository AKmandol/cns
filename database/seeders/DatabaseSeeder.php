<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Demo Admin',
                'password' => 'password',
                'role' => 'admin',
            ],
        );

        $ownerOne = User::updateOrCreate(
            ['email' => 'owner1@example.com'],
            [
                'name' => 'Demo Shop Owner One',
                'password' => 'password',
                'role' => 'owner',
            ],
        );

        $ownerTwo = User::updateOrCreate(
            ['email' => 'owner2@example.com'],
            [
                'name' => 'Demo Shop Owner Two',
                'password' => 'password',
                'role' => 'owner',
            ],
        );

        $shopOne = $ownerOne->shops()->updateOrCreate(
            ['slug' => 'demo-electronics'],
            [
                'name' => 'Demo Electronics',
                'description' => 'A sample electronics shop.',
                'address' => 'Dhaka',
                'is_active' => true,
            ],
        );

        $shopTwo = $ownerTwo->shops()->updateOrCreate(
            ['slug' => 'demo-home-store'],
            [
                'name' => 'Demo Home Store',
                'description' => 'A sample home goods shop.',
                'address' => 'Chattogram',
                'is_active' => true,
            ],
        );

        $products = collect([
            [
                'sku' => 'DEMO-MUG-001',
                'name' => 'Ceramic Coffee Mug',
                'description' => 'A reusable ceramic coffee mug.',
                'created_by' => $admin->id,
            ],
            [
                'sku' => 'DEMO-HEADPHONE-001',
                'name' => 'Wired Headphones',
                'description' => 'Lightweight over-ear wired headphones.',
                'created_by' => $ownerOne->id,
            ],
            [
                'sku' => 'DEMO-LAMP-001',
                'name' => 'Desk Lamp',
                'description' => 'An adjustable LED desk lamp.',
                'created_by' => $ownerTwo->id,
            ],
        ])->mapWithKeys(function (array $attributes) {
            $product = \App\Models\Product::updateOrCreate(
                ['sku' => $attributes['sku']],
                $attributes,
            );

            return [$attributes['sku'] => $product];
        });

        $listings = [
            [$shopOne, $products['DEMO-MUG-001'], '350.00', 'Classic mug for daily coffee.', 'products/demo-mug.jpg'],
            [$shopOne, $products['DEMO-HEADPHONE-001'], '1800.00', 'Comfortable headphones with clear sound.', 'products/demo-headphones.jpg'],
            [$shopTwo, $products['DEMO-MUG-001'], '400.00', 'A sturdy mug for your home or office.', 'products/demo-mug.jpg'],
            [$shopTwo, $products['DEMO-LAMP-001'], '2200.00', 'Warm, adjustable light for your desk.', 'products/demo-lamp.jpg'],
        ];

        foreach ($listings as [$shop, $product, $price, $description, $thumbnail]) {
            $shop->listings()->updateOrCreate(
                ['product_id' => $product->id],
                [
                    'price' => $price,
                    'description' => $description,
                    'thumbnail' => $thumbnail,
                    'is_active' => true,
                ],
            );
        }
    }
}
