# FACTORY PACKAGE X.3

Aplicação:

```bash
cd ~/factory.vitrineiapro.com.br
unzip -o FACTORY_PACKAGE_X_3.zip -d /tmp/factory-x-3
cp -Rf /tmp/factory-x-3/FACTORY_PACKAGE_X_3/* .
php artisan optimize:clear
git add .
git commit -m "FACTORY X.3 Projetos Enterprise Cards"
git push origin hostgator-baseline
```

Teste: `/admin/projetos-enterprise`
