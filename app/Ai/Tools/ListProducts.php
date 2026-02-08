<?php

namespace App\Ai\Tools;

use App\Models\Product;
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
        return 'Returns a list of products depending on a given input.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $name = $request['name'];
        $quantity = $request['quantity'];
        $price = $request['price'];

        return Product::query()
            ->when($name, function ($query, $name) {

                return $query->where('name', 'like', "%{$name}%");
            })
            ->when($quantity, function ($query, $quantity) {
                return $query->where('quantity', $quantity);
            })
            ->when($price, function ($query, $price) {
                return $query->where('price', $price);
            })
            ->get()
            ->map(function (Product $product) {
                return [
                    'name' => $product->name,
                    'description' => $product->description,
                    'price' => $product->price,
                    'quantity' => $product->quantity,
                ];
            });
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'name' => $schema->string()->nullable(),
            'price' => $schema->number()->nullable(),
            'quantity' => $schema->integer()->nullable(),
        ];
    }
}
