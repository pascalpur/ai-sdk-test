<?php

use App\Models\Product;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts.app', ['title' => 'Produkte'])] class extends Component {
    use WithPagination;

    public string $search = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function products()
    {
        return Product::query()
            ->with('category')
            ->when($this->search, fn($q) => $q->where('name', 'like', '%' . $this->search . '%'))
            ->orderBy('name')
            ->paginate(10);
    }

    public function delete(int $id): void
    {
        Product::findOrFail($id)->delete();
        $this->dispatch('product-deleted');
    }
}; ?>

<div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl">
    <div class="flex items-center justify-between">
        <flux:heading size="xl">{{ __('Produkte') }}</flux:heading>
        <flux:button variant="primary" :href="route('products.create')" wire:navigate icon="plus">
            {{ __('Erstellen') }}
        </flux:button>
    </div>

    <div class="flex items-center gap-4">
        <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" :placeholder="__('Suchen...')"
            class="max-w-sm" />
    </div>

    <div class="overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
        <flux:table>
            <flux:table.columns>
                <flux:table.column>{{ __('Name') }}</flux:table.column>
                <flux:table.column>{{ __('Kategorie') }}</flux:table.column>
                <flux:table.column>{{ __('Preis') }}</flux:table.column>
                <flux:table.column>{{ __('Menge') }}</flux:table.column>
                <flux:table.column class="w-24"></flux:table.column>
            </flux:table.columns>
            <flux:table.rows>
                @forelse($this->products as $product)
                    <flux:table.row>
                        <flux:table.cell class="font-medium">{{ $product->name }}</flux:table.cell>
                        <flux:table.cell>
                            <flux:badge size="sm" color="zinc">{{ $product->category?->name ?? '-' }}</flux:badge>
                        </flux:table.cell>
                        <flux:table.cell>{{ number_format((float) $product->price, 2, ',', '.') }} €</flux:table.cell>
                        <flux:table.cell>
                            <flux:badge size="sm" :color="$product->quantity > 0 ? 'green' : 'red'">
                                {{ $product->quantity }}
                            </flux:badge>
                        </flux:table.cell>
                        <flux:table.cell>
                            <div class="flex items-center gap-2">
                                <flux:button size="sm" variant="ghost" icon="pencil"
                                    :href="route('products.edit', $product->id)" wire:navigate />
                                <flux:button size="sm" variant="ghost" icon="trash" wire:click="delete({{ $product->id }})"
                                    wire:confirm="{{ __('Produkt wirklich löschen?') }}" />
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="5" class="text-center text-neutral-500 py-8">
                            {{ __('Keine Produkte vorhanden.') }}
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>

    <div class="mt-4">
        {{ $this->products->links() }}
    </div>
</div>