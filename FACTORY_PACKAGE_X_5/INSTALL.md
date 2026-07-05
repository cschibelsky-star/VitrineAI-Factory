# FACTORY PACKAGE X.5 — Operations Center

## Aplicar

```bash
cd ~/factory.vitrineiapro.com.br
unzip -o FACTORY_PACKAGE_X_5.zip -d /tmp/factory-x-5
cp -Rf /tmp/factory-x-5/FACTORY_PACKAGE_X_5/* .
php artisan optimize:clear
git add .
git commit -m "FACTORY X.5 Operations Center"
git push origin hostgator-baseline
```

## Testar

/admin/operations-center
