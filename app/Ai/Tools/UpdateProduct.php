<?php

namespace App\Ai\Tools;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class UpdateProduct implements Tool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Updates an existing product.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $productId = $request['productId'];
        $name = $request['name'];
        $description = $request['description'];
        $price = $request['price'];
        $quantity = $request['quantity'];
        $categoryId = $request['categoryId'];

        $product = Product::find($productId);

        if (! $product) {
            return 'Product not found.';
        }

        if ($categoryId) {
            $category = ProductCategory::find($categoryId);

            if (! $category) {
                return 'Category not found.';
            }
        }

        $product->update([
            'name' => $name ?? $product->name,
            'description' => $description ?? $product->description,
            'price' => $price ?? $product->price,
            'quantity' => $quantity ?? $product->quantity,
            'product_category_id' => $categoryId ?? $product->product_category_id,
        ]);

        return 'Product was updated. '.$product->fresh()->toJson();
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'productId' => $schema->integer(),
            'name' => $schema->string()->nullable(),
            'description' => $schema->string()->nullable(),
            'price' => $schema->number()->nullable(),
            'quantity' => $schema->integer()->nullable(),
            'categoryId' => $schema->integer()->nullable(),
        ];
    }
}
