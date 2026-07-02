# Vitrine AI Factory — Evolução Real 002

Este patch evolui a Factory existente para iniciar a criação real de aplicações Laravel + Filament com estrutura instalável.

## O que entra nesta evolução

- `BlueprintAutoFactory`: cria blueprints automáticos por tipo de sistema.
- `RealProjectScaffolder`: gera estrutura base de projeto Laravel.
- `RealCodeGenerator` atualizado:
  - limpa build anterior do mesmo slug;
  - cria estrutura base;
  - gera models;
  - gera migrations;
  - gera policies;
  - gera resources Filament;
  - gera pages Filament;
  - gera seeders;
  - gera README e README_HOSTGATOR;
  - gera `REAL_BUILD_REPORT.json`.
- Novo comando:

```bash
php artisan factory:make-real-project "Meu CRM" --type=crm --zip
```

## Tipos aceitos

```text
saas
crm
portal
tv_digital
guia_digital
compras_ia
```

## Comandos de teste

Gerar SaaS:

```bash
php artisan factory:make-real-project "SaaS Teste" --type=saas --zip
```

Gerar CRM:

```bash
php artisan factory:make-real-project "CRM Comercial" --type=crm --zip
```

Gerar portal:

```bash
php artisan factory:make-real-project "Portal News" --type=portal --zip
```

Gerar TV Digital:

```bash
php artisan factory:make-real-project "TV Digital Demo" --type=tv_digital --zip
```

Gerar Guia Digital:

```bash
php artisan factory:make-real-project "Conheça Sua Cidade" --type=guia_digital --zip
```

Gerar Compras IA:

```bash
php artisan factory:make-real-project "Compras IA" --type=compras_ia --zip
```

## Saída

Os projetos são gerados em:

```text
storage/app/factory/real-builds/{slug}
```

Os ZIPs são gerados em:

```text
storage/app/factory/exports
```

## Instalação no HostGator

1. Subir este patch por cima da Factory atual.
2. Rodar:

```bash
php artisan optimize:clear
composer dump-autoload
```

3. Testar:

```bash
php artisan factory:make-real-project "CRM Comercial" --type=crm --zip
```

## Status real após este patch

A Factory passa a criar um primeiro projeto Laravel/Filament estruturado, com código de CRUD base gerado automaticamente. Ainda não é a versão final autônoma; a próxima evolução deve ligar isso à tela visual do Builder/Studio e melhorar instalação automática do Filament/AdminPanel no projeto exportado.
