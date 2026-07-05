<x-filament-panels::page>
    <div class="space-y-6">
        <section class="rounded-3xl bg-gradient-to-br from-slate-950 via-cyan-950 to-blue-900 p-8 text-white shadow-2xl">
            <div class="text-xs font-bold uppercase tracking-[0.35em] text-cyan-300">Pipeline Visual</div>
            <h1 class="mt-3 text-4xl font-black">Provisioning Flow</h1>
            <p class="mt-3 text-slate-300">Acompanhamento visual das etapas reais executadas pela Factory Engine.</p>
        </section>

        <div class="space-y-5">
            @foreach ($this->projects as $project)
                @php($steps = ['Validação','Clone GitHub','Criar .env','Composer Install','Migrations','Assets','Concluído'])
                <div class="rounded-3xl border bg-white p-6 shadow-xl">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-2xl font-black text-slate-900">{{ $project->name }}</h2>
                            <p class="text-sm text-slate-500">{{ $project->domain }}</p>
                        </div>
                        <span class="rounded-full px-4 py-2 text-xs font-bold uppercase {{ $project->provisioning_status === 'completed' ? 'bg-emerald-100 text-emerald-700' : ($project->provisioning_status === 'failed' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700') }}">{{ $project->provisioning_status }}</span>
                    </div>

                    <div class="mt-6 grid grid-cols-1 gap-3 md:grid-cols-7">
                        @foreach ($steps as $step)
                            @php($log = $project->provisioningLogs()->where('step', $step)->latest()->first())
                            <div class="rounded-2xl border p-4 text-center {{ $log?->status === 'success' ? 'border-emerald-200 bg-emerald-50 text-emerald-800' : ($log?->status === 'error' ? 'border-red-200 bg-red-50 text-red-800' : 'border-slate-200 bg-slate-50 text-slate-500') }}">
                                <div class="text-xl">{{ $log?->status === 'success' ? '✓' : ($log?->status === 'error' ? '!' : '○') }}</div>
                                <div class="mt-2 text-xs font-black uppercase">{{ $step }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</x-filament-panels::page>
