<x-filament-panels::page>
    <div class="space-y-6">
        <section class="rounded-3xl bg-gradient-to-br from-slate-950 via-indigo-950 to-blue-900 p-8 text-white shadow-2xl">
            <div class="text-xs font-bold uppercase tracking-[0.35em] text-blue-300">HostGator / cPanel</div>
            <h1 class="mt-3 text-4xl font-black">cPanel Assistido</h1>
            <p class="mt-3 text-slate-300">Checklist operacional para subdomínio, document root, DNS, SSL e publicação do ambiente.</p>
        </section>

        <div class="grid grid-cols-1 gap-5 xl:grid-cols-2">
            @foreach ($this->projects as $project)
                <div class="rounded-3xl border bg-white p-6 shadow-xl">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h2 class="text-2xl font-black text-slate-900">{{ $project->name }}</h2>
                            <p class="text-sm text-slate-500">{{ $project->domain ?: 'Sem domínio' }}</p>
                        </div>
                        <span class="rounded-full bg-amber-100 px-4 py-2 text-xs font-bold uppercase text-amber-700">{{ $project->cpanel_status ?? 'pending' }}</span>
                    </div>

                    <div class="mt-6 grid grid-cols-1 gap-3 text-sm md:grid-cols-2">
                        <div class="rounded-2xl bg-slate-50 p-4"><strong>Subdomínio</strong><p class="mt-1 text-slate-600">{{ $project->domain }}</p></div>
                        <div class="rounded-2xl bg-slate-50 p-4"><strong>Document Root</strong><p class="mt-1 text-slate-600">{{ $project->document_root ?: ($project->deploy_path . '/public') }}</p></div>
                        <div class="rounded-2xl bg-slate-50 p-4"><strong>Deploy Path</strong><p class="mt-1 text-slate-600">{{ $project->deploy_path }}</p></div>
                        <div class="rounded-2xl bg-slate-50 p-4"><strong>Health</strong><p class="mt-1 text-slate-600">{{ $project->health_status ?? 'unknown' }}</p></div>
                    </div>

                    <div class="mt-5 rounded-2xl bg-blue-50 p-5 text-sm text-blue-900">
                        <strong>Checklist HostGator</strong>
                        <ol class="mt-2 list-decimal space-y-1 pl-5">
                            <li>Criar subdomínio no cPanel: {{ $project->domain }}</li>
                            <li>Definir Document Root: {{ $project->document_root ?: ($project->deploy_path . '/public') }}</li>
                            <li>Ativar SSL após DNS propagar.</li>
                            <li>Clicar em Verificar Saúde no projeto.</li>
                        </ol>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</x-filament-panels::page>
