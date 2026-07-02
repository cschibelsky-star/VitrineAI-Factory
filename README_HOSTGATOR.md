# Instalação HostGator — CRM Comercial Teste

1. Envie o ZIP gerado para a pasta do projeto no cPanel.
2. Extraia os arquivos.
3. Configure o `.env` com os dados do banco MySQL.
4. Pelo terminal SSH, execute:

```bash
composer install --no-dev --optimize-autoloader
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
```

5. Aponte o domínio ou subdomínio para a pasta `public`.

Observação: se o Composer não estiver disponível no servidor, rode `composer install` localmente e envie também a pasta `vendor`.