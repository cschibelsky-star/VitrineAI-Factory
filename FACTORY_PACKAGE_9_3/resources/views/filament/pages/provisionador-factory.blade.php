<x-filament-panels::page>
    <div class="space-y-6">
        <section class="rounded-3xl bg-gradient-to-br from-slate-950 via-blue-950 to-cyan-900 p-8 text-white shadow-2xl">
            <div class="text-xs font-bold uppercase tracking-[0.35em] text-cyan-300">Factory Provisioning</div>
            <h1 class="mt-3 text-4xl font-black">Provisionador Inteligente</h1>
            <p class="mt-3 text-slate-300">Crie ambientes a partir dos templates oficiais da Vitrine AI Pro com pipeline de deploy, logs e health check.</p>
        </section>

        <section class="rounded-3xl border bg-white p-6 shadow-xl">
            <form wire:submit="provisionar" class="space-y-6">
                {{ $this->form }}
                <div class="flex justify-end border-t pt-6">
                    <x-filament::button type="submit" size="lg" icon="heroicon-o-rocket-launch">
                        Criar e Provisionar
                    </x-filament::button>
                </div>
            </form>
        </section>
    </div>
</x-filament-panels::page>
