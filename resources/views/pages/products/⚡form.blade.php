<?php

use App\Models\Product;
use App\Models\ProductCategory;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;

new #[Layout('layouts.app', ['title' => 'Produkt'])] class extends Component {
    public ?int $productId = null;
    public bool $editMode = false;

    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('nullable|string|max:1000')]
    public ?string $description = '';

    #[Validate('required|numeric|min:0')]
    public string $price = '';

    #[Validate('required|integer|min:0')]
    public int $quantity = 0;

    #[Validate('required|exists:product_categories,id')]
    public string $product_category_id = '';

    public function mount(): void
    {
        $id = request()->route('id');

        if ($id) {
            $this->productId = (int) $id;
            $this->editMode = true;
            $product = Product::findOrFail($this->productId);
            $this->name = $product->name;
            $this->description = $product->description ?? '';
            $this->price = (string) $product->price;
            $this->quantity = $product->quantity;
            $this->product_category_id = (string) $product->product_category_id;
        }
    }

    #[Computed]
    public function categories()
    {
        return ProductCategory::orderBy('name')->get();
    }

    public function save(): void
    {
        $validated = $this->validate();
        $validated['price'] = (float) $validated['price'];

        if ($this->editMode) {
            $product = Product::findOrFail($this->productId);
            $product->update($validated);
            session()->flash('success', __('Produkt erfolgreich aktualisiert.'));
        } else {
            Product::create($validated);
            session()->flash('success', __('Produkt erfolgreich erstellt.'));
        }

        $this->redirect(route('products.index'), navigate: true);
    }
}; ?>

<div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl max-w-2xl">
    <div class="flex items-center gap-4">
        <flux:button variant="ghost" icon="arrow-left" :href="route('products.index')" wire:navigate />
        <flux:heading size="xl">
            {{ $editMode ? __('Produkt bearbeiten') : __('Produkt erstellen') }}
        </flux:heading>
    </div>

    @if(session('success'))
        <flux:callout variant="success">{{ session('success') }}</flux:callout>
    @endif

    <form wire:submit="save" class="space-y-6">
        <flux:input wire:model="name" :label="__('Name')" type="text" required autofocus
            :placeholder="__('Produktname eingeben')" />

        <flux:textarea wire:model="description" :label="__('Beschreibung')" rows="4"
            :placeholder="__('Optionale Beschreibung')" />

        <flux:select wire:model="product_category_id" :label="__('Kategorie')" required>
            <flux:select.option value="">{{ __('Kategorie wählen') }}</flux:select.option>
            @foreach($this->categories as $category)
                <flux:select.option :value="$category->id">{{ $category->name }}</flux:select.option>
            @endforeach
        </flux:select>

        <div class="grid grid-cols-2 gap-4">
            <flux:input wire:model="price" :label="__('Preis (€)')" type="number" step="0.01" min="0" required
                :placeholder="__('0.00')" />

            <flux:input wire:model="quantity" :label="__('Menge')" type="number" min="0" required
                :placeholder="__('0')" />
        </div>

        <div class="flex items-center gap-4">
            <flux:button variant="primary" type="submit">
                {{ $editMode ? __('Speichern') : __('Erstellen') }}
            </flux:button>

            <flux:button variant="ghost" :href="route('products.index')" wire:navigate>
                {{ __('Abbrechen') }}
            </flux:button>
        </div>
    </form>
</div>