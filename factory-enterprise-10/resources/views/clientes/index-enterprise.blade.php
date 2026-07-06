@extends('layouts.factory-enterprise')

@section('content')
<h1 class="fe-page-title">Clientes 360</h1>
<p class="fe-page-subtitle">Visão modernizada dos clientes, produtos, planos, licenças e status operacional.</p>

<div class="fe-grid kpis" style="grid-template-columns:repeat(4,minmax(0,1fr));margin-bottom:18px">
    <div class="fe-card"><div class="fe-kpi-label">Clientes</div><div class="fe-kpi-value">{{ $totalClientes ?? '—' }}</div><div class="fe-kpi-trend">Base ativa</div></div>
    <div class="fe-card"><div class="fe-kpi-label">Enterprise</div><div class="fe-kpi-value">{{ $clientesEnterprise ?? '—' }}</div><div class="fe-kpi-trend">Planos premium</div></div>
    <div class="fe-card"><div class="fe-kpi-label">Licenças</div><div class="fe-kpi-value">{{ $licencas ?? '—' }}</div><div class="fe-kpi-trend">Ativas</div></div>
    <div class="fe-card"><div class="fe-kpi-label">Implantação</div><div class="fe-kpi-value">{{ $implantacao ?? '—' }}</div><div class="fe-kpi-trend">Em andamento</div></div>
</div>

<div class="fe-card">
    <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;margin-bottom:16px">
        <h2 class="fe-section-title" style="margin:0">Lista de Clientes</h2>
        <a class="fe-button" href="/admin/clientes/create">Novo Cliente</a>
    </div>
    <table class="fe-table">
        <thead><tr><th>Cliente</th><th>Plano</th><th>Produtos</th><th>Licença</th><th>Status</th><th></th></tr></thead>
        <tbody>
            <tr><td>Prefeitura de Demonstração</td><td>Enterprise</td><td>TV Digital · Portal News</td><td>Ativa</td><td>Online</td><td><a class="fe-button secondary" href="#">Abrir 360</a></td></tr>
            <tr><td>Cliente Demo</td><td>Start</td><td>Guia Digital</td><td>Ativa</td><td>Implantação</td><td><a class="fe-button secondary" href="#">Abrir 360</a></td></tr>
        </tbody>
    </table>
</div>
@endsection
