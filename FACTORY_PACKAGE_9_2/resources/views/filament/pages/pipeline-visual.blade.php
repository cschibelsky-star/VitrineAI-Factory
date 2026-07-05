<link rel="stylesheet" href="/css/factory-enterprise.css">
<x-filament-panels::page>
    <div class="vf-shell space-y-6">
        <section class="vf-hero">
            <div class="vf-eyebrow">Pipeline Visual</div>
            <div class="vf-title">Provisioning Timeline</div>
            <div class="vf-subtitle">Acompanhamento visual das etapas executadas pela Factory Engine.</div>
        </section>
        @foreach ($this->projects as $project)
            @php
                $logs = $project->provisioningLogs()->latest()->take(12)->get()->reverse();
                $isFailed = $project->provisioning_status === 'failed';
            @endphp
            <section class="vf-card">
                <div style="display:flex;justify-content:space-between;align-items:center;gap:16px">
                    <div>
                        <h2 style="font-size:22px;font-weight:900;color:#0f172a">{{ $project->name }}</h2>
                        <p style="color:#64748b">{{ $project->domain }} · {{ $project->environment }}</p>
                    </div>
                    <span class="vf-pill {{ $project->provisioning_status === 'completed' ? 'green' : ($isFailed ? 'red' : 'gray') }}">{{ $project->provisioning_status }}</span>
                </div>
                <div class="vf-timeline mt-4">
                    @forelse($logs as $log)
                        <div class="vf-step">
                            <div class="vf-dot {{ $log->status === 'error' ? 'fail' : ($log->status === 'success' ? '' : 'wait') }}">{{ $log->status === 'error' ? '!' : '✓' }}</div>
                            <div class="vf-step-card">
                                <strong>{{ $log->step }}</strong>
                                <div style="color:#64748b;font-size:13px;margin-top:4px">{{ $log->message }}</div>
                                <div style="color:#94a3b8;font-size:12px;margin-top:6px">{{ $log->created_at?->format('d/m/Y H:i') }}</div>
                            </div>
                        </div>
                    @empty
                        <p style="color:#64748b">Nenhum log registrado.</p>
                    @endforelse
                </div>
            </section>
        @endforeach
    </div>
</x-filament-panels::page>
