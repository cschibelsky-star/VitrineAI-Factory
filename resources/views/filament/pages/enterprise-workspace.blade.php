<x-filament-panels::page>
    @php($stats = $this->stats)

    <div class="space-y-6">
        <div class="rounded-3xl bg-slate-950 p-8 text-white shadow-xl border border-slate-800">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <div class="text-xs uppercase tracking-[0.35em] text-cyan-300">Vitrine AI Factory</div>
                    <h1 class="mt-3 text-4xl font-black">Enterprise Workspace</h1>
                    <p class="mt-2 text-slate-300">Centro de comando para provisionamento, deploy, templates, projetos e operação do ecossistema.</p>
                </div>
                <div class="rounded-2xl bg-emerald-500/10 px-5 py-4 border border-emerald-500/20">
                    <div class="text-xs uppercase text-emerald-300">Status da Plataforma</div>
                    <div class="mt-1 text-2xl font-black text-emerald-300">OPERACIONAL</div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
            <x-factory.kpi-card label="Projetos" :value="$stats['projects']" tone="blue" />
            <x-factory.kpi-card label="Online" :value="$stats['online']" tone="green" />
            <x-factory.kpi-card label="Concluídos" :value="$stats['completed']" tone="green" />
            <x-factory.kpi-card label="Executando" :value="$stats['running']" tone="yellow" />
            <x-factory.kpi-card label="Falhas" :value="$stats['failed']" tone="red" />
            <x-factory.kpi-card label="Templates" :value="$stats['templates']" tone="purple" />
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
            <div class="xl:col-span-2 rounded-3xl bg-white shadow border overflow-hidden">
                <div class="p-5 border-b flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-black">Projetos Operacionais</h2>
                        <p class="text-sm text-gray-500">Últimos ambientes atualizados.</p>
                    </div>
                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold">Workspace</span>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 p-5">
                    @foreach ($this->projects as $project)
                        <div class="rounded-2xl border bg-gray-50 p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <div class="font-black">{{ $project->name }}</div>
                                    <div class="text-xs text-gray-500">{{ $project->product }} · {{ $project->environment }}</div>
                                </div>
                                <span class="rounded-full px-3 py-1 text-xs font-bold @if($project->provisioning_status === 'completed') bg-green-100 text-green-700 @elseif($project->provisioning_status === 'failed') bg-red-100 text-red-700 @else bg-yellow-100 text-yellow-700 @endif">
                                    {{ $project->provisioning_status ?? 'pending' }}
                                </span>
                            </div>
                            <div class="mt-4 text-xs text-gray-500">{{ $project->domain ?: 'Sem domínio' }}</div>
                            <div class="mt-3 flex gap-2">
                                @if($project->domain)
                                    <a target="_blank" href="https://{{ $project->domain }}" class="rounded-lg bg-white border px-3 py-2 text-xs font-bold">Abrir</a>
                                @endif
                                <a href="/admin/factory-projects/{{ $project->id }}/edit" class="rounded-lg bg-slate-900 text-white px-3 py-2 text-xs font-bold">Workspace</a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="rounded-3xl bg-slate-950 text-white shadow border border-slate-800 overflow-hidden">
                <div class="p-5 border-b border-slate-800">
                    <h2 class="text-xl font-black">Atividade Recente</h2>
                    <p class="text-sm text-slate-400">Eventos da Factory Engine.</p>
                </div>
                <div class="p-5 space-y-4">
                    @foreach ($this->logs as $log)
                        <div class="flex gap-3">
                            <div class="mt-1 h-2 w-2 rounded-full @if($log->status === 'success') bg-emerald-400 @elseif($log->status === 'error') bg-red-400 @else bg-cyan-400 @endif"></div>
                            <div>
                                <div class="text-sm font-bold">{{ $log->step }}</div>
                                <div class="text-xs text-slate-400">{{ $log->created_at?->format('d/m H:i') }} · {{ $log->status }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
