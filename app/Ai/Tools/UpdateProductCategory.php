<?php

namespace App\Ai\Tools;

use App\Models\ProductCategory;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class UpdateProductCategory implements Tool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Updates a product category.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $id = $request['id'];
        $name = $request['name'];
        $description = $request['description'];

        $category = ProductCategory::find($id);

        if (! $category) {
            return 'Product category not found.';
        }

        $category->name = $name;

        if($description) {
            $category->description = $description;
        }

        $category->save();

        return 'Product category updated.';
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()->required(),
            'name' => $schema->string()->required(),
            'description' => $schema->string()->nullable(),
        ];
    }
}
