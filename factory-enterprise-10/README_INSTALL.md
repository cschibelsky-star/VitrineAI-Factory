# FACTORY_ENTERPRISE_10.0_FULL_PATCH

Atualização visual Enterprise para a VitrineAI-Factory.

## Objetivo
Aplicar nova identidade Vitrine IA Pro, layout Enterprise, dashboard executivo e telas modernizadas sem alterar a lógica de negócio existente.

## Arquivos principais
- resources/css/factory-enterprise-10.css
- resources/views/layouts/factory-enterprise.blade.php
- resources/views/dashboard-enterprise.blade.php
- resources/views/comercial/index-enterprise.blade.php
- resources/views/clientes/index-enterprise.blade.php
- resources/views/factory/studio-enterprise.blade.php
- resources/views/marketplace/index-enterprise.blade.php
- resources/views/analytics/index-enterprise.blade.php
- public/factory-enterprise-10/factory-enterprise-10.js

## Aplicação manual no servidor
1. Fazer backup completo dos arquivos e banco.
2. Copiar a pasta `factory-enterprise-10` para a raiz do projeto.
3. Mover o CSS para `public/factory-enterprise-10/factory-enterprise-10.css` caso necessário.
4. Copiar as views Enterprise para `resources/views`.
5. Ajustar as rotas ou controllers para apontar para as novas views Enterprise.
6. Executar limpeza de cache:

```bash
php artisan view:clear
php artisan route:clear
php artisan config:clear
php artisan cache:clear
```

## Observação
Este patch é uma camada visual. Ele não remove tabelas, não altera regras comerciais e não muda a estrutura de dados da Factory atual.
