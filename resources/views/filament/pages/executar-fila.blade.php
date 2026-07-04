<x-filament-panels::page>
    <div class="p-6 bg-white rounded-xl shadow">
        <h2 class="text-2xl font-bold mb-2">Fila de Provisionamento</h2>
        <p class="text-gray-600 mb-6">Executa todos os projetos com status de provisionamento pendente.</p>

        <x-filament::button wire:click="executarPendentes">
            Executar Pendentes
        </x-filament::button>
    </div>
</x-filament-panels::page>
