<x-filament-panels::page>
    @php($stats = $this->stats)

    <div class="space-y-6">
        <div class="rounded-3xl bg-slate-950 text-white p-8 shadow-xl">
            <div class="text-xs tracking-[0.4em] uppercase text-cyan-300">Vitrine AI Factory</div>
            <div class="mt-3 flex flex-col md:flex-row md:items-end md:justify-between gap-4">
                <div>
                    <h1 class="text-4xl font-black">Operations Center</h1>
                    <p class="mt-2 text-slate-300">Centro operacional para deploys, provisionamento, health check e monitoramento do ecossistema.</p>
                </div>
                <div class="rounded-2xl bg-emerald-500/10 border border-emerald-400/30 px-5 py-3">
                    <div class="text-xs text-emerald-200 uppercase tracking-widest">Platform Health</div>
                    <div class="text-2xl font-black">{{ $stats['failed'] > 0 ? 'Atenção' : 'Operacional' }}</div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div class="rounded-2xl bg-white border shadow p-5"><div class="text-sm text-slate-500">Projetos</div><div class="text-3xl font-black">{{ $stats['projects'] }}</div></div>
            <div class="rounded-2xl bg-white border shadow p-5"><div class="text-sm text-slate-500">Concluídos</div><div class="text-3xl font-black text-emerald-600">{{ $stats['completed'] }}</div></div>
            <div class="rounded-2xl bg-white border shadow p-5"><div class="text-sm text-slate-500">Falhas</div><div class="text-3xl font-black text-rose-600">{{ $stats['failed'] }}</div></div>
            <div class="rounded-2xl bg-white border shadow p-5"><div class="text-sm text-slate-500">Online</div><div class="text-3xl font-black text-blue-600">{{ $stats['online'] }}</div></div>
            <div class="rounded-2xl bg-white border shadow p-5"><div class="text-sm text-slate-500">Templates</div><div class="text-3xl font-black">{{ $stats['templates'] }}</div></div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
            <div class="xl:col-span-2 rounded-3xl bg-white border shadow overflow-hidden">
                <div class="p-6 border-b">
                    <h2 class="text-2xl font-black">Ambientes Monitorados</h2>
                    <p class="text-sm text-slate-500">Projetos com status operacional e ações rápidas.</p>
                </div>
                <div class="divide-y">
                    @foreach ($this->projects as $project)
                        <div class="p-5 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                            <div>
                                <div class="font-black text-lg">{{ $project->name }}</div>
                                <div class="text-sm text-slate-500">{{ $project->product }} · {{ $project->environment }} · {{ $project->domain }}</div>
                            </div>
                            <div class="flex items-center gap-2 text-sm">
                                <span class="px-3 py-1 rounded-full bg-slate-100 font-bold">{{ $project->provisioning_status }}</span>
                                <span class="px-3 py-1 rounded-full bg-slate-100 font-bold">{{ $project->health_status ?? 'unknown' }}</span>
                                @if($project->domain)
                                    <a href="https://{{ $project->domain }}" target="_blank" class="px-3 py-1 rounded-full bg-slate-950 text-white font-bold">Abrir</a>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="rounded-3xl bg-white border shadow p-6">
                <h2 class="text-2xl font-black">Factory Brain</h2>
                <p class="text-sm text-slate-500 mt-1">Recomendações operacionais automáticas.</p>

                <div class="mt-5 space-y-3 text-sm">
                    @if($stats['failed'] > 0)
                        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-100 text-rose-700 font-semibold">Existem provisionamentos com falha. Verificar logs e health check.</div>
                    @endif
                    @if($stats['online'] === 0)
                        <div class="p-4 rounded-2xl bg-amber-50 border border-amber-100 text-amber-700 font-semibold">Nenhum ambiente online detectado. Validar DNS e cPanel.</div>
                    @endif
                    <div class="p-4 rounded-2xl bg-blue-50 border border-blue-100 text-blue-700 font-semibold">Próximo marco: integrar Site → CRM → Financeiro → Factory.</div>
                    <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-100 text-emerald-700 font-semibold">Engine de provisionamento ativa e pronta para integração.</div>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
