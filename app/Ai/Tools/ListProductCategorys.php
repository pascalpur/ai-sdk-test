<?php

namespace App\Ai\Tools;

use App\Models\ProductCategory;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class ListProductCategorys implements Tool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Returns all product categories';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        return ProductCategory::query()
            ->get()
            ->toJson();
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
        ];
    }
}
