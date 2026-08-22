<x-filament-panels::page>
    <div class="space-y-6">
        <x-filament::section>
            <x-slot name="heading">Releases liberadas para deploy</x-slot>
            <x-slot name="description">A Factory valida os gates e as evidências. A execução operacional permanece delegada ao Centro Operacional com confirmação.</x-slot>

            <div class="space-y-3">
                @forelse ($readyReleases as $item)
                    @php($release = $item['release'])
                    <div class="rounded-xl border p-4">
                        <div class="font-semibold">{{ $release->product?->name ?? 'Projeto' }} — {{ $release->version }}</div>
                        <div class="text-sm opacity-70">Build: {{ $release->build?->version ?? 'não vinculado' }} | HML: {{ $release->homologation?->status ?? 'não vinculada' }} / {{ $release->homologation?->health_status ?? 'health desconhecido' }}</div>
                        <div class="mt-2 text-sm font-medium">Readiness: liberada. Todos os gates obrigatórios foram satisfeitos.</div>
                    </div>
                @empty
                    <div class="text-sm opacity-70">Nenhuma release está liberada para deploy.</div>
                @endforelse
            </div>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">Releases bloqueadas</x-slot>
            <x-slot name="description">Uma release só pode avançar quando QA, documentação, build, HML e release estiverem consistentes e aprovados.</x-slot>

            <div class="space-y-3">
                @forelse ($blockedReleases as $item)
                    @php($release = $item['release'])
                    <div class="rounded-xl border p-4">
                        <div class="font-semibold">{{ $release->product?->name ?? 'Projeto' }} — {{ $release->version }}</div>
                        <div class="text-sm opacity-70">Status atual: {{ $release->status }}</div>
                        <div class="mt-2 text-sm">
                            <div class="font-medium">Bloqueios:</div>
                            <ul class="mt-1 list-disc space-y-1 pl-5">
                                @foreach ($item['readiness']['blockers'] as $blocker)
                                    <li>{{ $blocker }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @empty
                    <div class="text-sm opacity-70">Nenhuma release bloqueada entre as candidatas atuais.</div>
                @endforelse
            </div>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">Deploys recentes</x-slot>
            <div class="space-y-2">
                @forelse ($recentDeploys as $release)
                    <div class="text-sm">{{ $release->product?->name ?? 'Projeto' }} — {{ $release->version }} — {{ optional($release->deployed_at)->format('d/m/Y H:i') }}</div>
                @empty
                    <div class="text-sm opacity-70">Nenhum deploy registrado.</div>
                @endforelse
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
