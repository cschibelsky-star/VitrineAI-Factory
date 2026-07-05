<x-filament-panels::page>
    @php($stats = $this->stats)

    <div class="space-y-6">
        <div class="rounded-3xl bg-slate-950 text-white p-8 shadow-xl">
            <div class="text-xs uppercase tracking-[0.35em] text-cyan-300 font-semibold">Factory Workspace</div>
            <div class="mt-3 flex flex-col md:flex-row md:items-end md:justify-between gap-4">
                <div>
                    <h1 class="text-3xl md:text-4xl font-black">Projetos Enterprise</h1>
                    <p class="mt-2 text-slate-300">Ambientes provisionados, deploys, health check e operação por cliente.</p>
                </div>
                <div class="rounded-2xl bg-emerald-500/15 border border-emerald-400/30 px-5 py-3 text-emerald-200 font-bold">
                    LIVE OPERATIONS
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="rounded-2xl bg-white p-5 shadow border"><div class="text-sm text-slate-500">Projetos</div><div class="text-3xl font-black">{{ $stats['total'] }}</div></div>
            <div class="rounded-2xl bg-white p-5 shadow border"><div class="text-sm text-slate-500">Concluídos</div><div class="text-3xl font-black text-emerald-600">{{ $stats['completed'] }}</div></div>
            <div class="rounded-2xl bg-white p-5 shadow border"><div class="text-sm text-slate-500">Falhas</div><div class="text-3xl font-black text-red-600">{{ $stats['failed'] }}</div></div>
            <div class="rounded-2xl bg-white p-5 shadow border"><div class="text-sm text-slate-500">Online</div><div class="text-3xl font-black text-cyan-600">{{ $stats['online'] }}</div></div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-5">
            @foreach ($this->projects as $project)
                <div class="rounded-3xl bg-white shadow-lg border overflow-hidden">
                    <div class="p-5 bg-gradient-to-r from-slate-950 to-blue-950 text-white">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <div class="text-xs uppercase tracking-widest text-cyan-200">{{ $project->product ?: 'Produto' }}</div>
                                <h2 class="text-2xl font-black mt-1">{{ $project->name }}</h2>
                                <p class="text-sm text-slate-300 mt-1">{{ $project->client_name ?: 'Sem cliente' }} · {{ $project->environment }}</p>
                            </div>
                            <span class="px-3 py-1 rounded-full text-xs font-bold
                                @if($project->provisioning_status === 'completed') bg-emerald-400/20 text-emerald-200 border border-emerald-300/30
                                @elseif($project->provisioning_status === 'failed') bg-red-400/20 text-red-200 border border-red-300/30
                                @elseif($project->provisioning_status === 'running') bg-yellow-400/20 text-yellow-100 border border-yellow-300/30
                                @else bg-slate-400/20 text-slate-200 border border-slate-300/30 @endif">
                                {{ strtoupper($project->provisioning_status ?? 'unknown') }}
                            </span>
                        </div>
                    </div>

                    <div class="p-5 space-y-5">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                            <div class="rounded-xl bg-slate-50 p-3"><strong>Domínio</strong><p>{{ $project->domain ?: '-' }}</p></div>
                            <div class="rounded-xl bg-slate-50 p-3"><strong>Branch</strong><p>{{ $project->branch ?: '-' }}</p></div>
                            <div class="rounded-xl bg-slate-50 p-3"><strong>Health</strong><p>{{ $project->health_status ?: 'unknown' }}</p></div>
                            <div class="rounded-xl bg-slate-50 p-3"><strong>Deploy Path</strong><p class="truncate">{{ $project->deploy_path ?: '-' }}</p></div>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            @if ($project->domain)
                                <a class="px-4 py-2 rounded-xl bg-slate-950 text-white text-sm font-bold" target="_blank" href="https://{{ $project->domain }}">Abrir</a>
                            @endif
                            @if ($project->github_repository)
                                <a class="px-4 py-2 rounded-xl bg-slate-100 text-slate-800 text-sm font-bold" target="_blank" href="https://github.com/{{ $project->github_repository }}">GitHub</a>
                            @endif
                            <a class="px-4 py-2 rounded-xl bg-blue-50 text-blue-700 text-sm font-bold" href="/admin/factory-projects/{{ $project->id }}/edit">Logs / Editar</a>
                            <a class="px-4 py-2 rounded-xl bg-amber-50 text-amber-700 text-sm font-bold" href="/admin/pipeline-visual">Pipeline</a>
                        </div>

                        <div class="rounded-2xl bg-slate-50 p-4">
                            <div class="font-bold mb-3">Últimos eventos</div>
                            <div class="space-y-2 text-xs">
                                @forelse ($project->provisioningLogs()->latest()->take(4)->get() as $log)
                                    <div class="flex items-center justify-between border-b border-slate-200 pb-2">
                                        <span>{{ $log->step }}</span>
                                        <span class="font-bold">{{ $log->status }}</span>
                                    </div>
                                @empty
                                    <div class="text-slate-500">Nenhum evento registrado.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</x-filament-panels::page>
