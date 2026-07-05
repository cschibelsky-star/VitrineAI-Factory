# FACTORY PACKAGE X.7 — Enterprise Integration Foundation

## Aplicação

```bash
cd ~/factory.vitrineiapro.com.br
unzip -o FACTORY_PACKAGE_X_7.zip -d /tmp/factory-x-7
cp -Rf /tmp/factory-x-7/FACTORY_PACKAGE_X_7/* .
php artisan migrate --force
php artisan optimize:clear
git add .
git commit -m "FACTORY X.7 Enterprise Integration Foundation"
git push origin hostgator-baseline
```

## Testes

- /admin/events-center
- /admin/factory-brain
- /admin/operations-center
