<x-filament-panels::page>
    @php($stats = $this->stats)

    <div class="space-y-6">
        <div class="rounded-2xl bg-gray-950 p-8 text-white shadow">
            <div class="text-sm uppercase tracking-widest text-blue-300">VitrineAI Factory</div>
            <h1 class="mt-2 text-3xl font-bold">Enterprise Provisioning Center</h1>
            <p class="mt-2 text-gray-300">Central de templates, deploys, health check e provisionamento do ecossistema Vitrine AI Pro.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
            <div class="rounded-xl bg-white p-5 shadow border"><div class="text-sm text-gray-500">Projetos</div><div class="text-3xl font-bold">{{ $stats['total'] }}</div></div>
            <div class="rounded-xl bg-white p-5 shadow border"><div class="text-sm text-gray-500">Concluídos</div><div class="text-3xl font-bold text-green-600">{{ $stats['completed'] }}</div></div>
            <div class="rounded-xl bg-white p-5 shadow border"><div class="text-sm text-gray-500">Executando</div><div class="text-3xl font-bold text-yellow-600">{{ $stats['running'] }}</div></div>
            <div class="rounded-xl bg-white p-5 shadow border"><div class="text-sm text-gray-500">Falhas</div><div class="text-3xl font-bold text-red-600">{{ $stats['failed'] }}</div></div>
            <div class="rounded-xl bg-white p-5 shadow border"><div class="text-sm text-gray-500">Online</div><div class="text-3xl font-bold text-blue-600">{{ $stats['online'] }}</div></div>
            <div class="rounded-xl bg-white p-5 shadow border"><div class="text-sm text-gray-500">Templates</div><div class="text-3xl font-bold">{{ $stats['templates'] }}</div></div>
        </div>

        <div class="rounded-2xl bg-white shadow border overflow-hidden">
            <div class="p-5 border-b">
                <h2 class="text-xl font-bold">Pipeline de Provisionamento</h2>
                <p class="text-sm text-gray-500">Últimos projetos criados pela Factory.</p>
            </div>

            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 text-left">
                        <th class="p-3">Projeto</th>
                        <th class="p-3">Produto</th>
                        <th class="p-3">Domínio</th>
                        <th class="p-3">Provisionamento</th>
                        <th class="p-3">Saúde</th>
                        <th class="p-3">Ambiente</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($this->projects as $project)
                        <tr class="border-t">
                            <td class="p-3 font-semibold">{{ $project->name }}</td>
                            <td class="p-3">{{ $project->product }}</td>
                            <td class="p-3">{{ $project->domain }}</td>
                            <td class="p-3">
                                <span class="px-3 py-1 rounded-full text-xs font-bold
                                    @if($project->provisioning_status === 'completed') bg-green-100 text-green-700
                                    @elseif($project->provisioning_status === 'failed') bg-red-100 text-red-700
                                    @elseif($project->provisioning_status === 'running') bg-yellow-100 text-yellow-700
                                    @else bg-gray-100 text-gray-700 @endif">
                                    {{ $project->provisioning_status }}
                                </span>
                            </td>
                            <td class="p-3">{{ $project->health_status ?? 'unknown' }}</td>
                            <td class="p-3">{{ $project->environment }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-filament-panels::page>
