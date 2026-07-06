@extends('layouts.factory-enterprise')

@section('content')
<h1 class="fe-page-title">Comercial Enterprise</h1>
<p class="fe-page-subtitle">Pipeline comercial modernizado, preservando leads, propostas e próximas ações já existentes.</p>

<div class="fe-grid kpis" style="grid-template-columns:repeat(4,minmax(0,1fr));margin-bottom:18px">
    <div class="fe-card"><div class="fe-kpi-label">Leads</div><div class="fe-kpi-value">{{ $totalLeads ?? '—' }}</div><div class="fe-kpi-trend">Pipeline</div></div>
    <div class="fe-card"><div class="fe-kpi-label">Propostas</div><div class="fe-kpi-value">{{ $propostas ?? '—' }}</div><div class="fe-kpi-trend">Negociação</div></div>
    <div class="fe-card"><div class="fe-kpi-label">Valor estimado</div><div class="fe-kpi-value">R$ {{ $valorEstimado ?? '—' }}</div><div class="fe-kpi-trend">Receita potencial</div></div>
    <div class="fe-card"><div class="fe-kpi-label">Próximas ações</div><div class="fe-kpi-value">{{ $proximasAcoes ?? '—' }}</div><div class="fe-kpi-trend">Follow-up</div></div>
</div>

<div class="fe-card" style="margin-bottom:18px">
    <h2 class="fe-section-title">Pipeline</h2>
    <div style="display:grid;grid-template-columns:repeat(5,minmax(180px,1fr));gap:14px;overflow:auto">
        <div class="fe-card" style="box-shadow:none"><strong>Novo</strong><p style="color:var(--vitrine-muted)">Leads recebidos</p></div>
        <div class="fe-card" style="box-shadow:none"><strong>Contato</strong><p style="color:var(--vitrine-muted)">Primeiro atendimento</p></div>
        <div class="fe-card" style="box-shadow:none"><strong>Diagnóstico</strong><p style="color:var(--vitrine-muted)">Necessidade identificada</p></div>
        <div class="fe-card" style="box-shadow:none"><strong>Proposta</strong><p style="color:var(--vitrine-muted)">Proposta enviada</p></div>
        <div class="fe-card" style="box-shadow:none"><strong>Negociação</strong><p style="color:var(--vitrine-muted)">Fechamento</p></div>
    </div>
</div>

<div class="fe-card">
    <h2 class="fe-section-title">Leads e oportunidades</h2>
    <table class="fe-table">
        <thead><tr><th>Cliente</th><th>Produto</th><th>Status</th><th>Valor</th><th>Próxima ação</th><th></th></tr></thead>
        <tbody>
            <tr><td>Prefeitura de Demonstração</td><td>TV Digital Enterprise</td><td>Proposta</td><td>R$ 180.000</td><td>Enviar documentação</td><td><a class="fe-button secondary" href="#">Abrir</a></td></tr>
            <tr><td>Empresa Demo</td><td>Guia Digital da Cidade</td><td>Contato</td><td>R$ 25.000</td><td>Agendar apresentação</td><td><a class="fe-button secondary" href="#">Abrir</a></td></tr>
        </tbody>
    </table>
</div>
@endsection
