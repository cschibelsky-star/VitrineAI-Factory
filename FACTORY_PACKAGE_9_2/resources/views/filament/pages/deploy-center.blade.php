<link rel="stylesheet" href="/css/factory-enterprise.css">
<x-filament-panels::page>
    <div class="vf-shell space-y-6">
        <section class="vf-hero">
            <div class="vf-eyebrow">Deploy Center</div>
            <div class="vf-title">Release Operations</div>
            <div class="vf-subtitle">Central de atualizações, logs, health check e abertura de ambientes provisionados.</div>
        </section>
        @foreach ($this->projects as $project)
            <section class="vf-card">
                <div style="display:flex;justify-content:space-between;align-items:center;gap:16px;flex-wrap:wrap">
                    <div>
                        <h2 style="font-size:22px;font-weight:900;color:#0f172a">{{ $project->name }}</h2>
                        <p style="color:#64748b">{{ $project->domain }} — {{ $project->deploy_path }}</p>
                    </div>
                    <div class="vf-actions">
                        @if ($project->domain)
                            <a target="_blank" href="https://{{ $project->domain }}" class="vf-action primary">Abrir</a>
                        @endif
                        <button wire:click="atualizarProjeto({{ $project->id }})" type="button" class="vf-action">Atualizar</button>
                    </div>
                </div>
                <div class="vf-grid mt-4">
                    <div class="vf-card"><div class="vf-kpi-label">Status</div><strong>{{ $project->status }}</strong></div>
                    <div class="vf-card"><div class="vf-kpi-label">Provisionamento</div><strong>{{ $project->provisioning_status }}</strong></div>
                    <div class="vf-card"><div class="vf-kpi-label">Saúde</div><strong>{{ $project->health_status ?? 'unknown' }}</strong></div>
                    <div class="vf-card"><div class="vf-kpi-label">Branch</div><strong>{{ $project->branch }}</strong></div>
                </div>
                <div class="vf-timeline mt-4">
                    @foreach ($project->provisioningLogs()->latest()->take(5)->get() as $log)
                        <div class="vf-step">
                            <div class="vf-dot {{ $log->status === 'error' ? 'fail' : '' }}">{{ $log->status === 'error' ? '!' : '✓' }}</div>
                            <div class="vf-step-card"><strong>{{ $log->step }}</strong><div style="color:#64748b;font-size:13px">{{ $log->status }} · {{ $log->created_at?->format('d/m H:i') }}</div></div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endforeach
    </div>
</x-filament-panels::page>
