@extends('layouts.factory-enterprise')

@section('content')
<h1 class="fe-page-title">Analytics Enterprise</h1>
<p class="fe-page-subtitle">Cockpit executivo para receita, clientes, produtos, performance e consumo de IA.</p>

<div class="fe-grid kpis" style="grid-template-columns:repeat(5,minmax(0,1fr));margin-bottom:18px">
    <div class="fe-card"><div class="fe-kpi-label">MRR</div><div class="fe-kpi-value">R$ {{ $mrr ?? '—' }}</div><div class="fe-kpi-trend">Receita</div></div>
    <div class="fe-card"><div class="fe-kpi-label">ARR</div><div class="fe-kpi-value">R$ {{ $arr ?? '—' }}</div><div class="fe-kpi-trend">Anual</div></div>
    <div class="fe-card"><div class="fe-kpi-label">Clientes</div><div class="fe-kpi-value">{{ $clientes ?? '—' }}</div><div class="fe-kpi-trend">Ativos</div></div>
    <div class="fe-card"><div class="fe-kpi-label">Produtos</div><div class="fe-kpi-value">{{ $produtos ?? '—' }}</div><div class="fe-kpi-trend">Instalados</div></div>
    <div class="fe-card"><div class="fe-kpi-label">IA</div><div class="fe-kpi-value">{{ $ia ?? '—' }}</div><div class="fe-kpi-trend">Execuções</div></div>
</div>

<div class="fe-grid two">
    <div class="fe-card">
        <h2 class="fe-section-title">Receita e evolução</h2>
        <div style="height:300px;border-radius:16px;background:linear-gradient(135deg,rgba(37,167,255,.18),rgba(139,92,246,.12));display:flex;align-items:center;justify-content:center;color:var(--vitrine-muted)">Gráfico de receita</div>
    </div>
    <div class="fe-card">
        <h2 class="fe-section-title">Produtos mais fortes</h2>
        <div class="fe-status-list">
            <div class="fe-status-item"><span>TV Digital Enterprise</span><strong>Alta</strong></div>
            <div class="fe-status-item"><span>Guia Digital da Cidade</span><strong>Alta</strong></div>
            <div class="fe-status-item"><span>Portal News AI</span><strong>Média</strong></div>
            <div class="fe-status-item"><span>Gov360</span><strong>Roadmap</strong></div>
        </div>
    </div>
</div>
@endsection
