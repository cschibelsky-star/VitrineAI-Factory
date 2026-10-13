<x-filament-panels::page>
    <form wire:submit="generate" class="space-y-6">
        {{ $this->form }}

        <x-filament::button type="submit" icon="heroicon-o-rocket-launch">
            Construir Projeto
        </x-filament::button>
    </form>
</x-filament-panels::page>
