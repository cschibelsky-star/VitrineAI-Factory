@extends('layouts.factory-enterprise')

@section('content')
<h1 class="fe-page-title">Marketplace Enterprise</h1>
<p class="fe-page-subtitle">Catálogo visual dos produtos da Vitrine IA Pro, com instalação, atualização e documentação em destaque.</p>

<div class="fe-grid" style="grid-template-columns:repeat(3,minmax(0,1fr));margin-bottom:18px">
    <div class="fe-card">
        <div style="height:130px;border-radius:16px;background:linear-gradient(135deg,rgba(37,167,255,.25),rgba(139,92,246,.25));margin-bottom:16px"></div>
        <h2 class="fe-section-title">TV Digital Enterprise</h2>
        <p style="color:var(--vitrine-muted)">Portal, RSS, IA Editorial, Repórter IA e transmissão ao vivo.</p>
        <div class="fe-status-item"><span>Versão</span><strong>10.0</strong></div>
        <a class="fe-button" href="#">Atualizar</a>
    </div>
    <div class="fe-card">
        <div style="height:130px;border-radius:16px;background:linear-gradient(135deg,rgba(34,197,94,.22),rgba(37,167,255,.18));margin-bottom:16px"></div>
        <h2 class="fe-section-title">Guia Digital da Cidade</h2>
        <p style="color:var(--vitrine-muted)">Turismo, eventos, atrativos, gastronomia e cidade inteligente.</p>
        <div class="fe-status-item"><span>Versão</span><strong>2.0</strong></div>
        <a class="fe-button" href="#">Instalar</a>
    </div>
    <div class="fe-card">
        <div style="height:130px;border-radius:16px;background:linear-gradient(135deg,rgba(245,158,11,.22),rgba(139,92,246,.18));margin-bottom:16px"></div>
        <h2 class="fe-section-title">Portal News AI</h2>
        <p style="color:var(--vitrine-muted)">Portal de notícias automatizado com RSS, SEO e IA editorial.</p>
        <div class="fe-status-item"><span>Versão</span><strong>8.4</strong></div>
        <a class="fe-button" href="#">Documentação</a>
    </div>
</div>
@endsection
