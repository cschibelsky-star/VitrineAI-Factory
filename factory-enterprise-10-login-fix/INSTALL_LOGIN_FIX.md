# FACTORY_ENTERPRISE_10.0.2_LOGIN_FIX

Correção da tela de login para o padrão oficial Vitrine IA Pro.

## Aplicação na HostGator

Na raiz do Laravel:

```bash
cd ~/vitrine-ai-pro
mkdir -p public/factory-enterprise-login
mkdir -p resources/views/auth
mkdir -p resources/views/vendor/filament-panels/pages/auth
cp factory-enterprise-10-login-fix/public/factory-enterprise-login/login.css public/factory-enterprise-login/login.css
cp factory-enterprise-10-login-fix/resources/views/auth/vitrine-login-enterprise.blade.php resources/views/auth/vitrine-login-enterprise.blade.php
cp factory-enterprise-10-login-fix/resources/views/filament-login-override.blade.php resources/views/auth/login.blade.php
cp factory-enterprise-10-login-fix/resources/views/filament-login-override.blade.php resources/views/vendor/filament-panels/pages/auth/login.blade.php
php artisan view:clear
php artisan route:clear
php artisan config:clear
php artisan cache:clear
php artisan optimize:clear
```

## Resultado

A rota `/admin/login` deve assumir o padrão visual:

- lado esquerdo com logo Vitrine IA Pro;
- nome do produto ativo;
- descrição do produto;
- ícones de funcionalidades;
- WhatsApp;
- Suporte;
- Assistente IA;
- lado direito com formulário limpo de acesso.

## Observação

Se o logo não aparecer, coloque o arquivo `logo-vitrine-ai-pro-header.png` em:

```bash
public/assets/logo-vitrine-ai-pro-header.png
```
