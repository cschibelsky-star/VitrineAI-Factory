<x-filament-panels::page>
    @php($stats = $this->stats)
    <div class="space-y-6">
        <div class="rounded-3xl bg-slate-950 p-8 text-white shadow-xl">
            <div class="text-xs uppercase tracking-[0.35em] text-cyan-300">DevOps Center</div>
            <h1 class="mt-3 text-3xl font-black">Operação técnica do ecossistema</h1>
            <p class="mt-2 text-slate-300">Deploys, provisionamentos, saúde dos ambientes e templates operacionais.</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
            <div class="rounded-2xl bg-white p-5 shadow border"><div class="text-xs uppercase text-gray-500">Projetos</div><div class="text-3xl font-black">{{ $stats['total'] }}</div></div>
            <div class="rounded-2xl bg-white p-5 shadow border"><div class="text-xs uppercase text-gray-500">Concluídos</div><div class="text-3xl font-black text-green-600">{{ $stats['completed'] }}</div></div>
            <div class="rounded-2xl bg-white p-5 shadow border"><div class="text-xs uppercase text-gray-500">Executando</div><div class="text-3xl font-black text-yellow-600">{{ $stats['running'] }}</div></div>
            <div class="rounded-2xl bg-white p-5 shadow border"><div class="text-xs uppercase text-gray-500">Falhas</div><div class="text-3xl font-black text-red-600">{{ $stats['failed'] }}</div></div>
            <div class="rounded-2xl bg-white p-5 shadow border"><div class="text-xs uppercase text-gray-500">Online</div><div class="text-3xl font-black text-blue-600">{{ $stats['online'] }}</div></div>
            <div class="rounded-2xl bg-white p-5 shadow border"><div class="text-xs uppercase text-gray-500">Templates</div><div class="text-3xl font-black">{{ $stats['templates'] }}</div></div>
        </div>
    </div>
</x-filament-panels::page>
