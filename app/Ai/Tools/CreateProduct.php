<?php

namespace App\Ai\Tools;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class CreateProduct implements Tool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Legt ein neues Produkt in der Datenbank an. Frage den Benutzer nach allen erforderlichen Werten bevor du dieses Tool aufrufst.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $category = ProductCategory::where('name', 'like', '%'.$request['category'].'%')->first();

        if (! $category) {
            $availableCategories = ProductCategory::pluck('name')->implode(', ');

            return "Die Kategorie '{$request['category']}' wurde nicht gefunden. Verfügbare Kategorien: {$availableCategories}";
        }

        $product = Product::create([
            'name' => $request['name'],
            'description' => $request['description'] ?? null,
            'price' => $request['price'],
            'quantity' => $request['quantity'] ?? 0,
            'product_category_id' => $category->id,
        ]);

        return "Produkt '{$product->name}' wurde erfolgreich angelegt! (ID: {$product->id}, Preis: {$product->price}€, Kategorie: {$category->name})";
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'name' => $schema->string(),
            'description' => $schema->string()->nullable(),
            'price' => $schema->number(),
            'quantity' => $schema->integer()->nullable(),
            'category' => $schema->string(),
        ];
    }
}
