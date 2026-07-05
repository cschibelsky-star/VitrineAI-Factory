<x-filament-panels::page>
    <div class="space-y-4">
        @foreach ($this->projects as $project)
            <div class="p-5 bg-white rounded-xl shadow border">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h2 class="text-xl font-bold">{{ $project->name }}</h2>
                        <p class="text-sm text-gray-500">{{ $project->domain }} — {{ $project->deploy_path }}</p>
                    </div>

                    <div class="flex gap-2">
                        @if ($project->domain)
                            <a target="_blank" href="https://{{ $project->domain }}" class="px-4 py-2 rounded-lg bg-gray-100 font-semibold">
                                Abrir
                            </a>
                        @endif

                        <x-filament::button wire:click="atualizarProjeto({{ $project->id }})" color="warning">
                            Atualizar
                        </x-filament::button>
                    </div>
                </div>

                <div class="mt-4 grid grid-cols-1 md:grid-cols-4 gap-3 text-sm">
                    <div class="p-3 bg-gray-50 rounded-lg"><strong>Status:</strong> {{ $project->status }}</div>
                    <div class="p-3 bg-gray-50 rounded-lg"><strong>Provisionamento:</strong> {{ $project->provisioning_status }}</div>
                    <div class="p-3 bg-gray-50 rounded-lg"><strong>Saúde:</strong> {{ $project->health_status }}</div>
                    <div class="p-3 bg-gray-50 rounded-lg"><strong>Branch:</strong> {{ $project->branch }}</div>
                </div>

                <div class="mt-4 p-4 bg-gray-50 rounded-xl">
                    <strong>Últimos logs</strong>
                    <div class="mt-2 space-y-1 text-xs">
                        @foreach ($project->provisioningLogs()->latest()->take(5)->get() as $log)
                            <div>
                                <span class="font-semibold">{{ $log->created_at->format('d/m H:i') }}</span>
                                —
                                <span>{{ $log->step }}</span>
                                —
                                <span>{{ $log->status }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</x-filament-panels::page>
