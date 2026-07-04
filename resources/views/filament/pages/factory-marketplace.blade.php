<x-filament-panels::page>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        @foreach ($this->templates as $template)
            <div class="bg-white border shadow rounded-2xl p-5">
                <div class="text-xs uppercase tracking-widest text-blue-600 font-bold">{{ $template->category ?? 'Produto' }}</div>
                <h2 class="mt-2 text-xl font-bold">{{ $template->name }}</h2>
                <p class="text-gray-500 text-sm mt-1">{{ $template->product_type }}</p>
                <div class="mt-4 space-y-2 text-sm">
                    <div><strong>Versão:</strong> {{ $template->version ?? '1.0.0' }}</div>
                    <div><strong>Branch:</strong> {{ $template->default_branch }}</div>
                    <div><strong>Banco:</strong> {{ $template->database_driver ?? 'sqlite' }}</div>
                    <div><strong>Repo:</strong> {{ $template->base_repository }}</div>
                </div>
                <div class="mt-4">
                    <span class="px-3 py-1 rounded-full bg-green-100 text-green-700 text-xs font-bold">{{ $template->status }}</span>
                </div>
            </div>
        @endforeach
    </div>
</x-filament-panels::page>
