<link rel="stylesheet" href="/css/factory-enterprise.css">
<x-filament-panels::page>
    @php($stats = $this->stats)
    <div class="vf-shell space-y-6">
        <section class="vf-hero">
            <div class="vf-eyebrow">DevOps Center</div>
            <div class="vf-title">Operational Command</div>
            <div class="vf-subtitle">Monitoramento de provisionamentos, deploys, ambientes e saúde técnica da Factory.</div>
        </section>
        <section class="vf-grid">
            <div class="vf-card"><div class="vf-kpi-label">Projetos</div><div class="vf-kpi-value">{{ $stats['total'] ?? 0 }}</div></div>
            <div class="vf-card"><div class="vf-kpi-label">Concluídos</div><div class="vf-kpi-value">{{ $stats['completed'] ?? 0 }}</div></div>
            <div class="vf-card"><div class="vf-kpi-label">Executando</div><div class="vf-kpi-value">{{ $stats['running'] ?? 0 }}</div></div>
            <div class="vf-card"><div class="vf-kpi-label">Falhas</div><div class="vf-kpi-value">{{ $stats['failed'] ?? 0 }}</div></div>
        </section>
        <section class="vf-grid-3">
            <div class="vf-dark-card"><div class="vf-kpi-label" style="color:#7dd3fc">GitHub</div><div class="vf-kpi-value light">Online</div><span class="vf-pill green">● Conectado</span></div>
            <div class="vf-dark-card"><div class="vf-kpi-label" style="color:#86efac">HostGator</div><div class="vf-kpi-value light">Ativo</div><span class="vf-pill green">● Deploy Ready</span></div>
            <div class="vf-dark-card"><div class="vf-kpi-label" style="color:#fcd34d">Factory Engine</div><div class="vf-kpi-value light">Operacional</div><span class="vf-pill yellow">● Monitorando</span></div>
        </section>
        <section class="vf-card">
            <h2 style="font-size:22px;font-weight:900;color:#0f172a">Pipeline de Provisionamento</h2>
            <table class="vf-table mt-4">
                <thead><tr><th>Projeto</th><th>Produto</th><th>Domínio</th><th>Status</th><th>Saúde</th><th>Ambiente</th></tr></thead>
                <tbody>
                    @foreach($this->projects as $project)
                        <tr>
                            <td><strong>{{ $project->name }}</strong></td>
                            <td>{{ $project->product }}</td>
                            <td>{{ $project->domain }}</td>
                            <td><span class="vf-pill {{ $project->provisioning_status === 'completed' ? 'green' : ($project->provisioning_status === 'failed' ? 'red' : 'gray') }}">{{ $project->provisioning_status }}</span></td>
                            <td><span class="vf-pill {{ $project->health_status === 'online' ? 'green' : ($project->health_status === 'offline' ? 'red' : 'gray') }}">{{ $project->health_status ?? 'unknown' }}</span></td>
                            <td>{{ $project->environment }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </section>
    </div>
</x-filament-panels::page>
