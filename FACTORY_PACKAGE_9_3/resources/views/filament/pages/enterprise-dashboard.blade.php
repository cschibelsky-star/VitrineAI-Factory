<x-filament-panels::page>
    @php($stats = $this->stats ?? ['total'=>0,'completed'=>0,'running'=>0,'failed'=>0,'online'=>0,'templates'=>0])
    <div class="space-y-8">
        <section class="rounded-3xl bg-gradient-to-br from-slate-950 via-blue-950 to-cyan-900 p-8 text-white shadow-2xl">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <div class="text-xs font-bold uppercase tracking-[0.35em] text-cyan-300">Vitrine AI Factory</div>
                    <h1 class="mt-3 text-4xl font-black tracking-tight">Enterprise Control Center</h1>
                    <p class="mt-3 max-w-3xl text-slate-300">Centro executivo de provisionamento, deploy, templates, health check e operação dos produtos Vitrine AI Pro.</p>
                </div>
                <div class="rounded-2xl border border-emerald-400/30 bg-emerald-400/10 px-6 py-4 text-right">
                    <div class="text-xs uppercase tracking-widest text-emerald-200">Plataforma</div>
                    <div class="mt-1 text-2xl font-black text-emerald-300">Operacional</div>
                </div>
            </div>
        </section>

        <section class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-6">
            @foreach ([
                ['Projetos', $stats['total'] ?? 0, 'bg-slate-950 text-white'],
                ['Concluídos', $stats['completed'] ?? 0, 'bg-emerald-600 text-white'],
                ['Executando', $stats['running'] ?? 0, 'bg-amber-500 text-white'],
                ['Falhas', $stats['failed'] ?? 0, 'bg-red-600 text-white'],
                ['Online', $stats['online'] ?? 0, 'bg-blue-600 text-white'],
                ['Templates', $stats['templates'] ?? 0, 'bg-cyan-600 text-white'],
            ] as $card)
                <div class="rounded-2xl {{ $card[2] }} p-5 shadow-xl">
                    <div class="text-xs font-bold uppercase tracking-widest opacity-80">{{ $card[0] }}</div>
                    <div class="mt-4 text-4xl font-black">{{ $card[1] }}</div>
                </div>
            @endforeach
        </section>

        <section class="grid grid-cols-1 gap-6 xl:grid-cols-3">
            <div class="xl:col-span-2 rounded-3xl border bg-white p-6 shadow-xl">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-2xl font-black text-slate-900">Operações Recentes</h2>
                        <p class="text-sm text-slate-500">Projetos e provisionamentos monitorados pela Factory.</p>
                    </div>
                    <span class="rounded-full bg-slate-100 px-4 py-2 text-xs font-bold text-slate-600">LIVE OPS</span>
                </div>
                <div class="mt-6 space-y-3">
                    @foreach (($this->projects ?? collect()) as $project)
                        <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
                            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                                <div>
                                    <div class="text-lg font-black text-slate-900">{{ $project->name }}</div>
                                    <div class="text-sm text-slate-500">{{ $project->product }} · {{ $project->domain }}</div>
                                </div>
                                <div class="flex gap-2 text-xs font-bold uppercase">
                                    <span class="rounded-full px-3 py-1 {{ $project->provisioning_status === 'completed' ? 'bg-emerald-100 text-emerald-700' : ($project->provisioning_status === 'failed' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700') }}">{{ $project->provisioning_status }}</span>
                                    <span class="rounded-full bg-blue-100 px-3 py-1 text-blue-700">{{ $project->environment }}</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="rounded-3xl bg-slate-950 p-6 text-white shadow-xl">
                <h2 class="text-2xl font-black">Factory Brain</h2>
                <p class="mt-2 text-sm text-slate-400">Camada preparada para orquestrar marketing, vendas, deploy, suporte e operação.</p>
                <div class="mt-6 space-y-3 text-sm">
                    <div class="rounded-xl bg-white/10 p-3">Marketing IA · Preparado</div>
                    <div class="rounded-xl bg-white/10 p-3">Comercial IA · Preparado</div>
                    <div class="rounded-xl bg-white/10 p-3">Deploy Engine · Ativo</div>
                    <div class="rounded-xl bg-white/10 p-3">Health Check · Ativo</div>
                    <div class="rounded-xl bg-white/10 p-3">Integration Engine · Próximo pacote</div>
                </div>
            </div>
        </section>
    </div>
</x-filament-panels::page>
