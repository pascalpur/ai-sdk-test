<?php

namespace App\Ai\Tools;

use App\Models\ProductCategory;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Log;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class GetProductCategory implements Tool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Find a single product category by ID or name. Returns the category if found, or an error message with available categories if not found.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        Log::info('Kategorie');
        $categoryId = $request['categoryId'];
        $categoryName = $request['categoryName'];

        if (! $categoryId && ! $categoryName) {
            return 'Please provide either categoryId or categoryName.';
        }

        $category = null;

        if ($categoryId) {
            $category = ProductCategory::find($categoryId);
        } elseif ($categoryName) {
            $category = ProductCategory::where('name', $categoryName)->first();
        }

        if (! $category) {
            return 'Category not found. Current Categories are: '.$this->getProductCategories();
        }

        return json_encode([
            'id' => $category->id,
            'name' => $category->name,
        ]);
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'categoryId' => $schema->integer()->nullable(),
            'categoryName' => $schema->string()->nullable(),
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
