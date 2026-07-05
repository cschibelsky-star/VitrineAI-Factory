<x-filament-panels::page>
    <div class="space-y-6">
        <div class="rounded-2xl bg-gradient-to-r from-slate-950 via-indigo-950 to-slate-900 p-8 text-white shadow">
            <div class="text-xs uppercase tracking-[0.35em] text-indigo-300">Factory Intelligence</div>
            <h1 class="mt-3 text-3xl font-bold">Factory Brain Command Center</h1>
            <p class="mt-2 text-slate-300">Camada inicial de diagnóstico e recomendações operacionais do ecossistema Vitrine AI Pro.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @foreach ($this->insights as $item)
                <div class="rounded-2xl bg-white p-5 shadow border">
                    <div class="text-xs uppercase tracking-wider text-gray-400">{{ $item['level'] }}</div>
                    <h2 class="mt-2 text-xl font-bold">{{ $item['title'] }}</h2>
                    <p class="mt-2 text-sm text-gray-600">{{ $item['message'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</x-filament-panels::page>
