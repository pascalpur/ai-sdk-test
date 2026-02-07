<?php

use App\Models\ProductCategory;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;

new #[Layout('layouts.app', ['title' => 'Kategorie'])] class extends Component {
    public ?int $categoryId = null;
    public bool $editMode = false;

    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('nullable|string|max:1000')]
    public ?string $description = '';

    public function mount(): void
    {
        $id = request()->route('id');

        if ($id) {
            $this->categoryId = (int) $id;
            $this->editMode = true;
            $category = ProductCategory::findOrFail($this->categoryId);
            $this->name = $category->name;
            $this->description = $category->description ?? '';
        }
    }

    public function save(): void
    {
        $validated = $this->validate();

        if ($this->editMode) {
            $category = ProductCategory::findOrFail($this->categoryId);
            $category->update($validated);
            session()->flash('success', __('Kategorie erfolgreich aktualisiert.'));
        } else {
            ProductCategory::create($validated);
            session()->flash('success', __('Kategorie erfolgreich erstellt.'));
        }

        $this->redirect(route('product-categories.index'), navigate: true);
    }
}; ?>

<div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl max-w-2xl">
    <div class="flex items-center gap-4">
        <flux:button variant="ghost" icon="arrow-left" :href="route('product-categories.index')" wire:navigate />
        <flux:heading size="xl">
            {{ $editMode ? __('Kategorie bearbeiten') : __('Kategorie erstellen') }}
        </flux:heading>
    </div>

    @if(session('success'))
        <flux:callout variant="success">{{ session('success') }}</flux:callout>
    @endif

    <form wire:submit="save" class="space-y-6">
        <flux:input wire:model="name" :label="__('Name')" type="text" required autofocus
            :placeholder="__('Kategoriename eingeben')" />

        <flux:textarea wire:model="description" :label="__('Beschreibung')" rows="4"
            :placeholder="__('Optionale Beschreibung')" />

        <div class="flex items-center gap-4">
            <flux:button variant="primary" type="submit">
                {{ $editMode ? __('Speichern') : __('Erstellen') }}
            </flux:button>

            <flux:button variant="ghost" :href="route('product-categories.index')" wire:navigate>
                {{ __('Abbrechen') }}
            </flux:button>
        </div>
    </form>
</div>