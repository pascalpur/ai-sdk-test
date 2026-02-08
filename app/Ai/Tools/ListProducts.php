<?php

namespace App\Ai\Tools;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class ListProducts implements Tool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Returns a list of products or a single product depending on given inputs and metrics.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $id = $request['id'];
        $name = $request['name'];
        $quantity = $request['quantity'];
        $price = $request['price'];
        $quantityOperator = $request['quantityOperator'];
        $priceOperator = $request['priceOperator'];
        $categoryName = $request['categoryName'];
        $categoryId = $request['categoryId'];

        $category = null;


        if ($categoryId) {
            $category = ProductCategory::find($categoryId);

            if (! $category) {
                return "Category not found. Current Categories are: " . $this->getProductCategories();
            }
        }
        if ($categoryName) {
            $category = ProductCategory::where('name', $categoryName)->first();

            if (! $category) {
                return "Category not found. Current Categories are: " . $this->getProductCategories();
            }
        }

        return Product::query()
            ->when($id, function ($query, $id) {
                return $query->where('id', $id);
            })
            ->when($name, function ($query, $name) {
                $query->where('name', 'like', "%{$name}%");
            })
            ->when($quantity !== null, function ($query) use ($quantity, $quantityOperator) {
                $query->where(
                    'quantity',
                    $quantityOperator ?? '=',
                    $quantity
                );
            })
            ->when($price !== null, function ($query) use ($price, $priceOperator) {
                $query->where(
                    'price',
                    $priceOperator ?? '=',
                    $price
                );
            })
            ->when($category !== null, function ($query) use ($category) {
                return $query->where('product_category_id', $category->id);
            })
            ->get()
            ->map(function (Product $product) {
                return [
                    'name' => $product->name,
                    'description' => $product->description,
                    'price' => $product->price,
                    'quantity' => $product->quantity,
                ];
            })
            ->toJson();
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()->nullable(),
            'name' => $schema->string()->nullable(),
            'price' => $schema->number()->nullable(),
            'quantity' => $schema->integer()->nullable(),
            'quantityOperator' => $schema->string()->enum(['>', '<', '>=', '<='])->nullable(),
            'priceOperator' => $schema->string()->enum(['>', '<', '>=', '<='])->nullable(),
            'categoryName' => $schema->string()->nullable(),
            'categoryId' => $schema->integer()->nullable(),
        ];
    }

    private function getProductCategories(): string
    {
        return ProductCategory::query()
            ->get()
            ->map(function (ProductCategory $category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                ];
            })
            ->toJson();
    }
}
