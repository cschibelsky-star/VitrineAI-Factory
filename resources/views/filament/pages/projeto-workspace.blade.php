<x-filament-panels::page>
    @php
        $project = $this->project;
        $brain = $project ? app(\App\Factory\Services\FactoryBrainService::class)->recommendations($project) : [];
    @endphp

    <div class="space-y-6">
        <div class="rounded-3xl bg-slate-950 text-white p-8 shadow-xl">
            <div class="text-xs uppercase tracking-[0.35em] text-cyan-300">Factory Workspace</div>
            <div class="mt-3 flex flex-col md:flex-row md:items-end md:justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-black">{{ $project?->name ?? 'Nenhum projeto selecionado' }}</h1>
                    <p class="mt-2 text-slate-300">{{ $project?->product }} · {{ $project?->domain }} · {{ $project?->environment }}</p>
                </div>
                @if ($project?->domain)
                    <a href="https://{{ $project->domain }}" target="_blank" class="rounded-xl bg-cyan-400 px-5 py-3 font-bold text-slate-950">
                        Abrir Ambiente
                    </a>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div class="rounded-2xl bg-white p-5 shadow border"><div class="text-xs text-gray-500 uppercase">Status</div><div class="mt-2 text-xl font-black">{{ $project?->status ?? '-' }}</div></div>
            <div class="rounded-2xl bg-white p-5 shadow border"><div class="text-xs text-gray-500 uppercase">Provisionamento</div><div class="mt-2 text-xl font-black">{{ $project?->provisioning_status ?? '-' }}</div></div>
            <div class="rounded-2xl bg-white p-5 shadow border"><div class="text-xs text-gray-500 uppercase">Health</div><div class="mt-2 text-xl font-black">{{ $project?->health_status ?? '-' }}</div></div>
            <div class="rounded-2xl bg-white p-5 shadow border"><div class="text-xs text-gray-500 uppercase">Branch</div><div class="mt-2 text-xl font-black">{{ $project?->branch ?? '-' }}</div></div>
            <div class="rounded-2xl bg-white p-5 shadow border"><div class="text-xs text-gray-500 uppercase">cPanel</div><div class="mt-2 text-xl font-black">{{ $project?->cpanel_status ?? '-' }}</div></div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 rounded-3xl bg-white p-6 shadow border">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-2xl font-black">Linha operacional</h2>
                        <p class="text-sm text-gray-500">Últimos eventos do projeto.</p>
                    </div>
                    <span class="text-xs font-bold uppercase tracking-widest text-gray-400">Live Logs</span>
                </div>

                <div class="mt-6 space-y-4">
                    @forelse ($this->logs as $log)
                        <div class="flex gap-4 rounded-2xl bg-gray-50 p-4">
                            <div class="mt-1 h-3 w-3 rounded-full {{ $log->status === 'success' ? 'bg-green-500' : ($log->status === 'error' ? 'bg-red-500' : 'bg-blue-500') }}"></div>
                            <div>
                                <div class="font-bold">{{ $log->step }}</div>
                                <div class="text-sm text-gray-600">{{ $log->message }}</div>
                                <div class="mt-1 text-xs text-gray-400">{{ $log->created_at?->format('d/m/Y H:i') }} · {{ $log->status }}</div>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-2xl bg-gray-50 p-6 text-gray-500">Nenhum log encontrado.</div>
                    @endforelse
                </div>
            </div>

            <div class="rounded-3xl bg-white p-6 shadow border">
                <h2 class="text-2xl font-black">Factory Brain</h2>
                <p class="text-sm text-gray-500">Recomendações operacionais.</p>

                <div class="mt-6 space-y-3">
                    @foreach ($brain as $item)
                        <div class="rounded-2xl border p-4 {{ $item['level'] === 'critical' ? 'bg-red-50 border-red-100' : ($item['level'] === 'warning' ? 'bg-yellow-50 border-yellow-100' : ($item['level'] === 'success' ? 'bg-green-50 border-green-100' : 'bg-blue-50 border-blue-100')) }}">
                            <div class="font-bold">{{ $item['title'] }}</div>
                            <div class="mt-1 text-sm text-gray-600">{{ $item['message'] }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="rounded-3xl bg-white p-6 shadow border">
            <h2 class="text-2xl font-black">Projetos disponíveis</h2>
            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                @foreach ($this->projects as $item)
                    <a href="?project={{ $item->id }}" class="block rounded-2xl border bg-gray-50 p-4 hover:bg-white hover:shadow">
                        <div class="font-bold">{{ $item->name }}</div>
                        <div class="text-sm text-gray-500">{{ $item->product }} · {{ $item->domain }}</div>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</x-filament-panels::page>
