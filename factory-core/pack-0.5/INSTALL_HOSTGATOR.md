# Instalação — Vitrine IA Factory Core Pack 0.5

## 1. Copiar arquivos

Copiar o conteúdo do Pack 0.5 para dentro do projeto Laravel da Factory.

## 2. Executar composer

```bash
composer install --no-dev --optimize-autoloader
```

## 3. Configurar ambiente

```bash
cp .env.example .env
php artisan key:generate
```

Configurar banco MySQL no `.env`.

## 4. Rodar migrations

```bash
php artisan migrate
```

## 5. Rodar seeders

```bash
php artisan db:seed --class=FactoryCorePackSeeder
```

## 6. Limpar cache

```bash
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 7. Validar painel

Abrir o painel Filament e validar os menus:

- Produtos
- Blueprints
- Capabilities
- Engines
- Missions
- Builder

## Observação

Este pack é a base de consolidação. A próxima etapa é incorporar Mission Engine e Agent Engine no mesmo formato.
