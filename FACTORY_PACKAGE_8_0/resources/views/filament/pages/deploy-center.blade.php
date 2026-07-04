<x-filament-panels::page>
    <div class="space-y-4">
        @foreach ($this->projects as $project)
            <div class="bg-white rounded-2xl border shadow p-5 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h2 class="text-lg font-bold">{{ $project->name }}</h2>
                    <p class="text-sm text-gray-500">{{ $project->domain }} · {{ $project->branch }} · {{ $project->deployment_status ?? 'idle' }}</p>
                </div>
                <div class="flex gap-2">
                    @if($project->domain)
                        <a target="_blank" class="px-3 py-2 rounded-lg bg-green-600 text-white text-sm font-bold" href="https://{{ $project->domain }}">Abrir</a>
                    @endif
                    <button wire:click="health({{ $project->id }})" class="px-3 py-2 rounded-lg bg-blue-600 text-white text-sm font-bold">Health</button>
                    <button wire:click="atualizar({{ $project->id }})" class="px-3 py-2 rounded-lg bg-gray-900 text-white text-sm font-bold">Atualizar</button>
                </div>
            </div>
        @endforeach
    </div>
</x-filament-panels::page>
