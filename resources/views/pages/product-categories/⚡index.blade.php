<?php

use App\Models\ProductCategory;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts.app', ['title' => 'Kategorien'])] class extends Component {
    use WithPagination;

    public string $search = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function categories()
    {
        return ProductCategory::query()
            ->when($this->search, fn($q) => $q->where('name', 'like', '%' . $this->search . '%'))
            ->withCount('products')
            ->orderBy('name')
            ->paginate(10);
    }

    public function delete(int $id): void
    {
        ProductCategory::findOrFail($id)->delete();
        $this->dispatch('category-deleted');
    }
}; ?>

<div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl">
    <div class="flex items-center justify-between">
        <flux:heading size="xl">{{ __('Kategorien') }}</flux:heading>
        <flux:button variant="primary" :href="route('product-categories.create')" wire:navigate icon="plus">
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
                <flux:table.column>{{ __('Beschreibung') }}</flux:table.column>
                <flux:table.column>{{ __('Produkte') }}</flux:table.column>
                <flux:table.column class="w-24"></flux:table.column>
            </flux:table.columns>
            <flux:table.rows>
                @forelse($this->categories as $category)
                    <flux:table.row>
                        <flux:table.cell class="font-medium">{{ $category->name }}</flux:table.cell>
                        <flux:table.cell class="text-neutral-500">{{ Str::limit($category->description, 50) }}
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:badge size="sm">{{ $category->products_count }}</flux:badge>
                        </flux:table.cell>
                        <flux:table.cell>
                            <div class="flex items-center gap-2">
                                <flux:button size="sm" variant="ghost" icon="pencil"
                                    :href="route('product-categories.edit', $category->id)" wire:navigate />
                                <flux:button size="sm" variant="ghost" icon="trash" wire:click="delete({{ $category->id }})"
                                    wire:confirm="{{ __('Kategorie wirklich löschen?') }}" />
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="4" class="text-center text-neutral-500 py-8">
                            {{ __('Keine Kategorien vorhanden.') }}
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>

    <div class="mt-4">
        {{ $this->categories->links() }}
    </div>
</div>