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
        return 'Returns a list of products depending on given inputs and metrics.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $name = $request['name'];
        $quantity = $request['quantity'];
        $price = $request['price'];
        $quantityOperator = $request['quantityOperator'];
        $priceOperator = $request['priceOperator'];

        return Product::query()
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
            'name' => $schema->string()->nullable(),
            'price' => $schema->number()->nullable(),
            'quantity' => $schema->integer()->nullable(),
            'quantityOperator' => $schema->string()->enum(['>', '<', '>=', '<='])->nullable(),
            'priceOperator' => $schema->string()->enum(['>', '<', '>=', '<='])->nullable(),
        ];
    }
}
