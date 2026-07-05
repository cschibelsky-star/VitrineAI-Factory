<x-filament-panels::page>
    @php($stats = $this->stats)
    <div class="space-y-6">
        <section class="relative overflow-hidden rounded-3xl bg-slate-950 p-8 text-white shadow-xl">
            <div class="text-xs uppercase tracking-[0.35em] text-cyan-300">VitrineAI Factory</div>
            <h1 class="mt-3 text-4xl font-black">Enterprise Provisioning Platform</h1>
            <p class="mt-3 max-w-3xl text-slate-300">Sistema operacional de criação, deploy, atualização e monitoramento do ecossistema Vitrine AI Pro.</p>
            <div class="mt-6 flex flex-wrap gap-3 text-sm">
                <span class="rounded-full bg-green-500/15 px-4 py-2 text-green-300 ring-1 ring-green-400/30">● GitHub conectado</span>
                <span class="rounded-full bg-blue-500/15 px-4 py-2 text-blue-300 ring-1 ring-blue-400/30">● HostGator ativo</span>
                <span class="rounded-full bg-purple-500/15 px-4 py-2 text-purple-300 ring-1 ring-purple-400/30">● Factory Engine online</span>
            </div>
        </section>
        <section class="grid grid-cols-1 gap-4 md:grid-cols-6">
            <div class="rounded-2xl border bg-white p-5 shadow-sm"><div class="text-xs uppercase text-gray-500">Projetos</div><div class="mt-2 text-4xl font-black">{{ $stats['projects'] }}</div></div>
            <div class="rounded-2xl border bg-white p-5 shadow-sm"><div class="text-xs uppercase text-gray-500">Concluídos</div><div class="mt-2 text-4xl font-black text-green-600">{{ $stats['completed'] }}</div></div>
            <div class="rounded-2xl border bg-white p-5 shadow-sm"><div class="text-xs uppercase text-gray-500">Executando</div><div class="mt-2 text-4xl font-black text-yellow-600">{{ $stats['running'] }}</div></div>
            <div class="rounded-2xl border bg-white p-5 shadow-sm"><div class="text-xs uppercase text-gray-500">Falhas</div><div class="mt-2 text-4xl font-black text-red-600">{{ $stats['failed'] }}</div></div>
            <div class="rounded-2xl border bg-white p-5 shadow-sm"><div class="text-xs uppercase text-gray-500">Online</div><div class="mt-2 text-4xl font-black text-blue-600">{{ $stats['online'] }}</div></div>
            <div class="rounded-2xl border bg-white p-5 shadow-sm"><div class="text-xs uppercase text-gray-500">Templates</div><div class="mt-2 text-4xl font-black">{{ $stats['templates'] }}</div></div>
        </section>
        <section class="grid grid-cols-1 gap-4 lg:grid-cols-3">
            <div class="rounded-2xl border bg-white p-5 shadow-sm lg:col-span-2">
                <h2 class="text-xl font-bold">Projetos recentes</h2>
                <div class="mt-4 space-y-3">
                    @forelse($this->projects as $project)
                        <div class="rounded-xl border p-4 hover:bg-gray-50">
                            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                                <div><div class="font-bold">{{ $project->name }}</div><div class="text-sm text-gray-500">{{ $project->product }} · {{ $project->domain }}</div></div>
                                <div class="flex flex-wrap gap-2 text-xs font-bold">
                                    <span class="rounded-full px-3 py-1 @if($project->provisioning_status === 'completed') bg-green-100 text-green-700 @elseif($project->provisioning_status === 'failed') bg-red-100 text-red-700 @else bg-yellow-100 text-yellow-700 @endif">{{ $project->provisioning_status ?? 'pending' }}</span>
                                    <span class="rounded-full px-3 py-1 @if($project->health_status === 'online') bg-blue-100 text-blue-700 @elseif($project->health_status === 'offline') bg-red-100 text-red-700 @else bg-gray-100 text-gray-700 @endif">{{ $project->health_status ?? 'unknown' }}</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-xl border border-dashed p-6 text-center text-gray-500">Nenhum projeto cadastrado.</div>
                    @endforelse
                </div>
            </div>
            <div class="rounded-2xl border bg-slate-950 p-5 text-white shadow-sm">
                <h2 class="text-xl font-bold">Pipeline padrão</h2>
                <div class="mt-4 space-y-3 text-sm">
                    @foreach(['Template','GitHub','Clone','Composer','Banco','Migration','Assets','Admin','SSL','Health Check','Online'] as $step)
                        <div class="flex items-center gap-3"><span class="h-3 w-3 rounded-full bg-cyan-400"></span><span>{{ $step }}</span></div>
                    @endforeach
                </div>
            </div>
        </section>
    </div>
</x-filament-panels::page>
