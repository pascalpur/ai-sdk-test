<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => ucfirst(fake()->words(3, true)),
            'description' => fake()->paragraph(),
            'price' => fake()->randomFloat(2, 1, 500),
            'quantity' => fake()->numberBetween(0, 100),
            'product_category_id' => ProductCategory::factory(),
        ];
    }

    /**
     * Set a specific category for the product.
     */
    public function forCategory(ProductCategory $category): static
    {
        return $this->state(fn(array $attributes) => [
            'product_category_id' => $category->id,
        ]);
    }
}
