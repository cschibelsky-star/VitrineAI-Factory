# FACTORY PACKAGE X.6 — Factory Brain Command Center

Aplicar:

```bash
cd ~/factory.vitrineiapro.com.br
unzip -o FACTORY_PACKAGE_X_6.zip -d /tmp/factory-x-6
cp -Rf /tmp/factory-x-6/FACTORY_PACKAGE_X_6/* .
php artisan optimize:clear
git add .
git commit -m "FACTORY X.6 Factory Brain Command Center"
git push origin hostgator-baseline
```

Testar: `/admin/factory-brain`
