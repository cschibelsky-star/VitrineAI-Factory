<x-filament-panels::page>
    <div class="space-y-6">
        <div class="rounded-2xl bg-slate-950 p-8 text-white shadow">
            <div class="text-xs uppercase tracking-[0.35em] text-cyan-300">Vitrine AI Factory</div>
            <h1 class="mt-3 text-3xl font-bold">Enterprise Events Center</h1>
            <p class="mt-2 text-slate-300">Barramento interno para eventos entre Site, CRM, Comercial, Financeiro, Factory, Deploy e Factory Brain.</p>
            <div class="mt-5">
                <x-filament::button wire:click="gerarEventoTeste" icon="heroicon-o-bolt">
                    Gerar Evento de Teste
                </x-filament::button>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="rounded-xl bg-white p-5 shadow border"><div class="text-sm text-gray-500">Eventos</div><div class="text-3xl font-bold">{{ $this->events->count() }}</div></div>
            <div class="rounded-xl bg-white p-5 shadow border"><div class="text-sm text-gray-500">Origem</div><div class="text-xl font-bold">Factory</div></div>
            <div class="rounded-xl bg-white p-5 shadow border"><div class="text-sm text-gray-500">Destino</div><div class="text-xl font-bold">Brain</div></div>
            <div class="rounded-xl bg-white p-5 shadow border"><div class="text-sm text-gray-500">Status</div><div class="text-xl font-bold text-green-600">Ativo</div></div>
        </div>

        <div class="rounded-2xl bg-white shadow border overflow-hidden">
            <div class="p-5 border-b">
                <h2 class="text-xl font-bold">Últimos eventos</h2>
                <p class="text-sm text-gray-500">Base inicial do Enterprise Integration Engine.</p>
            </div>
            <div class="divide-y">
                @forelse ($this->events as $event)
                    <div class="p-4 flex items-start justify-between gap-4">
                        <div>
                            <div class="font-bold">{{ $event->event }}</div>
                            <div class="text-sm text-gray-500">{{ $event->source }} → {{ $event->target ?: 'Sem destino' }}</div>
                            <div class="text-sm mt-1">{{ $event->message }}</div>
                        </div>
                        <div class="text-right">
                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-700">{{ $event->status }}</span>
                            <div class="text-xs text-gray-500 mt-2">{{ $event->created_at?->format('d/m/Y H:i') }}</div>
                        </div>
                    </div>
                @empty
                    <div class="p-6 text-gray-500">Nenhum evento registrado ainda.</div>
                @endforelse
            </div>
        </div>
    </div>
</x-filament-panels::page>
