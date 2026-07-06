@extends('layouts.factory-enterprise')

@section('content')
<h1 class="fe-page-title">Factory Studio</h1>
<p class="fe-page-subtitle">Interface tecnológica para acompanhar builds, QA, deploys e logs sem alterar a lógica atual da Factory.</p>

<div class="fe-grid two" style="margin-bottom:18px">
    <div class="fe-card">
        <h2 class="fe-section-title">Build em andamento</h2>
        <div style="display:flex;justify-content:space-between;gap:16px;align-items:flex-end;margin-bottom:14px">
            <div><strong style="font-size:24px">TV Digital Enterprise</strong><div style="color:var(--vitrine-muted);margin-top:4px">Release 10.0</div></div>
            <div style="font-size:34px;font-weight:900">92%</div>
        </div>
        <div style="height:14px;background:rgba(15,23,42,.82);border-radius:999px;overflow:hidden;border:1px solid var(--vitrine-border)">
            <div style="height:100%;width:92%;background:linear-gradient(135deg,var(--vitrine-blue),var(--vitrine-purple));border-radius:999px"></div>
        </div>
        <div class="fe-status-list" style="margin-top:18px">
            <div class="fe-status-item"><span><span class="fe-dot"></span>Assets</span><strong>OK</strong></div>
            <div class="fe-status-item"><span><span class="fe-dot"></span>Banco</span><strong>OK</strong></div>
            <div class="fe-status-item"><span><span class="fe-dot"></span>Backend</span><strong>OK</strong></div>
            <div class="fe-status-item"><span><span class="fe-dot"></span>Frontend</span><strong>OK</strong></div>
            <div class="fe-status-item"><span><span class="fe-dot"></span>Deploy</span><strong>Executando</strong></div>
        </div>
    </div>

    <div class="fe-card">
        <h2 class="fe-section-title">Console</h2>
        <div style="background:#050814;border:1px solid var(--vitrine-border);border-radius:16px;padding:16px;font-family:ui-monospace,monospace;color:#b6c8e5;min-height:300px">
            <div>09:21 · Inicializando build Enterprise...</div>
            <div>09:22 · Compilando assets... OK</div>
            <div>09:23 · Executando validações... OK</div>
            <div>09:24 · Preparando deploy...</div>
            <div style="color:#86efac">09:25 · Pipeline em execução</div>
        </div>
    </div>
</div>

<div class="fe-card">
    <h2 class="fe-section-title">Histórico de Builds</h2>
    <table class="fe-table">
        <thead><tr><th>Versão</th><th>Projeto</th><th>QA</th><th>Deploy</th><th>Tempo</th></tr></thead>
        <tbody>
            <tr><td>10.0.1</td><td>Factory Enterprise</td><td>OK</td><td>Homologação</td><td>3m 28s</td></tr>
            <tr><td>9.9.8</td><td>Factory Enterprise</td><td>OK</td><td>Produção</td><td>4m 12s</td></tr>
        </tbody>
    </table>
</div>
@endsection
