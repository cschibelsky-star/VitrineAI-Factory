<x-filament-panels::page>
    <form wire:submit="provisionar" class="space-y-6">
        {{ $this->form }}

        <x-filament::button type="submit" size="lg" icon="heroicon-o-rocket-launch">
            Criar e Provisionar
        </x-filament::button>
    </form>
</x-filament-panels::page>
