<x-filament-panels::page>
    @php
        $online = collect($projects)->where('status', 'online')->count();
    @endphp

    <div class="space-y-6">
        <div class="rounded-2xl bg-gray-950 p-8 text-white shadow">
            <div class="text-sm uppercase tracking-widest text-blue-300">VitrineAI Factory</div>
            <h1 class="mt-2 text-3xl font-bold">Enterprise Control Center</h1>
            <p class="mt-2 text-gray-300">Central de engenharia, DevOps, provisionamento e operação do ecossistema Vitrine AI Pro.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="rounded-2xl bg-white p-5 shadow border">
                <div class="text-sm text-gray-500">Projetos Monitorados</div>
                <div class="mt-2 text-4xl font-bold">{{ count($projects) }}</div>
            </div>
            <div class="rounded-2xl bg-white p-5 shadow border">
                <div class="text-sm text-gray-500">Online</div>
                <div class="mt-2 text-4xl font-bold text-green-600">{{ $online }}</div>
            </div>
            <div class="rounded-2xl bg-white p-5 shadow border">
                <div class="text-sm text-gray-500">Deploy Center</div>
                <div class="mt-2 text-2xl font-bold text-blue-600">Ativo</div>
            </div>
            <div class="rounded-2xl bg-white p-5 shadow border">
                <div class="text-sm text-gray-500">Backups</div>
                <div class="mt-2 text-2xl font-bold text-emerald-600">OK</div>
            </div>
        </div>

        <div class="rounded-2xl bg-white shadow border overflow-hidden">
            <div class="p-5 border-b flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-bold">Projetos Monitorados</h2>
                    <p class="text-sm text-gray-500">Ambientes conectados ao DevOps Center.</p>
                </div>
                <span class="rounded-full bg-green-100 px-3 py-1 text-sm font-semibold text-green-700">Sistema Operacional</span>
            </div>

            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 text-left">
                        <th class="p-3">Projeto</th>
                        <th class="p-3">Status</th>
                        <th class="p-3">Ambiente</th>
                        <th class="p-3">Domínio</th>
                        <th class="p-3">Branch</th>
                        <th class="p-3">Versão</th>
                        <th class="p-3">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($projects as $project)
                        <tr class="border-t hover:bg-gray-50">
                            <td class="p-3">
                                <div class="font-bold">{{ $project['name'] }}</div>
                                <div class="text-xs text-gray-500">{{ $project['type'] }}</div>
                            </td>
                            <td class="p-3">
                                @if ($project['status'] === 'online')
                                    <span class="rounded-full bg-green-100 px-3 py-1 text-green-700 font-bold">● Online</span>
                                @else
                                    <span class="rounded-full bg-yellow-100 px-3 py-1 text-yellow-700 font-bold">● Pendente</span>
                                @endif
                            </td>
                            <td class="p-3">{{ $project['environment'] }}</td>
                            <td class="p-3">{{ $project['domain'] ?: '-' }}</td>
                            <td class="p-3">{{ $project['branch'] }}</td>
                            <td class="p-3">{{ $project['version'] }}</td>
                            <td class="p-3 space-x-3">
                                @if (!empty($project['domain']))
                                    <a class="font-semibold text-blue-600" target="_blank" href="https://{{ $project['domain'] }}">Abrir</a>
                                @endif
                                <a class="font-semibold text-blue-600" target="_blank" href="{{ $project['github'] }}">GitHub</a>
                                <span class="text-gray-400">Deploy</span>
                                <span class="text-gray-400">Logs</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="rounded-2xl bg-white p-5 shadow border">
                <h3 class="font-bold">Fila de Deploy</h3>
                <p class="text-sm text-gray-500 mt-1">Nenhum deploy pendente.</p>
            </div>
            <div class="rounded-2xl bg-white p-5 shadow border">
                <h3 class="font-bold">IA Operacional</h3>
                <p class="text-sm text-gray-500 mt-1">Agentes IA aguardando integração.</p>
            </div>
            <div class="rounded-2xl bg-white p-5 shadow border">
                <h3 class="font-bold">Saúde da Infraestrutura</h3>
                <p class="text-sm text-green-600 mt-1 font-semibold">GitHub, HostGator e Laravel ativos.</p>
            </div>
        </div>
    </div>
</x-filament-panels::page>
