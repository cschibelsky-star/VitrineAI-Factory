<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CRM Comercial Teste</title>
    <style>
        body{font-family:Arial,Helvetica,sans-serif;background:#0f172a;color:#e5e7eb;margin:0;display:flex;min-height:100vh;align-items:center;justify-content:center}
        .card{max-width:760px;padding:40px;border-radius:24px;background:#111827;border:1px solid #334155;box-shadow:0 20px 60px rgba(0,0,0,.35)}
        h1{font-size:38px;margin:0 0 12px}.badge{display:inline-block;padding:8px 12px;border-radius:999px;background:#1f2937;color:#93c5fd;margin-bottom:18px}
        p{line-height:1.7;color:#cbd5e1}.ok{color:#86efac;font-weight:700}
    </style>
</head>
<body>
    <main class="card">
        <span class="badge">Vitrine AI Factory</span>
        <h1>{{ $appName ?? 'CRM Comercial Teste' }}</h1>
        <p class="ok">Projeto gerado com sucesso.</p>
        <p>Esta aplicação foi criada automaticamente pela Factory. Próximos passos: configurar banco de dados, rodar migrations e acessar o painel Filament.</p>
    </main>
</body>
</html>