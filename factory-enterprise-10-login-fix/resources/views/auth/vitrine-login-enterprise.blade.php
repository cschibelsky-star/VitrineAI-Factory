@php
    $productName = config('app.product_name', config('app.name', 'Factory Enterprise'));
    $productDescription = config('app.product_description', 'Plataforma operacional inteligente do ecossistema Vitrine IA Pro.');
    $version = config('app.version', '10.0.2 Enterprise');
    $logoPath = public_path('assets/logo-vitrine-ai-pro-header.png');
    $logoUrl = file_exists($logoPath) ? asset('assets/logo-vitrine-ai-pro-header.png') : null;
@endphp
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $productName }} | Vitrine IA Pro</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('factory-enterprise-login/login.css') }}">
</head>
<body class="vitrine-login">
<main class="vip-login-shell">
    <section class="vip-login-left">
        <div>
            @if($logoUrl)
                <img class="vip-logo" src="{{ $logoUrl }}" alt="Vitrine IA Pro">
            @else
                <div class="vip-logo" style="font-size:42px;font-weight:900;letter-spacing:.08em">VITRINE <span style="color:#48b6ff">IA PRO</span></div>
            @endif
            <span class="vip-product-badge">Produto ativo</span>
            <h1 class="vip-product-title">{{ $productName }}</h1>
            <p class="vip-product-desc">{{ $productDescription }}</p>
            <div class="vip-orbit" aria-hidden="true">
                <div class="vip-feature f1"><b>▶</b>Ao vivo</div>
                <div class="vip-feature f2"><b>▥</b>Analytics</div>
                <div class="vip-feature f3"><b>⚙</b>Automação</div>
                <div class="vip-feature f4"><b>$</b>Gestão</div>
                <div class="vip-feature f5"><b>👥</b>Clientes</div>
                <div class="vip-vmark"><span>V</span></div>
            </div>
        </div>
        <div class="vip-help">
            <div>
                <h3>Precisa de ajuda?</h3>
                <p>Nossa equipe está pronta para ajudar no primeiro acesso, dúvidas de senha ou suporte operacional.</p>
                <div class="vip-help-actions">
                    <a class="vip-help-btn whatsapp" href="https://wa.me/5519999999999" target="_blank" rel="noopener">WhatsApp</a>
                    <a class="vip-help-btn" href="mailto:suporte@vitrineiapro.com.br">Suporte</a>
                </div>
            </div>
            <a class="vip-ai" href="mailto:suporte@vitrineiapro.com.br?subject=Assistente%20IA%20-%20Ajuda%20no%20acesso">
                <div class="vip-ai-bot">🤖</div>
                <strong>Assistente IA</strong>
                <small><span class="vip-online"></span>Online agora</small>
            </a>
        </div>
    </section>

    <section class="vip-login-right">
        <form class="vip-login-card" method="POST" action="{{ url()->current() }}">
            @csrf
            <div class="vip-security-icon">🛡</div>
            <h1>Acessar Plataforma</h1>
            <p class="vip-sub">Entre com suas credenciais para continuar</p>
            @if ($errors->any())
                <div style="margin-bottom:18px;color:#fecaca;background:rgba(239,68,68,.12);border:1px solid rgba(239,68,68,.25);padding:12px;border-radius:10px">Dados de acesso inválidos. Verifique e tente novamente.</div>
            @endif
            <label class="vip-field"><input name="email" type="email" value="{{ old('email') }}" required autofocus placeholder="Usuário ou e-mail"></label>
            <label class="vip-field"><input name="password" type="password" required placeholder="Sua senha"></label>
            <div class="vip-row">
                <label><input type="checkbox" name="remember"> Lembrar de mim</label>
                <a href="{{ url('/admin/password-reset/request') }}">Esqueceu sua senha?</a>
            </div>
            <button class="vip-primary" type="submit">Entrar</button>
            <button class="vip-secondary" type="button" onclick="window.location.href='mailto:comercial@vitrineiapro.com.br?subject=Solicitar%20cadastro%20{{ urlencode($productName) }}'">Cadastrar</button>
            <p class="vip-terms">Ao entrar, você concorda com nossos<br><a href="/termos" target="_blank">Termos de Uso</a> e <a href="/privacidade" target="_blank">Política de Privacidade</a>.</p>
        </form>
    </section>
</main>
<footer class="vip-footer">
    <span>© {{ date('Y') }} Vitrine IA Pro. Todos os direitos reservados.</span>
    <span>🛡 Segurança Garantida</span>
    <span>🔒 Dados Protegidos</span>
    <span>Ambiente 100% Seguro</span>
    <span>Versão: {{ $version }}</span>
</footer>
</body>
</html>
