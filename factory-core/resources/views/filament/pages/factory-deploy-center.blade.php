<x-filament-panels::page>
    <div class="space-y-6">
        <x-filament::section>
            <x-slot name="heading">Releases prontas para deploy</x-slot>
            <x-slot name="description">O Deploy Center apenas controla readiness e evidências. A execução operacional permanece delegada ao Centro Operacional com confirmação.</x-slot>

            <div class="space-y-3">
                @forelse ($readyReleases as $release)
                    <div class="rounded-xl border p-4">
                        <div class="font-semibold">{{ $release->product?->name ?? 'Projeto' }} — {{ $release->version }}</div>
                        <div class="text-sm opacity-70">Build: {{ $release->build?->version ?? 'não vinculado' }} | HML: {{ $release->homologation?->status ?? 'não vinculada' }}</div>
                        <div class="mt-2 text-sm">Status: pronta para deploy. Antes da execução, validar homologação aprovada, health, evidências e confirmação operacional.</div>
                    </div>
                @empty
                    <div class="text-sm opacity-70">Nenhuma release está pronta para deploy.</div>
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
