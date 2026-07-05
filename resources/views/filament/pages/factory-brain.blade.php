<x-filament-panels::page>
    @php($insights = $this->insights)

    <div class="space-y-6">
        <div class="rounded-3xl bg-slate-950 p-8 text-white shadow-xl">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <div class="text-xs font-bold uppercase tracking-[0.35em] text-cyan-300">Factory Brain</div>
                    <h1 class="mt-3 text-4xl font-black tracking-tight">Centro de Inteligência Operacional</h1>
                    <p class="mt-3 max-w-3xl text-sm text-slate-300">
                        Diagnóstico automatizado da VitrineAI Factory: provisionamento, health, cPanel, deploys, templates e recomendações executivas.
                    </p>
                </div>
                <div class="rounded-2xl border border-emerald-400/30 bg-emerald-400/10 px-5 py-3 text-sm font-bold text-emerald-200">
                    IA Operacional Ativa
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-7">
            <div class="rounded-2xl border bg-white p-5 shadow-sm"><div class="text-xs font-bold uppercase text-slate-500">Projetos</div><div class="mt-2 text-3xl font-black">{{ $insights['total_projects'] }}</div></div>
            <div class="rounded-2xl border bg-white p-5 shadow-sm"><div class="text-xs font-bold uppercase text-slate-500">Concluídos</div><div class="mt-2 text-3xl font-black text-emerald-600">{{ $insights['completed'] }}</div></div>
            <div class="rounded-2xl border bg-white p-5 shadow-sm"><div class="text-xs font-bold uppercase text-slate-500">Falhas</div><div class="mt-2 text-3xl font-black text-red-600">{{ $insights['failed'] }}</div></div>
            <div class="rounded-2xl border bg-white p-5 shadow-sm"><div class="text-xs font-bold uppercase text-slate-500">Executando</div><div class="mt-2 text-3xl font-black text-amber-600">{{ $insights['running'] }}</div></div>
            <div class="rounded-2xl border bg-white p-5 shadow-sm"><div class="text-xs font-bold uppercase text-slate-500">Online</div><div class="mt-2 text-3xl font-black text-blue-600">{{ $insights['online'] }}</div></div>
            <div class="rounded-2xl border bg-white p-5 shadow-sm"><div class="text-xs font-bold uppercase text-slate-500">Offline</div><div class="mt-2 text-3xl font-black text-orange-600">{{ $insights['offline'] }}</div></div>
            <div class="rounded-2xl border bg-white p-5 shadow-sm"><div class="text-xs font-bold uppercase text-slate-500">Templates</div><div class="mt-2 text-3xl font-black">{{ $insights['templates'] }}</div></div>
        </div>

        <div class="rounded-3xl border bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-black">Ações recomendadas</h2>
                    <p class="text-sm text-slate-500">Prioridades globais detectadas automaticamente.</p>
                </div>
                <span class="rounded-full bg-slate-950 px-4 py-2 text-xs font-bold uppercase text-white">Executive Brief</span>
            </div>

            <div class="mt-5 grid grid-cols-1 gap-4 md:grid-cols-3">
                @foreach ($insights['actions'] as $action)
                    <div class="rounded-2xl border p-5
                        @if($action['level'] === 'critical') bg-red-50 border-red-200
                        @elseif($action['level'] === 'warning') bg-amber-50 border-amber-200
                        @elseif($action['level'] === 'success') bg-emerald-50 border-emerald-200
                        @else bg-blue-50 border-blue-200 @endif">
                        <div class="text-sm font-black">{{ $action['title'] }}</div>
                        <p class="mt-2 text-sm text-slate-600">{{ $action['message'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="rounded-3xl border bg-white p-6 shadow-sm">
            <div>
                <h2 class="text-2xl font-black">Diagnóstico por projeto</h2>
                <p class="text-sm text-slate-500">Recomendações específicas para cada ambiente monitorado.</p>
            </div>

            <div class="mt-5 space-y-4">
                @foreach ($this->projects as $project)
                    <div class="rounded-2xl border p-5">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div>
                                <h3 class="text-xl font-black">{{ $project->name }}</h3>
                                <p class="text-sm text-slate-500">{{ $project->product }} • {{ $project->domain }} • {{ $project->environment }}</p>
                            </div>
                            <div class="flex gap-2 text-xs font-bold uppercase">
                                <span class="rounded-full bg-slate-100 px-3 py-1">{{ $project->provisioning_status ?? 'pending' }}</span>
                                <span class="rounded-full bg-slate-100 px-3 py-1">{{ $project->health_status ?? 'unknown' }}</span>
                            </div>
                        </div>

                        <div class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-3">
                            @foreach ($this->recommendationsFor($project) as $item)
                                <div class="rounded-xl border p-4
                                    @if($item['level'] === 'critical') bg-red-50 border-red-200
                                    @elseif($item['level'] === 'warning') bg-amber-50 border-amber-200
                                    @elseif($item['level'] === 'success') bg-emerald-50 border-emerald-200
                                    @else bg-sky-50 border-sky-200 @endif">
                                    <div class="text-sm font-black">{{ $item['title'] }}</div>
                                    <p class="mt-1 text-xs text-slate-600">{{ $item['message'] }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-filament-panels::page>
