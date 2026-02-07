<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = ProductCategory::all();

        if ($categories->isEmpty()) {
            $categories = ProductCategory::factory(5)->create();
        }

        foreach ($categories as $category) {
            Product::factory(4)->forCategory($category)->create();
        }
    }
}
