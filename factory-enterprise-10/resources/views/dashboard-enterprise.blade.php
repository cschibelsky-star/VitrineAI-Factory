@extends('layouts.factory-enterprise')

@section('content')
    <h1 class="fe-page-title">Cockpit Executivo</h1>
    <p class="fe-page-subtitle">Centro operacional da Vitrine IA Pro com visão de clientes, receita, Factory, IA e implantação.</p>

    <div class="fe-grid kpis" style="margin-bottom:18px">
        <div class="fe-card"><div class="fe-kpi-label">Clientes Ativos</div><div class="fe-kpi-value">{{ $clientesAtivos ?? '—' }}</div><div class="fe-kpi-trend">▲ Operação</div></div>
        <div class="fe-card"><div class="fe-kpi-label">MRR</div><div class="fe-kpi-value">R$ {{ $mrr ?? '—' }}</div><div class="fe-kpi-trend">▲ Receita</div></div>
        <div class="fe-card"><div class="fe-kpi-label">Projetos</div><div class="fe-kpi-value">{{ $projetos ?? '—' }}</div><div class="fe-kpi-trend">Factory</div></div>
        <div class="fe-card"><div class="fe-kpi-label">Builds</div><div class="fe-kpi-value">{{ $builds ?? '—' }}</div><div class="fe-kpi-trend">Atualizações</div></div>
        <div class="fe-card"><div class="fe-kpi-label">Deploys</div><div class="fe-kpi-value">{{ $deploys ?? '—' }}</div><div class="fe-kpi-trend">Produção</div></div>
        <div class="fe-card"><div class="fe-kpi-label">IA Online</div><div class="fe-kpi-value">{{ $iasOnline ?? '—' }}</div><div class="fe-kpi-trend">Agentes</div></div>
    </div>

    <div class="fe-grid two" style="margin-bottom:18px">
        <div class="fe-card">
            <h2 class="fe-section-title">Receita e crescimento</h2>
            <div style="height:260px;border-radius:16px;background:linear-gradient(135deg,rgba(37,167,255,.18),rgba(139,92,246,.12));display:flex;align-items:center;justify-content:center;color:var(--vitrine-muted)">
                Gráfico executivo da receita
            </div>
        </div>
        <div class="fe-card">
            <h2 class="fe-section-title">Saúde da Plataforma</h2>
            <div class="fe-status-list">
                <div class="fe-status-item"><span><span class="fe-dot"></span>API</span><strong>Online</strong></div>
                <div class="fe-status-item"><span><span class="fe-dot"></span>Banco</span><strong>Online</strong></div>
                <div class="fe-status-item"><span><span class="fe-dot"></span>IA</span><strong>Online</strong></div>
                <div class="fe-status-item"><span><span class="fe-dot"></span>Storage</span><strong>Online</strong></div>
                <div class="fe-status-item"><span><span class="fe-dot"></span>Backup</span><strong>OK</strong></div>
            </div>
        </div>
    </div>

    <div class="fe-grid two">
        <div class="fe-card">
            <h2 class="fe-section-title">Atividades recentes</h2>
            <table class="fe-table">
                <thead><tr><th>Horário</th><th>Evento</th><th>Status</th></tr></thead>
                <tbody>
                    <tr><td>Hoje</td><td>Lead qualificado pela IA Comercial</td><td>Concluído</td></tr>
                    <tr><td>Hoje</td><td>Build Enterprise processada</td><td>Concluído</td></tr>
                    <tr><td>Hoje</td><td>Backup automático validado</td><td>OK</td></tr>
                    <tr><td>Hoje</td><td>Atualização visual aplicada</td><td>Em execução</td></tr>
                </tbody>
            </table>
        </div>
        <div class="fe-card">
            <h2 class="fe-section-title">Ações rápidas</h2>
            <div style="display:grid;gap:10px">
                <a class="fe-button" href="/admin/clientes/create">Novo Cliente</a>
                <a class="fe-button secondary" href="/admin/comercial">Abrir Comercial</a>
                <a class="fe-button secondary" href="/admin/factory">Gerar Build</a>
                <a class="fe-button secondary" href="/admin/marketplace">Marketplace</a>
            </div>
        </div>
    </div>
@endsection
