<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Factory Enterprise' }} | Vitrine IA Pro</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('factory-enterprise-10/factory-enterprise-10.css') }}">
</head>
<body class="factory-enterprise">
<div class="fe-shell">
    <aside class="fe-sidebar">
        <div class="fe-brand">
            <div class="fe-brand-mark"></div>
            <div>
                <div class="fe-brand-title">Vitrine IA Pro</div>
                <div class="fe-brand-subtitle">Factory Enterprise 10.0</div>
            </div>
        </div>

        <div class="fe-nav-group">Operação</div>
        <a class="fe-nav-link {{ request()->is('admin') ? 'active' : '' }}" href="/admin">⌁ <span>Cockpit</span></a>
        <a class="fe-nav-link" href="/admin/clientes">◉ <span>Clientes</span></a>
        <a class="fe-nav-link" href="/admin/comercial">◆ <span>Comercial</span></a>
        <a class="fe-nav-link" href="/admin/produtos">▣ <span>Produtos</span></a>
        <a class="fe-nav-link" href="/admin/licencas">◇ <span>Licenças</span></a>

        <div class="fe-nav-group">Factory</div>
        <a class="fe-nav-link" href="/admin/factory">▰ <span>Factory Studio</span></a>
        <a class="fe-nav-link" href="/admin/marketplace">✦ <span>Marketplace</span></a>
        <a class="fe-nav-link" href="/admin/analytics">⌬ <span>Analytics</span></a>
        <a class="fe-nav-link" href="/admin/ia-center">✺ <span>IA Center</span></a>

        <div class="fe-nav-group">Sistema</div>
        <a class="fe-nav-link" href="/admin/configuracoes">⚙ <span>Configurações</span></a>
    </aside>

    <main class="fe-main">
        <header class="fe-header">
            <div>
                <strong>Factory Enterprise</strong>
                <div style="font-size:12px;color:var(--vitrine-muted)">Produção · Enterprise 10.0</div>
            </div>
            <form class="fe-search">
                <input type="search" placeholder="Pesquisar clientes, produtos, leads, builds e licenças...">
            </form>
            <div style="display:flex;align-items:center;gap:10px">
                <a class="fe-button secondary" href="/admin/ia-center">IA</a>
                <a class="fe-button secondary" href="/admin/notificacoes">Alertas</a>
                <a class="fe-button" href="/admin/clientes/create">Novo Cliente</a>
            </div>
        </header>

        <section class="fe-content">
            @yield('content')
        </section>
    </main>
</div>
<script src="{{ asset('factory-enterprise-10/factory-enterprise-10.js') }}"></script>
</body>
</html>
