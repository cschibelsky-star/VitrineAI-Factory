<x-filament-panels::page>
    <div class="space-y-4">
        @foreach ($this->projects as $project)
            <div class="p-5 bg-white rounded-xl shadow border">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-bold">{{ $project->name }}</h2>
                        <p class="text-gray-500">{{ $project->domain ?: 'Sem domínio' }}</p>
                    </div>
                    <span class="px-3 py-1 rounded-full text-sm font-semibold bg-yellow-100 text-yellow-700">
                        {{ $project->cpanel_status ?? 'pending' }}
                    </span>
                </div>

                <div class="mt-4 grid grid-cols-1 md:grid-cols-4 gap-4 text-sm">
                    <div class="p-3 rounded-lg bg-gray-50"><strong>Subdomínio</strong><p>{{ $project->domain }}</p></div>
                    <div class="p-3 rounded-lg bg-gray-50"><strong>Document Root</strong><p>{{ $project->document_root ?: ($project->deploy_path . '/public') }}</p></div>
                    <div class="p-3 rounded-lg bg-gray-50"><strong>Deploy Path</strong><p>{{ $project->deploy_path }}</p></div>
                    <div class="p-3 rounded-lg bg-gray-50"><strong>Health</strong><p>{{ $project->health_status ?? 'unknown' }}</p></div>
                </div>

                <div class="mt-4 p-4 bg-blue-50 rounded-xl text-sm">
                    <strong>Checklist HostGator:</strong>
                    <ol class="list-decimal ml-5 mt-2">
                        <li>Criar subdomínio no cPanel: {{ $project->domain }}</li>
                        <li>Definir Document Root: {{ $project->document_root ?: ($project->deploy_path . '/public') }}</li>
                        <li>Ativar SSL após DNS propagar.</li>
                        <li>Clicar em Verificar Saúde no projeto.</li>
                    </ol>
                </div>
            </div>
        @endforeach
    </div>
</x-filament-panels::page>
