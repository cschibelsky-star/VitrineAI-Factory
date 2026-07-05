<link rel="stylesheet" href="/css/factory-enterprise.css">
<x-filament-panels::page>
    @php($stats = $this->stats)
    <div class="vf-shell space-y-6">
        <section class="vf-hero">
            <div class="vf-eyebrow">VitrineAI Factory</div>
            <div class="vf-title">Enterprise Control Center</div>
            <div class="vf-subtitle">Centro de comando para provisionamento, deploy, monitoramento e operação do ecossistema Vitrine AI Pro.</div>
            <div class="vf-actions">
                <span class="vf-pill green">● Plataforma Operacional</span>
                <span class="vf-pill gray">Branch: hostgator-baseline</span>
                <span class="vf-pill yellow">Ambiente: Production</span>
            </div>
        </section>

        <section class="vf-grid">
            <div class="vf-card"><div class="vf-kpi-label">Projetos</div><div class="vf-kpi-value">{{ $stats['projects'] }}</div></div>
            <div class="vf-card"><div class="vf-kpi-label">Concluídos</div><div class="vf-kpi-value">{{ $stats['completed'] }}</div></div>
            <div class="vf-card"><div class="vf-kpi-label">Executando</div><div class="vf-kpi-value">{{ $stats['running'] }}</div></div>
            <div class="vf-card"><div class="vf-kpi-label">Templates</div><div class="vf-kpi-value">{{ $stats['templates'] }}</div></div>
        </section>

        <section class="vf-grid-3">
            <div class="vf-dark-card">
                <div class="vf-kpi-label" style="color:#93c5fd">Saúde Operacional</div>
                <div class="vf-kpi-value light">{{ $stats['online'] }} online</div>
                <div class="vf-progress mt-4"><span style="width: {{ $stats['projects'] ? min(100, round(($stats['online'] / max(1,$stats['projects'])) * 100)) : 0 }}%"></span></div>
            </div>
            <div class="vf-dark-card">
                <div class="vf-kpi-label" style="color:#fcd34d">Falhas</div>
                <div class="vf-kpi-value light">{{ $stats['failed'] }}</div>
                <p style="color:#cbd5e1;margin-top:8px">Ambientes que exigem atenção operacional.</p>
            </div>
            <div class="vf-dark-card">
                <div class="vf-kpi-label" style="color:#86efac">Factory Engine</div>
                <div class="vf-kpi-value light">Ativa</div>
                <p style="color:#cbd5e1;margin-top:8px">Provisionamento, logs, deploy center e health check disponíveis.</p>
            </div>
        </section>

        <section class="vf-card">
            <div style="display:flex;justify-content:space-between;align-items:center;gap:16px">
                <div><h2 style="font-size:22px;font-weight:900;color:#0f172a">Ambientes Recentes</h2><p style="color:#64748b">Projetos operados pela Factory.</p></div>
                <span class="vf-pill green">Enterprise Ops</span>
            </div>
            <table class="vf-table mt-4">
                <thead><tr><th>Projeto</th><th>Produto</th><th>Domínio</th><th>Provisionamento</th><th>Saúde</th></tr></thead>
                <tbody>
                    @foreach($this->projects as $project)
                        <tr>
                            <td><strong>{{ $project->name }}</strong><br><small>{{ $project->environment }}</small></td>
                            <td>{{ $project->product }}</td>
                            <td>{{ $project->domain }}</td>
                            <td><span class="vf-pill {{ $project->provisioning_status === 'completed' ? 'green' : ($project->provisioning_status === 'failed' ? 'red' : 'gray') }}">{{ $project->provisioning_status }}</span></td>
                            <td><span class="vf-pill {{ $project->health_status === 'online' ? 'green' : ($project->health_status === 'offline' ? 'red' : 'gray') }}">{{ $project->health_status ?? 'unknown' }}</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </section>
    </div>
</x-filament-panels::page>
