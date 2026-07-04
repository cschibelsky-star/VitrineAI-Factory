<x-filament-panels::page>
    <div class="space-y-5">
        @foreach ($this->projects as $project)
            @php($logs = $project->provisioningLogs->pluck('status', 'step'))
            <div class="bg-white rounded-2xl border shadow p-5">
                <div class="flex justify-between items-center mb-4">
                    <div>
                        <h2 class="text-xl font-bold">{{ $project->name }}</h2>
                        <p class="text-sm text-gray-500">{{ $project->domain }}</p>
                    </div>
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-gray-100">{{ $project->provisioning_status }}</span>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-7 gap-2 text-xs">
                    @foreach (['Validação','Clone GitHub','Criar .env','Composer Install','Migrations','Assets','Concluído'] as $step)
                        @php($status = $logs[$step] ?? null)
                        <div class="rounded-xl p-3 border {{ $status === 'success' ? 'bg-green-50 border-green-200 text-green-700' : ($status === 'error' ? 'bg-red-50 border-red-200 text-red-700' : 'bg-gray-50 text-gray-500') }}">
                            <strong>{{ $status === 'success' ? '✔' : ($status === 'error' ? '✖' : '○') }} {{ $step }}</strong>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</x-filament-panels::page>
