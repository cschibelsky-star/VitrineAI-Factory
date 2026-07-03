<x-filament-panels::page>
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="p-5 rounded-xl bg-white shadow">
            <div class="text-sm text-gray-500">Projetos</div>
            <div class="text-3xl font-bold">{{ count($projects) }}</div>
        </div>

        <div class="p-5 rounded-xl bg-white shadow">
            <div class="text-sm text-gray-500">GitHub</div>
            <div class="text-2xl font-bold text-green-600">Online</div>
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
                    <th class="p-3">Repositório</th>
                    <th class="p-3">Branch</th>
                    <th class="p-3">Ambiente</th>
                    <th class="p-3">Domínio</th>
                    <th class="p-3">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($projects as $project)
                    <tr class="border-t">
                        <td class="p-3 font-semibold">{{ $project['name'] }}</td>
                        <td class="p-3">{{ $project['repository'] }}</td>
                        <td class="p-3">{{ $project['branch'] }}</td>
                        <td class="p-3">{{ $project['environment'] }}</td>
                        <td class="p-3">{{ $project['domain'] ?: '-' }}</td>
                        <td class="p-3">
                            @if ($project['status'] === 'online')
                                <span class="text-green-600 font-bold">Online</span>
                            @else
                                <span class="text-yellow-600 font-bold">Pendente</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-filament-panels::page>
