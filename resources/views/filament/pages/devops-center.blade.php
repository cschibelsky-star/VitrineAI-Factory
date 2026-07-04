<x-filament-panels::page>
    @php
        $online = collect($projects)->where('status', 'online')->count();
    @endphp

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="p-5 rounded-xl bg-white shadow">
            <div class="text-sm text-gray-500">Projetos</div>
            <div class="text-3xl font-bold">{{ count($projects) }}</div>
        </div>
        <div class="p-5 rounded-xl bg-white shadow">
            <div class="text-sm text-gray-500">Online</div>
            <div class="text-3xl font-bold text-green-600">{{ $online }}</div>
        </div>
        <div class="p-5 rounded-xl bg-white shadow">
            <div class="text-sm text-gray-500">Deploy</div>
            <div class="text-2xl font-bold text-green-600">Ativo</div>
        </div>
        <div class="p-5 rounded-xl bg-white shadow">
            <div class="text-sm text-gray-500">Backups</div>
            <div class="text-2xl font-bold text-green-600">OK</div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow overflow-hidden">
        <div class="p-5 border-b">
            <h2 class="text-xl font-bold">Projetos Monitorados</h2>
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
                    <tr class="border-t">
                        <td class="p-3">
                            <div class="font-semibold">{{ $project['name'] }}</div>
                            <div class="text-xs text-gray-500">{{ $project['type'] }}</div>
                        </td>
                        <td class="p-3">
                            @if ($project['status'] === 'online')
                                <span class="text-green-600 font-bold">🟢 Online</span>
                            @else
                                <span class="text-yellow-600 font-bold">🟡 Pendente</span>
                            @endif
                        </td>
                        <td class="p-3">{{ $project['environment'] }}</td>
                        <td class="p-3">{{ $project['domain'] ?: '-' }}</td>
                        <td class="p-3">{{ $project['branch'] }}</td>
                        <td class="p-3">{{ $project['version'] }}</td>
                        <td class="p-3 space-x-2">
                            @if (!empty($project['domain']))
                                <a class="text-blue-600 font-semibold" target="_blank" href="https://{{ $project['domain'] }}">Abrir</a>
                            @endif
                            <a class="text-blue-600 font-semibold" target="_blank" href="{{ $project['github'] }}">GitHub</a>
                            <span class="text-gray-400">Deploy</span>
                            <span class="text-gray-400">Logs</span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-filament-panels::page>
